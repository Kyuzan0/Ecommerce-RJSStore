<?php
session_start();
include '../config/koneksi.php';
include_once '../config/helpers.php';
require_role('customer');

$user_id = (int) $_SESSION['id'];

// Support both single transaksi ID and order_ref
$order_ref = isset($_GET['ref']) ? $_GET['ref'] : '';
$transaksi_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$order_ref && !$transaksi_id) {
    header("Location: pembelian.php");
    exit;
}

$items = [];
$total_harga = 0;

if ($order_ref) {
    // Cart checkout: get all items with this order_ref
    $items = db_query($conn, 
        "SELECT t.id, t.order_ref, p.nama_produk, p.harga 
         FROM transaksi t JOIN produk p ON t.produk_id = p.id 
         WHERE t.order_ref = ? AND t.user_id = ? AND t.status = 'pending'", 
        ["si", $order_ref, $user_id]
    );
    if (empty($items)) {
        flash('warning', 'Pesanan tidak ditemukan atau sudah dibayar.');
        header("Location: pembelian.php");
        exit;
    }
    foreach ($items as $item) {
        $total_harga += $item['harga'];
    }
    $midtrans_order_id = $order_ref;
} else {
    // Legacy single-item
    $data = db_query_one($conn, 
        "SELECT t.*, p.nama_produk, p.harga FROM transaksi t JOIN produk p ON t.produk_id = p.id 
         WHERE t.id = ? AND t.user_id = ? AND t.status = 'pending'", 
        ["ii", $transaksi_id, $user_id]
    );
    if (!$data) {
        flash('warning', 'Transaksi tidak ditemukan atau sudah dibayar.');
        header("Location: pembelian.php");
        exit;
    }
    $items = [['id' => $data['id'], 'nama_produk' => $data['nama_produk'], 'harga' => $data['harga']]];
    $total_harga = $data['harga'];
    $midtrans_order_id = $data['id'];
}

// Build Midtrans payload
$serverKey = env('MIDTRANS_SERVER_KEY');

$midtrans_items = [];
foreach ($items as $item) {
    $midtrans_items[] = [
        'id' => (string) $item['id'],
        'price' => $item['harga'],
        'quantity' => 1,
        'name' => substr($item['nama_produk'], 0, 50)
    ];
}

$payload = [
    'transaction_details' => [
        'order_id' => $midtrans_order_id,
        'gross_amount' => $total_harga
    ],
    'item_details' => $midtrans_items,
    'customer_details' => [
        'first_name' => $_SESSION['name'],
        'email' => 'customer@example.com'
    ]
];

$ch = curl_init();
$midtransBaseUrl = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true' 
    ? 'https://app.midtrans.com' 
    : 'https://app.sandbox.midtrans.com';
curl_setopt($ch, CURLOPT_URL, $midtransBaseUrl . '/snap/v1/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode($serverKey . ':')
]);
$response = curl_exec($ch);
curl_close($ch);
$responseData = json_decode($response);
$snapToken = "";
if (isset($responseData->token)) {
    $snapToken = $responseData->token;
} else {
    flash('error', 'Gagal menyambung ke payment gateway.');
    header("Location: pembelian.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - RJSStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php 
    $snapUrl = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true' 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    ?>
    <script src="<?= $snapUrl ?>" data-client-key="<?= e(env('MIDTRANS_CLIENT_KEY')) ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                </div>
                <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                        <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Selesaikan Pembayaran</p>
                        <p class="font-bold text-gray-800">Tagihan Pesanan</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                    <?php foreach ($items as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500"><?= e($item['nama_produk']); ?></span>
                        <span class="font-semibold text-gray-800"><?= rupiah($item['harga']); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="border-t border-gray-200 pt-3 flex justify-between">
                        <span class="font-semibold text-gray-700">Total Pembayaran</span>
                        <span class="font-bold text-lg" style="color:#42B549"><?= rupiah($total_harga); ?></span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <button id="pay-button"
                    class="w-full text-white py-3.5 rounded-xl font-bold text-base transition hover:opacity-90 mb-3" style="background:#42B549">
                    Bayar Sekarang
                </button>
                <a href="pembelian.php" class="block text-center text-gray-400 hover:text-gray-600 text-sm">
                    ← Kembali ke Pesanan
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">Pembayaran aman menggunakan enkripsi SSL</p>
    </div>

    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');
        function triggerSnap() {
            window.snap.pay('<?= $snapToken; ?>', {
                onSuccess: function(result){ window.location.href = 'pembelian.php'; },
                onPending: function(result){ window.location.href = 'pembelian.php'; },
                onError: function(result){ window.location.href = 'pembelian.php?msg=error'; },
                onClose: function(){}
            });
        }
        window.onload = triggerSnap;
        payButton.onclick = triggerSnap;
    </script>
</body>
</html>
