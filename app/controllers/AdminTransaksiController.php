<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/Transaksi.php';

class AdminTransaksiController extends BaseController
{
    private $transaksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->transaksiModel = new Transaksi();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $search = $_GET['q'] ?? '';
        $filter_status = strtolower($_GET['status'] ?? '');
        $where_clauses = [];
        $params = [];

        if ($filter_status !== '' && in_array($filter_status, ['pending', 'success', 'cancelled'])) {
            $where_clauses[] = "LOWER(t.status) = ?";
            $params[] = $filter_status;
        }
        if ($search !== '') {
            $where_clauses[] = "(u.name LIKE ? OR p.nama_produk LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $where = "";
        $join = " JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id";
        if (!empty($where_clauses)) {
            $where = " WHERE " . implode(" AND ", $where_clauses);
        }

        $paging = paginate($this->db, "SELECT COUNT(*) as c FROM transaksi t" . $join . $where, $params, 10);
        $rows = $this->db->fetchAll(
            "SELECT t.*, u.name, p.nama_produk FROM transaksi t" . $join . $where . " ORDER BY t.tanggal DESC, t.id DESC LIMIT ? OFFSET ?",
            array_merge($params, [$paging['limit'], $paging['offset']])
        );

        // Get status counts
        $status_counts = [];
        $rows_status = $this->db->fetchAll("SELECT LOWER(status) as status, COUNT(*) as total FROM transaksi GROUP BY LOWER(status)", []);
        foreach ($rows_status as $rs) {
            $status_counts[$rs['status']] = (int) $rs['total'];
        }
        $total_transaksi = array_sum($status_counts);

        $extra_css = 'input[type=text],input[type=email],input[type=password],input[type=number],textarea,input[type=file],select { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus,select:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';

        $this->view('admin/transaksi/index', [
            'rows' => $rows,
            'paging' => $paging,
            'status_counts' => $status_counts,
            'total_transaksi' => $total_transaksi,
            'current_status' => $filter_status,
            'search' => $search,
            'active_page' => 'transaksi',
            'page_title' => 'Kelola Transaksi',
            'extra_css' => $extra_css
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $this->handleUpdateStatus();
        } elseif ($action === 'hapus') {
            $this->handleHapus();
        }

        $this->redirect('/admin-transaksi');
    }

    private function handleUpdateStatus()
    {
        $id = (int) ($_POST['transaksi_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if ($status !== '' && in_array($status, ['pending', 'success', 'cancelled'])) {
            $this->transaksiModel->updateStatusById($id, $status);
            flash('success', 'Status transaksi berhasil diupdate!');
        }
    }

    private function handleHapus()
    {
        $id = (int) ($_POST['transaksi_id'] ?? 0);
        $this->transaksiModel->delete($id);
        flash('success', 'Transaksi berhasil dihapus.');
    }
}
