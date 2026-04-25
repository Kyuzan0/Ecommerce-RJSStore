<?php
/**
 * Cart Dropdown Partial
 *
 * Available variables from layout:
 *   $this->auth  — Auth instance
 *   $cart_api_url, $checkout_url, $login_url — URLs (set by layout before include)
 *   $initial_cart_count — int
 */
$cart_api_url = $cart_api_url ?? url('/api/cart/get');
$cart_post_url = $cart_post_url ?? url('/api/cart');
$checkout_url = $checkout_url ?? url('/checkout');
$login_url = $login_url ?? url('/auth/login');
$is_logged_in_js = $this->auth->check() ? 'true' : 'false';
$initial_cart_count = $initial_cart_count ?? 0;
?>
<!-- Cart Icon + Dropdown -->
<div class="relative" id="cartDropdownContainer">
    <button id="cartToggleBtn" class="relative flex items-center gap-1.5 text-gray-600 hover:text-gray-800 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
        Keranjang
        <span id="cartBadge" class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center text-white text-xs font-bold rounded-full <?= $initial_cart_count > 0 ? '' : 'hidden' ?>" style="background:#E65100"><?= $initial_cart_count ?></span>
    </button>

    <div id="cartDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-200 z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Keranjang Belanja</h3>
            <span id="cartItemCount" class="text-xs text-gray-400"><span id="cartCountText"><?= $initial_cart_count ?></span> item</span>
        </div>

        <div id="cartItemsList" class="max-h-72 overflow-y-auto">
            <div id="cartEmpty" class="py-8 text-center <?= $initial_cart_count > 0 ? 'hidden' : '' ?>">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <p class="text-sm text-gray-400">Keranjang kosong</p>
            </div>
            <div id="cartItemsContent"></div>
        </div>

        <div id="cartFooter" class="border-t border-gray-100 px-4 py-3 <?= $initial_cart_count > 0 ? '' : 'hidden' ?>">
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm font-medium text-gray-600">Subtotal</span>
                <span id="cartSubtotal" class="font-bold text-base" style="color:#42B549">Rp 0</span>
            </div>
            <button id="cartCheckoutBtn" class="w-full text-white py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition cursor-pointer" style="background:#42B549">
                Checkout
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const CART_API_GET = '<?= e(url('/api/cart/get')) ?>';
    const CART_API_ADD = '<?= e(url('/api/cart/add')) ?>';
    const CART_API_REMOVE = '<?= e(url('/api/cart/remove')) ?>';
    const CHECKOUT_URL = '<?= e($checkout_url) ?>';
    const LOGIN_URL = '<?= e($login_url) ?>';
    const isLoggedIn = <?= $is_logged_in_js ?>;

    const cartToggleBtn = document.getElementById('cartToggleBtn');
    const cartDropdown = document.getElementById('cartDropdown');
    const cartBadge = document.getElementById('cartBadge');
    const cartCountText = document.getElementById('cartCountText');
    const cartItemsContent = document.getElementById('cartItemsContent');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartFooter = document.getElementById('cartFooter');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
    const cartContainer = document.getElementById('cartDropdownContainer');

    cartToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = cartDropdown.classList.contains('hidden');
        cartDropdown.classList.toggle('hidden');
        if (isHidden) refreshCart();
    });

    document.addEventListener('click', function(e) {
        if (cartContainer && !cartContainer.contains(e.target)) {
            cartDropdown.classList.add('hidden');
        }
    });

    cartCheckoutBtn.addEventListener('click', function() {
        if (!isLoggedIn) {
            window.location.href = LOGIN_URL + '?next=checkout';
        } else {
            window.location.href = CHECKOUT_URL;
        }
    });

    function refreshCart() {
        fetch(CART_API_GET + '?action=get')
            .then(r => r.json())
            .then(data => { if (data.success) renderCart(data.cart); })
            .catch(() => {});
    }

    function renderCart(cart) {
        updateBadge(cart.count);
        cartCountText.textContent = cart.count;
        cartSubtotal.textContent = cart.total_formatted;

        if (cart.count === 0) {
            cartEmpty.classList.remove('hidden');
            cartItemsContent.innerHTML = '';
            cartFooter.classList.add('hidden');
            return;
        }

        cartEmpty.classList.add('hidden');
        cartFooter.classList.remove('hidden');

        let html = '';
        cart.items.forEach(function(item) {
            html += '<div class="px-4 py-3 flex items-start gap-3 border-b border-gray-50 hover:bg-gray-50 transition" data-produk-id="' + item.produk_id + '">';
            html += '  <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">';
            html += '    <svg class="w-5 h-5" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>';
            html += '  </div>';
            html += '  <div class="flex-1 min-w-0">';
            html += '    <p class="text-sm font-semibold text-gray-800 truncate">' + escapeHtml(item.nama_produk) + '</p>';
            html += '    <p class="text-xs text-gray-400 mt-0.5">' + escapeHtml(item.tipe_produk || 'Lainnya') + '</p>';
            html += '    <p class="text-sm font-bold mt-1" style="color:#42B549">' + item.harga_formatted + '</p>';
            html += '  </div>';
            html += '  <button onclick="window._cartRemove(' + item.produk_id + ')" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition flex-shrink-0" title="Hapus">';
            html += '    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
            html += '  </button>';
            html += '</div>';
        });
        cartItemsContent.innerHTML = html;
    }

    function updateBadge(count) {
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.classList.remove('hidden');
        } else {
            cartBadge.classList.add('hidden');
        }
    }

    window._cartAdd = function(produkId, buttonEl) {
        if (buttonEl) { buttonEl.disabled = true; buttonEl.textContent = '...'; }

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('produk_id', produkId);

        fetch(CART_API_ADD, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCart(data.cart);
                    if (buttonEl) {
                        buttonEl.textContent = 'Di Keranjang \u2713';
                        buttonEl.style.background = '#FF9800';
                        buttonEl.classList.add('cart-added');
                        buttonEl.onclick = function() { cartDropdown.classList.remove('hidden'); refreshCart(); };
                        buttonEl.disabled = false;
                    }
                    if (typeof window.showToast === 'function') window.showToast('success', data.message || 'Ditambahkan ke keranjang!');
                } else {
                    if (buttonEl) { buttonEl.textContent = '+ Keranjang'; buttonEl.disabled = false; }
                    if (typeof window.showToast === 'function') window.showToast('warning', data.message || 'Gagal menambahkan.');
                }
            })
            .catch(() => {
                if (buttonEl) { buttonEl.textContent = '+ Keranjang'; buttonEl.disabled = false; }
                if (typeof window.showToast === 'function') window.showToast('error', 'Gagal menambahkan ke keranjang.');
            });
    };

    window._cartRemove = function(produkId) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('produk_id', produkId);

        fetch(CART_API_REMOVE, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCart(data.cart);
                    const btn = document.querySelector('[data-cart-produk="' + produkId + '"]');
                    if (btn) {
                        btn.textContent = '+ Keranjang';
                        btn.style.background = '#42B549';
                        btn.classList.remove('cart-added');
                        btn.disabled = false;
                        btn.onclick = function() { window._cartAdd(produkId, btn); };
                    }
                    if (typeof window.showToast === 'function') window.showToast('success', data.message || 'Dihapus dari keranjang.');
                }
            })
            .catch(() => {
                if (typeof window.showToast === 'function') window.showToast('error', 'Gagal menghapus dari keranjang.');
            });
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (<?= $initial_cart_count ?> > 0) {
        fetch(CART_API_GET + '?action=get')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.cart.count);
                    cartCountText.textContent = data.cart.count;
                }
            })
            .catch(() => {});
    }
})();
</script>
