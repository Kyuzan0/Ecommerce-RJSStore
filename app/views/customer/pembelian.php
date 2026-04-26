<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Pembelian</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Riwayat Pembelian</h1>
    
    <!-- Filter Tabs -->
    <div class="flex space-x-2 border-b border-gray-200">
        <a href="<?= url('/customer/pembelian') ?>" 
           class="px-4 py-2 <?= empty($current_status) ? 'border-b-2 border-green-600 text-green-600 font-semibold' : 'text-gray-600 hover:text-green-600' ?>">
            Semua
        </a>
        <a href="<?= url('/customer/pembelian?status=pending') ?>" 
           class="px-4 py-2 <?= $current_status === 'pending' ? 'border-b-2 border-green-600 text-green-600 font-semibold' : 'text-gray-600 hover:text-green-600' ?>">
            Pending
        </a>
        <a href="<?= url('/customer/pembelian?status=success') ?>" 
           class="px-4 py-2 <?= $current_status === 'success' ? 'border-b-2 border-green-600 text-green-600 font-semibold' : 'text-gray-600 hover:text-green-600' ?>">
            Success
        </a>
        <a href="<?= url('/customer/pembelian?status=cancelled') ?>" 
           class="px-4 py-2 <?= $current_status === 'cancelled' ? 'border-b-2 border-red-600 text-red-600 font-semibold' : 'text-gray-600 hover:text-red-600' ?>">
            Cancelled
        </a>
    </div>
</div>

<?php if (empty($grouped_transactions)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada transaksi</h3>
        <p class="text-gray-500 mb-6">Mulai belanja produk digital sekarang</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors">
            Lihat Produk
        </a>
    </div>
<?php else: ?>
    <!-- Transaction List -->
    <div class="space-y-6 mb-8">
        <?php 
        $status_colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'success' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'failed' => 'bg-red-100 text-red-800'
        ];
        
        foreach ($grouped_transactions as $group): 
            $status_class = $status_colors[$group['status']] ?? 'bg-gray-100 text-gray-800';
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Group Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Order ID: <span class="font-semibold text-gray-800"><?= e($group['order_ref']) ?></span></p>
                    <p class="text-xs text-gray-500"><?= format_tanggal($group['tanggal']) ?></p>
                </div>
                <span class="px-3 py-1 text-xs font-semibold rounded-full <?= $status_class ?>">
                    <?= strtoupper($group['status']) ?>
                </span>
            </div>
            
            <!-- Items -->
            <div class="divide-y divide-gray-200">
                <?php foreach ($group['items'] as $item): ?>
                <div class="px-6 py-4 flex items-center">
                    <!-- Product Image -->
                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex-shrink-0 mr-4">
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-800"><?= e($item['nama_produk']) ?></h4>
                        <p class="text-sm text-gray-600"><?= rupiah($item['harga']) ?></p>
                        
                        <!-- Rating Display -->
                        <?php if ($group['status'] === 'success'): ?>
                            <?php if (!empty($item['rating'])): ?>
                                <div class="flex items-center mt-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-4 h-4 <?= $i <= $item['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            <?php else: ?>
                                <a href="<?= url('/customer/rating/' . $item['id']) ?>" class="text-sm text-green-600 hover:text-green-700 mt-1 inline-block">
                                    Beri Nilai
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Group Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Total Pembayaran</p>
                    <p class="text-lg font-bold text-gray-800"><?= rupiah($group['total']) ?></p>
                </div>
                
                <div class="flex space-x-2">
                    <?php if ($group['status'] === 'pending'): ?>
                        <?php 
                        // Build payment URL
                        $payment_url = url('/customer/bayar');
                        if (strpos($group['order_ref'], 'ORD-') === 0) {
                            $payment_url .= '?ref=' . urlencode($group['order_ref']);
                        } else {
                            $payment_url .= '?id=' . $group['items'][0]['id'];
                        }
                        ?>
                        <a href="<?= $payment_url ?>" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            Bayar Sekarang
                        </a>
                    <?php elseif ($group['status'] === 'success'): ?>
                        <a href="<?= url('/customer/download') ?>" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                            Download
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
