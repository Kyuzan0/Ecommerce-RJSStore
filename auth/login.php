<?php 
session_start(); 
include_once '../config/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - RJS Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .toko-green { color: #42B549; }
        .bg-toko-green { background-color: #42B549; }
        input:focus { outline: none; border-color: #42B549; box-shadow: 0 0 0 3px rgba(66,181,73,0.15); }
        .divider { display: flex; align-items: center; gap: 12px; color: #9ca3af; font-size: 13px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white border-b border-gray-200 py-4 px-6">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                </div>
                <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
            </div>
            <span class="text-sm text-gray-400">Toko Digital Terpercaya</span>
        </div>
    </header>
    <div class="min-h-screen flex items-center justify-center p-4 -mt-16">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="mb-7">
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">Masuk ke RJSStore</h1>
                    <p class="text-sm text-gray-500">Nikmati belanja produk digital dengan mudah</p>
                </div>
                <?php
                // Show flash for checkout redirect
                $next = $_GET['next'] ?? '';
                if ($next === 'checkout') {
                    flash('info', 'Silakan login untuk melanjutkan pembayaran.');
                }
                ?>
                <?= flash_render() ?>
                <form method="POST" action="proses_login.php" class="space-y-4">
                    <?php if ($next): ?>
                    <input type="hidden" name="redirect_next" value="<?= e($next) ?>">
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" required placeholder="Masukkan email kamu"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi</label>
                        <input type="password" name="password" required placeholder="Masukkan kata sandi"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <button type="submit"
                        class="w-full text-white py-3 rounded-xl font-semibold transition text-sm mt-2 hover:opacity-90" style="background:#42B549">
                        Masuk
                    </button>
                </form>
                <div class="divider my-6">atau</div>
                <p class="text-center text-sm text-gray-600">
                    Belum punya akun?
                    <a href="register.php" class="font-semibold hover:underline ml-1" style="color:#42B549">Daftar Sekarang</a>
                </p>
            </div>
            <p class="text-center text-xs text-gray-400 mt-5">Dengan masuk, kamu menyetujui Syarat & Ketentuan serta Kebijakan Privasi RJSStore</p>
        </div>
    </div>
<?php include __DIR__ . '/../includes/toast.php'; ?>
</body>
</html>
