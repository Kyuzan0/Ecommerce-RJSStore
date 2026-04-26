<?php
/**
 * Seeder: Insert dummy data for produk, transaksi, and keranjang
 *
 * Run via CLI:     php database/seed_dummy_data.php
 * Run via browser: http://localhost/ecommerce/database/seed_dummy_data.php
 *
 * Flow matches the real app:
 *   1. Customer browses produk → adds to keranjang
 *   2. Checkout → creates transaksi with status='pending', order_ref='ORD-{userId}-{timestamp}'
 *   3. Midtrans webhook → updates status to 'success' or 'failed'
 *   4. Admin can manually set 'pending'/'success'/'cancelled'
 *   5. Customer can rate/review only 'success' transactions
 *   6. Laporan/dashboard filters by status='success'
 *   7. Download page filters by status='success'
 *
 * Prerequisites:
 *   - Tables users, produk, transaksi, keranjang must exist
 *   - Migrations (tipe_produk, order_ref, keranjang) must be applied first
 *
 * Idempotent: safe to run multiple times. Use --fresh to wipe and reseed.
 */

// Bootstrap the app (same as public/index.php)
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/functions.php';
require_once BASE_PATH . '/app/core/Database.php';

$db = Database::getInstance();

$isCli = php_sapi_name() === 'cli';
$br    = $isCli ? "\n" : "<br>";
$bold  = fn(string $s) => $isCli ? $s : "<strong>$s</strong>";
$fresh = $isCli ? in_array('--fresh', $argv ?? []) : isset($_GET['fresh']);

if (!$isCli) echo "<h2>RJSStore - Dummy Data Seeder</h2>";
else echo "=== RJSStore - Dummy Data Seeder ===$br";

// ─── Fresh mode: wipe existing seed data ───────────────────────────────────────
if ($fresh) {
    $db->query("DELETE FROM keranjang");
    $db->query("DELETE FROM transaksi");
    $db->query("DELETE FROM produk");
    echo "{$bold('Fresh mode')}: cleared keranjang, transaksi, produk.$br$br";
}

