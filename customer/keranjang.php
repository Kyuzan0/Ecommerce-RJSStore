<?php
$page_title = 'Keranjang Belanja';
$active_page = 'keranjang';
include '../includes/customer_header.php';

$user_id = (int) $_SESSION['id'];

// Handle remove from cart
if (isset($_POST['hapus_item']) && csrf_validate()) {
    $produk_id = (int) $_POST['produk_id'];
    db_execute($conn, "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?", ["ii", $user_id, $produk_id]);
    flash('success', 'Produk dihapus dari keranjang.');
    header("Location: keranjang.php");
    exit;
}

// Handle clear cart
if (isset($_POST['kosongkan']) && csrf_validate()) {
    db_execute($conn, "DELETE FROM keranjang WHERE user_id = ?", ["i", $user_id]);
    flash('success', 'Keranjang dikosongkan.');
    header("Location: keranjang.php");
    exit;
}

// Get cart items
$cart_items = db_query($conn, 
    "SELECT k.id as cart_id, k.produk_id, p.nama_produk, p.harga, p.deskripsi, p.file_upload 
     FROM keranjang k 
     JOIN produk p ON k.produk_id = p.id 
     WHERE k.user_id = ? 
     ORDER BY k.created_at DESC", 
    ["i", $user_id]
);

$total_harga = 0;
foreach ($cart_items as $item) {
    $total_harga += $item['harga'];
}

include '../includes/customer_sidebar.php';
?>
        <?= flash_render() ?>

        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="dashboard.php" class="hover:text-green-600">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Keranjang Belanja</span>
        </div>

        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl font-bold text-gray-800">Keranjang Belanja</h1>
            <?php if (count($cart_items) > 0): ?>
            <span class="text-sm text-gray-500 bg-white border border-gray-200 px-3 py-1.5 rounded-lg">
                <?= count($cart_items) ?> produk
            </span>
            <?php endif; ?>
        </div>

        <?php if (count($cart_items) > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-3">
                <?php foreach ($cart_items as $item): ?>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                                <svg class="w-7 h-7" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800"><?= e($item['nama_produk']) ?></p>
                                <p class="text-xs text-gray-400 mt-0.5 line-clamp-1"><?= e($item['deskripsi']) ?></p>
                                <p class="text-sm font-bold mt-1" style="color:#42B549"><?= rupiah($item['harga']) ?></p>
                            </div>
                        </div>
                        <form method="POST" onsubmit="return confirm('Hapus produk ini dari keranjang?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="produk_id" value="<?= $item['produk_id'] ?>">
                            <button type="submit" name="hapus_item" value="1" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Clear cart -->
                <div class="pt-2">
                    <form method="POST" onsubmit="return confirm('Kosongkan semua item di keranjang?')">
                        <?= csrf_field() ?>
                        <button type="submit" name="kosongkan" value="1" class="text-sm text-red-500 hover:text-red-700 font-medium flex items-center gap-1.5 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Kosongkan Keranjang
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-20">
                    <h3 class="font-bold text-gray-800 mb-4">Ringkasan Belanja</h3>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total Produk</span>
                            <span class="font-medium text-gray-700"><?= count($cart_items) ?> item</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-800">Total Harga</span>
                            <span class="font-bold text-lg" style="color:#42B549"><?= rupiah($total_harga) ?></span>
                        </div>
                    </div>

                    <a href="checkout.php" 
                       class="block w-full text-center text-white py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition" 
                       style="background:#42B549">
                        Checkout (<?= count($cart_items) ?> item)
                    </a>
                    
                    <a href="produk.php" class="block w-full text-center text-gray-600 py-2.5 mt-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition border border-gray-200">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Empty Cart -->
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center" style="background:#E8F5E9">
                <svg class="w-10 h-10" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-700 mb-2">Keranjang Kosong</h3>
            <p class="text-gray-400 text-sm mb-5">Belum ada produk di keranjang belanja kamu.</p>
            <a href="produk.php" class="inline-block px-6 py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition" style="background:#42B549">Mulai Belanja</a>
        </div>
        <?php endif; ?>

<?php include '../includes/customer_footer.php'; ?>
