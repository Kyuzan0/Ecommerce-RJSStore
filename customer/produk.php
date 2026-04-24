<?php
$page_title = 'Katalog Produk';
$active_page = 'produk';
$extra_css = '.product-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.1); transform: translateY(-2px); } .product-card { transition: all 0.2s; }';
include '../includes/customer_header.php';

$user_id = (int) $_SESSION['id'];

// Pre-fetch cart and purchased product IDs for button state
$cart_produk_ids = [];
$purchased_produk_ids = [];
$cart_rows = db_query($conn, "SELECT produk_id FROM keranjang WHERE user_id = ?", ["i", $user_id]);
foreach ($cart_rows as $cr) $cart_produk_ids[] = (int) $cr['produk_id'];
$purch_rows = db_query($conn, "SELECT produk_id FROM transaksi WHERE user_id = ? AND status IN ('pending', 'success')", ["i", $user_id]);
foreach ($purch_rows as $pr) $purchased_produk_ids[] = (int) $pr['produk_id'];

include '../includes/customer_sidebar.php';
?>
        <?= flash_render() ?>

        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="dashboard.php" class="hover:text-green-600">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Katalog Produk</span>
        </div>

        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl font-bold text-gray-800">Katalog Produk Digital</h1>
            <span class="text-sm text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
                Produk tersedia untuk kamu
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php
            $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';
            if ($search_keyword != '') {
                $like = '%' . $search_keyword . '%';
                $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk WHERE nama_produk LIKE ? OR deskripsi LIKE ?", ["ss", $like, $like], 12);
                $products = db_query($conn, "SELECT * FROM produk WHERE nama_produk LIKE ? OR deskripsi LIKE ? ORDER BY id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'], ["ss", $like, $like]);
            } else {
                $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk", [], 12);
                $products = db_query($conn, "SELECT * FROM produk ORDER BY id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset']);
            }
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
                           onclick="document.getElementById('cartDropdown').classList.remove('hidden');"
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
            <?php endforeach; ?>
        </div>

        <?= pagination_render($paging) ?>

<?php include '../includes/customer_footer.php'; ?>
