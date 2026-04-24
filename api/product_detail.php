<?php
/**
 * Product Detail API Endpoint
 * 
 * Returns product info + reviews as JSON for the product detail modal.
 * 
 * GET ?id={product_id}
 */
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/koneksi.php';
include_once __DIR__ . '/../config/helpers.php';

$produk_id = (int) ($_GET['id'] ?? 0);
if ($produk_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak valid.']);
    exit;
}

// Fetch product
$produk = db_query_one($conn, "SELECT id, nama_produk, deskripsi, harga, tipe_produk FROM produk WHERE id = ?", ["i", $produk_id]);
if (!$produk) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
    exit;
}

// Fetch rating summary
$rating_summary = db_query_one($conn, "SELECT AVG(rating) as avg_rate, COUNT(id) as total FROM transaksi WHERE produk_id = ? AND rating > 0", ["i", $produk_id]);
$avg_rating = $rating_summary['avg_rate'] ? round($rating_summary['avg_rate'], 1) : 0;
$total_reviews = (int) $rating_summary['total'];

// Fetch reviews (all reviews for this product, newest first)
$reviews = db_query($conn, 
    "SELECT t.rating, t.ulasan, t.tanggal, u.name as nama_user 
     FROM transaksi t 
     JOIN users u ON t.user_id = u.id 
     WHERE t.produk_id = ? AND t.rating > 0 AND t.ulasan != '' 
     ORDER BY t.tanggal DESC 
     LIMIT 20", 
    ["i", $produk_id]
);

$tipe_cfg = tipe_produk_config($produk['tipe_produk'] ?? 'Lainnya');

$review_list = [];
foreach ($reviews as $r) {
    $review_list[] = [
        'nama_user' => $r['nama_user'],
        'initial' => strtoupper(mb_substr($r['nama_user'], 0, 1)),
        'rating' => (int) $r['rating'],
        'ulasan' => $r['ulasan'],
        'tanggal' => format_tanggal($r['tanggal']),
    ];
}

echo json_encode([
    'success' => true,
    'product' => [
        'id' => (int) $produk['id'],
        'nama_produk' => $produk['nama_produk'],
        'deskripsi' => $produk['deskripsi'],
        'harga' => (int) $produk['harga'],
        'harga_formatted' => rupiah($produk['harga']),
        'tipe_produk' => $produk['tipe_produk'] ?? 'Lainnya',
        'tipe_label' => $tipe_cfg['label'],
        'tipe_color' => $tipe_cfg['color'],
        'tipe_bg' => $tipe_cfg['bg'],
        'avg_rating' => $avg_rating,
        'total_reviews' => $total_reviews,
    ],
    'reviews' => $review_list,
]);
