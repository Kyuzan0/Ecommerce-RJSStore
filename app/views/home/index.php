<!-- HERO BANNER (only on first page without search) -->
<?php if (empty($search) && ($paging['page'] ?? 1) == 1): ?>
<div class="max-w-7xl mx-auto px-6 mt-6">
    <div class="rounded-2xl p-8 md:p-12 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #42B549 0%, #2E7D32 100%)">
        <div class="relative z-10 max-w-lg">
            <h1 class="text-2xl md:text-3xl font-extrabold mb-3">Selamat Datang di RJSStore</h1>
            <p class="text-green-100 text-sm md:text-base mb-5">Temukan berbagai produk digital berkualitas dengan harga terbaik. Download langsung setelah pembayaran!</p>
            <?php if (!$this->auth->check()): ?>
            <a href="<?= url('/auth/register') ?>" class="inline-flex items-center gap-2 bg-white text-green-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-green-50 transition">
                Mulai Belanja
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-10">
            <svg class="w-48 h-48" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ULASAN PELANGGAN -->
<?php if (!empty($ulasan_terbaru) && empty($search) && ($paging['page'] ?? 1) == 1): ?>
<div class="max-w-7xl mx-auto px-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Ulasan Pelanggan</h2>
        <span class="text-sm text-gray-400">Dari pembeli terverifikasi</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($ulasan_terbaru as $ulasan): ?>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 flex flex-col gap-3 hover:shadow-md transition">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:#42B549">
                    <?= strtoupper(substr($ulasan['nama_user'], 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate"><?= e($ulasan['nama_user']) ?></p>
                    <p class="text-xs text-gray-400 truncate"><?= e($ulasan['nama_produk']) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="w-4 h-4 <?= $i <= $ulasan['rating'] ? 'text-yellow-400' : 'text-gray-200' ?>" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <?php endfor; ?>
                <span class="text-xs text-gray-400 ml-1"><?= format_tanggal($ulasan['tanggal']) ?></span>
            </div>
            <p class="text-sm text-gray-600 line-clamp-3"><?= e($ulasan['ulasan']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="max-w-7xl mx-auto px-6 py-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-gray-800">
            <?php if (!empty($search)): ?>
                Hasil Pencarian: "<?= e($search) ?>"
            <?php else: ?>
                Katalog Produk Digital
            <?php endif; ?>
        </h2>
        <span class="text-sm text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
            Produk digital terpercaya
        </span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php if (empty($products)): ?>
        <div class="col-span-full text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-500 font-medium">Tidak ada produk ditemukan</p>
            <?php if (!empty($search)): ?>
            <a href="<?= url('/') ?>" class="inline-block mt-3 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 transition" style="color:#42B549">Lihat semua produk</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php foreach ($products as $row):
            $pid = (int) $row['id'];
            $in_cart = in_array($pid, $cart_produk_ids);
            $purchased = in_array($pid, $purchased_produk_ids);
            $tipe_cfg = tipe_produk_config($row['tipe_produk'] ?? 'Lainnya');
            $avg = $row['avg_rating'] ? round((float) $row['avg_rating'], 1) : '0.0';
            $total_ulasan = (int) ($row['total_reviews'] ?? 0);
        ?>
        <div class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100 flex flex-col cursor-pointer" data-product-id="<?= $pid ?>" data-cart-state="<?= $purchased ? 'purchased' : ($in_cart ? 'in_cart' : 'default') ?>" onclick="openProductModal(<?= $pid ?>, event)">
            <div class="relative h-40 flex items-center justify-center" style="background: linear-gradient(135deg, <?= $tipe_cfg['bg'] ?> 0%, <?= $tipe_cfg['bg'] ?>dd 100%)">
                <svg class="w-16 h-16 opacity-40" style="color:<?= $tipe_cfg['color'] ?>" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                <span class="absolute top-2 left-2 bg-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm" style="color:<?= $tipe_cfg['color'] ?>"><?= e($tipe_cfg['label']) ?></span>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <h3 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-2"><?= e($row['nama_produk']); ?></h3>
                <p class="text-xs text-gray-400 mb-3 line-clamp-2 flex-1"><?= e($row['deskripsi']); ?></p>
                
                <div class="flex items-center gap-1.5 mb-2">
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700"><?= $avg; ?></span>
                    <span class="text-xs text-gray-400">(<?= $total_ulasan; ?> Ulasan)</span>
                </div>
                
                <p class="font-bold text-lg mb-3" style="color:#42B549"><?= rupiah($row['harga']); ?></p>
                
                <?php if ($purchased): ?>
                    <span class="block text-center text-gray-400 py-2.5 rounded-xl text-sm font-medium bg-gray-100 cursor-not-allowed">
                        Sudah Dibeli
                    </span>
                <?php elseif ($in_cart): ?>
                    <button data-cart-produk="<?= $pid ?>"
                       onclick="document.getElementById('cartDropdown').classList.remove('hidden'); event.stopPropagation();"
                       class="w-full text-center text-white py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition cursor-pointer cart-added" style="background:#FF9800">
                        Di Keranjang ✓
                    </button>
                <?php else: ?>
                    <button data-cart-produk="<?= $pid ?>"
                       onclick="window._cartAdd(<?= $pid ?>, this); event.stopPropagation();"
                       class="w-full text-center text-white py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition cursor-pointer" style="background:#42B549">
                        + Keranjang
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?= pagination_render($paging) ?>
</div>

<!-- Product Detail Modal -->
<div id="modal-product" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md" onclick="closeProductModal()"></div>
    <div class="relative flex items-center justify-center min-h-full p-4 md:p-6 pointer-events-none">
        <div class="bg-white rounded-[2rem] shadow-2xl border border-gray-100 w-full max-w-3xl pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden relative" style="transform:scale(0.95);opacity:0;transition:transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1.1), opacity 0.2s ease-in-out">
            
            <!-- Close Button -->
            <button onclick="closeProductModal()" class="absolute top-4 right-4 z-20 p-2 bg-white/50 hover:bg-white/80 backdrop-blur-md rounded-full text-gray-500 hover:text-gray-800 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Loading state -->
            <div id="mp-loading" class="flex flex-col items-center justify-center py-24 z-10 absolute inset-0 bg-white">
                <svg class="animate-spin h-10 w-10 text-green-500 mb-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <p class="text-sm text-gray-500 font-medium animate-pulse">Memuat detail produk...</p>
            </div>

            <!-- Content (hidden until loaded) -->
            <div id="mp-content" class="hidden h-full overflow-hidden md:grid md:grid-cols-2">
                <div class="relative flex flex-col min-h-[320px] bg-white border-b md:border-b-0 md:border-r border-white/60" id="mp-hero-bg" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)">
                    <div class="absolute inset-0 overflow-hidden pointer-events-none">
                        <div class="absolute -top-24 -left-24 w-48 h-48 bg-white/40 rounded-full blur-3xl"></div>
                        <div class="absolute top-1/3 -right-20 w-40 h-40 bg-white/35 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-16 left-10 w-36 h-36 bg-white/30 rounded-full blur-3xl"></div>
                    </div>

                    <div class="relative z-10 flex-1 flex flex-col px-6 py-8 md:px-8 md:py-10">
                        <div class="mb-6 inline-flex items-center justify-center p-4 rounded-3xl bg-white/90 shadow-[0_18px_40px_-20px_rgba(0,0,0,0.28)] self-start" id="mp-icon-container">
                            <svg class="w-12 h-12" id="mp-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span id="mp-type-badge" class="text-xs font-bold px-3 py-1 rounded-full border border-current/20 bg-white/70 backdrop-blur-sm"></span>
                            <div class="flex items-center gap-1.5 bg-white/70 backdrop-blur-sm px-3 py-1 rounded-full shadow-sm">
                                <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span id="mp-rating" class="text-xs font-bold text-gray-800"></span>
                                <span id="mp-review-count" class="text-[10px] text-gray-500"></span>
                            </div>
                        </div>

                        <h2 id="mp-title" class="font-extrabold text-2xl md:text-[2rem] text-gray-900 mb-3 leading-tight text-left"></h2>
                        <p id="mp-price" class="font-black text-3xl md:text-4xl mb-6" style="color:#42B549"></p>

                        <div class="rounded-[1.75rem] bg-white/78 backdrop-blur-md border border-white/70 shadow-[0_18px_50px_-28px_rgba(0,0,0,0.28)] p-5 md:p-6 mb-6">
                            <h4 class="text-[11px] font-bold tracking-[0.24em] text-gray-400 uppercase mb-3">Informasi Produk</h4>
                            <p id="mp-desc" class="text-sm text-gray-600 leading-relaxed whitespace-pre-line"></p>
                        </div>

                        <div class="mt-auto">
                            <div id="mp-cta-container" class="w-full"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col bg-gradient-to-b from-white to-[#f7faf7] min-h-[320px]">
                    <div class="px-6 py-6 md:px-8 md:py-8 border-b border-gray-100/80 shrink-0">
                        <p class="text-[11px] font-bold tracking-[0.24em] text-gray-400 uppercase mb-2">Ulasan Pelanggan</p>
                        <h4 class="text-xl font-bold text-gray-900">Apa kata pembeli</h4>
                        <p class="text-sm text-gray-500 mt-1">Baca pengalaman pelanggan sebelum menambahkan produk ke keranjang.</p>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 py-6 md:px-8 md:py-8" id="mp-body">
                        <div id="mp-reviews" class="grid grid-cols-1 gap-4">
                        </div>

                        <div id="mp-no-reviews" class="hidden bg-white rounded-[1.75rem] border border-gray-100 p-8 text-center shadow-[0_20px_50px_-35px_rgba(0,0,0,0.2)]">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <p class="text-gray-500 font-medium">Belum ada ulasan</p>
                            <p class="text-xs text-gray-400 mt-1">Jadilah yang pertama mencoba produk ini!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var PRODUCT_DETAIL_API = '<?= url("/api/product-detail") ?>';
var CART_API_URL = '<?= url("/api/cart") ?>';
var PRODUCT_MODAL_LOADING_HTML = '<svg class="animate-spin h-10 w-10 text-green-500 mb-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg><p class="text-sm text-gray-500 font-medium animate-pulse">Memuat detail produk...</p>';

function escapeHtml(unsafe) {
    return (unsafe || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getProductCard(productId) {
    return document.querySelector('.product-card[data-product-id="' + productId + '"]');
}

function getProductCardButton(productId) {
    return document.querySelector('[data-cart-produk="' + productId + '"]');
}

function getProductCartState(productId) {
    var card = getProductCard(productId);
    return card ? (card.getAttribute('data-cart-state') || 'default') : 'default';
}

function setProductCartState(productId, state) {
    var card = getProductCard(productId);
    if (card) {
        card.setAttribute('data-cart-state', state);
    }
}

function renderModalCTA(productId, state, isLoading) {
    var ctaContainer = document.getElementById('mp-cta-container');
    if (!ctaContainer) return;

    if (state === 'purchased') {
        ctaContainer.innerHTML = '<button class="w-full py-4 rounded-2xl font-bold text-gray-400 bg-gray-100 cursor-not-allowed transition-all">Sudah Dibeli</button>';
        return;
    }

    if (state === 'in_cart') {
        ctaContainer.innerHTML = '<button onclick="openCartFromModal()" class="w-full py-4 rounded-2xl font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-orange-500/30" style="background: linear-gradient(135deg, #FFB74D 0%, #F57C00 100%); box-shadow: 0 10px 25px -5px rgba(245,124,0,0.4)">Di Keranjang ✓</button>';
        return;
    }

    if (isLoading) {
        ctaContainer.innerHTML = '<button disabled class="w-full py-4 rounded-2xl font-bold text-white shadow-lg transition-all opacity-90 cursor-wait" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); box-shadow: 0 10px 25px -5px rgba(76,175,80,0.4)"><span class="inline-flex items-center gap-2"><svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Menambahkan...</span></button>';
        return;
    }

    ctaContainer.innerHTML = '<button onclick="handleModalCartAdd(' + productId + ')" class="w-full py-4 rounded-2xl font-bold text-white shadow-lg transition-all hover:scale-[1.02] hover:shadow-green-500/30" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); box-shadow: 0 10px 25px -5px rgba(76,175,80,0.4)">+ Keranjang</button>';
}

function syncPageCardToInCart(productId) {
    var cardBtn = getProductCardButton(productId);
    if (!cardBtn) return;

    cardBtn.textContent = 'Di Keranjang ✓';
    cardBtn.style.background = '#FF9800';
    cardBtn.classList.add('cart-added');
    cardBtn.disabled = false;
    cardBtn.onclick = function(e) {
        e.stopPropagation();
        document.getElementById('cartDropdown').classList.remove('hidden');
    };

    setProductCartState(productId, 'in_cart');
}

function renderModalReviews(reviews) {
    var reviewsEl = document.getElementById('mp-reviews');
    var noReviews = document.getElementById('mp-no-reviews');

    reviewsEl.innerHTML = '';

    if (reviews && reviews.length > 0) {
        noReviews.classList.add('hidden');
        reviewsEl.classList.remove('hidden');

        reviews.forEach(function(rev) {
            var stars = '';
            for (var i = 1; i <= 5; i++) {
                stars += '<svg class="w-4 h-4 ' + (i <= rev.rating ? 'text-yellow-400' : 'text-gray-200') + '" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
            }

            reviewsEl.innerHTML += '<div class="bg-gray-50 rounded-2xl p-5 border border-gray-100/50 hover:bg-white hover:shadow-md hover:border-gray-200 transition-all">' +
                '<div class="flex items-start justify-between gap-3 mb-3">' +
                    '<div class="flex items-center gap-3 min-w-0">' +
                        '<div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-sm shrink-0" style="background: linear-gradient(135deg, #42B549 0%, #2E7D32 100%)">' + escapeHtml(rev.initial) + '</div>' +
                        '<div class="min-w-0">' +
                            '<p class="text-sm font-bold text-gray-800 truncate">' + escapeHtml(rev.nama_user) + '</p>' +
                            '<span class="text-xs text-gray-400 font-medium">' + escapeHtml(rev.tanggal) + '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex items-center gap-0.5 bg-white px-2 py-1 rounded-full shadow-sm shrink-0">' + stars + '</div>' +
                '</div>' +
                '<p class="text-sm text-gray-600 leading-relaxed">' + escapeHtml(rev.ulasan) + '</p>' +
            '</div>';
        });

        return;
    }

    noReviews.classList.remove('hidden');
    reviewsEl.classList.add('hidden');
}

function renderModalProduct(product) {
    document.getElementById('mp-title').textContent = product.nama_produk;
    document.getElementById('mp-price').textContent = product.harga_formatted;
    document.getElementById('mp-desc').textContent = product.deskripsi;

    var badge = document.getElementById('mp-type-badge');
    badge.textContent = product.tipe_label;
    badge.style.color = product.tipe_color;
    badge.style.borderColor = product.tipe_color + '40';
    badge.style.background = product.tipe_bg + '33';

    document.getElementById('mp-icon').style.color = product.tipe_color;
    document.getElementById('mp-hero-bg').style.background = 'linear-gradient(135deg, ' + product.tipe_bg + '15 0%, ' + product.tipe_bg + '30 100%)';
    document.getElementById('mp-icon-container').style.boxShadow = '0 8px 30px ' + product.tipe_bg + '40';

    var ratingVal = parseFloat(product.avg_rating);
    document.getElementById('mp-rating').textContent = ratingVal > 0 ? product.avg_rating : '-';
    document.getElementById('mp-review-count').textContent = product.total_reviews + ' ulasan';

    renderModalCTA(product.id, getProductCartState(product.id), false);
}

function resetProductModalState() {
    document.getElementById('mp-content').classList.add('hidden');
    var loading = document.getElementById('mp-loading');
    loading.classList.remove('hidden');
    loading.innerHTML = PRODUCT_MODAL_LOADING_HTML;
    document.getElementById('mp-reviews').innerHTML = '';
    document.getElementById('mp-no-reviews').classList.add('hidden');
    document.getElementById('mp-cta-container').innerHTML = '';
}

function openCartFromModal() {
    closeProductModal();
    var cartDropdown = document.getElementById('cartDropdown');
    if (cartDropdown) {
        cartDropdown.classList.remove('hidden');
    }
}

function openProductModal(productId, event) {
    if (event && (event.target.closest('button') || event.target.closest('a'))) return;

    var modal = document.getElementById('modal-product');
    resetProductModalState();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    requestAnimationFrame(function() {
        var content = modal.querySelector('.modal-content');
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    });

    fetch(PRODUCT_DETAIL_API + '/' + productId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                document.getElementById('mp-loading').innerHTML = '<div class="text-center"><p class="text-sm text-red-500 font-medium mb-3">' + escapeHtml(data.message || 'Gagal memuat data produk.') + '</p><button onclick="openProductModal(' + productId + ')" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#42B549">Coba Lagi</button></div>';
                return;
            }

            renderModalProduct(data.product);
            renderModalReviews(data.reviews || []);

            document.getElementById('mp-loading').classList.add('hidden');
            document.getElementById('mp-content').classList.remove('hidden');
        })
        .catch(function() {
            document.getElementById('mp-loading').innerHTML = '<div class="text-center"><p class="text-sm text-red-500 font-medium mb-3">Gagal memuat data produk.</p><button onclick="openProductModal(' + productId + ')" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#42B549">Coba Lagi</button></div>';
        });
}

function handleModalCartAdd(productId) {
    renderModalCTA(productId, 'default', true);

    var formData = new FormData();
    formData.append('action', 'add');
    formData.append('produk_id', productId);

    fetch(CART_API_URL + '/add', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                syncPageCardToInCart(productId);
                renderModalCTA(productId, 'in_cart', false);

                var cartToggleBtn = document.getElementById('cartToggleBtn');
                if (cartToggleBtn && typeof cartToggleBtn.click === 'function') {
                    cartToggleBtn.click();
                    setTimeout(function() {
                        document.getElementById('cartDropdown').classList.add('hidden');
                    }, 50);
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('success', data.message || 'Produk ditambahkan ke keranjang!');
                }
                return;
            }

            renderModalCTA(productId, getProductCartState(productId), false);
            if (typeof window.showToast === 'function') {
                window.showToast('warning', data.message || 'Gagal menambahkan ke keranjang.');
            }
        })
        .catch(function() {
            renderModalCTA(productId, getProductCartState(productId), false);
            if (typeof window.showToast === 'function') {
                window.showToast('error', 'Gagal menambahkan ke keranjang.');
            }
        });
}

function closeProductModal() {
    var modal = document.getElementById('modal-product');
    var content = modal.querySelector('.modal-content');
    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';

    setTimeout(function() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        resetProductModalState();
    }, 300);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('modal-product').classList.contains('hidden')) {
        closeProductModal();
    }
});
</script>
