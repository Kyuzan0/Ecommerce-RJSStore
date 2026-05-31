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
            $pid = (int) $product['id'];
            $tipe_class = $tipe_colors[$product['tipe_produk']] ?? 'bg-gray-100 text-gray-800';
            $is_purchased = in_array($product['id'], $purchased_produk_ids);
            $is_in_cart = in_array($product['id'], $cart_produk_ids);
            $is_akun = (($product['tipe_produk'] ?? '') === 'Akun');
        ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden product-card cursor-pointer" data-product-id="<?= $pid ?>" data-cart-state="<?= $is_purchased ? 'purchased' : ($is_in_cart ? 'in_cart' : 'default') ?>" onclick="openProductModal(<?= $pid ?>, event)">
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
                <?php if ($is_akun): ?>
                    <p class="text-lg font-bold text-green-600 mb-3"><span class="text-xs font-normal text-gray-400">Mulai </span><?= rupiah($product['harga']) ?></p>
                <?php else: ?>
                    <p class="text-lg font-bold text-green-600 mb-3"><?= rupiah($product['harga']) ?></p>
                <?php endif; ?>

                <!-- Action Button -->
                <?php if ($is_purchased): ?>
                    <button disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed">
                        Sudah Dibeli
                    </button>
                <?php elseif ($is_in_cart): ?>
                    <button data-cart-produk="<?= $pid ?>" disabled class="w-full bg-orange-400 text-white py-2 px-4 rounded-lg cursor-not-allowed">
                        Di Keranjang
                    </button>
                <?php elseif ($is_akun): ?>
                    <button onclick="openProductModal(<?= $pid ?>); event.stopPropagation();"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors">
                        Pilih Varian
                    </button>
                <?php else: ?>
                    <button data-cart-produk="<?= $pid ?>" onclick="window._cartAdd(<?= $pid ?>, this); event.stopPropagation();"
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

<!-- Product Detail Modal -->
<div id="modal-product" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md" onclick="closeProductModal()"></div>
    <div class="relative flex items-center justify-center min-h-full p-4 md:p-6 pointer-events-none">
        <div class="bg-white rounded-[2rem] shadow-2xl border border-gray-100 w-full max-w-3xl pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden relative" style="transform:scale(0.95);opacity:0;transition:transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1.1), opacity 0.2s ease-in-out">
            <button onclick="closeProductModal()" class="absolute top-4 right-4 z-20 p-2 bg-white/50 hover:bg-white/80 backdrop-blur-md rounded-full text-gray-500 hover:text-gray-800 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div id="mp-loading" class="flex flex-col items-center justify-center py-24 z-10 absolute inset-0 bg-white">
                <svg class="animate-spin h-10 w-10 text-green-500 mb-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <p class="text-sm text-gray-500 font-medium animate-pulse">Memuat detail produk...</p>
            </div>

            <div id="mp-content" class="hidden h-full overflow-hidden md:grid md:grid-cols-2">
                <div class="relative flex flex-col min-h-[320px] bg-white border-b md:border-b-0 md:border-r border-white/60 overflow-y-auto" id="mp-hero-bg" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)">
                    <div class="relative z-10 flex-1 flex flex-col px-6 py-8 md:px-8 md:py-10">
                        <div class="mb-6 inline-flex items-center justify-center p-4 rounded-3xl bg-white/90 shadow self-start" id="mp-icon-container">
                            <svg class="w-12 h-12" id="mp-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span id="mp-type-badge" class="text-xs font-bold px-3 py-1 rounded-full border bg-white/70"></span>
                            <div class="flex items-center gap-1.5 bg-white/70 px-3 py-1 rounded-full shadow-sm">
                                <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span id="mp-rating" class="text-xs font-bold text-gray-800"></span>
                                <span id="mp-review-count" class="text-[10px] text-gray-500"></span>
                            </div>
                        </div>

                        <h2 id="mp-title" class="font-extrabold text-2xl md:text-[2rem] text-gray-900 mb-3 leading-tight"></h2>
                        <p id="mp-price" class="font-black text-3xl md:text-4xl mb-6" style="color:#42B549"></p>

                        <div id="mp-variants" class="hidden mb-6">
                            <label class="block text-[11px] font-bold tracking-[0.24em] text-gray-400 uppercase mb-2">Pilih Varian</label>
                            <select id="mp-variant-select" onchange="onVariantChange()" class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 bg-white outline-none focus:border-green-500 transition cursor-pointer">
                                <option value="">— Pilih varian —</option>
                            </select>
                        </div>

                        <div class="rounded-[1.75rem] bg-white/78 border border-white/70 shadow p-5 md:p-6 mb-6">
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
                    </div>
                    <div class="flex-1 overflow-y-auto px-6 py-6 md:px-8 md:py-8" id="mp-body">
                        <div id="mp-reviews" class="grid grid-cols-1 gap-4"></div>
                        <div id="mp-no-reviews" class="hidden bg-white rounded-[1.75rem] border border-gray-100 p-8 text-center shadow">
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
var MP_VARIANTS = [];
var MP_SELECTED_VARIANT = null;

