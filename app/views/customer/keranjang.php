<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Keranjang</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Keranjang Belanja</h1>
</div>

<?php if (empty($items)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-md p-12 text-center border-2 border-dashed border-gray-300">
        <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Kosong</h3>
        <p class="text-gray-500 mb-6">Belum ada produk di keranjang Anda</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors">
            Mulai Belanja
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
            <?php 
            $total = 0;
            foreach ($items as $item): 
                $total += $item['harga'];
            ?>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center">
                <!-- Product Image -->
                <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0 mr-4">
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800 mb-1"><?= e($item['nama_produk']) ?></h3>
                    <p class="text-sm text-gray-500 mb-2"><?= e($item['tipe_produk']) ?></p>
                    <p class="text-lg font-bold text-green-600"><?= rupiah($item['harga']) ?></p>
                </div>
                
                <!-- Remove Button -->
                <form method="POST" action="<?= url('/customer/keranjang') ?>" class="ml-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="hapus_item">
                    <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
                    <button type="submit" 
                            onclick="return confirm('Hapus produk dari keranjang?')"
                            class="text-red-600 hover:text-red-800 p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
            
            <!-- Clear Cart Button -->
            <form method="POST" action="<?= url('/customer/keranjang') ?>" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="kosongkan">
                <button type="submit" 
                        onclick="return confirm('Kosongkan semua item di keranjang?')"
                        class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Kosongkan Keranjang
                </button>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h3>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal (<?= count($items) ?> item)</span>
                        <span><?= rupiah($total) ?></span>
                    </div>
                    <div class="border-t pt-3">
                        <div class="flex justify-between font-bold text-gray-800 text-lg">
                            <span>Total</span>
                            <span class="text-green-600"><?= rupiah($total) ?></span>
                        </div>
                    </div>
                </div>
                
                <a href="<?= url('/customer/checkout') ?>" 
                   class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-lg font-semibold transition-colors mb-3">
                    Checkout
                </a>
                
                <a href="<?= url('/customer/produk') ?>" 
                   class="block w-full text-center text-green-600 hover:text-green-700 py-2">
                    Lanjut Belanja
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
