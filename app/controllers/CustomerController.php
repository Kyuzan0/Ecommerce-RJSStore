<?php

require_once __DIR__ . '/../core/BaseController.php';

class CustomerController extends BaseController
{
    private $userModel;
    private $produkModel;
    private $keranjangModel;
    private $transaksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Produk.php';
        require_once __DIR__ . '/../models/Keranjang.php';
        require_once __DIR__ . '/../models/Transaksi.php';
        
        $this->userModel = new User();
        $this->produkModel = new Produk();
        $this->keranjangModel = new Keranjang();
        $this->transaksiModel = new Transaksi();
    }

    public function dashboard()
    {
        $user_id = $this->auth->id();

        // Stats
        $total_transaksi = $this->transaksiModel->countByUser($user_id);
        $total_success = $this->transaksiModel->countByUser($user_id, 'success');
        $total_pending = $this->transaksiModel->countByUser($user_id, 'pending');
        $total_spent = $this->transaksiModel->getTotalSpentByUser($user_id);
        $total_download = $this->transaksiModel->countDownloadable($user_id);

        // Recent transactions (last 5)
        $recent_transactions = $this->transaksiModel->getByUser($user_id, null, 5, 0);

        // Downloadable products (last 4)
        $downloadable_items = $this->transaksiModel->getDownloadable($user_id, 4, 0);

        // Latest products (recommendations - newest 4)
        $latest_products = $this->produkModel->search('', 4, 0);

        // Pre-fetch purchased product IDs for recommendation badges
        $purchased_items = $this->db->fetchAll(
            "SELECT DISTINCT produk_id FROM transaksi WHERE user_id = ? AND status IN ('pending', 'success')",
            [$user_id]
        );
        $purchased_produk_ids = array_column($purchased_items, 'produk_id');

        $this->view('customer/dashboard', [
            'active_page' => 'dashboard',
            'page_title' => 'Dashboard',
            'total_transaksi' => $total_transaksi,
            'total_success' => $total_success,
            'total_pending' => $total_pending,
            'total_spent' => $total_spent,
            'total_download' => $total_download,
            'recent_transactions' => $recent_transactions,
            'downloadable_items' => $downloadable_items,
            'latest_products' => $latest_products,
            'purchased_produk_ids' => $purchased_produk_ids,
        ], 'customer');
    }

    public function produk()
    {
        $user_id = $this->auth->id();
        $search = $_GET['search'] ?? '';

        // Pre-fetch cart product IDs
        $cart_items = $this->db->fetchAll(
            "SELECT produk_id FROM keranjang WHERE user_id = ?",
            [$user_id]
        );
        $cart_produk_ids = array_column($cart_items, 'produk_id');

        // Pre-fetch purchased product IDs
        $purchased_items = $this->db->fetchAll(
            "SELECT DISTINCT produk_id FROM transaksi WHERE user_id = ? AND status = 'success'",
            [$user_id]
        );
        $purchased_produk_ids = array_column($purchased_items, 'produk_id');

        // Pagination
        $count_params = [];
        $count_sql = "SELECT COUNT(*) as c FROM produk";
        if ($search !== '') {
            $count_sql .= " WHERE nama_produk LIKE ? OR deskripsi LIKE ?";
            $count_params = ['%' . $search . '%', '%' . $search . '%'];
        }
        $paging = paginate($this->db, $count_sql, $count_params, 12);

        // Get products with rating subquery
        $products = $this->produkModel->search($search, $paging['limit'], $paging['offset']);

        $this->view('customer/produk', [
            'products' => $products,
            'paging' => $paging,
            'search' => $search,
            'cart_produk_ids' => $cart_produk_ids,
            'purchased_produk_ids' => $purchased_produk_ids,
            'active_page' => 'produk',
            'page_title' => 'Produk',
            'extra_css' => '.product-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); transition: all 0.3s ease; }'
        ], 'customer');
    }

    public function keranjang()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleKeranjang();
            return;
        }

        $user_id = $this->auth->id();
        $items = $this->keranjangModel->getByUser($user_id);

        $this->view('customer/keranjang', [
            'items' => $items,
            'active_page' => 'keranjang',
            'page_title' => 'Keranjang'
        ], 'customer');
    }

    private function handleKeranjang()
    {
        $this->csrfValidate();
        $user_id = $this->auth->id();
        $action = $_POST['action'] ?? '';

        if ($action === 'hapus_item') {
            $produk_id = (int)($_POST['produk_id'] ?? 0);
            if ($produk_id > 0) {
                $this->keranjangModel->removeItem($user_id, $produk_id);
                flash('success', 'Produk berhasil dihapus dari keranjang');
            }
        } elseif ($action === 'kosongkan') {
            $this->keranjangModel->clearByUser($user_id);
            flash('success', 'Keranjang berhasil dikosongkan');
        }

        $this->redirect('/customer/keranjang');
    }

    public function pembelian()
    {
        $user_id = $this->auth->id();
        $status = $_GET['status'] ?? '';

        // Handle error message from payment
        if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
            flash('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        }

        // Pagination
        $count_sql = "SELECT COUNT(*) as c FROM transaksi WHERE user_id = ?";
        $count_params = [$user_id];
        if ($status !== '' && in_array($status, ['pending', 'success', 'cancelled'])) {
            $count_sql .= " AND status = ?";
            $count_params[] = $status;
        }
        $paging = paginate($this->db, $count_sql, $count_params, 10);

        // Get transactions
        $filter_status = ($status !== '' && in_array($status, ['pending', 'success', 'cancelled'])) ? $status : null;
        $transactions = $this->transaksiModel->getByUser($user_id, $filter_status, $paging['limit'], $paging['offset']);

        // Group transactions by order_ref
        $grouped = [];
        foreach ($transactions as $item) {
            $ref = $item['order_ref'] ?? 'single_' . $item['id'];
            if (!isset($grouped[$ref])) {
                $grouped[$ref] = [
                    'order_ref' => $ref,
                    'status' => $item['status'],
                    'tanggal' => $item['tanggal'],
                    'items' => [],
                    'total' => 0
                ];
            }
            $grouped[$ref]['items'][] = $item;
            $grouped[$ref]['total'] += $item['harga'];
        }

        $this->view('customer/pembelian', [
            'grouped_transactions' => array_values($grouped),
            'paging' => $paging,
            'current_status' => $status,
            'active_page' => 'pembelian',
            'page_title' => 'Pembelian'
        ], 'customer');
    }

    public function download()
    {
        $user_id = $this->auth->id();

        // Pagination
        $paging = paginate(
            $this->db,
            "SELECT COUNT(*) as c FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.user_id = ? AND t.status = 'success' AND p.file_upload IS NOT NULL AND p.file_upload != ''",
            [$user_id],
            12
        );

        // Get downloadable items
        $items = $this->transaksiModel->getDownloadable($user_id, $paging['limit'], $paging['offset']);

        $this->view('customer/download', [
            'items' => $items,
            'paging' => $paging,
            'active_page' => 'download',
            'page_title' => 'Download'
        ], 'customer');
    }

    public function profile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfile();
            return;
        }

        $user = $this->userModel->find($this->auth->id());

        $this->view('customer/profile', [
            'user' => $user,
            'active_page' => 'profile',
            'page_title' => 'Profile'
        ], 'customer');
    }

    private function handleProfile()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';
        $user_id = $this->auth->id();

        if ($action === 'update_profile') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($name) || empty($email)) {
                flash('error', 'Nama dan email harus diisi');
                $this->redirect('/customer/profile');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Format email tidak valid');
                $this->redirect('/customer/profile');
                return;
            }

            // Check email uniqueness (exclude current user)
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$email, $user_id]
            );

            if ($existing) {
                flash('error', 'Email sudah digunakan oleh pengguna lain');
                $this->redirect('/customer/profile');
                return;
            }

            $this->db->execute(
                "UPDATE users SET name = ?, email = ? WHERE id = ?",
                [$name, $email, $user_id]
            );

            // Update session name
            $_SESSION['name'] = $name;

            flash('success', 'Profile berhasil diperbarui');
        } elseif ($action === 'update_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                flash('error', 'Semua field password harus diisi');
                $this->redirect('/customer/profile');
                return;
            }

            // Get current user
            $user = $this->userModel->find($user_id);

            // Verify current password (support both password_hash and MD5)
            $password_valid = false;
            if (password_verify($current_password, $user['password'])) {
                $password_valid = true;
            } elseif (md5($current_password) === $user['password']) {
                $password_valid = true;
            }

            if (!$password_valid) {
                flash('error', 'Password saat ini tidak sesuai');
                $this->redirect('/customer/profile');
                return;
            }

            if (strlen($new_password) < 6) {
                flash('error', 'Password baru minimal 6 karakter');
                $this->redirect('/customer/profile');
                return;
            }

            if ($new_password !== $confirm_password) {
                flash('error', 'Konfirmasi password tidak sesuai');
                $this->redirect('/customer/profile');
                return;
            }

            // Update password with password_hash
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $this->db->execute(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashed, $user_id]
            );

            flash('success', 'Password berhasil diperbarui');
        }

        $this->redirect('/customer/profile');
    }
}
