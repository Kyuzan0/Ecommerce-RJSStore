<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
    </ol>
</nav>

<!-- Hero Banner -->
<div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl shadow-sm p-8 mb-6 text-white">
    <h1 class="text-2xl font-bold mb-1">Selamat Datang, <?= e($this->auth->user()['name']) ?>!</h1>
    <p class="text-green-100 text-sm">Kelola pembelian dan download produk digital Anda dengan mudah</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <!-- Total Transaksi -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E3F2FD">
                <svg class="w-5 h-5" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <span class="text-xs text-gray-400 font-medium">TRANSAKSI</span>
        </div>
        <p class="text-3xl font-bold text-gray-800"><?= $total_transaksi ?></p>
        <p class="text-xs text-gray-500 mt-1">Total pembelian</p>
    </div>

    <!-- Total Berhasil -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs text-gray-400 font-medium">BERHASIL</span>
        </div>
        <p class="text-3xl font-bold text-gray-800"><?= $total_success ?></p>
        <p class="text-xs text-gray-500 mt-1">Transaksi sukses</p>
    </div>

    <!-- Menunggu Pembayaran -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF3E0">
                <svg class="w-5 h-5" style="color:#E65100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-xs text-gray-400 font-medium">PENDING</span>
        </div>
        <p class="text-3xl font-bold text-gray-800"><?= $total_pending ?></p>
        <p class="text-xs text-gray-500 mt-1">Menunggu bayar</p>
    </div>

    <!-- Total Pengeluaran -->
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#F3E5F5">
                <svg class="w-5 h-5" style="color:#7B1FA2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-xs text-gray-400 font-medium">SPENDING</span>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= rupiah($total_spent) ?></p>
        <p class="text-xs text-gray-500 mt-1">Total pengeluaran</p>
    </div>
</div>

