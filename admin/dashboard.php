<?php
$page_title = 'Dashboard Admin';
$active_page = 'dashboard';
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include '../includes/admin_header.php';

// Stats Data
$total_user = db_count($conn, "SELECT COUNT(*) as c FROM users");
$total_produk = db_count($conn, "SELECT COUNT(*) as c FROM produk");
$total_transaksi = db_count($conn, "SELECT COUNT(*) as c FROM transaksi");

// Pendapatan 7 hari terakhir
$rows_7hari = db_query($conn, "SELECT DATE(t.tanggal) as tgl, SUM(p.harga) as pendapatan 
    FROM transaksi t 
    JOIN produk p ON t.produk_id = p.id 
    WHERE t.status='success' AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY DATE(t.tanggal) 
    ORDER BY tgl ASC");

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

include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <h1 class="text-xl font-bold text-gray-800 mb-1">Dashboard</h1>
        <p class="text-sm text-gray-500 mb-6">Selamat datang kembali, <?= e(current_user_name()); ?>.</p>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                        <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">PRODUK</span>
                </div>
                <p class="text-3xl font-bold text-gray-800"><?= $total_produk; ?></p>
                <p class="text-xs text-gray-500 mt-1">Total produk aktif</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E3F2FD">
                        <svg class="w-5 h-5" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">PENGGUNA</span>
                </div>
                <p class="text-3xl font-bold text-gray-800"><?= $total_user; ?></p>
                <p class="text-xs text-gray-500 mt-1">Total pengguna terdaftar</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF3E0">
                        <svg class="w-5 h-5" style="color:#E65100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">TRANSAKSI</span>
                </div>
                <p class="text-3xl font-bold text-gray-800"><?= $total_transaksi; ?></p>
                <p class="text-xs text-gray-500 mt-1">Total seluruh transaksi</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Chart Container (Takes 2 columns on large screens) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold mb-4 text-gray-800">Pendapatan 7 Hari Terakhir</h2>
                <div class="relative h-64 w-full">
                    <canvas id="chartPendapatan"></canvas>
                </div>
            </div>

            <!-- Quick Actions Container (Takes 1 column) -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h2 class="font-bold text-gray-800 mb-4 text-sm">Aksi Cepat</h2>
                <div class="flex flex-col gap-3">
                    <a href="produk.php" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition">
                        <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#E8F5E9"><svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></div>
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-gray-800">Tambah Produk</span>
                            <span class="block text-xs text-gray-500">Upload produk baru</span>
                        </div>
                    </a>
                    <a href="transaksi.php" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition">
                        <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#E3F2FD"><svg class="w-5 h-5" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-gray-800">Lihat Transaksi</span>
                            <span class="block text-xs text-gray-500">Cek orderan masuk</span>
                        </div>
                    </a>
                    <a href="user.php" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-purple-300 hover:bg-purple-50 transition">
                        <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#F3E5F5"><svg class="w-5 h-5" style="color:#7B1FA2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-gray-800">Kelola User</span>
                            <span class="block text-xs text-gray-500">Data pelanggan</span>
                        </div>
                    </a>
                    <a href="laporan.php" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-orange-300 hover:bg-orange-50 transition">
                        <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#FFF3E0"><svg class="w-5 h-5" style="color:#E65100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-gray-800">Laporan</span>
                            <span class="block text-xs text-gray-500">Rekap pendapatan</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

<?php include '../includes/admin_footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currencyFormatter = function(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        };

        const maxPendapatan = <?= $max_pendapatan; ?>;
        // Jika data semua 0, kita set max Y-axis ke nilai default supaya chart tidak menampilkan desimal 0.1
        const yAxisMax = maxPendapatan === 0 ? 100000 : undefined;
        // Agar langkah kelipatan bilangan bulat dan lebih rapi
        const stepSize = maxPendapatan === 0 ? 25000 : undefined;

        // Line chart - Pendapatan 7 hari
        const ctxPendapatan = document.getElementById('chartPendapatan').getContext('2d');
        new Chart(ctxPendapatan, {
            type: 'line',
            data: {
                labels: <?= json_encode($label_7hari); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?= json_encode($data_7hari); ?>,
                    borderColor: '#42B549',
                    backgroundColor: 'rgba(66, 181, 73, 0.15)',
                    borderWidth: 3,
                    pointBackgroundColor: '#42B549',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return currencyFormatter(context.parsed.y); }
                        }
                    }
                }<?php if ($max_pendapatan === 0): ?>,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100000,
                        ticks: { 
                            stepSize: 25000,
                            callback: currencyFormatter 
                        }
                    }
                }
                <?php else: ?>,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: currencyFormatter }
                    }
                }
                <?php endif; ?>
            }
        });
    });
</script>
