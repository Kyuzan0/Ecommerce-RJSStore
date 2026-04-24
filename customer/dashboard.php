<?php
$page_title = 'Beranda';
$active_page = 'dashboard';
include '../includes/customer_header.php';
include '../includes/customer_sidebar.php';
?>

        <!-- Banner Hero -->
        <div class="rounded-2xl p-7 mb-6 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #42B549 0%, #2d8a33 100%)">
            <div class="absolute right-0 top-0 opacity-10">
                <svg width="220" height="160" viewBox="0 0 220 160" fill="white"><circle cx="160" cy="30" r="80"/><circle cx="60" cy="120" r="60"/></svg>
            </div>
            <div class="relative z-10">
                <p class="text-green-100 text-sm font-medium mb-1">👋 Selamat datang kembali,</p>
                <h1 class="text-3xl font-bold mb-2"><?php echo $_SESSION['name']; ?>!</h1>
                <p class="text-green-100 text-sm max-w-md mb-5">Temukan ribuan produk digital berkualitas tinggi. Beli, bayar, dan unduh dalam hitungan menit.</p>
                <div class="flex gap-3">
                    <a href="produk.php" class="bg-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition" style="color:#42B549">
                        Mulai Belanja
                    </a>
                    <a href="pembelian.php" class="bg-white/20 border border-white/40 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-white/30 transition">
                        Cek Pesanan
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Menu Grid -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <a href="produk.php" class="bg-white rounded-2xl p-5 flex flex-col items-center gap-3 border border-gray-100 hover:border-green-300 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-105 transition" style="background:#E8F5E9">
                    <svg class="w-6 h-6" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Produk</span>
            </a>
            <a href="pembelian.php" class="bg-white rounded-2xl p-5 flex flex-col items-center gap-3 border border-gray-100 hover:border-blue-300 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-105 transition" style="background:#E3F2FD">
                    <svg class="w-6 h-6" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Pembelian</span>
            </a>
            <a href="download.php" class="bg-white rounded-2xl p-5 flex flex-col items-center gap-3 border border-gray-100 hover:border-purple-300 hover:shadow-sm transition group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-105 transition" style="background:#F3E5F5">
                    <svg class="w-6 h-6" style="color:#7B1FA2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Download</span>
            </a>
            <div class="bg-white rounded-2xl p-5 flex flex-col items-center gap-3 border border-gray-100 hover:border-orange-300 hover:shadow-sm transition cursor-pointer group">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center group-hover:scale-105 transition" style="background:#FFF3E0">
                    <svg class="w-6 h-6" style="color:#E65100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700">Bantuan</span>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                    <svg class="w-6 h-6" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Keamanan Akun</p>
                    <p class="font-bold text-gray-800">Sesi Anda Aktif & Aman</p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E3F2FD">
                    <svg class="w-6 h-6" style="color:#1976D2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Butuh Bantuan?</p>
                    <p class="font-bold text-gray-800">Hubungi Tim Support</p>
                </div>
            </div>
        </div>

<?php include '../includes/customer_footer.php'; ?>
