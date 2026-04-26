<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <h1 class="text-xl font-bold text-gray-800">Laporan Penjualan</h1>
    <div class="relative" id="exportDropdown">
        <button type="button" onclick="document.getElementById('exportMenu').classList.toggle('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition shadow-sm"
                style="background:#42B549">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export
            <svg class="w-3.5 h-3.5 -mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div id="exportMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-xl border border-gray-100 shadow-lg z-50 overflow-hidden">
            <a href="<?= url('/admin-laporan/export') ?>?<?= http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'status' => $status_filter])) ?>"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export to CSV
            </a>
            <a href="<?= url('/admin-laporan/exportPdf') ?>?<?= http_build_query(array_filter(['start_date' => $start_date, 'end_date' => $end_date, 'status' => $status_filter])) ?>"
               target="_blank"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export to PDF
            </a>
        </div>
    </div>
</div>
<script>
document.addEventListener('click', function(e) {
    var dd = document.getElementById('exportDropdown');
    if (dd && !dd.contains(e.target)) {
        document.getElementById('exportMenu').classList.add('hidden');
    }
});
</script>

<!-- Date Range Filter -->
<form method="GET" action="<?= url('/admin-laporan') ?>" class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-6">
    <div class="flex flex-col sm:flex-row items-end gap-3">
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" value="<?= e($start_date ?? '') ?>"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-200 focus:border-green-400 outline-none transition">
        </div>
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Akhir</label>
            <input type="date" name="end_date" value="<?= e($end_date ?? '') ?>"
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-200 focus:border-green-400 outline-none transition">
        </div>
        <div class="flex-1 min-w-0">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-200 focus:border-green-400 outline-none transition bg-white">
                <option value="">Semua Status</option>
                <option value="success" <?= ($status_filter ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
                <option value="pending" <?= ($status_filter ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="failed" <?= ($status_filter ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2 text-sm font-semibold text-white rounded-xl hover:opacity-90 transition shadow-sm" style="background:#42B549">
                <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            <?php if ($start_date || $end_date || $status_filter): ?>
            <a href="<?= url('/admin-laporan') ?>" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Reset</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Stat Cards (6 cards) -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-blue-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">ITEM TERJUAL</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#E3F2FD">
                <svg class="w-4 h-4" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_item']) ?></h2>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-green-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">TOTAL PENDAPATAN</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#E8F5E9">
                <svg class="w-4 h-4" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-800"><?= rupiah($stats['total_pendapatan']) ?></h2>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-purple-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">RATA-RATA HARIAN</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#F3E5F5">
                <svg class="w-4 h-4" style="color:#7B1FA2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-800"><?= rupiah($stats['avg_daily']) ?></h2>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-orange-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">TRANSAKSI PENDING</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#FFF3E0">
                <svg class="w-4 h-4" style="color:#E65100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-gray-800"><?= number_format($stats['pending_count']) ?></h2>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-pink-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">PRODUK TERLARIS</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#FCE4EC">
                <svg class="w-4 h-4" style="color:#C2185B" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
        </div>
        <h2 class="text-lg font-bold text-gray-800 truncate" title="<?= e($stats['top_product']) ?>"><?= e($stats['top_product']) ?></h2>
        <p class="text-xs text-gray-400 mt-0.5"><?= $stats['top_product_qty'] ?> terjual</p>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-cyan-500">
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs text-gray-500 font-medium tracking-wide">PERTUMBUHAN</p>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#E0F7FA">
                <svg class="w-4 h-4" style="color:#00838F" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <?php if ($stats['growth'] !== null): ?>
            <h2 class="text-2xl font-bold <?= $stats['growth'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                <?= $stats['growth'] >= 0 ? '+' : '' ?><?= $stats['growth'] ?>%
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">vs periode sebelumnya</p>
        <?php else: ?>
            <h2 class="text-lg font-bold text-gray-400">-</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pilih rentang tanggal</p>
        <?php endif; ?>
    </div>
</div>

<!-- Charts Row 1: Daily & Monthly Revenue -->
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

<!-- Charts Row 2: Product Type Pie & Status Donut -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h2 class="text-sm font-bold mb-4 text-gray-800">Penjualan per Tipe Produk</h2>
        <div class="relative h-64 w-full flex items-center justify-center">
            <canvas id="grafikTipeProduk"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h2 class="text-sm font-bold mb-4 text-gray-800">Breakdown Status Transaksi</h2>
        <div class="relative h-64 w-full flex items-center justify-center">
            <canvas id="grafikStatus"></canvas>
        </div>
    </div>
</div>

<!-- Top Products Ranking -->
<?php if (!empty($top_products)): ?>
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
    <div class="p-6 pb-3">
        <h2 class="text-sm font-bold text-gray-800">Top 10 Produk Terlaris</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-t border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">#</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">PRODUK</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">TIPE</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">HARGA</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">TERJUAL</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">TOTAL</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($top_products as $i => $prod): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-3 text-gray-400 font-bold"><?= $i + 1 ?></td>
                    <td class="px-6 py-3 font-medium text-gray-800"><?= e($prod['nama_produk']) ?></td>
                    <td class="px-6 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                            <?= ($prod['tipe_produk'] ?? '') === 'digital' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' ?>">
                            <?= ucfirst(e($prod['tipe_produk'] ?? '-')) ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right text-gray-600"><?= rupiah((int) $prod['harga']) ?></td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800"><?= number_format($prod['jml_terjual']) ?></td>
                    <td class="px-6 py-3 text-right font-semibold text-green-600"><?= rupiah((int) $prod['total_pendapatan']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Transaction Detail Table -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
    <div class="p-6 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-sm font-bold text-gray-800">Detail Transaksi</h2>
        <form method="GET" action="<?= url('/admin-laporan') ?>" class="flex items-center gap-2">
            <?php if ($start_date): ?><input type="hidden" name="start_date" value="<?= e($start_date) ?>"><?php endif; ?>
            <?php if ($end_date): ?><input type="hidden" name="end_date" value="<?= e($end_date) ?>"><?php endif; ?>
            <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= e($status_filter) ?>"><?php endif; ?>
            <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Cari nama, produk, order ref..."
                   class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-200 focus:border-green-400 outline-none transition w-64">
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-xl hover:opacity-90 transition" style="background:#42B549">Cari</button>
        </form>
    </div>

    <?php if (!empty($transactions)): ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-t border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">TANGGAL</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">ORDER REF</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">PELANGGAN</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">PRODUK</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">TIPE</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">HARGA</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 tracking-wide">STATUS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($transactions as $trx): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-3 text-gray-600 whitespace-nowrap"><?= format_tanggal($trx['tanggal']) ?></td>
                    <td class="px-6 py-3 text-gray-500 font-mono text-xs"><?= e($trx['order_ref'] ?? '-') ?></td>
                    <td class="px-6 py-3 font-medium text-gray-800"><?= e($trx['nama_user']) ?></td>
                    <td class="px-6 py-3 text-gray-700"><?= e($trx['nama_produk']) ?></td>
                    <td class="px-6 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                            <?= ($trx['tipe_produk'] ?? '') === 'digital' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' ?>">
                            <?= ucfirst(e($trx['tipe_produk'] ?? '-')) ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-right text-gray-700"><?= rupiah((int) $trx['harga']) ?></td>
                    <td class="px-6 py-3 text-center">
                        <?php
                        $statusClass = match($trx['status']) {
                            'success' => 'bg-green-50 text-green-700',
                            'pending' => 'bg-yellow-50 text-yellow-700',
                            'failed'  => 'bg-red-50 text-red-700',
                            default   => 'bg-gray-50 text-gray-700',
                        };
                        ?>
                        <span class="inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full <?= $statusClass ?>">
                            <?= ucfirst(e($trx['status'])) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($paging['total_pages'] > 1): ?>
    <div class="px-6 py-3">
        <?= pagination_render($paging) ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="px-6 py-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-sm">Tidak ada transaksi ditemukan.</p>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencyFormatter = function(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
    };

    const chartColors = {
        green: { border: '#10B981', bg: 'rgba(16, 185, 129, 0.15)' },
        blue: { border: '#3B82F6', bg: 'rgba(59, 130, 246, 0.15)' },
        gray: { border: '#9CA3AF', bg: 'rgba(156, 163, 175, 0.15)' },
        pie: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316'],
        status: { Success: '#10B981', Pending: '#F59E0B', Failed: '#EF4444' }
    };

    // ---- Daily Revenue Chart ----
    const tanggalArr = <?= $tanggal_arr ?>;
    const pendapatanHarianArr = <?= $pendapatan_harian_arr ?>;
    const hasComparison = <?= $has_comparison ? 'true' : 'false' ?>;

    const dailyDatasets = [{
        label: 'Pendapatan Harian',
        data: pendapatanHarianArr,
        borderColor: chartColors.green.border,
        backgroundColor: chartColors.green.bg,
        borderWidth: 2.5,
        pointBackgroundColor: chartColors.green.border,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        fill: true,
        tension: 0.3
    }];

    <?php if ($has_comparison): ?>
    dailyDatasets.push({
        label: 'Periode Sebelumnya',
        data: <?= $prev_harian_arr ?>,
        borderColor: chartColors.gray.border,
        backgroundColor: chartColors.gray.bg,
        borderWidth: 2,
        borderDash: [5, 5],
        pointRadius: 3,
        fill: false,
        tension: 0.3
    });
    <?php endif; ?>

    new Chart(document.getElementById('grafikHarian').getContext('2d'), {
        type: 'line',
        data: { labels: tanggalArr, datasets: dailyDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + currencyFormatter(ctx.parsed.y); } } },
                legend: { display: hasComparison, position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 15, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: currencyFormatter, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { ticks: { font: { size: 10 }, maxRotation: 45 }, grid: { display: false } }
            }
        }
    });

    // ---- Monthly Revenue Chart ----
    const bulanArr = <?= $bulan_arr ?>;
    const pendapatanBulananArr = <?= $pendapatan_bulanan_arr ?>;

    const monthlyDatasets = [{
        label: 'Pendapatan Bulanan',
        data: pendapatanBulananArr,
        backgroundColor: chartColors.blue.border,
        borderRadius: 8,
        borderSkipped: false
    }];

    <?php if ($has_comparison): ?>
    monthlyDatasets.push({
        label: 'Periode Sebelumnya',
        data: <?= $prev_bulanan_arr ?>,
        backgroundColor: 'rgba(156, 163, 175, 0.4)',
        borderRadius: 8,
        borderSkipped: false
    });
    <?php endif; ?>

    new Chart(document.getElementById('grafikBulanan').getContext('2d'), {
        type: 'bar',
        data: { labels: bulanArr, datasets: monthlyDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                tooltip: { callbacks: { label: function(ctx) { return ctx.dataset.label + ': ' + currencyFormatter(ctx.parsed.y); } } },
                legend: { display: hasComparison, position: 'top', labels: { usePointStyle: true, pointStyle: 'rect', padding: 15, font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: currencyFormatter, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // ---- Product Type Pie Chart ----
    const typeLabels = <?= $type_labels ?>;
    const typeData = <?= $type_data ?>;

    if (typeLabels.length > 0) {
        new Chart(document.getElementById('grafikTipeProduk').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: chartColors.pie.slice(0, typeLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': ' + currencyFormatter(ctx.parsed); } } }
                }
            }
        });
    } else {
        document.getElementById('grafikTipeProduk').parentElement.innerHTML = '<p class="text-sm text-gray-400">Tidak ada data</p>';
    }

    // ---- Status Donut Chart ----
    const statusLabels = <?= $status_labels ?>;
    const statusData = <?= $status_data ?>;

    if (statusLabels.length > 0) {
        const statusColors = statusLabels.map(function(label) { return chartColors.status[label] || '#9CA3AF'; });
        new Chart(document.getElementById('grafikStatus').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': ' + ctx.parsed + ' transaksi'; } } }
                }
            }
        });
    } else {
        document.getElementById('grafikStatus').parentElement.innerHTML = '<p class="text-sm text-gray-400">Tidak ada data</p>';
    }
});
</script>
