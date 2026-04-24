<?php
session_start();
include __DIR__ . '/config/koneksi.php';
include_once __DIR__ . '/config/helpers.php';

$is_logged_in = isset($_SESSION['role']);
$is_customer = $is_logged_in && $_SESSION['role'] === 'customer';
$is_admin = $is_logged_in && $_SESSION['role'] === 'admin';

// Admin goes to admin dashboard
if ($is_admin) {
    header('Location: admin/dashboard.php');
    exit;
}

// Initialize session cart for guests
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate initial cart count
$initial_cart_count = 0;
if ($is_customer) {
    $initial_cart_count = db_count($conn, "SELECT COUNT(*) as c FROM keranjang WHERE user_id = ?", ["i", (int)$_SESSION['id']]);
} else {
    $initial_cart_count = count($_SESSION['cart']);
}

// Build sets of cart/purchased product IDs for button state
$cart_produk_ids = [];
$purchased_produk_ids = [];
if ($is_customer) {
    $user_id = (int) $_SESSION['id'];
    $cart_rows = db_query($conn, "SELECT produk_id FROM keranjang WHERE user_id = ?", ["i", $user_id]);
    foreach ($cart_rows as $cr) $cart_produk_ids[] = (int) $cr['produk_id'];
    $purch_rows = db_query($conn, "SELECT produk_id FROM transaksi WHERE user_id = ? AND status IN ('pending', 'success')", ["i", $user_id]);
    foreach ($purch_rows as $pr) $purchased_produk_ids[] = (int) $pr['produk_id'];
} else {
    foreach ($_SESSION['cart'] as $ci) $cart_produk_ids[] = (int) $ci['produk_id'];
}

