<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="logout-modal-overlay" style="display:none;">
    <div class="logout-modal-card">
        <div class="logout-modal-icon-wrap">
            <div class="logout-modal-icon-bg">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
        </div>
        <h3 class="logout-modal-title">Keluar dari akun?</h3>
        <p class="logout-modal-desc">Anda akan keluar dari sesi saat ini. Pastikan semua pekerjaan sudah tersimpan.</p>
        <div class="logout-modal-actions">
            <button type="button" onclick="closeLogoutModal()" class="logout-modal-btn-cancel">Batal</button>
            <a href="<?= url('/auth/logout') ?>" class="logout-modal-btn-confirm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
                Ya, Keluar
            </a>
        </div>
    </div>
</div>

<style>
.logout-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0);
    backdrop-filter: blur(0px);
    transition: background 0.25s ease, backdrop-filter 0.25s ease;
    padding: 16px;
}
.logout-modal-overlay.active {
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
}
.logout-modal-card {
    background: #fff;
    border-radius: 16px;
    padding: 32px 28px 24px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.08);
    text-align: center;
    transform: scale(0.9) translateY(10px);
    opacity: 0;
    transition: transform 0.3s cubic-bezier(0.21, 1.02, 0.73, 1), opacity 0.25s ease;
}
.logout-modal-overlay.active .logout-modal-card {
    transform: scale(1) translateY(0);
    opacity: 1;
}
.logout-modal-icon-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}
.logout-modal-icon-bg {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #fef2f2;
    border: 1px solid #fecaca;
    display: flex;
    align-items: center;
    justify-content: center;
}
.logout-modal-title {
    font-size: 17px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 6px;
    font-family: 'Inter', sans-serif;
}
.logout-modal-desc {
    font-size: 13.5px;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 24px;
    font-family: 'Inter', sans-serif;
}
.logout-modal-actions {
    display: flex;
    gap: 10px;
}
.logout-modal-btn-cancel {
    flex: 1;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: 'Inter', sans-serif;
}
.logout-modal-btn-cancel:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
}
.logout-modal-btn-confirm {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    background: #ef4444;
    border: 1px solid #dc2626;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
}
.logout-modal-btn-confirm:hover {
    background: #dc2626;
    border-color: #b91c1c;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}
.logout-modal-btn-cancel:active,
.logout-modal-btn-confirm:active {
    transform: scale(0.97);
}

/* Closing animation */
.logout-modal-overlay.closing {
    background: rgba(0, 0, 0, 0);
    backdrop-filter: blur(0px);
}
.logout-modal-overlay.closing .logout-modal-card {
    transform: scale(0.9) translateY(10px);
    opacity: 0;
}
</style>

<script>
(function() {
    function openLogoutModal() {
        var modal = document.getElementById('logoutModal');
        if (!modal) return;
        modal.style.display = 'flex';
        // Trigger reflow for animation
        modal.offsetHeight;
        modal.classList.add('active');
        modal.classList.remove('closing');
        document.body.style.overflow = 'hidden';
    }

    function closeLogoutModal() {
        var modal = document.getElementById('logoutModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.classList.add('closing');
        document.body.style.overflow = '';
        setTimeout(function() {
            modal.style.display = 'none';
            modal.classList.remove('closing');
        }, 300);
    }

    // Close on overlay click (not card)
    document.addEventListener('click', function(e) {
        var modal = document.getElementById('logoutModal');
        if (modal && e.target === modal) {
            closeLogoutModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('logoutModal');
            if (modal && modal.classList.contains('active')) {
                closeLogoutModal();
            }
        }
    });

    // Expose globally
    window.openLogoutModal = openLogoutModal;
    window.closeLogoutModal = closeLogoutModal;
})();
</script>
