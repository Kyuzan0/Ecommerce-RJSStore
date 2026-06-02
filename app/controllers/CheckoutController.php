<?php

class CheckoutController extends BaseController
{
    private Keranjang $keranjangModel;
    private Transaksi $transaksiModel;
    private MidtransService $midtrans;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        $this->keranjangModel = new Keranjang();
        $this->transaksiModel = new Transaksi();
        $this->midtrans = new MidtransService();
    }

    /**
     * Checkout page — order summary + Midtrans payment.
     */
    public function index(): void
    {
        $userId = $this->auth->id();

        // Get cart items
        $cartItems = $this->keranjangModel->getByUser($userId);

        if (empty($cartItems)) {
            flash('warning', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
            $this->redirect('/customer/keranjang');
            return;
        }

        $totalHarga = 0;
        foreach ($cartItems as $item) {
            $totalHarga += (int) $item['harga'];
        }

        $snapToken = '';
        $orderRef  = '';

        // Process checkout on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_checkout'])) {
            $this->csrfValidate();

            $orderRef = 'ORD-' . $userId . '-' . time();

            // Create transactions with DB transaction (bug fix: was manual DELETE rollback)
            $success = $this->transaksiModel->createOrder($userId, $cartItems, $orderRef);

            if (!$success) {
                flash('error', 'Gagal membuat pesanan. Silakan coba lagi.');
                $this->redirect('/customer/checkout');
                return;
            }

            ActivityLog::log('checkout', 'transaksi', null, 'Customer created order ' . $orderRef);

            // Get actual user email (bug fix: was hardcoded 'customer@example.com')
            $user = (new User())->find($userId);
            $userEmail = $user['email'] ?? 'customer@example.com';

            // Call Midtrans Snap API
            $snapToken = $this->getMidtransToken($orderRef, $totalHarga, $cartItems, $userEmail);

            if (!$snapToken) {
                // Rollback: delete transactions
                $this->db->execute("DELETE FROM transaksi WHERE order_ref = ?", [$orderRef]);
                flash('error', 'Gagal menyambung ke payment gateway. Silakan coba lagi.');
                $this->redirect('/customer/checkout');
                return;
            }

            // Clear cart on successful token generation
            $this->keranjangModel->clearByUser($userId);

            // Store active order reference in session for callback fallback
            $_SESSION['active_order_ref'] = $orderRef;
        }

        // Midtrans Snap JS URL
        $snapUrl = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true'
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';

        $this->view('checkout/index', [
            'page_title'  => 'Checkout - RJSStore',
            'cart_items'   => $cartItems,
            'total_harga'  => $totalHarga,
            'snap_token'   => $snapToken,
            'snap_url'     => $snapUrl,
            'client_key'   => env('MIDTRANS_CLIENT_KEY'),
            'order_ref'    => $orderRef,
        ], 'checkout');
    }

    /**
     * Handle payment return from frontend (Midtrans Snap onSuccess).
     *
     * SECURITY: We do NOT trust the frontend's claim of success. We verify the
     * real transaction status server-to-server with Midtrans before changing
     * anything. The frontend redirect only tells us *which* order to re-check.
     */
    public function callback(): void
    {
        $userId   = $this->auth->id();
        $orderRef = $_GET['order_id'] ?? $_SESSION['active_order_ref'] ?? '';

        // Clean up the session reference
        if (isset($_SESSION['active_order_ref'])) {
            unset($_SESSION['active_order_ref']);
        }

        if (empty($orderRef)) {
            flash('error', 'Referensi pembayaran tidak valid.');
            $this->redirect('/customer/pembelian');
            return;
        }

        // Strip retry suffix (e.g. -r1234567) to get the original order_ref or ID in database
        $dbOrderRef = preg_replace('/-r\d+$/', '', $orderRef);

        // Ensure the order belongs to the current user
        if (strpos($dbOrderRef, 'ORD-') === 0) {
            $owned = $this->db->fetchOne(
                "SELECT id FROM transaksi WHERE order_ref = ? AND user_id = ? LIMIT 1",
                [$dbOrderRef, $userId]
            );
        } else {
            $owned = $this->db->fetchOne(
                "SELECT id FROM transaksi WHERE id = ? AND user_id = ? LIMIT 1",
                [(int) $dbOrderRef, $userId]
            );
        }

        if (!$owned) {
            flash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/customer/pembelian');
            return;
        }

        // Verify the real status with Midtrans (using the raw orderRef with suffix, since Midtrans knows it as orderRef)
        $statusPayload = $this->midtrans->getTransactionStatus($orderRef);
        $status = $statusPayload ? $this->midtrans->mapStatus($statusPayload) : null;

        if ($status === 'success') {
            if (strpos($dbOrderRef, 'ORD-') === 0) {
                $this->transaksiModel->updateStatusByRef($dbOrderRef, 'success');
            } else {
                $this->transaksiModel->updateStatusById((int) $dbOrderRef, 'success');
            }
            flash('success', 'Pembayaran berhasil! Terima kasih atas pembelian Anda.');
        } elseif ($status === 'pending') {
            flash('info', 'Pembayaran Anda sedang diproses. Status akan diperbarui otomatis setelah pembayaran dikonfirmasi.');
        } elseif ($status === 'failed') {
            if (strpos($dbOrderRef, 'ORD-') === 0) {
                $this->transaksiModel->updateStatusByRef($dbOrderRef, 'failed');
            } else {
                $this->transaksiModel->updateStatusById((int) $dbOrderRef, 'failed');
            }
            flash('error', 'Pembayaran gagal atau dibatalkan.');
        } else {
            // Could not verify (network/Midtrans issue) — leave status untouched.
            flash('info', 'Status pembayaran sedang diverifikasi. Silakan cek kembali beberapa saat lagi.');
        }

        $this->redirect('/customer/pembelian');
    }

    /**
     * Get Midtrans Snap token via the shared service.
     */
    private function getMidtransToken(string $orderRef, int $totalHarga, array $cartItems, string $email): ?string
    {
        $midtransItems = [];
        foreach ($cartItems as $item) {
            $name = $item['nama_produk'];
            if (!empty($item['durasi'])) {
                $name .= ' (' . $item['durasi'];
                if (!empty($item['paket'])) {
                    $name .= ' ' . $item['paket'];
                }
                $name .= ')';
            }
            $midtransItems[] = [
                'id'       => (string) $item['produk_id'],
                'price'    => (int) $item['harga'],
                'quantity' => 1,
                'name'     => substr($name, 0, 50),
            ];
        }

        $customer = [
            'first_name' => $this->auth->user()['name'],
            'email'      => $email,
        ];

        return $this->midtrans->createSnapToken($orderRef, $totalHarga, $midtransItems, $customer);
    }
}