// ─── Produk ────────────────────────────────────────────────────────────────────
$produk = [
    // Akun (idx 0-3)
    ['nama_produk' => 'Netflix Premium 1 Bulan',       'harga' => 45000,  'deskripsi' => 'Akun Netflix Premium UHD 4K, sharing slot. Garansi full 1 bulan.',                          'tipe_produk' => 'Akun',     'file_upload' => 'netflix_premium.txt'],
    ['nama_produk' => 'Spotify Premium Lifetime',       'harga' => 35000,  'deskripsi' => 'Akun Spotify Premium upgrade ke akun pribadi. Garansi lifetime.',                            'tipe_produk' => 'Akun',     'file_upload' => 'spotify_lifetime.txt'],
    ['nama_produk' => 'Disney+ Hotstar VIP 3 Bulan',   'harga' => 25000,  'deskripsi' => 'Akun Disney+ Hotstar VIP, bisa nonton semua konten eksklusif.',                              'tipe_produk' => 'Akun',     'file_upload' => 'disney_hotstar.txt'],
    ['nama_produk' => 'YouTube Premium 1 Bulan',        'harga' => 20000,  'deskripsi' => 'Akun YouTube Premium tanpa iklan, bisa download video offline.',                              'tipe_produk' => 'Akun',     'file_upload' => 'youtube_premium.txt'],

    // Ebook (idx 4-6)
    ['nama_produk' => 'Belajar PHP untuk Pemula',       'harga' => 75000,  'deskripsi' => 'Ebook lengkap belajar PHP dari dasar hingga membuat project CRUD. Format PDF, 250 halaman.', 'tipe_produk' => 'Ebook',    'file_upload' => 'belajar_php.pdf'],
    ['nama_produk' => 'Panduan Laravel 11 Lengkap',     'harga' => 120000, 'deskripsi' => 'Ebook panduan Laravel 11 dari instalasi hingga deploy. Termasuk source code project.',       'tipe_produk' => 'Ebook',    'file_upload' => 'panduan_laravel11.pdf'],
    ['nama_produk' => 'Mastering JavaScript Modern',    'harga' => 95000,  'deskripsi' => 'Ebook JavaScript ES6+ lengkap dengan contoh real-world project. 320 halaman.',               'tipe_produk' => 'Ebook',    'file_upload' => 'mastering_js.pdf'],

    // Game (idx 7-10)
    ['nama_produk' => 'Minecraft Java Edition Key',     'harga' => 150000, 'deskripsi' => 'License key Minecraft Java Edition original. Bisa redeem langsung di minecraft.net.',         'tipe_produk' => 'Game',     'file_upload' => 'minecraft_key.txt'],
    ['nama_produk' => 'Valorant Points 1000 VP',        'harga' => 135000, 'deskripsi' => 'Top up 1000 Valorant Points via gift card code. Region Indonesia.',                          'tipe_produk' => 'Game',     'file_upload' => 'valorant_1000vp.txt'],
    ['nama_produk' => 'Steam Wallet IDR 100.000',       'harga' => 105000, 'deskripsi' => 'Kode Steam Wallet senilai Rp100.000. Bisa digunakan untuk beli game di Steam.',              'tipe_produk' => 'Game',     'file_upload' => 'steam_wallet_100k.txt'],
    ['nama_produk' => 'Mobile Legends 500 Diamonds',    'harga' => 140000, 'deskripsi' => 'Top up 500 Diamonds Mobile Legends. Proses instan via ID game.',                             'tipe_produk' => 'Game',     'file_upload' => 'ml_500dm.txt'],

    // Software (idx 11-14)
    ['nama_produk' => 'Windows 11 Pro License',         'harga' => 250000, 'deskripsi' => 'Lisensi Windows 11 Pro original retail. Aktivasi online, lifetime.',                         'tipe_produk' => 'Software', 'file_upload' => 'win11_pro_key.txt'],
    ['nama_produk' => 'Microsoft Office 365 1 Tahun',   'harga' => 180000, 'deskripsi' => 'Akun Office 365 Personal 1 tahun. Word, Excel, PowerPoint + 1TB OneDrive.',                  'tipe_produk' => 'Software', 'file_upload' => 'office365_1yr.txt'],
    ['nama_produk' => 'Canva Pro 1 Tahun',              'harga' => 85000,  'deskripsi' => 'Akun Canva Pro invite ke tim. Akses semua template premium dan fitur AI.',                   'tipe_produk' => 'Software', 'file_upload' => 'canva_pro.txt'],
    ['nama_produk' => 'Adobe Creative Cloud 1 Bulan',   'harga' => 95000,  'deskripsi' => 'Akun Adobe CC all apps: Photoshop, Illustrator, Premiere Pro, dll. Garansi 1 bulan.',        'tipe_produk' => 'Software', 'file_upload' => 'adobe_cc.txt'],

    // Template (idx 15-17)
    ['nama_produk' => 'Template Landing Page Starter',  'harga' => 50000,  'deskripsi' => 'Template landing page responsive HTML/CSS/JS. Cocok untuk UMKM dan portfolio.',              'tipe_produk' => 'Template', 'file_upload' => 'landing_page_starter.zip'],
    ['nama_produk' => 'Template Dashboard Admin Pro',   'harga' => 175000, 'deskripsi' => 'Template admin dashboard dengan chart, tabel, dan CRUD. Built with Tailwind CSS.',            'tipe_produk' => 'Template', 'file_upload' => 'dashboard_admin_pro.zip'],
    ['nama_produk' => 'Template Toko Online Bootstrap', 'harga' => 125000, 'deskripsi' => 'Template e-commerce lengkap dengan keranjang, checkout, dan halaman produk. Bootstrap 5.',    'tipe_produk' => 'Template', 'file_upload' => 'toko_online_bs5.zip'],

    // Lainnya (idx 18-20)
    ['nama_produk' => 'Preset Lightroom Aesthetic Pack','harga' => 30000,  'deskripsi' => 'Paket 20 preset Lightroom aesthetic untuk foto Instagram. Format DNG + XMP.',                'tipe_produk' => 'Lainnya',  'file_upload' => 'preset_aesthetic.zip'],
    ['nama_produk' => 'Font Bundle Premium 50 Fonts',   'harga' => 65000,  'deskripsi' => 'Koleksi 50 font premium untuk desain grafis dan branding. Format OTF/TTF.',                  'tipe_produk' => 'Lainnya',  'file_upload' => 'font_bundle_50.zip'],
    ['nama_produk' => 'Icon Pack 1000 SVG Icons',       'harga' => 40000,  'deskripsi' => 'Koleksi 1000 icon SVG untuk web dan mobile app. Termasuk Figma file.',                       'tipe_produk' => 'Lainnya',  'file_upload' => 'icon_pack_1000.zip'],
];

