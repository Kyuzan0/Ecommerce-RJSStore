<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/GaransiKlaim.php';

class CustomerGaransiController extends BaseController
{
    private GaransiKlaim $garansiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        $this->garansiModel = new GaransiKlaim();
    }

    /**
     * Submit a warranty claim (POST).
     */
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSubmit();
            return;
        }

        // Show customer's claims
        $userId = $this->auth->id();
        $claims = $this->garansiModel->getByUser($userId);

        $this->view('customer/garansi', [
            'claims'      => $claims,
            'active_page' => 'garansi',
            'page_title'  => 'Klaim Garansi',
        ], 'customer');
    }

    private function handleSubmit()
    {
        $this->csrfValidate();
        $userId = $this->auth->id();
        $transaksiId = (int) ($_POST['transaksi_id'] ?? 0);
        $alasan = trim($_POST['alasan'] ?? '');

        if ($transaksiId <= 0 || $alasan === '') {
            flash('error', 'Data klaim tidak valid.');
            $this->redirect('/customer-garansi');
            return;
        }

        // Verify ownership and status
        $tx = $this->db->fetchOne(
            "SELECT t.id, t.varian_id, v.garansi_hari, t.tanggal
             FROM transaksi t
             LEFT JOIN produk_varian v ON t.varian_id = v.id
             WHERE t.id = ? AND t.user_id = ? AND t.status = 'success'",
            [$transaksiId, $userId]
        );

        if (!$tx) {
            flash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/customer-garansi');
            return;
        }

        // Check warranty period
        $garansiHari = (int) ($tx['garansi_hari'] ?? 0);
        if ($garansiHari > 0) {
            $batasGaransi = date('Y-m-d H:i:s', strtotime($tx['tanggal'] . ' + ' . $garansiHari . ' days'));
            if (date('Y-m-d H:i:s') > $batasGaransi) {
                flash('error', 'Masa garansi sudah berakhir (' . $garansiHari . ' hari sejak pembelian).');
                $this->redirect('/customer-garansi');
                return;
            }
        }

        // Check no active claim
        if ($this->garansiModel->hasActiveKlaim($transaksiId)) {
            flash('error', 'Sudah ada klaim aktif untuk transaksi ini.');
            $this->redirect('/customer-garansi');
            return;
        }

        $this->garansiModel->submitKlaim($transaksiId, $userId, $alasan);
        Notifikasi::send($userId, 'Klaim Garansi Dikirim', 'Klaim garansi Anda sedang diproses oleh admin.', 'info');
        flash('success', 'Klaim garansi berhasil dikirim. Admin akan meninjau dalam 1x24 jam.');
        $this->redirect('/customer-garansi');
    }
}
