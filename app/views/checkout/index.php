<div class="w-full max-w-lg">
    <div class="text-center mb-6">
        <div class="flex items-center justify-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
            </div>
            <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
        </div>
        <p class="text-sm text-gray-500">Checkout Pesanan</p>
    </div>

    <?= flash_render() ?>

    <?php if (!empty($snap_token)): ?>
    <!-- Payment triggered - show loading state -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background:#E8F5E9">
            <svg class="w-8 h-8 animate-pulse" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <h3 class="font-bold text-gray-800 mb-2">Memproses Pembayaran...</h3>
        <p class="text-sm text-gray-500 mb-4">Popup pembayaran akan muncul. Jangan tutup halaman ini.</p>
        <button id="pay-button" class="w-full text-white py-3 rounded-xl font-semibold text-sm transition hover:opacity-90" style="background:#42B549">
            Buka Pembayaran
        </button>
    </div>

    <script type="text/javascript">
        function triggerSnap() {
            window.snap.pay('<?= e($snap_token) ?>', {
                onSuccess: function(result){
                    var orderId = result.order_id || '<?= e($order_ref) ?>';
                    window.location.href = '<?= url("/customer/checkout/callback") ?>?order_id=' + encodeURIComponent(orderId);
                },
                onPending: function(result){ window.location.href = '<?= url("/customer/pembelian") ?>'; },
                onError: function(result){ window.location.href = '<?= url("/customer/pembelian") ?>?msg=error'; },
                onClose: function(){ window.location.href = '<?= url("/customer/pembelian") ?>'; }
            });
        }
        window.onload = triggerSnap;
        document.getElementById('pay-button').onclick = triggerSnap;
    </script>

    <?php else: ?>
    <!-- Checkout review -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Ringkasan Pesanan</p>
                    <p class="font-bold text-gray-800"><?= count($cart_items) ?> Produk Digital</p>
                </div>
            </div>

            <div class="space-y-3">
                <?php foreach ($cart_items as $item): ?>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                            <svg class="w-5 h-5" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800"><?= e($item['nama_produk']) ?></p>
                            <p class="text-xs text-gray-400 line-clamp-1"><?= e($item['deskripsi']) ?></p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-gray-700 flex-shrink-0 ml-3"><?= rupiah($item['harga']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-b border-gray-100">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-700">Total Pembayaran</span>
                <span class="font-bold text-xl" style="color:#42B549"><?= rupiah($total_harga) ?></span>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="<?= url('/customer/checkout') ?>">
                <?= csrf_field() ?>
                <button type="submit" name="proses_checkout" value="1"
                    class="w-full text-white py-3.5 rounded-xl font-bold text-base transition hover:opacity-90 cursor-pointer" style="background:#42B549">
                    Bayar Sekarang - <?= rupiah($total_harga) ?>
                </button>
            </form>
            <a href="<?= url('/customer/keranjang') ?>" class="block text-center text-gray-400 hover:text-gray-600 text-sm mt-3">
                ← Kembali ke Keranjang
            </a>
        </div>
    </div>
    <?php endif; ?>

    <p class="text-center text-xs text-gray-400 mt-4">Pembayaran aman menggunakan enkripsi SSL</p>
</div>
