<?php
/**
 * Seed dummy data following the actual application flow:
 *
 * 1. Create products (admin adds products via /admin-produk)
 * 2. Register customers (users register via /auth/register)
 * 3. Customers checkout (cart → checkout → transaksi records with order_ref)
 * 4. Payment webhook updates status (pending → success/failed)
 * 5. Customers leave ratings & reviews (only on status=success)
 *
 * Run: php database/seed_dummy.php
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/functions.php';
require_once BASE_PATH . '/app/core/Database.php';

$db = Database::getInstance();

echo "=== RJSStore Dummy Data Seeder ===\n\n";

// ─────────────────────────────────────────────
// Step 1: Admin creates products (via /admin-produk)
// Flow: AdminProdukController → Produk::create()
// ─────────────────────────────────────────────
echo "[Step 1] Creating products...\n";

$products = [
    // Akun
    ['nama_produk' => 'Netflix Premium 1 Bulan',     'harga' => 55000,  'deskripsi' => 'Akun Netflix Premium UHD 4K, sharing slot. Garansi full 1 bulan.', 'tipe_produk' => 'Akun', 'file_upload' => 'netflix_premium.txt'],
    ['nama_produk' => 'Spotify Premium 3 Bulan',     'harga' => 45000,  'deskripsi' => 'Akun Spotify Premium individual, bebas iklan, download offline.', 'tipe_produk' => 'Akun', 'file_upload' => 'spotify_premium.txt'],
    ['nama_produk' => 'Disney+ Hotstar 1 Bulan',     'harga' => 40000,  'deskripsi' => 'Akun Disney+ Hotstar, akses semua konten Marvel, Star Wars, dll.', 'tipe_produk' => 'Akun', 'file_upload' => 'disney_hotstar.txt'],
    ['nama_produk' => 'YouTube Premium 1 Bulan',     'harga' => 30000,  'deskripsi' => 'YouTube tanpa iklan, YouTube Music, download video offline.', 'tipe_produk' => 'Akun', 'file_upload' => 'youtube_premium.txt'],
    ['nama_produk' => 'Canva Pro 1 Tahun',           'harga' => 85000,  'deskripsi' => 'Akun Canva Pro dengan akses semua template premium dan fitur AI.', 'tipe_produk' => 'Akun', 'file_upload' => 'canva_pro.txt'],

    // Ebook
    ['nama_produk' => 'Belajar PHP dari Nol',        'harga' => 75000,  'deskripsi' => 'Ebook lengkap belajar PHP & MySQL untuk pemula hingga mahir. 350+ halaman.', 'tipe_produk' => 'Ebook', 'file_upload' => 'belajar_php.pdf'],
    ['nama_produk' => 'Panduan Laravel 11',           'harga' => 120000, 'deskripsi' => 'Ebook panduan lengkap Laravel 11 dengan studi kasus e-commerce.', 'tipe_produk' => 'Ebook', 'file_upload' => 'panduan_laravel.pdf'],
    ['nama_produk' => 'JavaScript Modern ES6+',       'harga' => 65000,  'deskripsi' => 'Ebook JavaScript modern: arrow functions, async/await, modules, dan lainnya.', 'tipe_produk' => 'Ebook', 'file_upload' => 'javascript_modern.pdf'],
    ['nama_produk' => 'UI/UX Design Fundamentals',    'harga' => 90000,  'deskripsi' => 'Panduan lengkap desain UI/UX dari wireframe hingga prototype.', 'tipe_produk' => 'Ebook', 'file_upload' => 'uiux_design.pdf'],

    // Game
    ['nama_produk' => 'Mobile Legends Diamond 500',   'harga' => 140000, 'deskripsi' => 'Top up 500 diamond Mobile Legends. Proses instan via ID game.', 'tipe_produk' => 'Game', 'file_upload' => 'ml_diamond.txt'],
    ['nama_produk' => 'PUBG Mobile UC 600',           'harga' => 150000, 'deskripsi' => 'Top up 600 UC PUBG Mobile. Kirim via ID dan server.', 'tipe_produk' => 'Game', 'file_upload' => 'pubg_uc.txt'],
    ['nama_produk' => 'Genshin Impact Genesis 300',   'harga' => 80000,  'deskripsi' => 'Top up 300 Genesis Crystal Genshin Impact.', 'tipe_produk' => 'Game', 'file_upload' => 'genshin_genesis.txt'],
    ['nama_produk' => 'Free Fire Diamond 500',        'harga' => 75000,  'deskripsi' => 'Top up 500 diamond Free Fire. Proses cepat.', 'tipe_produk' => 'Game', 'file_upload' => 'ff_diamond.txt'],
    ['nama_produk' => 'Valorant Points 1000',         'harga' => 160000, 'deskripsi' => 'Top up 1000 Valorant Points untuk beli skin dan battle pass.', 'tipe_produk' => 'Game', 'file_upload' => 'valorant_vp.txt'],

    // Software
    ['nama_produk' => 'Windows 11 Pro License',       'harga' => 250000, 'deskripsi' => 'Lisensi Windows 11 Pro original, aktivasi lifetime.', 'tipe_produk' => 'Software', 'file_upload' => 'windows11_key.txt'],
    ['nama_produk' => 'Microsoft Office 365 1 Tahun', 'harga' => 180000, 'deskripsi' => 'Lisensi Office 365 Personal: Word, Excel, PowerPoint, 1TB OneDrive.', 'tipe_produk' => 'Software', 'file_upload' => 'office365_key.txt'],
    ['nama_produk' => 'Antivirus Kaspersky 1 Tahun',  'harga' => 95000,  'deskripsi' => 'Lisensi Kaspersky Internet Security 1 device, 1 tahun.', 'tipe_produk' => 'Software', 'file_upload' => 'kaspersky_key.txt'],

    // Template
    ['nama_produk' => 'Template Landing Page Starter', 'harga' => 50000,  'deskripsi' => 'Template landing page responsive dengan Tailwind CSS. Cocok untuk startup.', 'tipe_produk' => 'Template', 'file_upload' => 'landing_page.zip'],
    ['nama_produk' => 'Template Dashboard Admin Pro',  'harga' => 175000, 'deskripsi' => 'Template admin dashboard lengkap: chart, tabel, form, dark mode.', 'tipe_produk' => 'Template', 'file_upload' => 'dashboard_admin.zip'],
    ['nama_produk' => 'Template Toko Online Bootstrap','harga' => 125000, 'deskripsi' => 'Template e-commerce responsive dengan Bootstrap 5. Siap pakai.', 'tipe_produk' => 'Template', 'file_upload' => 'toko_online.zip'],

    // Lainnya
    ['nama_produk' => 'Preset Lightroom Mobile Pack',  'harga' => 35000,  'deskripsi' => 'Paket 20 preset Lightroom Mobile untuk foto aesthetic dan cinematic.', 'tipe_produk' => 'Lainnya', 'file_upload' => 'preset_lightroom.zip'],
    ['nama_produk' => 'Sound Effect Pack 100+',        'harga' => 60000,  'deskripsi' => 'Koleksi 100+ sound effect untuk video editing. Format WAV & MP3.', 'tipe_produk' => 'Lainnya', 'file_upload' => 'sfx_pack.zip'],
    ['nama_produk' => 'Font Bundle Premium 50 Fonts',  'harga' => 45000,  'deskripsi' => 'Paket 50 font premium untuk desain grafis. Lisensi komersial.', 'tipe_produk' => 'Lainnya', 'file_upload' => 'font_bundle.zip'],
];

$productIds = [];
foreach ($products as $p) {
    $db->execute(
        "INSERT INTO produk (nama_produk, harga, deskripsi, tipe_produk, file_upload) VALUES (?, ?, ?, ?, ?)",
        [$p['nama_produk'], $p['harga'], $p['deskripsi'], $p['tipe_produk'], $p['file_upload']]
    );
    $productIds[] = $db->lastInsertId();
    echo "  + {$p['nama_produk']} ({$p['tipe_produk']}) - Rp " . number_format($p['harga'], 0, ',', '.') . "\n";
}
echo "  Total: " . count($productIds) . " products created.\n\n";

// ─────────────────────────────────────────────
// Step 2: Customers register (via /auth/register)
// Flow: AuthController::register() → User::createUser() → password_hash()
// ─────────────────────────────────────────────
echo "[Step 2] Registering customers...\n";

$customers = [
    ['name' => 'Andi Pratama',    'email' => 'andi@gmail.com'],
    ['name' => 'Siti Nurhaliza',  'email' => 'siti@gmail.com'],
    ['name' => 'Budi Santoso',    'email' => 'budi@gmail.com'],
    ['name' => 'Dewi Lestari',    'email' => 'dewi@gmail.com'],
    ['name' => 'Rizky Fauzan',    'email' => 'rizky.f@gmail.com'],
    ['name' => 'Putri Ayu',       'email' => 'putri@gmail.com'],
    ['name' => 'Hendra Wijaya',   'email' => 'hendra@gmail.com'],
    ['name' => 'Maya Sari',       'email' => 'maya@gmail.com'],
    ['name' => 'Fajar Nugroho',   'email' => 'fajar@gmail.com'],
    ['name' => 'Lina Marlina',    'email' => 'lina@gmail.com'],
];

// All customers use password "password123" — same as User::createUser() flow
$defaultPassword = password_hash('password123', PASSWORD_DEFAULT);
$customerIds = [];

foreach ($customers as $c) {
    $db->execute(
        "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')",
        [$c['name'], $c['email'], $defaultPassword]
    );
    $customerIds[] = $db->lastInsertId();
    echo "  + {$c['name']} <{$c['email']}>\n";
}
echo "  Total: " . count($customerIds) . " customers registered.\n\n";

// ─────────────────────────────────────────────
// Step 3: Customers make purchases (checkout flow)
// Flow: Cart → CheckoutController::index() → Transaksi::createOrder()
//       → order_ref = 'ORD-{userId}-{timestamp}'
//       → 1 transaksi record per product in cart
//       → status = 'pending' initially
// ─────────────────────────────────────────────
echo "[Step 3] Simulating checkout transactions...\n";

// Define orders: each customer makes 1-4 orders over the past 90 days
// Each order has 1-4 products (simulating cart items)
$orders = [
    // Andi — 4 orders (power buyer)
    ['customer_idx' => 0, 'product_indices' => [0, 5],       'days_ago' => 85, 'status' => 'success'],
    ['customer_idx' => 0, 'product_indices' => [9, 12],      'days_ago' => 60, 'status' => 'success'],
    ['customer_idx' => 0, 'product_indices' => [14, 15],     'days_ago' => 30, 'status' => 'success'],
    ['customer_idx' => 0, 'product_indices' => [17, 20],     'days_ago' => 5,  'status' => 'pending'],

    // Siti — 3 orders
    ['customer_idx' => 1, 'product_indices' => [1, 6, 7],    'days_ago' => 78, 'status' => 'success'],
    ['customer_idx' => 1, 'product_indices' => [3, 8],       'days_ago' => 45, 'status' => 'success'],
    ['customer_idx' => 1, 'product_indices' => [18],         'days_ago' => 10, 'status' => 'success'],

    // Budi — 3 orders (one failed)
    ['customer_idx' => 2, 'product_indices' => [0, 1, 2],    'days_ago' => 70, 'status' => 'success'],
    ['customer_idx' => 2, 'product_indices' => [10, 13],     'days_ago' => 40, 'status' => 'failed'],
    ['customer_idx' => 2, 'product_indices' => [14, 16],     'days_ago' => 15, 'status' => 'success'],

    // Dewi — 2 orders
    ['customer_idx' => 3, 'product_indices' => [4, 8],       'days_ago' => 65, 'status' => 'success'],
    ['customer_idx' => 3, 'product_indices' => [19, 21],     'days_ago' => 20, 'status' => 'success'],

    // Rizky — 3 orders
    ['customer_idx' => 4, 'product_indices' => [5, 6],       'days_ago' => 55, 'status' => 'success'],
    ['customer_idx' => 4, 'product_indices' => [9],          'days_ago' => 35, 'status' => 'success'],
    ['customer_idx' => 4, 'product_indices' => [15, 17, 22], 'days_ago' => 8,  'status' => 'success'],

    // Putri — 2 orders (one pending)
    ['customer_idx' => 5, 'product_indices' => [2, 3, 7],    'days_ago' => 50, 'status' => 'success'],
    ['customer_idx' => 5, 'product_indices' => [11, 20],     'days_ago' => 2,  'status' => 'pending'],

    // Hendra — 3 orders
    ['customer_idx' => 6, 'product_indices' => [0, 4],       'days_ago' => 72, 'status' => 'success'],
    ['customer_idx' => 6, 'product_indices' => [10, 11, 12], 'days_ago' => 38, 'status' => 'success'],
    ['customer_idx' => 6, 'product_indices' => [16],         'days_ago' => 12, 'status' => 'success'],

    // Maya — 2 orders (one failed)
    ['customer_idx' => 7, 'product_indices' => [1, 5, 8],    'days_ago' => 62, 'status' => 'success'],
    ['customer_idx' => 7, 'product_indices' => [18, 19],     'days_ago' => 25, 'status' => 'failed'],

    // Fajar — 3 orders
    ['customer_idx' => 8, 'product_indices' => [6, 7],       'days_ago' => 48, 'status' => 'success'],
    ['customer_idx' => 8, 'product_indices' => [13, 14],     'days_ago' => 22, 'status' => 'success'],
    ['customer_idx' => 8, 'product_indices' => [0, 2, 21],   'days_ago' => 3,  'status' => 'success'],

    // Lina — 2 orders
    ['customer_idx' => 9, 'product_indices' => [3, 9, 10],   'days_ago' => 42, 'status' => 'success'],
    ['customer_idx' => 9, 'product_indices' => [22, 17],     'days_ago' => 7,  'status' => 'success'],
];

$totalTransactions = 0;
$baseTimestamp = time();

foreach ($orders as $order) {
    $userId    = $customerIds[$order['customer_idx']];
    $userName  = $customers[$order['customer_idx']]['name'];
    $daysAgo   = $order['days_ago'];
    $tanggal   = date('Y-m-d', strtotime("-{$daysAgo} days"));
    $status    = $order['status'];

    // Generate order_ref exactly like CheckoutController: 'ORD-{userId}-{timestamp}'
    // Use a unique timestamp per order (offset by days_ago + random seconds)
    $orderTimestamp = $baseTimestamp - ($daysAgo * 86400) + rand(0, 43200);
    $orderRef = "ORD-{$userId}-{$orderTimestamp}";

    $itemNames = [];
    foreach ($order['product_indices'] as $pIdx) {
        $produkId = $productIds[$pIdx];
        $db->execute(
            "INSERT INTO transaksi (user_id, produk_id, tanggal, status, order_ref) VALUES (?, ?, ?, ?, ?)",
            [$userId, $produkId, $tanggal, $status, $orderRef]
        );
        $itemNames[] = $products[$pIdx]['nama_produk'];
        $totalTransactions++;
    }

    $itemList = implode(', ', array_map(fn($n) => substr($n, 0, 25), $itemNames));
    echo "  [{$tanggal}] {$userName} → {$status} ({$itemList}...)\n";
}
echo "  Total: {$totalTransactions} transaction records across " . count($orders) . " orders.\n\n";

// ─────────────────────────────────────────────
// Step 4: Customers rate purchased products (only status=success)
// Flow: CustomerRatingController::index() → Transaksi::setRating()
// ─────────────────────────────────────────────
echo "[Step 4] Adding ratings & reviews (success transactions only)...\n";

$reviewTexts = [
    5 => [
        'Sangat puas! Produk sesuai deskripsi dan proses cepat.',
        'Mantap banget, recommended seller!',
        'Kualitas premium, worth the price. Terima kasih!',
        'Proses instan, langsung bisa dipakai. Top!',
        'Luar biasa, sudah beli berkali-kali dan selalu puas.',
        'Pelayanan cepat dan ramah. Produk oke banget!',
    ],
    4 => [
        'Bagus, sesuai ekspektasi. Pengiriman cepat.',
        'Produk oke, cuma instruksinya kurang detail.',
        'Overall puas, semoga ada promo lagi.',
        'Good product, fast delivery. Recommended.',
        'Cukup memuaskan, akan beli lagi nanti.',
    ],
    3 => [
        'Lumayan, tapi ada sedikit kendala di awal.',
        'Produknya standar, sesuai harga lah.',
        'Oke sih, tapi respon agak lama.',
    ],
    2 => [
        'Kurang sesuai ekspektasi, deskripsi agak misleading.',
        'Agak kecewa, tapi masih bisa dipakai.',
    ],
    1 => [
        'Tidak sesuai deskripsi sama sekali.',
    ],
];

// Rating distribution: mostly 4-5 stars (realistic for digital products)
$ratingWeights = [5, 5, 5, 5, 5, 4, 4, 4, 4, 3, 3, 2];

$successTransactions = $db->fetchAll(
    "SELECT id, user_id, produk_id FROM transaksi WHERE status = 'success'"
);

$ratedCount = 0;
foreach ($successTransactions as $tx) {
    // ~70% of successful transactions get rated (realistic)
    if (rand(1, 100) > 70) {
        continue;
    }

    $rating = $ratingWeights[array_rand($ratingWeights)];
    $reviews = $reviewTexts[$rating];
    $ulasan = $reviews[array_rand($reviews)];

    $db->execute(
        "UPDATE transaksi SET rating = ?, ulasan = ? WHERE id = ?",
        [$rating, $ulasan, $tx['id']]
    );
    $ratedCount++;
}
echo "  Rated {$ratedCount} out of " . count($successTransactions) . " successful transactions.\n\n";

// ─────────────────────────────────────────────
// Step 5: Some customers have items in cart (active shoppers)
// Flow: CartController::apiAdd() → Keranjang::addItem()
// ─────────────────────────────────────────────
echo "[Step 5] Adding items to active carts...\n";

$activeCarts = [
    // Andi has 2 items in cart (browsing for next purchase)
    ['customer_idx' => 0, 'product_indices' => [21, 22]],
    // Dewi has 1 item
    ['customer_idx' => 3, 'product_indices' => [6]],
    // Fajar has 3 items
    ['customer_idx' => 8, 'product_indices' => [1, 4, 18]],
    // Lina has 2 items
    ['customer_idx' => 9, 'product_indices' => [5, 15]],
];

$cartCount = 0;
foreach ($activeCarts as $cart) {
    $userId   = $customerIds[$cart['customer_idx']];
    $userName = $customers[$cart['customer_idx']]['name'];
    foreach ($cart['product_indices'] as $pIdx) {
        $produkId = $productIds[$pIdx];
        $db->execute(
            "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)",
            [$userId, $produkId]
        );
        $cartCount++;
    }
    echo "  + {$userName}: " . count($cart['product_indices']) . " items in cart\n";
}
echo "  Total: {$cartCount} cart items.\n\n";

// ─────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────
echo "=== Summary ===\n";

$stats = [
    'products'     => $db->fetchOne("SELECT COUNT(*) as c FROM produk")['c'],
    'customers'    => $db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'customer'")['c'],
    'transactions' => $db->fetchOne("SELECT COUNT(*) as c FROM transaksi")['c'],
    'success'      => $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'success'")['c'],
    'pending'      => $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'pending'")['c'],
    'failed'       => $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'failed'")['c'],
    'rated'        => $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE rating > 0")['c'],
    'cart_items'   => $db->fetchOne("SELECT COUNT(*) as c FROM keranjang")['c'],
    'revenue'      => $db->fetchOne("SELECT COALESCE(SUM(p.harga), 0) as total FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status = 'success'")['total'],
];

echo "  Products:      {$stats['products']}\n";
echo "  Customers:     {$stats['customers']}\n";
echo "  Transactions:  {$stats['transactions']} (success: {$stats['success']}, pending: {$stats['pending']}, failed: {$stats['failed']})\n";
echo "  Rated:         {$stats['rated']}\n";
echo "  Cart items:    {$stats['cart_items']}\n";
echo "  Total revenue: Rp " . number_format($stats['revenue'], 0, ',', '.') . "\n";

// Product type breakdown
echo "\n  Products by type:\n";
$types = $db->fetchAll("SELECT tipe_produk, COUNT(*) as c FROM produk GROUP BY tipe_produk ORDER BY c DESC");
foreach ($types as $t) {
    echo "    {$t['tipe_produk']}: {$t['c']}\n";
}

// Top 5 products by sales
echo "\n  Top 5 products by sales:\n";
$topProducts = $db->fetchAll(
    "SELECT p.nama_produk, COUNT(*) as sold, SUM(p.harga) as revenue
     FROM transaksi t JOIN produk p ON t.produk_id = p.id
     WHERE t.status = 'success'
     GROUP BY t.produk_id, p.nama_produk
     ORDER BY sold DESC, revenue DESC
     LIMIT 5"
);
foreach ($topProducts as $i => $tp) {
    $rank = $i + 1;
    echo "    {$rank}. {$tp['nama_produk']} — {$tp['sold']} sold (Rp " . number_format($tp['revenue'], 0, ',', '.') . ")\n";
}

echo "\nDone! All dummy data follows the actual application flow.\n";
echo "Login credentials: any customer email with password 'password123'\n";