$insertedProduk = 0;
foreach ($produk as $p) {
    $existing = $db->fetchOne("SELECT id FROM produk WHERE nama_produk = ?", [$p['nama_produk']]);
    if ($existing) {
        continue;
    }
    $db->query(
        "INSERT INTO produk (nama_produk, harga, deskripsi, tipe_produk, file_upload) VALUES (?, ?, ?, ?, ?)",
        [$p['nama_produk'], $p['harga'], $p['deskripsi'], $p['tipe_produk'], $p['file_upload']]
    );
    $insertedProduk++;
}
echo "Produk: {$bold((string)$insertedProduk)} rows inserted.$br";

// ─── Resolve IDs ───────────────────────────────────────────────────────────────
$produkRows   = $db->fetchAll("SELECT id FROM produk ORDER BY id");
$produkIds    = array_column($produkRows, 'id');
$customerRows = $db->fetchAll("SELECT id FROM users WHERE role = 'customer' ORDER BY id");
$customerIds  = array_column($customerRows, 'id');

if (empty($produkIds) || empty($customerIds)) {
    echo "Transaksi: Skipped (no produk or customer data).$br";
    echo "Keranjang: Skipped (no produk or customer data).$br";
    echo "{$br}{$bold('Seeding complete!')}$br";
    exit;
}

// Helper: generate order_ref matching CheckoutController format: ORD-{userId}-{timestamp}
// We use a fake but realistic timestamp for each order
$orderCounter = 1700000000; // base fake timestamp
function nextOrderRef(int $userId): string {
    global $orderCounter;
    $orderCounter++;
    return "ORD-{$userId}-{$orderCounter}";
}

// ─── Transaksi ─────────────────────────────────────────────────────────────────
// Dates relative to today so dashboard 7-day chart always has data
$today     = date('Y-m-d');
$d1        = date('Y-m-d', strtotime('-1 day'));
$d2        = date('Y-m-d', strtotime('-2 days'));
$d3        = date('Y-m-d', strtotime('-3 days'));
$d4        = date('Y-m-d', strtotime('-4 days'));
$d5        = date('Y-m-d', strtotime('-5 days'));
$d6        = date('Y-m-d', strtotime('-6 days'));
$d10       = date('Y-m-d', strtotime('-10 days'));
$d15       = date('Y-m-d', strtotime('-15 days'));
$d20       = date('Y-m-d', strtotime('-20 days'));
$d30       = date('Y-m-d', strtotime('-30 days'));
$d45       = date('Y-m-d', strtotime('-45 days'));
$d60       = date('Y-m-d', strtotime('-60 days'));

// Customer IDs by index: 0=rizky(id3?), 1=syafwan(id4?), 2=customer(id2?)
// We use idx to reference $customerIds array

$transaksi = [];

// ── Customer 0 (rizky): active buyer ──────────────────────────────────────────
// Order 1: bought Netflix + Spotify 2 months ago (multi-item, success, reviewed)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 0,  'tanggal' => $d60, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Akun langsung aktif, mantap! Kualitas UHD jernih banget.'];
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 1,  'tanggal' => $d60, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Spotify lifetime beneran work, sudah 2 bulan masih aman.'];

// Order 2: bought PHP ebook 45 days ago (single item, success, reviewed)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 4,  'tanggal' => $d45, 'status' => 'success', 'order_ref' => $ref, 'rating' => 4, 'ulasan' => 'Ebook-nya lengkap, penjelasan mudah dipahami. Recommended buat pemula.'];

