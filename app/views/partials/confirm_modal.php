<!-- Global Custom Confirm & Alert Modal -->
<div id="custom-modal-dialog" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div id="custom-modal-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>
    
    <!-- Modal Shell -->
    <div id="custom-modal-shell" class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-sm w-full p-6 text-center transform scale-95 opacity-0 transition-all duration-300 z-10">
        <!-- Icon Container -->
        <div class="mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center bg-gray-50 border border-gray-100 text-gray-500" id="custom-modal-icon-wrap">
            <!-- Icon placeholder -->
            <svg class="w-6 h-6" id="custom-modal-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"></svg>
        </div>
        
        <!-- Header -->
        <h3 class="text-base font-bold text-gray-800 mb-2 truncate" id="custom-modal-title">Konfirmasi</h3>
        
        <!-- Message -->
        <p class="text-sm text-gray-500 mb-6 leading-relaxed" id="custom-modal-message">Apakah Anda yakin?</p>
        
        <!-- Footer / Buttons -->
        <div class="flex gap-2 justify-center" id="custom-modal-footer">
            <button type="button" id="custom-modal-btn-cancel" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-xs font-semibold hover:bg-gray-200 transition cursor-pointer">
                Batal
            </button>
            <button type="button" id="custom-modal-btn-ok" class="px-4 py-2 text-white rounded-xl text-xs font-semibold hover:opacity-90 transition cursor-pointer">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('custom-modal-dialog');
    const backdrop = document.getElementById('custom-modal-backdrop');
    const shell = document.getElementById('custom-modal-shell');
    const titleEl = document.getElementById('custom-modal-title');
    const messageEl = document.getElementById('custom-modal-message');
    const iconWrap = document.getElementById('custom-modal-icon-wrap');
    const iconEl = document.getElementById('custom-modal-icon');
    const btnCancel = document.getElementById('custom-modal-btn-cancel');
    const btnOk = document.getElementById('custom-modal-btn-ok');

    let onOkCallback = null;
    let onCancelCallback = null;

    // SVG icons
    const icons = {
        question: `<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9.75h4.875a2.625 2.625 0 010 5.25H12M8.25 9.75L10.5 7.5M8.25 9.75L10.5 12M12 18.75h.008v.008H12v-.008z" />`,
        warning: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />`,
        info: `<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.083.987l-.551 1.654a.75.75 0 00.97.97l1.654-.551a.75.75 0 01.987 1.083l-.02.041a.75.75 0 01-1.083.987l-1.654-.551a.75.75 0 00-.97-.97l-1.654.551a.75.75 0 01-.987-1.083l.02-.041a.75.75 0 011.083-.987l1.654.551a.75.75 0 00.97.97l1.654-.551a.75.75 0 01.987 1.083l-.02.041a.75.75 0 01-1.083.987l-1.654-.551a.75.75 0 00-.97-.97z" />`
    };

    function showModal(type, title, message, onOk, onCancel, options) {
        options = options || {};
        titleEl.textContent = title || (type === 'alert' ? 'Notifikasi' : 'Konfirmasi');
        messageEl.textContent = message || '';
        onOkCallback = onOk || null;
        onCancelCallback = onCancel || null;

        // Customize layout based on type
        if (type === 'alert') {
            btnCancel.classList.add('hidden');
            btnOk.textContent = options.okLabel || 'Tutup';
            btnOk.style.background = '#42B549'; // Green success/info
            iconWrap.className = 'mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center bg-green-50 border border-green-100 text-green-500';
            iconEl.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />`;
        } else {
            btnCancel.classList.remove('hidden');
            btnCancel.textContent = options.cancelLabel || 'Batal';
            btnOk.textContent = options.okLabel || 'Ya, Lanjutkan';
            btnOk.style.background = '#C62828'; // Red warning/danger
            iconWrap.className = 'mx-auto mb-4 w-12 h-12 rounded-full flex items-center justify-center bg-red-50 border border-red-100 text-red-500';
            iconEl.innerHTML = icons.warning;
        }

        // Display modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Trigger animations
        setTimeout(() => {
            backdrop.style.opacity = '1';
            shell.style.transform = 'scale(1)';
            shell.style.opacity = '1';
        }, 10);
    }

    function closeModal() {
        backdrop.style.opacity = '0';
        shell.style.transform = 'scale(0.95)';
        shell.style.opacity = '0';

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    // Bind event handlers
    btnOk.addEventListener('click', () => {
        closeModal();
        if (onOkCallback) onOkCallback();
    });

    btnCancel.addEventListener('click', () => {
        closeModal();
        if (onCancelCallback) onCancelCallback();
    });

    // Register globally on window object
    window.showCustomAlert = function(title, message, onClose) {
        showModal('alert', title, message, onClose, null);
    };

    window.showCustomConfirm = function(title, message, onConfirm, onCancel, options) {
        showModal('confirm', title, message, onConfirm, onCancel, options);
    };

    // Override default alert
    window.alert = function(message) {
        window.showCustomAlert('Notifikasi', message);
    };

    // Global event listener to intercept forms and clicks using data-confirm
    document.addEventListener('submit', function(e) {
        const confirmMsg = e.target.getAttribute('data-confirm');
        if (confirmMsg) {
            e.preventDefault();
            window.showCustomConfirm('Konfirmasi', confirmMsg, function() {
                const form = e.target;
                form.removeAttribute('data-confirm');
                form.submit();
            });
        }
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-confirm]');
        if (btn && btn.tagName !== 'FORM') {
            e.preventDefault();
            const confirmMsg = btn.getAttribute('data-confirm');
            window.showCustomConfirm('Konfirmasi', confirmMsg, function() {
                if (btn.tagName === 'A') {
                    window.location.href = btn.href;
                } else if (btn.type === 'submit') {
                    const form = btn.closest('form');
                    if (form) {
                        form.removeAttribute('data-confirm');
                        form.submit();
                    }
                } else {
                    btn.removeAttribute('data-confirm');
                    btn.click();
                }
            });
        }
    });
})();
</script>
