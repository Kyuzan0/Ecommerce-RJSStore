<!-- Breadcrumb -->
<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
    </ol>
</nav>

<!-- Hero Banner -->
<div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-lg p-8 mb-8 text-white">
    <h1 class="text-3xl font-bold mb-2">Selamat Datang, <?= e($this->auth->user()['name']) ?>!</h1>
    <p class="text-green-50">Kelola pembelian dan download produk digital Anda dengan mudah</p>
</div>

<!-- Quick Menu Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Produk -->
    <a href="<?= url('/customer/produk') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Produk</h3>
                <p class="text-sm text-gray-500">Lihat produk</p>
            </div>
        </div>
    </a>

    <!-- Pembelian -->
    <a href="<?= url('/customer/pembelian') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Pembelian</h3>
                <p class="text-sm text-gray-500">Riwayat transaksi</p>
            </div>
        </div>
    </a>

    <!-- Download -->
    <a href="<?= url('/customer/download') ?>" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Download</h3>
                <p class="text-sm text-gray-500">Unduh produk</p>
            </div>
        </div>
    </a>

    <!-- Bantuan -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Bantuan</h3>
                <p class="text-sm text-gray-500">Butuh bantuan?</p>
            </div>
        </div>
    </div>
</div>

<!-- Info Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Security Tip -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <div>
                <h4 class="font-semibold text-blue-900 mb-2">Tips Keamanan</h4>
                <p class="text-sm text-blue-800">Jangan bagikan password Anda kepada siapapun. Pastikan logout setelah selesai menggunakan aplikasi.</p>
            </div>
        </div>
    </div>

    <!-- Support Info -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <div>
                <h4 class="font-semibold text-green-900 mb-2">Dukungan Pelanggan</h4>
                <p class="text-sm text-green-800">Butuh bantuan? Hubungi tim support kami melalui email atau live chat yang tersedia.</p>
            </div>
        </div>
    </div>
</div>
