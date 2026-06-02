<div class="relative w-full max-w-md">
    <!-- Decorative background blobs -->
    <div class="absolute -top-12 -left-12 w-48 h-48 bg-green-500/10 rounded-full blur-3xl dark:bg-green-500/5"></div>
    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl dark:bg-emerald-500/5"></div>

    <!-- Glassmorphism Card -->
    <div class="relative bg-white/70 dark:bg-gray-900/60 backdrop-blur-xl rounded-[2.5rem] p-8 md:p-10 border border-white/60 dark:border-gray-800 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.08)] dark:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] text-center">
        
        <!-- Glowing Circular Loader -->
        <div class="relative w-20 h-20 mx-auto mb-8">
            <!-- Background ring -->
            <div class="absolute inset-0 rounded-full border-4 border-gray-100 dark:border-gray-800"></div>
            <!-- Spinning ring -->
            <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-green-500 animate-spin"></div>
            <!-- Inner pulse icon -->
            <div class="absolute inset-0 flex items-center justify-center">
                <svg class="w-8 h-8 text-green-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>

        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Memverifikasi Pembayaran</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
            Mohon tunggu sebentar, kami sedang menyinkronkan status pembayaran Anda dengan payment gateway.
        </p>
        
        <!-- Action Subtext / Status indicator -->
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-50 dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/50">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
            </span>
            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">Menghubungi Midtrans...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderId = '<?= e($order_ref) ?>';
    const verifyUrl = '<?= url("/customer/checkout/callback") ?>?action=verify&order_id=' + encodeURIComponent(orderId);
    const redirectUrl = '<?= url("/customer/pembelian") ?>';

    // Small delay to simulate smooth loading transitions
    setTimeout(function() {
        fetch(verifyUrl)
            .then(response => response.json())
            .then(data => {
                // Redirect back to purchases list - flash message handles user feedback
                window.location.href = redirectUrl;
            })
            .catch(error => {
                console.error('Verification error:', error);
                window.location.href = redirectUrl;
            });
    }, 1500);
});
</script>
