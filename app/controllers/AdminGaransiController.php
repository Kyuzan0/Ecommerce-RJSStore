<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/GaransiKlaim.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AdminGaransiController extends BaseController
{
    private GaransiKlaim $garansiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->garansiModel = new GaransiKlaim();
    }

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $status = $_GET['status'] ?? '';
        $paging = paginate($this->db, "SELECT COUNT(*) AS c FROM garansi_klaim" . ($status ? " WHERE status = ?" : ""), $status ? [$status] : [], 15);
        $claims = $this->garansiModel->getAllAdmin($status ?: null, $paging['limit'], $paging['offset']);

        $this->view('admin/garansi/index', [
            'claims'         => $claims,
            'paging'         => $paging,
            'current_status' => $status,
            'active_page'    => 'garansi',
            'page_title'     => 'Klaim Garansi',
        ], 'admin');
    }

    private function handlePost()
    {
        $this->csrfValidate();
        $id = (int) ($_POST['klaim_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $adminNote = trim($_POST['admin_note'] ?? '');

        if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
            flash('error', 'Data tidak valid.');
            $this->redirect('/admin-garansi');
            return;
        }

        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $this->garansiModel->resolve($id, $status, $adminNote);

        // Notify customer
        $klaim = $this->garansiModel->find($id);
        if ($klaim) {
            $msg = ($status === 'approved')
                ? 'Klaim garansi Anda disetujui. Akun pengganti akan segera dikirim.'
                : 'Klaim garansi Anda ditolak.' . ($adminNote ? ' Alasan: ' . $adminNote : '');
            Notifikasi::send((int) $klaim['user_id'], 'Klaim Garansi ' . ucfirst($status), $msg, $status === 'approved' ? 'success' : 'warning');
        }

        ActivityLog::log('resolve_garansi', 'garansi_klaim', $id, $status . ': ' . $adminNote);
        flash('success', 'Klaim berhasil di-' . ($status === 'approved' ? 'setujui' : 'tolak') . '.');
        $this->redirect('/admin-garansi');
    }
}
