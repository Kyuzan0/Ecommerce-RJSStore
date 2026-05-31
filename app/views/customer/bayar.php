<div class="max-w-2xl mx-auto">
    <!-- Payment Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-8">
        <h1 class="ds-page-title mb-5">Pembayaran</h1>

        <!-- Order Items -->
        <div class="mb-5">
            <h3 class="text-sm font-semibold text-gray-600 mb-3">Detail Pesanan</h3>
            <div class="divide-y divide-gray-100 border-y border-gray-100">
                <?php foreach ($items as $item): ?>
                <div class="flex flex-wrap justify-between items-center gap-2 py-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-800 text-sm sm:text-base truncate"><?= e($item['nama_produk']) ?></p>
                        <p class="text-xs text-gray-500 mt-0.5"><?= e($item['tipe_produk']) ?></p>
                    </div>
                    <p class="font-semibold text-gray-800 flex-shrink-0"><?= rupiah($item['harga']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Total -->
        <div class="border-t border-gray-200 pt-4 mb-5">
            <div class="flex justify-between items-center gap-3">
                <span class="text-base sm:text-lg font-semibold text-gray-800">Total Pembayaran</span>
                <span class="text-xl sm:text-2xl font-bold text-green-600"><?= rupiah($total) ?></span>
            </div>
        </div>

        <!-- Payment Button -->
        <button id="pay-button"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition-colors">
            Bayar Sekarang
        </button>

        <!-- Back Link -->
        <div class="mt-4 text-center">
            <a href="<?= url('/customer/pembelian') ?>" class="text-sm text-gray-600 hover:text-green-600">
                Kembali ke Pembelian
            </a>
        </div>
    </div>
</div>

<script src="<?= e($snap_url) ?>" data-client-key="<?= e($client_key) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const snapToken = '<?= e($snap_token) ?>';
    const callbackBase = '<?= url("/customer/checkout/callback") ?>';
    const pembelianUrl = '<?= url("/customer/pembelian") ?>';
    const errorUrl = '<?= url("/customer/pembelian?msg=error") ?>';

    function handleSuccess(result) {
        var orderId = result.order_id || '';
        window.location.href = callbackBase + '?order_id=' + encodeURIComponent(orderId);
    }
    function handlePending(result) { window.location.href = pembelianUrl; }
    function handleError(result) { window.location.href = errorUrl; }

    // Auto-trigger payment on page load
    snap.pay(snapToken, {
        onSuccess: handleSuccess,
        onPending: handlePending,
        onError: handleError,
        onClose: function() { console.log('Payment popup closed'); }
    });

    // Also bind to button click
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay(snapToken, {
            onSuccess: handleSuccess,
            onPending: handlePending,
            onError: handleError,
            onClose: function() { console.log('Payment popup closed'); }
        });
    });
});
</script>