$page_title = 'RJSStore - Toko Produk Digital';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background:#F5F5F5; }
        .product-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
        .product-card { transition: all 0.2s; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<!-- TOP NAV -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center gap-4">
        <a href="index.php" class="flex items-center gap-2 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
            </div>
            <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
        </a>
        <div class="flex-1 max-w-xl">
            <form action="index.php" method="GET" class="flex bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                <input type="text" name="search" placeholder="Cari produk di RJSStore..." value="<?= e($_GET['search'] ?? '') ?>" class="flex-1 px-4 py-2.5 bg-transparent text-sm outline-none">
                <button type="submit" class="px-5 py-2.5 text-white text-sm font-semibold" style="background:#42B549">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>
        <div class="flex items-center gap-3 ml-auto">
            <?php
            // Cart dropdown (works for both guest and logged-in)
            $cart_api_url = 'api/cart_action.php';
            $checkout_url = 'customer/checkout.php';
            $login_url = 'auth/login.php';
            include __DIR__ . '/includes/cart_dropdown.php';
            ?>

            <?php if ($is_customer): ?>
                <a href="customer/pembelian.php" class="flex items-center gap-1.5 text-gray-600 hover:text-gray-800 text-sm px-3 py-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Pesanan
                </a>
                <div class="relative" id="profileDropdown">
                    <button onclick="document.getElementById('profileMenu').classList.toggle('hidden')" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 rounded-xl px-3 py-2 transition cursor-pointer">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:#42B549">
                            <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700"><?= e($_SESSION['name']) ?></span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                        <a href="customer/dashboard.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4"/></svg>
                            Dashboard
                        </a>
                        <a href="customer/profile.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                            Settings Profile
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="auth/logout.php" class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/></svg>
                            Keluar
                        </a>
                    </div>
                </div>
                <script>document.addEventListener('click',function(e){var d=document.getElementById('profileDropdown');var m=document.getElementById('profileMenu');if(d&&!d.contains(e.target)){m.classList.add('hidden')}});</script>
            <?php elseif (!$is_logged_in): ?>
                <a href="auth/login.php" class="text-sm font-medium text-gray-700 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition">Masuk</a>
                <a href="auth/register.php" class="text-sm font-semibold text-white px-4 py-2 rounded-lg transition hover:opacity-90" style="background:#42B549">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="flex-1">
<!-- HERO BANNER (only on first page without search) -->
<?php if (empty($_GET['search']) && ($_GET['page'] ?? 1) == 1): ?>
<div class="max-w-7xl mx-auto px-6 mt-6">
    <div class="rounded-2xl p-8 md:p-12 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #42B549 0%, #2E7D32 100%)">
        <div class="relative z-10 max-w-lg">
            <h1 class="text-2xl md:text-3xl font-extrabold mb-3">Selamat Datang di RJSStore</h1>
            <p class="text-green-100 text-sm md:text-base mb-5">Temukan berbagai produk digital berkualitas dengan harga terbaik. Download langsung setelah pembayaran!</p>
            <?php if (!$is_logged_in): ?>
            <a href="auth/register.php" class="inline-flex items-center gap-2 bg-white text-green-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-green-50 transition">
                Mulai Belanja
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <div class="absolute right-8 top-1/2 -translate-y-1/2 opacity-10">
            <svg class="w-48 h-48" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MAIN CONTENT -->
<div class="max-w-7xl mx-auto px-6 py-6">
    <?= flash_render() ?>

    <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-gray-800">
            <?php if (!empty($_GET['search'])): ?>
                Hasil Pencarian: "<?= e($_GET['search']) ?>"
            <?php else: ?>
                Katalog Produk Digital
            <?php endif; ?>
        </h2>
        <span class="text-sm text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
            Produk digital terpercaya
        </span>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php
        $search_keyword = $_GET['search'] ?? '';
        if ($search_keyword != '') {
            $like = '%' . $search_keyword . '%';
            $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk WHERE nama_produk LIKE ? OR deskripsi LIKE ?", ["ss", $like, $like], 12);
            $products = db_query($conn, "SELECT * FROM produk WHERE nama_produk LIKE ? OR deskripsi LIKE ? ORDER BY id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'], ["ss", $like, $like]);
        } else {
            $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk", [], 12);
            $products = db_query($conn, "SELECT * FROM produk ORDER BY id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset']);
        }

        if (empty($products)):
        ?>
        <div class="col-span-full text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <p class="text-gray-500 font-medium">Tidak ada produk ditemukan</p>
            <?php if (!empty($search_keyword)): ?>
            <a href="index.php" class="inline-block mt-3 text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-50 transition" style="color:#42B549">Lihat semua produk</a>
            <?php endif; ?>
        </div>
        <?php else:
        foreach($products as $row):
            $pid = (int) $row['id'];
            $in_cart = in_array($pid, $cart_produk_ids);
            $purchased = in_array($pid, $purchased_produk_ids);
        ?>
        <?php $tipe_cfg = tipe_produk_config($row['tipe_produk'] ?? 'Lainnya'); ?>
        <div class="product-card bg-white rounded-2xl overflow-hidden border border-gray-100 flex flex-col">
            <div class="relative h-40 flex items-center justify-center" style="background: linear-gradient(135deg, <?= $tipe_cfg['bg'] ?> 0%, <?= $tipe_cfg['bg'] ?>dd 100%)">
                <svg class="w-16 h-16 opacity-40" style="color:<?= $tipe_cfg['color'] ?>" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                <span class="absolute top-2 left-2 bg-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm" style="color:<?= $tipe_cfg['color'] ?>"><?= e($tipe_cfg['label']) ?></span>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <h3 class="font-semibold text-gray-800 text-sm mb-1 line-clamp-2"><?= e($row['nama_produk']); ?></h3>
                <p class="text-xs text-gray-400 mb-3 line-clamp-2 flex-1"><?= e($row['deskripsi']); ?></p>
                
                <?php
                $d_rate = db_query_one($conn, "SELECT AVG(rating) as avg_rate, COUNT(id) as total FROM transaksi WHERE produk_id = ? AND rating > 0", ["i", $row['id']]);
                $avg = $d_rate['avg_rate'] ? round($d_rate['avg_rate'], 1) : "0.0";
                $total_ulasan = $d_rate['total'];
                ?>
                <div class="flex items-center gap-1.5 mb-2">
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700"><?= $avg; ?></span>
                    <span class="text-xs text-gray-400">(<?= $total_ulasan; ?> Ulasan)</span>
                </div>
                
                <p class="font-bold text-lg mb-3" style="color:#42B549"><?= rupiah($row['harga']); ?></p>
                
                <?php if ($purchased): ?>
                    <span class="block text-center text-gray-400 py-2.5 rounded-xl text-sm font-medium bg-gray-100 cursor-not-allowed">
                        Sudah Dibeli
                    </span>
                <?php elseif ($in_cart): ?>
                    <button data-cart-produk="<?= $pid ?>"
                       onclick="document.getElementById('cartDropdown').classList.remove('hidden'); window._cartRemove || void(0);"
                       class="w-full text-center text-white py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition cursor-pointer cart-added" style="background:#FF9800">
                        Di Keranjang ✓
                    </button>
                <?php else: ?>
                    <button data-cart-produk="<?= $pid ?>"
                       onclick="window._cartAdd(<?= $pid ?>, this)"
                       class="w-full text-center text-white py-2.5 rounded-xl text-sm font-semibold hover:opacity-90 transition cursor-pointer" style="background:#42B549">
                        + Keranjang
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?= pagination_render($paging) ?>
</div>

</div>

<!-- FOOTER -->
<footer class="bg-white border-t border-gray-200 mt-8">
    <div class="max-w-7xl mx-auto px-6 py-6 text-center">
        <p class="text-sm text-gray-500">&copy; <?= date('Y') ?> RJSStore. Toko Produk Digital Terpercaya.</p>
    </div>
</footer>

<?php include __DIR__ . '/includes/toast.php'; ?>
</body>
</html>
