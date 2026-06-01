<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Admin') ?> - RJSStore</title>
    <?php $ga_id = env('GA_MEASUREMENT_ID', ''); if ($ga_id): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga_id) ?>"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($ga_id) ?>');</script>
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={darkMode:'class'}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
    <link rel="stylesheet" href="<?= url('/assets/css/dark-mode.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/assets/css/dark-mode.css') ?>">
    <?php if (isset($extra_css)): ?><style><?= $extra_css ?></style><?php endif; ?>
    <script>
        (function(){
            var theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="app-shell min-h-screen flex flex-col lg:h-screen lg:overflow-hidden">
<header class="sticky top-0 z-40 border-b">
    <div class="px-4 md:px-6 py-3 flex items-center gap-3 md:gap-4">
        <button type="button" class="app-shell__menu-toggle lg:hidden" aria-label="Buka menu" onclick="document.body.classList.toggle('aside-open')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="flex items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
            </div>
            <span class="text-lg md:text-xl font-bold text-gray-800 truncate">RJS<span style="color:#42B549">Store</span></span>
        </div>
        <span class="text-xs md:text-sm font-semibold px-2.5 md:px-3 py-1 rounded-lg ds-hide-sm" style="background:#FFF3E0; color:#E65100">Panel Admin</span>
        <div class="relative ml-auto" id="profileDropdown">
            <button onclick="document.getElementById('profileMenu').classList.toggle('hidden')" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 rounded-xl px-2.5 md:px-3 py-2 transition cursor-pointer">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#1565C0">
                    <?= strtoupper(substr($this->auth->user()['name'], 0, 1)) ?>
                </div>
                <span class="text-sm font-medium text-gray-700 ds-hide-sm"><?= e($this->auth->user()['name']) ?></span>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                <a href="<?= url('/admin-profile') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings Profile
                </a>
                <a href="javascript:void(0)" onclick="toggleDarkMode()" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-400 dark-icon-moon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg class="w-4 h-4 text-yellow-500 dark-icon-sun hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="dark-label-text">Mode Gelap</span>
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <a href="javascript:void(0)" onclick="openLogoutModal()" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
                    Keluar
                </a>
            </div>
        </div>
    </div>
</header>
<script>document.addEventListener('click',function(e){var d=document.getElementById('profileDropdown');var m=document.getElementById('profileMenu');if(d&&!d.contains(e.target)){m.classList.add('hidden')}});</script>

<div class="app-shell__overlay" onclick="document.body.classList.remove('aside-open')"></div>

<div class="app-shell__body flex flex-1 lg:overflow-hidden">
    <aside class="app-shell__aside flex-shrink-0 flex flex-col pt-4 pb-6 px-3 overflow-y-auto border-r">
        <div class="mb-3 px-3"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Utama</p></div>
        <nav class="flex flex-col gap-1" onclick="if(window.innerWidth<1024)document.body.classList.remove('aside-open')">
            <a href="<?= url('/admin-dashboard') ?>" class="sidebar-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>Dashboard</a>
            <a href="<?= url('/admin-produk') ?>" class="sidebar-link <?= ($active_page ?? '') === 'produk' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>Produk</a>
            <a href="<?= url('/admin-transaksi') ?>" class="sidebar-link <?= ($active_page ?? '') === 'transaksi' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Transaksi</a>
            <a href="<?= url('/admin-user') ?>" class="sidebar-link <?= ($active_page ?? '') === 'user' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Pengguna</a>
            <a href="<?= url('/admin-laporan') ?>" class="sidebar-link <?= ($active_page ?? '') === 'laporan' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Laporan</a>
        </nav>

        <div class="mt-5 mb-3 px-3"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Layanan</p></div>
        <nav class="flex flex-col gap-1" onclick="if(window.innerWidth<1024)document.body.classList.remove('aside-open')">
            <a href="<?= url('/admin-chat') ?>" class="sidebar-link <?= ($active_page ?? '') === 'chat' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>Live Chat</a>
            <a href="<?= url('/admin-garansi') ?>" class="sidebar-link <?= ($active_page ?? '') === 'garansi' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>Garansi</a>
        </nav>

        <div class="mt-5 mb-3 px-3"><p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengaturan</p></div>
        <nav class="flex flex-col gap-1 flex-1" onclick="if(window.innerWidth<1024)document.body.classList.remove('aside-open')">
            <a href="<?= url('/admin-preset') ?>" class="sidebar-link <?= ($active_page ?? '') === 'preset' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>Preset Akun</a>
            <a href="<?= url('/admin-audit') ?>" class="sidebar-link <?= ($active_page ?? '') === 'audit' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Audit Log</a>
        </nav>

        <div class="px-1 mt-4 pt-4 border-t border-gray-100">
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="sidebar-link text-red-500 hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col px-4 md:px-6 py-4 lg:overflow-y-auto">
        <?= flash_render() ?>
        <?= $content ?>
    </main>
</div>

<?php include BASE_PATH . '/app/views/partials/toast.php'; ?>
<?php include BASE_PATH . '/app/views/partials/logout_modal.php'; ?>
<script>
function toggleDarkMode() {
    var html = document.documentElement;
    html.classList.toggle('dark');
    var isDark = html.classList.contains('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateDarkIcons();
}
function updateDarkIcons() {
    var isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('.dark-icon-moon').forEach(function(el) { el.style.display = isDark ? 'none' : 'block'; });
    document.querySelectorAll('.dark-icon-sun').forEach(function(el) { el.style.display = isDark ? 'block' : 'none'; });
    document.querySelectorAll('.dark-label-text').forEach(function(el) { el.textContent = isDark ? 'Mode Terang' : 'Mode Gelap'; });
}
updateDarkIcons();
// Close drawer on Escape
document.addEventListener('keydown', function(e){ if(e.key==='Escape') document.body.classList.remove('aside-open'); });
</script>
</body>
</html>
