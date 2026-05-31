<!-- Toast Container -->
<div id="toast-container" style="position:fixed;top:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

<style>
/* Animations + structural-only rules; colors come from design tokens
   (see public/assets/css/dark-mode.css → section 14 Toasts) */
@keyframes toast-in {
    from { opacity:0; transform:translateX(40px) scale(0.96); }
    to { opacity:1; transform:translateX(0) scale(1); }
}
@keyframes toast-out {
    from { opacity:1; transform:translateX(0) scale(1); }
    to { opacity:0; transform:translateX(40px) scale(0.96); }
}
.toast-item {
    position:relative; overflow:hidden;
    display:flex; align-items:flex-start; gap:10px;
    padding:14px 18px;
    min-width:300px; max-width:420px;
    pointer-events:auto; cursor:pointer;
    animation:toast-in 0.35s cubic-bezier(0.21,1.02,0.73,1) forwards;
}
.toast-item.toast-exit {
    animation:toast-out 0.25s cubic-bezier(0.06,0.71,0.55,1) forwards;
}
.toast-icon { flex-shrink:0; width:20px; height:20px; margin-top:1px; }
.toast-body { flex:1; }
.toast-title { font-size:13px; font-weight:600; margin-bottom:2px; }
.toast-msg { font-size:13px; line-height:1.4; }
.toast-progress { position:absolute; bottom:0; left:0; height:3px; transition:width linear; border-radius:0 0 12px 12px; }
</style>

<script>
(function(){
    var icons = {
        success: '<svg class="toast-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg class="toast-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        warning: '<svg class="toast-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
        info: '<svg class="toast-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
    };
    var titles = { success:'Berhasil', error:'Gagal', warning:'Perhatian', info:'Info' };
    var durations = { success:4000, error:6000, warning:5000, info:4000 };

    function showToast(type, message) {
        var container = document.getElementById('toast-container');
        if (!container) return;

        var el = document.createElement('div');
        el.className = 'toast-item toast-' + type;
        var dur = durations[type] || 4000;

        el.innerHTML = (icons[type] || icons.info) +
            '<div class="toast-body">' +
                '<div class="toast-title">' + (titles[type] || 'Info') + '</div>' +
                '<div class="toast-msg">' + message + '</div>' +
            '</div>' +
            '<div class="toast-progress" style="width:100%"></div>';

        el.addEventListener('click', function(){ dismiss(el); });
        container.appendChild(el);

        var bar = el.querySelector('.toast-progress');
        requestAnimationFrame(function(){
            bar.style.width = '0%';
            bar.style.transitionDuration = dur + 'ms';
        });

        var timer = setTimeout(function(){ dismiss(el); }, dur);
        el._timer = timer;
    }

    function dismiss(el) {
        if (el._dismissed) return;
        el._dismissed = true;
        clearTimeout(el._timer);
        el.classList.add('toast-exit');
        setTimeout(function(){ el.remove(); }, 250);
    }

    var dataEl = document.getElementById('flash-data');
    if (dataEl) {
        try {
            var messages = JSON.parse(dataEl.getAttribute('data-messages'));
            var delay = 0;
            messages.forEach(function(m) {
                setTimeout(function(){ showToast(m.type, m.message); }, delay);
                delay += 150;
            });
        } catch(e) {}
        dataEl.remove();
    }

    window.showToast = showToast;
})();
</script>
