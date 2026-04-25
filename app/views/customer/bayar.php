<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Payment Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Pembayaran</h1>
            
            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">Detail Pesanan</h3>
                <div class="space-y-3">
                    <?php foreach ($items as $item): ?>
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <div>
                            <p class="font-medium text-gray-800"><?= e($item['nama_produk']) ?></p>
                            <p class="text-sm text-gray-500"><?= e($item['tipe_produk']) ?></p>
                        </div>
                        <p class="font-semibold text-gray-800"><?= rupiah($item['harga']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Total -->
            <div class="border-t border-gray-300 pt-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-800">Total Pembayaran</span>
                    <span class="text-2xl font-bold text-green-600"><?= rupiah($total) ?></span>
                </div>
            </div>
            
            <!-- Payment Button -->
            <button id="pay-button" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition-colors">
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
</div>

<script src="<?= e($snap_url) ?>/snap.js" data-client-key="<?= e($client_key) ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const snapToken = '<?= e($snap_token) ?>';
    
    // Auto-trigger payment on page load
    snap.pay(snapToken, {
        onSuccess: function(result) {
            console.log('Payment success:', result);
            window.location.href = '<?= url('/customer/pembelian') ?>';
        },
        onPending: function(result) {
            console.log('Payment pending:', result);
            window.location.href = '<?= url('/customer/pembelian') ?>';
        },
        onError: function(result) {
            console.error('Payment error:', result);
            window.location.href = '<?= url('/customer/pembelian?msg=error') ?>';
        },
        onClose: function() {
            console.log('Payment popup closed');
        }
    });
    
    // Also bind to button click
    document.getElementById('pay-button').addEventListener('click', function() {
        snap.pay(snapToken, {
            onSuccess: function(result) {
                window.location.href = '<?= url('/customer/pembelian') ?>';
            },
            onPending: function(result) {
                window.location.href = '<?= url('/customer/pembelian') ?>';
            },
            onError: function(result) {
                window.location.href = '<?= url('/customer/pembelian?msg=error') ?>';
            },
            onClose: function() {
                console.log('Payment popup closed');
            }
        });
    });
});
</script>
