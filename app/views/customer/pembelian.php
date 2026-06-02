<!-- Breadcrumb -->
<nav class="mb-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Pembelian</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="ds-page-header">
    <h1 class="ds-page-title">Riwayat Pembelian</h1>
</div>

<!-- Filter Tabs (horizontal scroll on mobile) -->
<nav class="ds-tabs mb-6">
    <a href="<?= url('/customer/pembelian') ?>" class="<?= empty($current_status) ? 'is-active' : '' ?>">Semua</a>
    <a href="<?= url('/customer/pembelian?status=pending') ?>" class="<?= $current_status === 'pending' ? 'is-active' : '' ?>">Pending</a>
    <a href="<?= url('/customer/pembelian?status=success') ?>" class="<?= $current_status === 'success' ? 'is-active' : '' ?>">Success</a>
    <a href="<?= url('/customer/pembelian?status=cancelled') ?>" class="<?= $current_status === 'cancelled' ? 'is-active' : '' ?>">Cancelled</a>
</nav>

<?php if (empty($grouped_transactions)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-2xl border border-gray-100 p-8 sm:p-12 text-center">
        <svg class="w-14 h-14 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Belum ada transaksi</h3>
        <p class="text-sm text-gray-500 mb-6">Mulai belanja produk digital sekarang</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
            Lihat Produk
        </a>
    </div>
<?php else: ?>
    <!-- Transaction List -->
    <div class="space-y-4 sm:space-y-6 mb-8">
        <?php
        $status_chip_map = [
            'pending'   => 'warning',
            'success'   => 'success',
            'cancelled' => 'danger',
            'failed'    => 'danger',
        ];

        foreach ($grouped_transactions as $group):
            $chip_variant = $status_chip_map[$group['status']] ?? 'neutral';
        ?>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <!-- Group Header -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 border-b border-gray-100 flex flex-wrap justify-between items-start gap-2">
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm text-gray-500">Order ID: <span class="font-semibold text-gray-800 break-all"><?= e($group['order_ref']) ?></span></p>
                    <p class="text-xs text-gray-500 mt-0.5"><?= format_tanggal($group['tanggal']) ?></p>
                </div>
                <span class="ds-chip ds-chip--<?= e($chip_variant) ?> flex-shrink-0">
                    <?= strtoupper($group['status']) ?>
                </span>
            </div>

            <!-- Items -->
            <div class="divide-y divide-gray-100">
                <?php foreach ($group['items'] as $item): ?>
                <div class="px-4 sm:px-6 py-3 sm:py-4 flex items-start gap-3 sm:gap-4">
                    <!-- Product Image -->
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>

                    <!-- Product Info -->
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-800 text-sm sm:text-base truncate"><?= e($item['nama_produk']) ?></h4>
                        <?php if (!empty($item['durasi'])): ?>
                            <p class="text-xs text-gray-500 mt-0.5"><?= e($item['durasi'] . (!empty($item['paket']) ? ' - ' . $item['paket'] : '')) ?></p>
                        <?php endif; ?>
                        <p class="text-sm text-gray-700 mt-1 font-medium"><?= rupiah($item['harga']) ?></p>

                        <!-- Action Buttons & Rating Display -->
                        <?php if ($group['status'] === 'success'): ?>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <?php if ($item['tipe_produk'] === 'Akun'): ?>
                                    <button type="button" onclick="document.getElementById('akun-detail-<?= $item['id'] ?>').classList.toggle('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors">
                                        Lihat Akun
                                    </button>
                                <?php else: ?>
                                    <a href="<?= url('/customer/download-file/' . $item['id']) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors">
                                        Download
                                    </a>
                                <?php endif; ?>

                                <?php if (!empty($item['rating'])): ?>
                                    <div class="flex items-center">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <svg class="w-4 h-4 <?= $i <= $item['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= url('/customer/rating/' . $item['id']) ?>" class="text-xs sm:text-sm text-green-600 hover:text-green-700 font-medium">
                                        Beri Nilai
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if ($item['tipe_produk'] === 'Akun'): ?>
                                <div id="akun-detail-<?= $item['id'] ?>" class="hidden mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg text-sm">
                                    <p class="text-blue-800"><span class="font-semibold">Email:</span> <?= e($item['account_email'] ?? '-') ?></p>
                                    <p class="text-blue-800"><span class="font-semibold">Password:</span> <?= e($item['account_password'] ?? '-') ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Group Footer -->
            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-100 flex flex-wrap justify-between items-center gap-3">
                <div>
                    <p class="text-xs text-gray-500">Total Pembayaran</p>
                    <p class="text-base sm:text-lg font-bold text-gray-800"><?= rupiah($group['total']) ?></p>
                </div>

                <div class="flex gap-2 flex-shrink-0">
                    <?php if ($group['status'] === 'pending'): ?>
                        <?php
                        $payment_url = url('/customer/bayar');
                        if (strpos($group['order_ref'], 'ORD-') === 0) {
                            $payment_url .= '?ref=' . urlencode($group['order_ref']);
                        } else {
                            $payment_url .= '?id=' . $group['items'][0]['id'];
                        }
                        ?>
                        <a href="<?= $payment_url ?>"
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-colors">
                            Bayar Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?= pagination_render($paging) ?>
<?php endif; ?>
