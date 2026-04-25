<?php
require_once __DIR__ . '/../core/BaseController.php';

class AdminLaporanController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
    }

    public function index()
    {
        // 1. Ringkasan statistik
        $data = $this->db->fetchOne(
            "SELECT SUM(p.harga) as total, COUNT(t.id) as jml FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success'",
            []
        );

        // 2. Grafik Penjualan Harian
        $rows_harian = $this->db->fetchAll(
            "SELECT DATE(t.tanggal) as tanggal, SUM(p.harga) as pendapatan FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success' GROUP BY DATE(t.tanggal) ORDER BY tanggal ASC",
            []
        );
        $tanggal_arr = [];
        $pendapatan_harian_arr = [];
        foreach ($rows_harian as $row) {
            $tanggal_arr[] = date('d M Y', strtotime($row['tanggal']));
            $pendapatan_harian_arr[] = $row['pendapatan'];
        }

        // 3. Grafik Penjualan Bulanan
        $rows_bulanan = $this->db->fetchAll(
            "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') as bulan, SUM(p.harga) as pendapatan FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success' GROUP BY bulan ORDER BY bulan ASC",
            []
        );
        $bulan_arr = [];
        $pendapatan_bulanan_arr = [];
        foreach ($rows_bulanan as $row) {
            $bulan_arr[] = date('M Y', strtotime($row['bulan'] . '-01'));
            $pendapatan_bulanan_arr[] = $row['pendapatan'];
        }

        $extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

        $this->view('admin/laporan/index', [
            'total_item' => $data['jml'] ?? 0,
            'total_pendapatan' => $data['total'] ?? 0,
            'tanggal_arr' => json_encode($tanggal_arr),
            'pendapatan_harian_arr' => json_encode($pendapatan_harian_arr),
            'bulan_arr' => json_encode($bulan_arr),
            'pendapatan_bulanan_arr' => json_encode($pendapatan_bulanan_arr),
            'active_page' => 'laporan',
            'page_title' => 'Laporan Penjualan',
            'extra_head' => $extra_head
        ], 'admin');
    }
}