function escapeHtml(unsafe) {
    return (unsafe || '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
function getCard(id) { return document.querySelector('.product-card[data-product-id="' + id + '"]'); }
function getCartState(id) { var c = getCard(id); return c ? (c.getAttribute('data-cart-state') || 'default') : 'default'; }
function setCartState(id, s) { var c = getCard(id); if (c) c.setAttribute('data-cart-state', s); }

function renderModalCTA(productId, state, isLoading) {
    var el = document.getElementById('mp-cta-container');
    if (!el) return;
    if (state === 'purchased') {
        el.innerHTML = '<button class="w-full py-4 rounded-2xl font-bold text-gray-400 bg-gray-100 cursor-not-allowed">Sudah Dibeli</button>';
        return;
    }
    if (state === 'in_cart') {
        el.innerHTML = '<button onclick="closeProductModal()" class="w-full py-4 rounded-2xl font-bold text-white" style="background:#FF9800">Di Keranjang \u2713</button>';
        return;
    }
    if (isLoading) {
        el.innerHTML = '<button disabled class="w-full py-4 rounded-2xl font-bold text-white opacity-90 cursor-wait" style="background:#42B549">Menambahkan...</button>';
        return;
    }
    el.innerHTML = '<button onclick="handleModalCartAdd(' + productId + ')" class="w-full py-4 rounded-2xl font-bold text-white transition hover:opacity-90" style="background:#42B549">+ Keranjang</button>';
}

function renderVariants(product, variants) {
    var wrap = document.getElementById('mp-variants');
    var sel = document.getElementById('mp-variant-select');
    MP_VARIANTS = variants || [];
    MP_SELECTED_VARIANT = null;
    if (!MP_VARIANTS.length) { wrap.classList.add('hidden'); sel.innerHTML = ''; return; }
    wrap.classList.remove('hidden');
    var opts = '<option value="">— Pilih varian —</option>';
    MP_VARIANTS.forEach(function(v) {
        var out = (v.stok !== undefined && v.stok <= 0);
        var stokText = (v.stok !== undefined) ? (out ? ' (Habis)' : ' — Stok: ' + v.stok) : '';
        opts += '<option value="' + v.id + '"' + (out ? ' disabled' : '') + '>' + escapeHtml(v.label) + ' — ' + escapeHtml(v.harga_formatted) + stokText + '</option>';
    });
    sel.innerHTML = opts;
}

function onVariantChange() {
    var sel = document.getElementById('mp-variant-select');
    var id = parseInt(sel.value, 10);
    if (!id) { MP_SELECTED_VARIANT = null; return; }
    var variant = MP_VARIANTS.find(function(v) { return v.id === id; });
    if (variant) {
        MP_SELECTED_VARIANT = variant;
        document.getElementById('mp-price').textContent = variant.harga_formatted;
    }
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
            reviewsEl.innerHTML += '<div class="bg-gray-50 rounded-2xl p-5 border border-gray-100/50"><div class="flex items-center gap-3 mb-3"><div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:#42B549">' + escapeHtml(rev.initial) + '</div><div><p class="text-sm font-bold text-gray-800">' + escapeHtml(rev.nama_user) + '</p><span class="text-xs text-gray-400">' + escapeHtml(rev.tanggal) + '</span></div><div class="flex items-center gap-0.5 ml-auto">' + stars + '</div></div><p class="text-sm text-gray-600">' + escapeHtml(rev.ulasan) + '</p></div>';
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
    badge.style.background = product.tipe_bg + '33';
    document.getElementById('mp-icon').style.color = product.tipe_color;
    document.getElementById('mp-hero-bg').style.background = 'linear-gradient(135deg, ' + product.tipe_bg + '15 0%, ' + product.tipe_bg + '30 100%)';
    var ratingVal = parseFloat(product.avg_rating);
    document.getElementById('mp-rating').textContent = ratingVal > 0 ? product.avg_rating : '-';
    document.getElementById('mp-review-count').textContent = product.total_reviews + ' ulasan';
    renderModalCTA(product.id, getCartState(product.id), false);
}

function resetModal() {
    document.getElementById('mp-content').classList.add('hidden');
    document.getElementById('mp-loading').classList.remove('hidden');
    document.getElementById('mp-reviews').innerHTML = '';
    document.getElementById('mp-no-reviews').classList.add('hidden');
    document.getElementById('mp-cta-container').innerHTML = '';
    document.getElementById('mp-variants').classList.add('hidden');
}

function openProductModal(productId, event) {
    if (event && (event.target.closest('button') || event.target.closest('a'))) return;
    var modal = document.getElementById('modal-product');
    resetModal();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function() {
        var c = modal.querySelector('.modal-content');
        c.style.transform = 'scale(1)';
        c.style.opacity = '1';
    });
    fetch(PRODUCT_DETAIL_API + '/' + productId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                document.getElementById('mp-loading').innerHTML = '<p class="text-sm text-red-500">Gagal memuat produk.</p>';
                return;
            }
            renderModalProduct(data.product);
            renderVariants(data.product, data.variants || []);
            renderModalReviews(data.reviews || []);
            document.getElementById('mp-loading').classList.add('hidden');
            document.getElementById('mp-content').classList.remove('hidden');
        })
        .catch(function() {
            document.getElementById('mp-loading').innerHTML = '<p class="text-sm text-red-500">Gagal memuat produk.</p>';
        });
}

