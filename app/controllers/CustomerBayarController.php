<?php

require_once __DIR__ . '/../core/BaseController.php';

class CustomerBayarController extends BaseController
{
    private $transaksiModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        
        require_once __DIR__ . '/../models/Transaksi.php';
        require_once __DIR__ . '/../models/User.php';
        
        $this->transaksiModel = new Transaksi();
        $this->userModel = new User();
    }

    public function index()
    {
        $user_id = $this->auth->id();
        $order_ref = $_GET['ref'] ?? '';
        $transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Get pending items
        if (!empty($order_ref) && strpos($order_ref, 'ORD-') === 0) {
            $items = $this->transaksiModel->getPendingByRef($order_ref, $user_id);
        } elseif ($transaksi_id > 0) {
            $single = $this->transaksiModel->getPendingById($transaksi_id, $user_id);
            $items = $single ? [$single] : [];
        } else {
            flash('error', 'Data pembayaran tidak valid');
            $this->redirect('/customer/pembelian');
            return;
        }

        if (empty($items)) {
            flash('error', 'Transaksi tidak ditemukan atau sudah dibayar');
            $this->redirect('/customer/pembelian');
            return;
        }

        // Calculate total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['harga'];
        }

        // Get actual user data
        $user = $this->userModel->find($user_id);
        $user_email = $user['email'];
        $user_name = $user['name'];

        // Build Midtrans payload
        $order_id = $items[0]['order_ref'] ?? 'TRX-' . $items[0]['id'];
        
        $item_details = [];
        foreach ($items as $item) {
            $item_details[] = [
                'id' => $item['produk_id'],
                'price' => (int)$item['harga'],
                'quantity' => 1,
                'name' => $item['nama_produk']
            ];
        }

        $transaction_details = [
            'order_id' => $order_id,
            'gross_amount' => (int)$total
        ];

        $customer_details = [
            'first_name' => $user_name,
            'email' => $user_email
        ];

        $payload = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details
        ];

        // Call Midtrans Snap API
        $isProduction = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true';
        $snap_url = $isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
        $snap_js_url = $isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
        $server_key = env('MIDTRANS_SERVER_KEY');
        $client_key = env('MIDTRANS_CLIENT_KEY');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $snap_url . '/transactions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($server_key . ':')
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 201) {
            flash('error', 'Gagal menghubungi payment gateway');
            $this->redirect('/customer/pembelian?msg=error');
            return;
        }

        $result = json_decode($response, true);
        $snap_token = $result['token'] ?? '';

        if (empty($snap_token)) {
            flash('error', 'Gagal mendapatkan token pembayaran');
            $this->redirect('/customer/pembelian?msg=error');
            return;
        }

        // Render payment page
        $this->view('customer/bayar', [
            'items' => $items,
            'total' => $total,
            'snap_token' => $snap_token,
            'snap_url' => $snap_js_url,
            'client_key' => $client_key
        ], 'checkout');
    }
}