<!-- Main Content: Recent Transactions + Downloadable -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Recent Transactions (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between p-5 pb-0">
            <h2 class="text-sm font-bold text-gray-800">Transaksi Terakhir</h2>
            <a href="<?= url('/customer/pembelian') ?>" class="text-xs font-medium hover:underline" style="color:#42B549">Lihat Semua →</a>
        </div>
        <?php if (!empty($recent_transactions)): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm mt-4">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-xs font-medium text-gray-400 px-5 pb-3">INVOICE</th>
                        <th class="text-left text-xs font-medium text-gray-400 pb-3">PRODUK</th>
                        <th class="text-left text-xs font-medium text-gray-400 pb-3">TANGGAL</th>
                        <th class="text-left text-xs font-medium text-gray-400 pb-3">HARGA</th>
                        <th class="text-left text-xs font-medium text-gray-400 pb-3 pr-5">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $trx): ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs text-gray-600">#<?= e($trx['order_ref'] ?? $trx['id']) ?></span>
                        </td>
                        <td class="py-3">
                            <span class="font-medium text-gray-800 text-xs"><?= e($trx['nama_produk'] ?? '-') ?></span>
                        </td>
                        <td class="py-3">
                            <span class="text-xs text-gray-500"><?= format_tanggal($trx['tanggal']) ?></span>
                        </td>
                        <td class="py-3">
                            <span class="text-xs font-medium text-gray-700"><?= rupiah($trx['harga'] ?? 0) ?></span>
                        </td>
                        <td class="py-3 pr-5">
                            <?= status_transaksi_badge($trx['status']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background:#E3F2FD">
                <svg class="w-6 h-6" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm text-gray-500">Belum ada transaksi</p>
            <a href="<?= url('/customer/produk') ?>" class="inline-block mt-2 text-xs font-medium hover:underline" style="color:#42B549">Jelajahi Produk →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Siap Download (1 col) -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between p-5 pb-0">
            <h2 class="text-sm font-bold text-gray-800">Siap Download</h2>
            <a href="<?= url('/customer/download') ?>" class="text-xs font-medium hover:underline" style="color:#42B549">Semua →</a>
        </div>
        <?php if (!empty($downloadable_items)): ?>
        <div class="flex flex-col gap-3 p-5">
            <?php foreach ($downloadable_items as $item): ?>
            <a href="<?= url('/customer/download') ?>" class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition">
                <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-xs font-medium text-gray-800 truncate"><?= e($item['nama_produk']) ?></span>
                    <span class="block text-xs text-gray-400"><?= rupiah($item['harga'] ?? 0) ?></span>
                </div>
                <?= tipe_produk_badge($item['tipe_produk'] ?? '') ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="p-8 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3" style="background:#E8F5E9">
                <svg class="w-6 h-6" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </div>
            <p class="text-sm text-gray-500">Belum ada produk</p>
            <a href="<?= url('/customer/produk') ?>" class="inline-block mt-2 text-xs font-medium hover:underline" style="color:#42B549">Beli Produk →</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Recommendations -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6">
    <div class="flex items-center justify-between p-5 pb-0">
        <h2 class="text-sm font-bold text-gray-800">Produk Terbaru</h2>
        <a href="<?= url('/customer/produk') ?>" class="text-xs font-medium hover:underline" style="color:#42B549">Lihat Semua →</a>
    </div>
    <?php if (!empty($latest_products)): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5">
        <?php foreach ($latest_products as $prod): ?>
        <a href="<?= url('/customer/produk/' . $prod['id']) ?>" class="group block rounded-xl border border-gray-100 hover:border-green-300 hover:shadow-md transition overflow-hidden">
            <!-- Product Image / Placeholder -->
            <div class="aspect-[4/3] bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center relative">
                <?php if (!empty($prod['thumbnail'])): ?>
                    <img src="<?= url('/uploads/thumbnails/' . $prod['thumbnail']) ?>" alt="<?= e($prod['nama_produk']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <?php endif; ?>
                <?php if (in_array($prod['id'], $purchased_produk_ids)): ?>
                    <span class="absolute top-2 right-2 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Dimiliki</span>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <h3 class="text-xs font-medium text-gray-800 truncate group-hover:text-green-600 transition"><?= e($prod['nama_produk']) ?></h3>
                <div class="flex items-center justify-between mt-1.5">
                    <span class="text-xs font-bold" style="color:#42B549"><?= rupiah($prod['harga']) ?></span>
                    <?php if (isset($prod['avg_rating']) && $prod['avg_rating'] > 0): ?>
                    <span class="flex items-center gap-0.5 text-xs text-gray-400">
                        <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?= number_format($prod['avg_rating'], 1) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-8 text-center">
        <p class="text-sm text-gray-500">Belum ada produk tersedia</p>
    </div>
    <?php endif; ?>
</div>

<!-- Quick Links + Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Quick Links -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4">Menu Cepat</h2>
        <div class="flex flex-col gap-3">
            <a href="<?= url('/customer/produk') ?>" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-green-300 hover:bg-green-50 transition">
                <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="flex-1">
                    <span class="block text-sm font-bold text-gray-800">Produk</span>
                    <span class="block text-xs text-gray-500">Jelajahi produk digital</span>
                </div>
            </a>
            <a href="<?= url('/customer/pembelian') ?>" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-blue-300 hover:bg-blue-50 transition">
                <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#E3F2FD">
                    <svg class="w-5 h-5" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="flex-1">
                    <span class="block text-sm font-bold text-gray-800">Pembelian</span>
                    <span class="block text-xs text-gray-500">Riwayat transaksi</span>
                </div>
            </a>
            <a href="<?= url('/customer/download') ?>" class="flex items-center gap-4 p-3 rounded-xl border border-gray-100 hover:border-purple-300 hover:bg-purple-50 transition">
                <div class="w-10 h-10 rounded-lg flex shrink-0 items-center justify-center" style="background:#F3E5F5">
                    <svg class="w-5 h-5" style="color:#7B1FA2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <div class="flex-1">
                    <span class="block text-sm font-bold text-gray-800">Download</span>
                    <span class="block text-xs text-gray-500">Unduh produk digital</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="flex flex-col gap-4">
        <!-- Security Tip -->
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 flex-1">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex shrink-0 items-center justify-center" style="background:#E3F2FD">
                    <svg class="w-5 h-5" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-blue-900 mb-1">Tips Keamanan</h4>
                    <p class="text-xs text-blue-800 leading-relaxed">Jangan bagikan password Anda kepada siapapun. Pastikan logout setelah selesai menggunakan aplikasi.</p>
                </div>
            </div>
        </div>

        <!-- Support Info -->
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex-1">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex shrink-0 items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-green-900 mb-1">Dukungan Pelanggan</h4>
                    <p class="text-xs text-green-800 leading-relaxed">Butuh bantuan? Hubungi tim support kami melalui email atau live chat yang tersedia.</p>
                </div>
            </div>
        </div>
    </div>
</div>
