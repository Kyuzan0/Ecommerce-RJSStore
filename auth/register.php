<?php 
session_start(); 
include_once '../config/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - RJS Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        input:focus { outline: none; border-color: #42B549; box-shadow: 0 0 0 3px rgba(66,181,73,0.15); }
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
    <div class="min-h-screen flex items-center justify-center p-4 -mt-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <div class="mb-7">
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">Daftar Akun Baru</h1>
                    <p class="text-sm text-gray-500">Bergabung dan mulai belanja produk digital</p>
                </div>
                <?= flash_render() ?>
                <form method="POST" action="proses_register.php" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Masukkan nama lengkap"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" required placeholder="Masukkan email kamu"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi</label>
                        <input type="password" name="password" required placeholder="Minimal 8 karakter"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Kata Sandi</label>
                        <input type="password" name="confirm_password" required placeholder="Ulangi kata sandi"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition">
                    </div>
                    <button type="submit"
                        class="w-full text-white py-3 rounded-xl font-semibold transition text-sm mt-2 hover:opacity-90" style="background:#42B549">
                        Daftar Sekarang
                    </button>
                </form>
                <p class="text-center text-sm text-gray-600 mt-6">
                    Sudah punya akun?
                    <a href="login.php" class="font-semibold hover:underline ml-1" style="color:#42B549">Masuk di sini</a>
                </p>
            </div>
            <p class="text-center text-xs text-gray-400 mt-5">Dengan mendaftar, kamu menyetujui Syarat & Ketentuan serta Kebijakan Privasi RJSStore</p>
        </div>
    </div>
<?php include __DIR__ . '/../includes/toast.php'; ?>
</body>
</html>