function handleModalCartAdd(productId) {
    if (MP_VARIANTS.length > 0 && !MP_SELECTED_VARIANT) {
        if (typeof window.showToast === 'function') window.showToast('warning', 'Silakan pilih varian terlebih dahulu.');
        return;
    }
    renderModalCTA(productId, 'default', true);
    var formData = new FormData();
    formData.append('action', 'add');
    formData.append('produk_id', productId);
    if (MP_SELECTED_VARIANT) formData.append('varian_id', MP_SELECTED_VARIANT.id);
    fetch(CART_API_URL + '/add', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                setCartState(productId, 'in_cart');
                renderModalCTA(productId, 'in_cart', false);
                if (typeof window.refreshCartBadge === 'function') window.refreshCartBadge();
                if (typeof window.showToast === 'function') window.showToast('success', data.message || 'Ditambahkan ke keranjang!');
            } else {
                renderModalCTA(productId, getCartState(productId), false);
                if (typeof window.showToast === 'function') window.showToast('warning', data.message || 'Gagal menambahkan.');
            }
        })
        .catch(function() {
            renderModalCTA(productId, getCartState(productId), false);
            if (typeof window.showToast === 'function') window.showToast('error', 'Gagal menambahkan ke keranjang.');
        });
}

function closeProductModal() {
    var modal = document.getElementById('modal-product');
    var c = modal.querySelector('.modal-content');
    c.style.transform = 'scale(0.95)';
    c.style.opacity = '0';
    setTimeout(function() { modal.classList.add('hidden'); document.body.style.overflow = ''; resetModal(); }, 300);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('modal-product').classList.contains('hidden')) closeProductModal();
});
</script>
