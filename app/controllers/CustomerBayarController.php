<?php

require_once __DIR__ . '/../core/BaseController.php';

class CustomerBayarController extends BaseController
{
    private $transaksiModel;
    private $userModel;
    private MidtransService $midtrans;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        
        require_once __DIR__ . '/../models/Transaksi.php';
        require_once __DIR__ . '/../models/User.php';
        
        $this->transaksiModel = new Transaksi();
        $this->userModel = new User();
        $this->midtrans = new MidtransService();
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
                'name' => substr($item['nama_produk'], 0, 50)
            ];
        }

        $customer_details = [
            'first_name' => $user_name,
            'email' => $user_email
        ];

        // Request Snap token via the shared service
        $snap_token = $this->midtrans->createSnapToken($order_id, (int)$total, $item_details, $customer_details);

        if (empty($snap_token)) {
            flash('error', 'Gagal menghubungi payment gateway');
            $this->redirect('/customer/pembelian?msg=error');
            return;
        }

        // Render payment page
        $this->view('customer/bayar', [
            'items' => $items,
            'total' => $total,
            'snap_token' => $snap_token,
            'snap_url' => $this->midtrans->getSnapJsUrl(),
            'client_key' => $this->midtrans->getClientKey()
        ], 'checkout');
    }
}