// Order 3: bought Minecraft key 30 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 7,  'tanggal' => $d30, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Key langsung bisa dipakai, proses cepat. Terima kasih!'];

// Order 4: bought Windows 11 + Office 365 last week (multi-item, success, not yet reviewed)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 11, 'tanggal' => $d5, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 12, 'tanggal' => $d5, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// Order 5: bought Landing Page template 2 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 15, 'tanggal' => $d2, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Template keren, responsive, tinggal edit konten aja.'];

// Order 6: bought Preset Lightroom today (pending - just checked out, waiting payment)
$ref = nextOrderRef($customerIds[0]);
$transaksi[] = ['user_idx' => 0, 'produk_idx' => 18, 'tanggal' => $today, 'status' => 'pending', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// ── Customer 1 (syafwan): moderate buyer ──────────────────────────────────────
// Order 1: bought Laravel ebook 45 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 5,  'tanggal' => $d45, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Panduan Laravel-nya detail banget, source code-nya jalan semua.'];

// Order 2: bought Steam Wallet 20 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 9,  'tanggal' => $d20, 'status' => 'success', 'order_ref' => $ref, 'rating' => 4, 'ulasan' => 'Steam Wallet berhasil di-redeem, proses cepat.'];

// Order 3: bought Canva Pro + Adobe CC 6 days ago (multi-item, success, partially reviewed)
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 13, 'tanggal' => $d6, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Canva Pro langsung bisa dipakai, semua fitur premium terbuka.'];
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 14, 'tanggal' => $d6, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// Order 4: bought Dashboard Admin template 3 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 16, 'tanggal' => $d3, 'status' => 'success', 'order_ref' => $ref, 'rating' => 4, 'ulasan' => 'Template dashboard-nya lengkap, chart dan tabelnya bagus.'];

// Order 5: tried to buy Valorant VP yesterday but cancelled payment
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 8,  'tanggal' => $d1, 'status' => 'cancelled', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// Order 6: bought Font Bundle today (pending)
$ref = nextOrderRef($customerIds[1]);
$transaksi[] = ['user_idx' => 1, 'produk_idx' => 19, 'tanggal' => $today, 'status' => 'pending', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// ── Customer 2 (customer): occasional buyer ───────────────────────────────────
// Order 1: bought Disney+ Hotstar 30 days ago (success, reviewed)
$ref = nextOrderRef($customerIds[2]);
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 2,  'tanggal' => $d30, 'status' => 'success', 'order_ref' => $ref, 'rating' => 4, 'ulasan' => 'Disney+ Hotstar oke, konten lumayan lengkap.'];

// Order 2: bought YouTube Premium + ML Diamonds 15 days ago (multi-item, success, reviewed)
$ref = nextOrderRef($customerIds[2]);
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 3,  'tanggal' => $d15, 'status' => 'success', 'order_ref' => $ref, 'rating' => 3, 'ulasan' => 'YouTube Premium lumayan, tapi kadang logout sendiri.'];
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 10, 'tanggal' => $d15, 'status' => 'success', 'order_ref' => $ref, 'rating' => 5, 'ulasan' => 'Top up ML Diamonds cepat banget, langsung masuk.'];

// Order 3: bought JS ebook 4 days ago (success, not yet reviewed)
$ref = nextOrderRef($customerIds[2]);
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 6,  'tanggal' => $d4, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

// Order 4: bought Toko Online template + Icon Pack today (success - just paid via Midtrans)
$ref = nextOrderRef($customerIds[2]);
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 17, 'tanggal' => $today, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];
$transaksi[] = ['user_idx' => 2, 'produk_idx' => 20, 'tanggal' => $today, 'status' => 'success', 'order_ref' => $ref, 'rating' => 0, 'ulasan' => null];

