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
        ], 'checkout');
    }

    /**
     * Handle payment callback from frontend (onSuccess).
     * Updates transaction status to 'success' immediately.
     */
    public function callback(): void
    {
        $this->requirePost();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data) {
            $this->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            return;
        }

        $userId = $this->auth->id();
        $orderRef = $data['order_id'] ?? '';
        $transactionStatus = $data['transaction_status'] ?? '';

        if (empty($orderRef)) {
            $this->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
            return;
        }

        // Only update to success if Midtrans reports settlement/capture
        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            $status = 'success';
        } elseif ($transactionStatus === 'pending') {
            $status = 'pending';
        } else {
            // Fallback: if status_code is 200, treat as success
            $statusCode = $data['status_code'] ?? '';
            if ($statusCode === '200') {
                $status = 'success';
            } else {
                $this->json(['status' => 'ok', 'message' => 'No update needed']);
                return;
            }
        }

        // Update by order_ref or legacy single ID
        if (strpos($orderRef, 'ORD-') === 0) {
            $this->transaksiModel->updateStatusByRef($orderRef, $status);
        } else {
            $this->transaksiModel->updateStatusById((int)$orderRef, $status);
        }

        $this->json(['status' => 'ok', 'new_status' => $status]);
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
