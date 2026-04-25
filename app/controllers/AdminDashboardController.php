<?php
require_once __DIR__ . '/../core/BaseController.php';

class AdminDashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
    }

    public function index()
    {
        // Stats Data
        $total_user = $this->db->fetchOne("SELECT COUNT(*) as c FROM users", [])['c'] ?? 0;
        $total_produk = $this->db->fetchOne("SELECT COUNT(*) as c FROM produk", [])['c'] ?? 0;
        $total_transaksi = $this->db->fetchOne("SELECT COUNT(*) as c FROM transaksi", [])['c'] ?? 0;

        // Pendapatan 7 hari terakhir
        $rows_7hari = $this->db->fetchAll(
            "SELECT DATE(t.tanggal) as tgl, SUM(p.harga) as pendapatan 
            FROM transaksi t 
            JOIN produk p ON t.produk_id = p.id 
            WHERE t.status='success' AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            GROUP BY DATE(t.tanggal) 
            ORDER BY tgl ASC",
            []
        );

        // Build array 7 hari (isi 0 untuk hari tanpa transaksi)
        $label_7hari = [];
        $data_7hari = [];
        $map_pendapatan = [];
        $max_pendapatan = 0;
        foreach ($rows_7hari as $row) {
            $map_pendapatan[$row['tgl']] = (int) $row['pendapatan'];
            if ((int)$row['pendapatan'] > $max_pendapatan) {
                $max_pendapatan = (int)$row['pendapatan'];
            }
        }
        for ($i = 6; $i >= 0; $i--) {
            $tgl = date('Y-m-d', strtotime("-{$i} days"));
            $label_7hari[] = date('d M', strtotime($tgl));
            $data_7hari[] = $map_pendapatan[$tgl] ?? 0;
        }

        $extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

        $this->view('admin/dashboard', [
            'total_produk' => $total_produk,
            'total_pengguna' => $total_user,
            'total_transaksi' => $total_transaksi,
            'label_7hari' => json_encode($label_7hari),
            'data_7hari' => json_encode($data_7hari),
            'max_pendapatan' => $max_pendapatan,
            'active_page' => 'dashboard',
            'page_title' => 'Dashboard Admin',
            'extra_head' => $extra_head
        ], 'admin');
    }
}
