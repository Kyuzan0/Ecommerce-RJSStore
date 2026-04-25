<?php

require_once __DIR__ . '/../core/BaseController.php';

class CustomerRatingController extends BaseController
{
    private $transaksiModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('customer');
        
        require_once __DIR__ . '/../models/Transaksi.php';
        
        $this->transaksiModel = new Transaksi();
    }

    public function index($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRating($id);
            return;
        }

        $user_id = $this->auth->id();
        $transaksi_id = (int)$id;

        // Validate transaksi
        $transaksi = $this->db->fetchOne(
            "SELECT t.*, p.nama_produk 
             FROM transaksi t 
             JOIN produk p ON t.produk_id = p.id 
             WHERE t.id = ? AND t.user_id = ?",
            [$transaksi_id, $user_id]
        );

        if (!$transaksi) {
            flash('error', 'Transaksi tidak ditemukan');
            $this->redirect('/customer/pembelian');
            return;
        }

        if ($transaksi['status'] !== 'success') {
            flash('error', 'Hanya transaksi yang berhasil yang dapat diberi rating');
            $this->redirect('/customer/pembelian');
            return;
        }

        if (!empty($transaksi['rating'])) {
            flash('error', 'Transaksi ini sudah diberi rating');
            $this->redirect('/customer/pembelian');
            return;
        }

        // Render rating form
        $this->view('customer/rating', [
            'transaksi' => $transaksi
        ], 'checkout');
    }

    private function handleRating($id)
    {
        $this->csrfValidate();
        $user_id = $this->auth->id();
        $transaksi_id = (int)$id;

        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $ulasan = trim($_POST['ulasan'] ?? '');

        if ($rating < 1 || $rating > 5) {
            flash('error', 'Rating harus antara 1-5');
            $this->redirect('/customer/rating/' . $transaksi_id);
            return;
        }

        // Validate transaksi ownership and status
        $transaksi = $this->db->fetchOne(
            "SELECT id, status, rating FROM transaksi WHERE id = ? AND user_id = ?",
            [$transaksi_id, $user_id]
        );

        if (!$transaksi) {
            flash('error', 'Transaksi tidak ditemukan');
            $this->redirect('/customer/pembelian');
            return;
        }

        if ($transaksi['status'] !== 'success') {
            flash('error', 'Hanya transaksi yang berhasil yang dapat diberi rating');
            $this->redirect('/customer/pembelian');
            return;
        }

        if (!empty($transaksi['rating'])) {
            flash('error', 'Transaksi ini sudah diberi rating');
            $this->redirect('/customer/pembelian');
            return;
        }

        // Update rating
        $this->transaksiModel->setRating($transaksi_id, $rating, $ulasan);

        flash('success', 'Terima kasih atas rating Anda!');
        $this->redirect('/customer/pembelian');
    }
}
