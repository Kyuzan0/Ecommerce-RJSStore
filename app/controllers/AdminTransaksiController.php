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
        $filter_status = $_GET['status'] ?? '';

        // Validate status filter
        $valid_statuses = array_keys(status_transaksi_list());
        if ($filter_status !== '' && !in_array($filter_status, $valid_statuses)) {
            $filter_status = '';
        }

        // Count for pagination
        $total = $this->transaksiModel->countAdminList(
            $search !== '' ? $search : null,
            $filter_status !== '' ? $filter_status : null
        );

        $per_page = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total_pages = max(1, (int) ceil($total / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $paging = [
            'page'        => $page,
            'per_page'    => $per_page,
            'total'       => $total,
            'total_pages' => $total_pages,
            'offset'      => $offset,
            'limit'       => $per_page,
        ];

        // Fetch transactions
        $transactions = $this->transaksiModel->getAdminList(
            $search !== '' ? $search : null,
            $filter_status !== '' ? $filter_status : null,
            $per_page,
            $offset
        );

        // Status counts for filter tabs
        $status_counts = [];
        foreach ($valid_statuses as $s) {
            $status_counts[$s] = $this->transaksiModel->countAdminList(
                $search !== '' ? $search : null,
                $s
            );
        }
        $total_transaksi = $this->transaksiModel->countAdminList(
            $search !== '' ? $search : null,
            null
        );

        $this->view('admin/transaksi/index', [
            'transactions'    => $transactions,
            'paging'          => $paging,
            'status_counts'   => $status_counts,
            'total_transaksi' => $total_transaksi,
            'current_status'  => $filter_status,
            'search'          => $search,
            'active_page'     => 'transaksi',
            'page_title'      => 'Kelola Transaksi',
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $this->handleUpdateStatus();
        } elseif ($action === 'hapus_bulk') {
            $this->handleHapusBulk();
        }

        $this->redirect('/admin-transaksi');
    }

    private function handleUpdateStatus()
    {
        $id = (int) ($_POST['transaksi_id'] ?? 0);
        $new_status = $_POST['new_status'] ?? '';

        $valid_statuses = array_keys(status_transaksi_list());
        if ($id <= 0 || !in_array($new_status, $valid_statuses)) {
            flash('error', 'Data tidak valid.');
            return;
        }

        $this->transaksiModel->updateStatusById($id, $new_status);
        flash('success', 'Status transaksi berhasil diperbarui.');
    }

    private function handleHapusBulk()
    {
        $ids = $_POST['transaksi_ids'] ?? [];
        if (empty($ids)) {
            flash('error', 'Tidak ada transaksi yang dipilih.');
            return;
        }

        $deleted = 0;
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) continue;
            $this->transaksiModel->delete($id);
            $deleted++;
        }

        flash('success', $deleted . ' transaksi berhasil dihapus.');
    }
}
