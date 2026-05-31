<!-- Breadcrumb -->
<nav class="mb-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Keranjang</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="ds-page-header">
    <h1 class="ds-page-title">Keranjang Belanja</h1>
</div>

<?php if (empty($items)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-8 sm:p-12 text-center">
        <svg class="w-16 h-16 sm:w-20 sm:h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <h3 class="text-lg sm:text-xl font-semibold text-gray-700 mb-2">Keranjang Kosong</h3>
        <p class="text-sm text-gray-500 mb-6">Belum ada produk di keranjang Anda</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
            Mulai Belanja
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-3">
            <?php
            $total = 0;
            foreach ($items as $item):
                $total += $item['harga'];
            ?>
            <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 flex items-center gap-3 sm:gap-4">
                <!-- Product Image -->
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-gray-100 rounded-xl flex-shrink-0 flex items-center justify-center">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>

                <!-- Product Info -->
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-800 text-sm sm:text-base truncate"><?= e($item['nama_produk']) ?></h3>
                    <?php if (!empty($item['durasi'])): ?>
                        <p class="text-xs text-gray-500 mt-0.5 truncate"><?= e($item['durasi'] . (!empty($item['paket']) ? ' - ' . $item['paket'] : '')) ?></p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-0.5"><?= e($item['tipe_produk']) ?></p>
                    <?php endif; ?>
                    <p class="text-base sm:text-lg font-bold text-green-600 mt-1"><?= rupiah($item['harga']) ?></p>
                </div>

                <!-- Remove Button -->
                <form method="POST" action="<?= url('/customer/keranjang') ?>" class="flex-shrink-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="hapus_item">
                    <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
                    <input type="hidden" name="varian_id" value="<?= $item['varian_id'] ?? '' ?>">
                    <button type="submit"
                            onclick="return confirm('Hapus produk dari keranjang?')"
                            aria-label="Hapus dari keranjang"
                            class="w-10 h-10 inline-flex items-center justify-center text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>

            <!-- Clear Cart Button -->
            <form method="POST" action="<?= url('/customer/keranjang') ?>" class="pt-2">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="kosongkan">
                <button type="submit"
                        onclick="return confirm('Kosongkan semua item di keranjang?')"
                        class="text-red-500 hover:text-red-600 text-sm font-medium">
                    Kosongkan Keranjang
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 lg:sticky lg:top-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h3>

                <div class="space-y-3 mb-5">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal (<?= count($items) ?> item)</span>
                        <span><?= rupiah($total) ?></span>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex justify-between font-bold text-gray-800 text-base sm:text-lg">
                            <span>Total</span>
                            <span class="text-green-600"><?= rupiah($total) ?></span>
                        </div>
                    </div>
                </div>

                <a href="<?= url('/customer/checkout') ?>"
                   class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-xl font-semibold transition-colors mb-2">
                    Checkout
                </a>

                <a href="<?= url('/customer/produk') ?>"
                   class="block w-full text-center text-gray-500 hover:text-green-600 text-sm py-2">
                    Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
