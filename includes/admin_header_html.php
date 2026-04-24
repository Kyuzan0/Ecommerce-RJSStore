<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - RJSStore' : 'Admin - RJSStore'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F5F5; }
        .sidebar-link { display:flex; align-items:center; gap:12px; padding:10px 14px; border-radius:10px; color:#374151; font-size:14px; transition:all 0.15s; text-decoration:none; }
        .sidebar-link:hover { background:#f3f4f6; }
        .sidebar-link.active { background:#e8f5e9; color:#42B549; font-weight:600; }
        <?php if (isset($extra_css)) echo $extra_css; ?>
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="px-6 py-3 flex items-center gap-4">
        <div class="flex items-center gap-2 w-52 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
            </div>
            <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
        </div>
        <span class="text-sm font-semibold px-3 py-1 rounded-lg" style="background:#FFF3E0; color:#E65100">Panel Admin</span>
        <div class="relative ml-auto" id="profileDropdown">
            <button onclick="document.getElementById('profileMenu').classList.toggle('hidden')" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 rounded-xl px-3 py-2 transition cursor-pointer">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#1565C0">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
                <span class="text-sm font-medium text-gray-700"><?php echo $_SESSION['name']; ?></span>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                <a href="profile.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                    Settings Profile
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <a href="../auth/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
                    Keluar
                </a>
            </div>
        </div>
    </div>
</header>
<script>document.addEventListener('click',function(e){var d=document.getElementById('profileDropdown');var m=document.getElementById('profileMenu');if(d&&!d.contains(e.target)){m.classList.add('hidden')}});</script>

<div class="flex flex-1 overflow-hidden">
