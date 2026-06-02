<!-- Breadcrumb -->
<nav class="mb-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Koleksi</li>
    </ol>
</nav>

<!-- Page Header -->
<div class="ds-page-header">
    <h1 class="ds-page-title">Koleksi Produk</h1>
    <p class="ds-page-subtitle mt-1">Akses semua produk digital dan akun yang telah Anda beli</p>
</div>

<?php if (empty($items)): ?>
    <!-- Empty State -->
    <div class="bg-white rounded-2xl border border-gray-100 p-8 sm:p-12 text-center">
        <svg class="w-14 h-14 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-2">Belum ada produk di koleksi Anda</h3>
        <p class="text-sm text-gray-500 mb-6">Beli produk terlebih dahulu untuk dapat mengaksesnya di sini</p>
        <a href="<?= url('/customer/produk') ?>" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
            Lihat Produk
        </a>
    </div>
<?php else: ?>
    <!-- Collection Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
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
                <?php if (!empty($item['durasi'])): ?>
                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-lg mb-2" style="background:#E8F5E9; color:#2E7D32"><?= e($item['durasi'] . (!empty($item['paket']) ? ' - ' . $item['paket'] : '')) ?></span>
                <?php endif; ?>
                <p class="text-sm text-gray-500 mb-4">
                    Dibeli: <?= format_tanggal($item['tanggal']) ?>
                </p>

                <?php
                $uid = (int)$item['produk_id'] . '_' . (int)($item['varian_id'] ?? 0);
                ?>
                <?php if (($item['tipe_produk'] ?? '') === 'Akun' && !empty($item['account_info'])): ?>
                    <!-- Account Credentials -->
                    <button type="button"
                            onclick="toggleAkun('<?= $uid ?>')"
                            class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-lg font-semibold transition-colors">
                        <div class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Lihat Akun
                        </div>
                    </button>
                    <div id="akun-<?= $uid ?>" class="hidden mt-3">
                        <div class="bg-gray-900 rounded-lg p-3 relative">
                            <pre id="akun-text-<?= $uid ?>" class="text-xs text-green-300 whitespace-pre-wrap break-words font-mono leading-relaxed"><?= e($item['account_info']) ?></pre>
                        </div>
                        <button type="button"
                                onclick="copyAkun('<?= $uid ?>')"
                                class="mt-2 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm py-2 rounded-lg font-medium transition-colors">
                            Salin Informasi Akun
                        </button>
                    </div>
                <?php elseif (!empty($item['file_upload'])): ?>
                    <!-- Download Button -->
                    <a href="<?= url('/customer/download-file/' . (int)$item['produk_id']) ?>" 
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

<script>
function toggleAkun(id) {
    var box = document.getElementById('akun-' + id);
    if (box) box.classList.toggle('hidden');
}
function copyAkun(id) {
    var el = document.getElementById('akun-text-' + id);
    if (!el) return;
    var text = el.innerText;
    navigator.clipboard.writeText(text).then(function() {
        if (window.showToast) {
            showToast('success', 'Informasi akun disalin ke clipboard');
        } else {
            alert('Informasi akun disalin!');
        }
    });
}
</script>
