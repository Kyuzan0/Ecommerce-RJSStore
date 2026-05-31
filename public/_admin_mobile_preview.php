<?php
// Self-contained mobile mock that mirrors admin produk markup
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Mobile Preview</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={darkMode:'class'}</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/dark-mode.css">
<script>
  (function(){var t=localStorage.getItem('theme');if(t==='dark')document.documentElement.classList.add('dark');})();
</script>
</head>
<body class="app-shell min-h-screen flex flex-col">
<header class="sticky top-0 z-40 border-b">
    <div class="px-4 py-3 flex items-center gap-3">
        <button class="app-shell__menu-toggle lg:hidden" aria-label="Menu" onclick="document.documentElement.classList.toggle('dark')">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549"><svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg></div>
            <span class="text-lg font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
        </div>
        <button class="ml-auto bg-gray-100 hover:bg-gray-200 rounded-xl px-3 py-2 text-sm">A ▾</button>
    </div>
</header>

<div class="app-shell__body flex flex-1">
    <main class="flex-1 flex flex-col px-4 py-4">

<!-- TOOLBAR -->
<div class="ds-toolbar">
    <form class="relative ds-toolbar__search">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" placeholder="Cari produk..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm" style="padding-left:2.5rem">
    </form>
    <button class="ds-toolbar__action px-4 py-2.5 text-white rounded-xl text-sm font-semibold" style="background:#42B549">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Produk</span>
    </button>
</div>

<!-- FILTER -->
<div class="ds-filter-row">
    <a class="ds-chip ds-chip--neutral ds-chip--filter is-active">Semua <span class="ds-chip__count">12</span></a>
    <a class="ds-chip ds-chip--accent ds-chip--filter">Akun <span class="ds-chip__count">3</span></a>
    <a class="ds-chip ds-chip--tertiary ds-chip--filter">Ebook <span class="ds-chip__count">2</span></a>
    <a class="ds-chip ds-chip--warning ds-chip--filter">Game <span class="ds-chip__count">5</span></a>
    <a class="ds-chip ds-chip--success ds-chip--filter">Software <span class="ds-chip__count">1</span></a>
    <a class="ds-chip ds-chip--rose ds-chip--filter">Template <span class="ds-chip__count">0</span></a>
</div>

<!-- TABLE / MOBILE CARDS -->
<div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden">
    <div class="ds-table-wrap ds-table-wrap--wide hidden md:block">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-3 py-3 text-center w-10"><input type="checkbox" class="w-4 h-4 rounded"></th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">No</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Info Produk</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Harga</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rating</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">File</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-3 text-center"><input type="checkbox"></td>
                    <td class="px-5 py-3 text-center text-sm text-gray-500 font-medium">1</td>
                    <td class="px-5 py-3"><p class="font-semibold text-gray-800 text-sm">Spotify Premium 1 Bulan</p><p class="text-xs text-gray-500 max-w-xs truncate">Akun premium individual untuk satu bulan akses penuh tanpa iklan</p></td>
                    <td class="px-5 py-4"><?= tipe_produk_badge('Akun') ?></td>
                    <td class="px-5 py-4 font-bold" style="color:#42B549"><?= rupiah(50000) ?></td>
                    <td class="px-5 py-4">4.5 (12)</td>
                    <td class="px-5 py-4"><a class="text-xs font-medium hover:underline" style="color:#1976D2">Lihat File</a></td>
                    <td class="px-5 py-4 text-center"><button class="text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#FFF8E1; color:#F57F17">Edit</button></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden p-3 space-y-3">
        <?php foreach ([
            ['nama'=>'Spotify Premium 1 Bulan','desc'=>'Akun premium individual untuk satu bulan akses penuh tanpa iklan','tipe'=>'Akun','harga'=>50000,'rating'=>'4.5','total'=>12, 'no'=>1],
            ['nama'=>'Free Fire Diamond 500','desc'=>'Top up 500 diamond Free Fire instan ke akun kamu.','tipe'=>'Game','harga'=>75000,'rating'=>'4.5','total'=>2, 'no'=>2],
            ['nama'=>'Genshin Impact Genesis 300','desc'=>'Top up 300 Genesis Crystal Genshin Impact.','tipe'=>'Game','harga'=>80000,'rating'=>'4.0','total'=>1, 'no'=>3],
        ] as $r): ?>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 relative">
            <input type="checkbox" class="absolute top-4 right-4 w-5 h-5 rounded border-gray-300 cursor-pointer accent-green-600">
            <div class="pr-8 mb-3">
                <p class="text-xs text-gray-400 mb-0.5">#<?= $r['no'] ?></p>
                <p class="font-semibold text-gray-800 text-sm leading-tight"><?= e($r['nama']) ?></p>
                <p class="text-xs text-gray-500 line-clamp-2 mt-1"><?= e($r['desc']) ?></p>
            </div>
            <div class="flex items-center flex-wrap gap-x-4 gap-y-2 text-xs mb-3">
                <?= tipe_produk_badge($r['tipe']) ?>
                <span class="font-bold" style="color:#42B549"><?= rupiah($r['harga']) ?></span>
                <span class="flex items-center gap-1 text-gray-500">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?= $r['rating'] ?> <span class="text-gray-400">(<?= $r['total'] ?>)</span>
                </span>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-100">
                <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg" style="background:#FFF8E1; color:#F57F17">Edit</button>
                <button class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg" style="background:#FFEBEE; color:#C62828">Hapus</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

    </main>
</div>
</body>
</html>
