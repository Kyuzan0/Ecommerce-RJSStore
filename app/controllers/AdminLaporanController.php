<?php
require_once __DIR__ . '/../core/BaseController.php';

class AdminLaporanController extends BaseController
{
    private Transaksi $transaksi;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');

        require_once BASE_PATH . '/app/models/Transaksi.php';
        $this->transaksi = new Transaksi();
    }

    public function index()
    {
        // Parse date filter from query string
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $search = $_GET['search'] ?? null;
        $statusFilter = $_GET['status'] ?? null;

        // Validate dates
        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        // 1. Filtered stats (6 cards)
        $stats = $this->transaksi->getFilteredStats($startDate, $endDate);

        // 2. Daily revenue chart
        $rowsHarian = $this->transaksi->getFilteredDailyRevenue($startDate, $endDate);
        $tanggalArr = [];
        $pendapatanHarianArr = [];
        foreach ($rowsHarian as $row) {
            $tanggalArr[] = date('d M Y', strtotime($row['tanggal']));
            $pendapatanHarianArr[] = (int) $row['pendapatan'];
        }

        // 3. Monthly revenue chart
        $rowsBulanan = $this->transaksi->getFilteredMonthlyRevenue($startDate, $endDate);
        $bulanArr = [];
        $pendapatanBulananArr = [];
        foreach ($rowsBulanan as $row) {
            $bulanArr[] = date('M Y', strtotime($row['bulan'] . '-01'));
            $pendapatanBulananArr[] = (int) $row['pendapatan'];
        }

        // 4. Previous period comparison (only when date filter is active)
        $prevHarianArr = [];
        $prevBulananArr = [];
        $prevTanggalArr = [];
        $prevBulanArr = [];
        if ($startDate && $endDate) {
            $prevDaily = $this->transaksi->getPreviousPeriodRevenue($startDate, $endDate, 'daily');
            foreach ($prevDaily as $row) {
                $prevTanggalArr[] = date('d M Y', strtotime($row['tanggal']));
                $prevHarianArr[] = (int) $row['pendapatan'];
            }

            $prevMonthly = $this->transaksi->getPreviousPeriodRevenue($startDate, $endDate, 'monthly');
            foreach ($prevMonthly as $row) {
                $prevBulanArr[] = date('M Y', strtotime($row['bulan'] . '-01'));
                $prevBulananArr[] = (int) $row['pendapatan'];
            }
        }

        // 5. Top products ranking
        $topProducts = $this->transaksi->getTopProducts($startDate, $endDate, 10);

        // 6. Product type breakdown (pie chart)
        $typeBreakdown = $this->transaksi->getProductTypeBreakdown($startDate, $endDate);
        $typeLabels = [];
        $typeData = [];
        foreach ($typeBreakdown as $row) {
            $typeLabels[] = ucfirst($row['tipe_produk'] ?? 'Lainnya');
            $typeData[] = (int) $row['total'];
        }

        // 7. Status breakdown (donut chart)
        $statusBreakdown = $this->transaksi->getStatusBreakdown($startDate, $endDate);
        $statusLabels = [];
        $statusData = [];
        foreach ($statusBreakdown as $row) {
            $statusLabels[] = ucfirst($row['status']);
            $statusData[] = (int) $row['jml'];
        }

        // 8. Transaction detail table with pagination
        $perPage = 15;
        $countSql = "SELECT COUNT(*) AS total FROM transaksi t JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id WHERE 1=1";
        $countParams = [];

        if ($startDate) {
            $countSql .= " AND t.tanggal >= ?";
            $countParams[] = $startDate;
        }
        if ($endDate) {
            $countSql .= " AND t.tanggal <= ?";
            $countParams[] = $endDate;
        }
        if ($search) {
            $like = '%' . $search . '%';
            $countSql .= " AND (u.name LIKE ? OR p.nama_produk LIKE ? OR t.order_ref LIKE ?)";
            $countParams[] = $like;
            $countParams[] = $like;
            $countParams[] = $like;
        }
        if ($statusFilter) {
            $countSql .= " AND t.status = ?";
            $countParams[] = $statusFilter;
        }

        $paging = paginate($this->db, $countSql, $countParams, $perPage);
        $transactions = $this->transaksi->getLaporanList($startDate, $endDate, $search, $statusFilter, $perPage, $paging['offset']);

        $extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

        $this->view('admin/laporan/index', [
            // Stats
            'stats'                  => $stats,
            // Daily chart
            'tanggal_arr'            => json_encode($tanggalArr),
            'pendapatan_harian_arr'  => json_encode($pendapatanHarianArr),
            // Monthly chart
            'bulan_arr'              => json_encode($bulanArr),
            'pendapatan_bulanan_arr' => json_encode($pendapatanBulananArr),
            // Previous period comparison
            'has_comparison'         => $startDate && $endDate,
            'prev_tanggal_arr'       => json_encode($prevTanggalArr),
            'prev_harian_arr'        => json_encode($prevHarianArr),
            'prev_bulan_arr'         => json_encode($prevBulanArr),
            'prev_bulanan_arr'       => json_encode($prevBulananArr),
            // Top products
            'top_products'           => $topProducts,
            // Product type breakdown
            'type_labels'            => json_encode($typeLabels),
            'type_data'              => json_encode($typeData),
            // Status breakdown
            'status_labels'          => json_encode($statusLabels),
            'status_data'            => json_encode($statusData),
            // Transaction table
            'transactions'           => $transactions,
            'paging'                 => $paging,
            // Filters
            'start_date'             => $startDate,
            'end_date'               => $endDate,
            'search'                 => $search,
            'status_filter'          => $statusFilter,
            // Layout
            'active_page'            => 'laporan',
            'page_title'             => 'Laporan Penjualan',
            'extra_head'             => $extra_head,
        ], 'admin');
    }

    /**
     * Export CSV — GET /admin-laporan/export?start_date=...&end_date=...&status=...
     */
    public function export()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        $rows = $this->transaksi->getExportData($startDate, $endDate, $status);

        $filename = 'laporan_penjualan';
        if ($startDate) $filename .= '_' . $startDate;
        if ($endDate) $filename .= '_' . $endDate;
        $filename .= '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        fputcsv($output, ['ID', 'Order Ref', 'Tanggal', 'Status', 'Nama User', 'Email', 'Nama Produk', 'Harga', 'Tipe Produk']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['id'],
                $row['order_ref'],
                $row['tanggal'],
                $row['status'],
                $row['nama_user'],
                $row['email'],
                $row['nama_produk'],
                $row['harga'],
                $row['tipe_produk'],
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export PDF — GET /admin-laporan/exportPdf?start_date=...&end_date=...&status=...
     * Renders a print-optimized HTML page; browser "Save as PDF" via window.print().
     */
    public function exportPdf()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = null;
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = null;
        }

        $rows = $this->transaksi->getExportData($startDate, $endDate, $status);
        $stats = $this->transaksi->getFilteredStats($startDate, $endDate);

        $periodLabel = 'Semua Periode';
        if ($startDate && $endDate) {
            $periodLabel = format_tanggal($startDate) . ' — ' . format_tanggal($endDate);
        } elseif ($startDate) {
            $periodLabel = 'Dari ' . format_tanggal($startDate);
        } elseif ($endDate) {
            $periodLabel = 'Sampai ' . format_tanggal($endDate);
        }

        require BASE_PATH . '/app/views/admin/laporan/print.php';
        exit;
    }
}
