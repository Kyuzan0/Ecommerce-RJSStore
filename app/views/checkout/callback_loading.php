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

    // Parse debug mode query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const isDebugMode = urlParams.get('debug') === '1' || urlParams.get('debug') === 'true';

    console.log("%c[GA4 Debug] Initializing payment verification page...", "color: #3B82F6; font-weight: bold; font-size: 11px;");
    console.log("%c[GA4 Debug] Target Order Reference: " + orderId, "color: #6B7280;");
    console.log("%c[GA4 Debug] Checking gtag status: " + (typeof gtag === 'function' ? '✅ Loaded' : '❌ NOT Loaded / Blocked'), "color: " + (typeof gtag === 'function' ? '#10B981' : '#EF4444') + "; font-weight: bold;");

    if (isDebugMode) {
        console.log("%c[GA4 Debug] Debug Mode active: Auto-redirect disabled.", "color: #3B82F6; font-weight: bold;");
        
        // Update status indicator text
        const statusText = document.querySelector('span.text-xs');
        if (statusText) {
            statusText.textContent = "Debug Mode Aktif";
        }
        
        // Append a manual redirection button
        const cardContainer = document.querySelector('.relative.bg-white\\/70, .relative.bg-white\\/70\\/60');
        const fallbackContainer = cardContainer || document.querySelector('.backdrop-blur-xl');
        if (fallbackContainer) {
            const btn = document.createElement('a');
            btn.href = redirectUrl;
            btn.className = "mt-6 inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white bg-green-500 hover:bg-green-600 rounded-xl transition-all duration-300 shadow-lg shadow-green-500/25";
            btn.textContent = "Lanjutkan ke Pembelian";
            btn.style.display = "inline-block";
            fallbackContainer.appendChild(btn);
        }
    }

    // Small delay to simulate smooth loading transitions
    setTimeout(function() {
        fetch(verifyUrl)
            .then(response => response.json())
            .then(data => {
                console.log("%c[GA4 Debug] Verification Response received:", "color: #10B981; font-weight: bold;", data);
                
                if (data.success && data.status === 'success') {
                    // Send Google Analytics 4 Ecommerce Purchase Event
                    if (typeof gtag === 'function') {
                        const purchasePayload = {
                            transaction_id: data.transaction_id,
                            affiliation: "RJSStore",
                            value: parseFloat(data.value),
                            tax: 0,
                            shipping: 0,
                            currency: "IDR",
                            items: data.items,
                            debug_mode: true
                        };

                        console.log("%c[GA4 Debug] Dispatching GA4 purchase event...", "color: #8B5CF6; font-weight: bold;");
                        console.log("%c[GA4 Debug] Event Payload:", "color: #8B5CF6;", purchasePayload);

                        gtag("event", "purchase", {
                            ...purchasePayload,
                            event_callback: function() {
                                console.log("%c[GA4 Debug] GA4 purchase event successfully dispatched.", "color: #10B981; font-weight: bold;");
                                if (!isDebugMode) {
                                    window.location.href = redirectUrl;
                                }
                            },
                            event_timeout: 2000 // Safeguard in case tracker is blocked/delayed
                        });

                        // Fallback redirect for debug mode since event_callback will run but not redirect
                        if (isDebugMode) {
                            console.log("%c[GA4 Debug] Event sent to gtag queue. Auto-redirect bypassed.", "color: #3B82F6;");
                        }
                    } else {
                        console.warn("%c[GA4 Debug] gtag is not defined. Skipping tracking...", "color: #F59E0B; font-weight: bold;");
                        if (!isDebugMode) {
                            window.location.href = redirectUrl;
                        }
                    }
                } else {
                    console.log("%c[GA4 Debug] Verification success: " + data.success + ", status: " + data.status + ".", "color: #F59E0B;");
                    if (!isDebugMode) {
                        window.location.href = redirectUrl;
                    }
                }
            })
            .catch(error => {
                console.error('%c[GA4 Debug] Verification fetch error:', 'color: #EF4444; font-weight: bold;', error);
                if (!isDebugMode) {
                    window.location.href = redirectUrl;
                }
            });
    }, 1500);
});
</script>
