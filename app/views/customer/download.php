<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Download</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Download Produk</h1>
    <p class="text-gray-600 mt-2">Produk yang sudah Anda beli dapat diunduh di sini</p>
</div>

<?php if (empty($items)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada produk untuk diunduh</h3>
        <p class="text-gray-500 mb-6">Beli produk terlebih dahulu untuk dapat mengunduhnya</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors">
            Lihat Produk
        </a>
    </div>
<?php else: ?>
    <!-- Download Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($items as $item): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
            <!-- Green Gradient Top Bar -->
            <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-600"></div>
            
            <!-- Product Image -->
            <div class="h-40 bg-gray-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            
            <!-- Product Info -->
            <div class="p-4">
                <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2"><?= e($item['nama_produk']) ?></h3>
                <p class="text-sm text-gray-500 mb-4">
                    Dibeli: <?= format_tanggal($item['tanggal']) ?>
                </p>
                
                <!-- Download Button -->
                <?php if (!empty($item['file_upload'])): ?>
                    <a href="<?= url('/uploads/' . e($item['file_upload'])) ?>" 
                       download
                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg font-semibold transition-colors">
                        <div class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download
                        </div>
                    </a>
                <?php else: ?>
                    <button disabled class="block w-full bg-gray-400 text-white text-center py-2 rounded-lg cursor-not-allowed">
                        File Tidak Tersedia
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?= pagination_render($paging) ?>
<?php endif; ?>
