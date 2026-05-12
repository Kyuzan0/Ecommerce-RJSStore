<?php

class CheckoutController extends BaseController
{
    private Keranjang $keranjangModel;
    private Transaksi $transaksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        $this->keranjangModel = new Keranjang();
        $this->transaksiModel = new Transaksi();
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
     * Handle payment success callback from frontend.
     * Called via GET redirect after Midtrans Snap onSuccess.
     * Directly marks the transaction as 'success'.
     */
    public function callback(): void
    {
        $userId = $this->auth->id();
        $orderRef = $_GET['order_id'] ?? '';

        if (!empty($orderRef)) {
            // Update status to success
            if (strpos($orderRef, 'ORD-') === 0) {
                // Verify this order belongs to the current user before updating
                $items = $this->db->fetchAll(
                    "SELECT id FROM transaksi WHERE order_ref = ? AND user_id = ? AND status = 'pending'",
                    [$orderRef, $userId]
                );
                if (!empty($items)) {
                    $this->transaksiModel->updateStatusByRef($orderRef, 'success');
                }
            } else {
                // Legacy single transaction ID
                $txId = (int)$orderRef;
                $item = $this->db->fetchOne(
                    "SELECT id FROM transaksi WHERE id = ? AND user_id = ? AND status = 'pending'",
                    [$txId, $userId]
                );
                if ($item) {
                    $this->transaksiModel->updateStatusById($txId, 'success');
                }
            }
        }

        flash('success', 'Pembayaran berhasil! Terima kasih atas pembelian Anda.');
        $this->redirect('/customer/pembelian');
    }

    /**
     * Get Midtrans Snap token via API.
     */
    private function getMidtransToken(string $orderRef, int $totalHarga, array $cartItems, string $email): ?string
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');

        $midtransItems = [];
        foreach ($cartItems as $item) {
            $midtransItems[] = [
                'id'       => (string) $item['produk_id'],
                'price'    => (int) $item['harga'],
                'quantity' => 1,
                'name'     => substr($item['nama_produk'], 0, 50),
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderRef,
                'gross_amount' => $totalHarga,
            ],
            'item_details'     => $midtransItems,
            'customer_details' => [
                'first_name' => $this->auth->user()['name'],
                'email'      => $email,
            ],
        ];

        $baseUrl = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true'
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/snap/v1/transactions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response);
        return $data->token ?? null;
    }
}