$insertedTransaksi = 0;
foreach ($transaksi as $t) {
    $userId   = $customerIds[$t['user_idx']]  ?? null;
    $produkId = $produkIds[$t['produk_idx']]   ?? null;

    if ($userId === null || $produkId === null) {
        echo "Warning: skipped transaksi (user_idx={$t['user_idx']}, produk_idx={$t['produk_idx']} out of range)$br";
        continue;
    }

    // Idempotent: skip if this order_ref + produk_id already exists
    $existing = $db->fetchOne(
        "SELECT id FROM transaksi WHERE order_ref = ? AND produk_id = ?",
        [$t['order_ref'], $produkId]
    );
    if ($existing) {
        continue;
    }

    $db->query(
        "INSERT INTO transaksi (user_id, produk_id, tanggal, status, order_ref, rating, ulasan) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$userId, $produkId, $t['tanggal'], $t['status'], $t['order_ref'], $t['rating'], $t['ulasan']]
    );
    $insertedTransaksi++;
}
echo "Transaksi: {$bold((string)$insertedTransaksi)} rows inserted.$br";

// ─── Keranjang ─────────────────────────────────────────────────────────────────
// Only add products that the customer has NOT already purchased (status='success')
// This matches real flow: cart is cleared after checkout, so cart items = not yet bought

// Collect purchased produk_ids per customer
$purchasedByCustomer = [];
foreach ($customerIds as $idx => $cid) {
    $rows = $db->fetchAll(
        "SELECT DISTINCT produk_id FROM transaksi WHERE user_id = ? AND status IN ('success', 'pending')",
        [$cid]
    );
    $purchasedByCustomer[$idx] = array_column($rows, 'produk_id');
}

$keranjang = [
    // Customer 0 (rizky): browsing more games, hasn't bought these yet
    ['user_idx' => 0, 'produk_idx' => 8],   // Valorant Points
    ['user_idx' => 0, 'produk_idx' => 13],   // Canva Pro

    // Customer 1 (syafwan): interested in ebooks
    ['user_idx' => 1, 'produk_idx' => 4],    // PHP Ebook
    ['user_idx' => 1, 'produk_idx' => 6],    // JS Ebook

    // Customer 2 (customer): looking at software
    ['user_idx' => 2, 'produk_idx' => 11],   // Windows 11
    ['user_idx' => 2, 'produk_idx' => 1],    // Spotify
];

$insertedKeranjang = 0;
$skippedKeranjang  = 0;
foreach ($keranjang as $k) {
    $userId   = $customerIds[$k['user_idx']]  ?? null;
    $produkId = $produkIds[$k['produk_idx']]   ?? null;

    if ($userId === null || $produkId === null) {
        continue;
    }

    // Skip if already purchased (would be inconsistent)
    if (in_array($produkId, $purchasedByCustomer[$k['user_idx']] ?? [])) {
        $skippedKeranjang++;
        continue;
    }

    // Idempotent: skip if already in cart
    $existing = $db->fetchOne(
        "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?",
        [$userId, $produkId]
    );
    if ($existing) {
        continue;
    }

    $db->query(
        "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)",
        [$userId, $produkId]
    );
    $insertedKeranjang++;
}
echo "Keranjang: {$bold((string)$insertedKeranjang)} rows inserted";
if ($skippedKeranjang > 0) {
    echo " ($skippedKeranjang skipped - already purchased)";
}
echo ".$br";

// ─── Summary ───────────────────────────────────────────────────────────────────
echo "{$br}{$bold('Seeding complete!')}$br";

$stats = $db->fetchOne("SELECT COUNT(*) as c FROM produk");
echo "Total produk: {$stats['c']}$br";

$stats = $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'success'");
echo "Total transaksi success: {$stats['c']}$br";

$stats = $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'pending'");
echo "Total transaksi pending: {$stats['c']}$br";

$stats = $db->fetchOne("SELECT COUNT(*) as c FROM transaksi WHERE status = 'cancelled'");
echo "Total transaksi cancelled: {$stats['c']}$br";

$stats = $db->fetchOne("SELECT SUM(p.harga) as total FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.status = 'success'");
echo "Total pendapatan (success): Rp " . number_format((int)($stats['total'] ?? 0), 0, ',', '.') . "$br";

$stats = $db->fetchOne("SELECT COUNT(*) as c FROM keranjang");
echo "Total keranjang items: {$stats['c']}$br";

if (!$isCli) echo "<br><a href='/ecommerce/'>Back to RJSStore</a>";
