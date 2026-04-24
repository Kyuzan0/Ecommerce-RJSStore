<?php
$page_title = 'Laporan Penjualan';
$active_page = 'laporan';
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include '../includes/admin_header.php';

// 1. Ringkasan statistik
$data = db_query_one($conn, "SELECT SUM(p.harga) as total, COUNT(t.id) as jml FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success'");

// 2. Grafik Penjualan Harian
$rows_harian = db_query($conn, "SELECT t.tanggal, SUM(p.harga) as pendapatan FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success' GROUP BY t.tanggal ORDER BY t.tanggal ASC");
$tanggal_arr = [];
$pendapatan_harian_arr = [];
foreach ($rows_harian as $row) {
    $tanggal_arr[] = date('d M Y', strtotime($row['tanggal']));
    $pendapatan_harian_arr[] = $row['pendapatan'];
}

// 3. Grafik Penjualan Bulanan
$rows_bulanan = db_query($conn, "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') as bulan, SUM(p.harga) as pendapatan FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status='success' GROUP BY bulan ORDER BY bulan ASC");
$bulan_arr = [];
$pendapatan_bulanan_arr = [];
foreach ($rows_bulanan as $row) {
    $bulan_arr[] = date('M Y', strtotime($row['bulan'] . '-01'));
    $pendapatan_bulanan_arr[] = $row['pendapatan'];
}

include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <h1 class="text-xl font-bold text-gray-800 mb-5">Laporan Keuangan</h1>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-blue-500">
                <p class="text-xs text-gray-500 font-medium tracking-wide">JUMLAH ITEM TERJUAL</p>
                <h2 class="text-3xl font-bold text-gray-800 mt-2"><?= $data['jml'] ?? 0; ?></h2>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-green-500">
                <p class="text-xs text-gray-500 font-medium tracking-wide">JUMLAH PENDAPATAN</p>
                <h2 class="text-3xl font-bold text-gray-800 mt-2"><?= rupiah($data['total'] ?? 0); ?></h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold mb-4 text-gray-800">Grafik Pendapatan Harian</h2>
                <div class="relative h-64 w-full">
                    <canvas id="grafikHarian"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold mb-4 text-gray-800">Grafik Pendapatan Bulanan</h2>
                <div class="relative h-64 w-full">
                    <canvas id="grafikBulanan"></canvas>
                </div>
            </div>
        </div>

<?php include '../includes/admin_footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currencyFormatter = function(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        };

        const ctxHarian = document.getElementById('grafikHarian').getContext('2d');
        new Chart(ctxHarian, {
            type: 'line',
            data: {
                labels: <?= json_encode($tanggal_arr); ?>,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: <?= json_encode($pendapatan_harian_arr); ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#ffffff',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return currencyFormatter(context.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: currencyFormatter }
                    }
                }
            }
        });

        const ctxBulanan = document.getElementById('grafikBulanan').getContext('2d');
        new Chart(ctxBulanan, {
            type: 'bar',
            data: {
                labels: <?= json_encode($bulan_arr); ?>,
                datasets: [{
                    label: 'Pendapatan Bulanan (Rp)',
                    data: <?= json_encode($pendapatan_bulanan_arr); ?>,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return currencyFormatter(context.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: currencyFormatter }
                    }
                }
            }
        });
    });
</script>
