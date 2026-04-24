<?php
/**
 * Cart Dropdown Partial
 * 
 * Renders the cart icon with badge + dropdown UI + all JavaScript logic.
 * Used by both index.php (public storefront) and customer_header.php (logged-in pages).
 * 
 * Required variables before include:
 *   $cart_api_url  - path to api/cart_action.php (e.g. 'api/cart_action.php' or '../api/cart_action.php')
 *   $checkout_url  - path to checkout page (e.g. 'customer/checkout.php' or 'checkout.php')
 *   $login_url     - path to login page (e.g. 'auth/login.php' or '../auth/login.php')
 *   $is_logged_in  - boolean, whether user is logged in
 *   $initial_cart_count - int, initial cart item count for badge
 */
$cart_api_url = $cart_api_url ?? 'api/cart_action.php';
$checkout_url = $checkout_url ?? 'customer/checkout.php';
$login_url = $login_url ?? 'auth/login.php';
$is_logged_in_js = ($is_logged_in ?? false) ? 'true' : 'false';
$initial_cart_count = $initial_cart_count ?? 0;
?>
<!-- Cart Icon + Dropdown -->
<div class="relative" id="cartDropdownContainer">
    <button id="cartToggleBtn" class="relative flex items-center gap-1.5 text-gray-600 hover:text-gray-800 text-sm px-3 py-2 rounded-lg hover:bg-gray-100 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
        Keranjang
        <span id="cartBadge" class="absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center text-white text-xs font-bold rounded-full <?= $initial_cart_count > 0 ? '' : 'hidden' ?>" style="background:#E65100"><?= $initial_cart_count ?></span>
    </button>

    <!-- Dropdown Panel -->
    <div id="cartDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-200 z-50 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Keranjang Belanja</h3>
            <span id="cartItemCount" class="text-xs text-gray-400"><span id="cartCountText"><?= $initial_cart_count ?></span> item</span>
        </div>

        <!-- Items Container (scrollable) -->
        <div id="cartItemsList" class="max-h-72 overflow-y-auto">
            <!-- Items rendered by JS -->
            <div id="cartEmpty" class="py-8 text-center <?= $initial_cart_count > 0 ? 'hidden' : '' ?>">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <p class="text-sm text-gray-400">Keranjang kosong</p>
            </div>
            <div id="cartItemsContent"></div>
        </div>

        <!-- Footer: Subtotal + Checkout -->
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
    const CART_API = '<?= e($cart_api_url) ?>';
    const CHECKOUT_URL = '<?= e($checkout_url) ?>';
    const LOGIN_URL = '<?= e($login_url) ?>';
    const isLoggedIn = <?= $is_logged_in_js ?>;

    const cartToggleBtn = document.getElementById('cartToggleBtn');
    const cartDropdown = document.getElementById('cartDropdown');
    const cartBadge = document.getElementById('cartBadge');
    const cartCountText = document.getElementById('cartCountText');
    const cartItemsList = document.getElementById('cartItemsList');
    const cartItemsContent = document.getElementById('cartItemsContent');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartFooter = document.getElementById('cartFooter');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartCheckoutBtn = document.getElementById('cartCheckoutBtn');
    const cartContainer = document.getElementById('cartDropdownContainer');

    // Toggle dropdown
    cartToggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const isHidden = cartDropdown.classList.contains('hidden');
        cartDropdown.classList.toggle('hidden');
        if (isHidden) {
            refreshCart();
        }
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (cartContainer && !cartContainer.contains(e.target)) {
            cartDropdown.classList.add('hidden');
        }
    });

    // Checkout button
    cartCheckoutBtn.addEventListener('click', function() {
        if (!isLoggedIn) {
            window.location.href = LOGIN_URL + '?next=checkout';
        } else {
            window.location.href = CHECKOUT_URL;
        }
    });

    // Refresh cart from server
    function refreshCart() {
        fetch(CART_API + '?action=get')
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCart(data.cart);
                }
            })
            .catch(() => {});
    }

    // Render cart items in dropdown
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

    // Update badge count
    function updateBadge(count) {
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.classList.remove('hidden');
        } else {
            cartBadge.classList.add('hidden');
        }
    }

    // Add to cart (called from product buttons)
    window._cartAdd = function(produkId, buttonEl) {
        if (buttonEl) {
            buttonEl.disabled = true;
            buttonEl.textContent = '...';
        }

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('produk_id', produkId);

        fetch(CART_API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCart(data.cart);
                    if (buttonEl) {
                        buttonEl.textContent = 'Di Keranjang ✓';
                        buttonEl.style.background = '#FF9800';
                        buttonEl.classList.add('cart-added');
                        buttonEl.onclick = function() {
                            cartDropdown.classList.remove('hidden');
                            refreshCart();
                        };
                        buttonEl.disabled = false;
                    }
                    showToast(data.message, 'success');
                } else {
                    if (buttonEl) {
                        buttonEl.textContent = '+ Keranjang';
                        buttonEl.disabled = false;
                    }
                    showToast(data.message, 'warning');
                }
            })
            .catch(() => {
                if (buttonEl) {
                    buttonEl.textContent = '+ Keranjang';
                    buttonEl.disabled = false;
                }
                showToast('Gagal menambahkan ke keranjang.', 'error');
            });
    };

    // Remove from cart
    window._cartRemove = function(produkId) {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('produk_id', produkId);

        fetch(CART_API, { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderCart(data.cart);
                    // Reset the product button if visible on page
                    const btn = document.querySelector('[data-cart-produk="' + produkId + '"]');
                    if (btn) {
                        btn.textContent = '+ Keranjang';
                        btn.style.background = '#42B549';
                        btn.classList.remove('cart-added');
                        btn.disabled = false;
                        btn.onclick = function() { window._cartAdd(produkId, btn); };
                    }
                    showToast(data.message, 'success');
                }
            })
            .catch(() => {
                showToast('Gagal menghapus dari keranjang.', 'error');
            });
    };

    // Toast notification
    function showToast(message, type) {
        const colors = {
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            error: 'bg-red-500',
            info: 'bg-blue-500'
        };
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 z-[100] px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg transition-all duration-300 ' + (colors[type] || colors.info);
        toast.textContent = message;
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        document.body.appendChild(toast);

        requestAnimationFrame(function() {
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
        });

        setTimeout(function() {
            toast.style.transform = 'translateY(20px)';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 2500);
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initial load
    if (<?= $initial_cart_count ?> > 0) {
        // Pre-fetch cart data for badge accuracy
        fetch(CART_API + '?action=get')
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
