<?php
session_start();
include '../config/koneksi.php';
include_once '../config/helpers.php';
require_role('customer');

if (!isset($_GET['id'])) {
    header("Location: pembelian.php");
    exit;
}

$id = (int) $_GET['id'];
$user_id = (int) $_SESSION['id'];

// Proses simpan rating
if (isset($_POST['submit']) && csrf_validate()) {
    $rating = (int) $_POST['rating'];
    $ulasan = $_POST['ulasan'] ?? '';
    
    db_execute($conn, "UPDATE transaksi SET rating = ?, ulasan = ? WHERE id = ? AND user_id = ?", ["isii", $rating, $ulasan, $id, $user_id]);
    flash('success', 'Terima kasih atas ulasan kamu!');
    header("Location: pembelian.php");
    exit;
}

// Ambil data transaksi
$data = db_query_one($conn, "SELECT t.*, p.nama_produk FROM transaksi t JOIN produk p ON t.produk_id = p.id WHERE t.id = ? AND t.user_id = ? AND t.status = 'success'", ["ii", $id, $user_id]);

// Jika transaksi tidak ada atau sudah dirating, kembalikan ke pembelian
if (!$data || $data['rating'] > 0) {
    header("Location: pembelian.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Penilaian - RJSStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 8px; }
        .rating input { display: none; }
        .rating label { cursor: pointer; color: #E5E7EB; width: 40px; height: 40px; transition: color 0.2s; }
        .rating label svg { width: 100%; height: 100%; }
        .rating input:checked ~ label { color: #F59E0B; }
        .rating label:hover, .rating label:hover ~ label { color: #FCD34D; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 text-center">
            <h2 class="text-xl font-bold text-gray-800">Beri Penilaian</h2>
            <p class="text-sm text-gray-500 mt-1">Bagaimana pengalaman kamu membeli <strong><?= e($data['nama_produk']); ?></strong>?</p>
        </div>
        
        <form method="POST" class="p-6">
            <?= csrf_field() ?>
            <div class="mb-6">
                <div class="rating">
                    <input type="radio" name="rating" id="star5" value="5" required><label for="star5"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></label>
                    <input type="radio" name="rating" id="star4" value="4"><label for="star4"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></label>
                    <input type="radio" name="rating" id="star3" value="3"><label for="star3"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></label>
                    <input type="radio" name="rating" id="star2" value="2"><label for="star2"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></label>
                    <input type="radio" name="rating" id="star1" value="1"><label for="star1"><svg fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg></label>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ulasan Produk (Opsional)</label>
                <textarea name="ulasan" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-200 transition" placeholder="Tulis pendapatmu tentang produk ini..."></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" name="submit" class="flex-1 text-white py-3 rounded-xl font-semibold transition hover:opacity-90" style="background:#42B549">Kirim Penilaian</button>
                <a href="pembelian.php" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition text-center">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>
