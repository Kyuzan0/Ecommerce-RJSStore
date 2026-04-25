<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Produk</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Produk Digital</h1>
    
    <!-- Search Form -->
    <form method="GET" action="<?= url('/customer/produk') ?>" class="max-w-md">
        <div class="relative">
            <input type="text" 
                   name="search" 
                   value="<?= e($search) ?>" 
                   placeholder="Cari produk..." 
                   class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-green-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>

<?php if (empty($products)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada produk ditemukan</h3>
        <p class="text-gray-500">Coba kata kunci pencarian lain</p>
    </div>
<?php else: ?>
    <!-- Product Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <?php 
        $tipe_colors = [
            'Aplikasi' => 'bg-blue-100 text-blue-800',
            'Game' => 'bg-purple-100 text-purple-800',
            'Template' => 'bg-green-100 text-green-800',
            'Plugin' => 'bg-orange-100 text-orange-800',
            'Ebook' => 'bg-pink-100 text-pink-800'
        ];
        
        foreach ($products as $product): 
            $tipe_class = $tipe_colors[$product['tipe_produk']] ?? 'bg-gray-100 text-gray-800';
            $is_purchased = in_array($product['id'], $purchased_produk_ids);
            $is_in_cart = in_array($product['id'], $cart_produk_ids);
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden product-card">
            <!-- Product Image -->
            <div class="h-48 bg-gray-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <!-- Product Info -->
            <div class="p-4">
                <!-- Type Badge -->
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded <?= $tipe_class ?> mb-2">
                    <?= e($product['tipe_produk']) ?>
                </span>
                
                <!-- Product Name -->
                <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2"><?= e($product['nama_produk']) ?></h3>
                
                <!-- Rating -->
                <div class="flex items-center mb-2">
                    <?php 
                    $avg_rating = $product['avg_rating'] ?? 0;
                    $total_rating = $product['total_rating'] ?? 0;
                    for ($i = 1; $i <= 5; $i++): 
                    ?>
                        <svg class="w-4 h-4 <?= $i <= $avg_rating ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    <?php endfor; ?>
                    <span class="text-xs text-gray-500 ml-1">(<?= $total_rating ?>)</span>
                </div>
                
                <!-- Price -->
                <p class="text-lg font-bold text-green-600 mb-3"><?= rupiah($product['harga']) ?></p>
                
                <!-- Action Button -->
                <?php if ($is_purchased): ?>
                    <button disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed">
                        Sudah Dibeli
                    </button>
                <?php elseif ($is_in_cart): ?>
                    <button disabled class="w-full bg-orange-400 text-white py-2 px-4 rounded-lg cursor-not-allowed">
                        Di Keranjang
                    </button>
                <?php else: ?>
                    <button onclick="addToCart(<?= $product['id'] ?>)" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors">
                        + Keranjang
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?= pagination_render($paging) ?>
<?php endif; ?>

<script>
function addToCart(produkId) {
    fetch('<?= url('/api/cart/add') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: JSON.stringify({ produk_id: produkId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Gagal menambahkan ke keranjang');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>
