<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'RJSStore') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F5F5; }
        .toko-green { color: #42B549; }
        .bg-toko { background: #42B549; }
        .sidebar-link { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; color:#374151; font-size:14px; transition:all 0.15s; text-decoration:none; }
        .sidebar-link:hover { background:#f3f4f6; }
        .sidebar-link.active { background:#e8f5e9; color:#42B549; font-weight:600; }
        .nav-badge { background:#FFF3E0; color:#E65100; font-size:11px; font-weight:700; padding:2px 7px; border-radius:99px; }
        <?php if (isset($extra_css)) echo $extra_css; ?>
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

<!-- TOP NAV -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="px-6 py-3 flex items-center gap-4">
        <div class="flex items-center gap-2 w-52 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
            </div>
            <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
        </div>
        <div class="flex-1 max-w-xl">
            <form action="<?= url('/customer/produk') ?>" method="GET" class="flex bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                <input type="text" name="search" placeholder="Cari produk di RJSStore..." class="flex-1 px-4 py-2.5 bg-transparent text-sm outline-none">
                <button type="submit" class="px-5 py-2.5 text-white text-sm font-semibold" style="background:#42B549">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>
        <div class="flex items-center gap-3 ml-auto">
            <?php
            $initial_cart_count = $initial_cart_count ?? 0;
            include BASE_PATH . '/app/views/partials/cart_dropdown.php';
            ?>
            <a href="<?= url('/customer/pembelian') ?>" class="flex items-center gap-1.5 text-gray-600 hover:text-gray-800 text-sm px-3 py-2 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Pesanan
            </a>
            <div class="relative" id="profileDropdown">
                <button onclick="document.getElementById('profileMenu').classList.toggle('hidden')" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 rounded-xl px-3 py-2 transition cursor-pointer">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#42B549">
                        <?= strtoupper(substr($this->auth->user()['name'], 0, 1)) ?>
                    </div>
                    <span class="text-sm font-medium text-gray-700"><?= e($this->auth->user()['name']) ?></span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                    <a href="<?= url('/customer/profile') ?>" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        Settings Profile
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="javascript:void(0)" onclick="openLogoutModal()" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
<script>document.addEventListener('click',function(e){var d=document.getElementById('profileDropdown');var m=document.getElementById('profileMenu');if(d&&!d.contains(e.target)){m.classList.add('hidden')}});</script>

<div class="flex flex-1 overflow-hidden">
    <aside class="w-56 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col pt-4 pb-6 px-3 overflow-y-auto">
        <div class="mb-4 px-3"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menu</p></div>
        <nav class="flex flex-col gap-1 flex-1">
            <a href="<?= url('/customer/dashboard') ?>" class="sidebar-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>Dashboard</a>
            <a href="<?= url('/customer/produk') ?>" class="sidebar-link <?= ($active_page ?? '') === 'produk' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>Produk</a>
            <a href="<?= url('/customer/keranjang') ?>" class="sidebar-link <?= ($active_page ?? '') === 'keranjang' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>Keranjang</a>
            <a href="<?= url('/customer/pembelian') ?>" class="sidebar-link <?= ($active_page ?? '') === 'pembelian' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>Pembelian</a>
            <a href="<?= url('/customer/download') ?>" class="sidebar-link <?= ($active_page ?? '') === 'download' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Download</a>
            <a href="<?= url('/customer/profile') ?>" class="sidebar-link <?= ($active_page ?? '') === 'profile' ? 'active' : '' ?>"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Profile</a>
        </nav>
        <div class="px-1 mt-4 pt-4 border-t border-gray-100">
            <a href="javascript:void(0)" onclick="openLogoutModal()" class="sidebar-link text-red-500 hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Keluar
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col p-6 overflow-y-auto">
        <?= flash_render() ?>
        <?= $content ?>
    </main>
</div>

<?php include BASE_PATH . '/app/views/partials/toast.php'; ?>
<?php include BASE_PATH . '/app/views/partials/logout_modal.php'; ?>
</body>
</html>
