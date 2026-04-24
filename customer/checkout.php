<?php
session_start();
include '../config/koneksi.php';
include_once '../config/helpers.php';
require_role('customer');

$user_id = (int) $_SESSION['id'];

// Get cart items
$cart_items = db_query($conn, 
    "SELECT k.produk_id, p.nama_produk, p.harga, p.deskripsi 
     FROM keranjang k 
     JOIN produk p ON k.produk_id = p.id 
     WHERE k.user_id = ? 
     ORDER BY k.created_at DESC", 
    ["i", $user_id]
);

// Redirect if cart is empty
if (empty($cart_items)) {
    flash('warning', 'Keranjang kosong. Tambahkan produk terlebih dahulu.');
    header("Location: keranjang.php");
    exit;
}

$total_harga = 0;
foreach ($cart_items as $item) {
    $total_harga += $item['harga'];
}

$snapToken = "";
$order_ref = "";

// Process checkout
if (isset($_POST['proses_checkout']) && csrf_validate()) {
    $tanggal = date('Y-m-d');
    $order_ref = 'ORD-' . $user_id . '-' . time();
    
    // Create transaksi for each cart item
    $all_success = true;
    foreach ($cart_items as $item) {
        $result = db_execute($conn, 
            "INSERT INTO transaksi (user_id, produk_id, tanggal, status, order_ref) VALUES (?, ?, ?, 'pending', ?)", 
            ["iiss", $user_id, $item['produk_id'], $tanggal, $order_ref]
        );
        if (!$result) {
            $all_success = false;
            break;
        }
    }
    
    if (!$all_success) {
        // Rollback: delete any transaksi created with this order_ref
        db_execute($conn, "DELETE FROM transaksi WHERE order_ref = ?", ["s", $order_ref]);
        flash('error', 'Gagal membuat pesanan. Silakan coba lagi.');
        header("Location: checkout.php");
        exit;
    }
    
    // Call Midtrans Snap API
    $serverKey = env('MIDTRANS_SERVER_KEY');
    
    // Build item details for Midtrans
    $midtrans_items = [];
    foreach ($cart_items as $item) {
        $midtrans_items[] = [
            'id' => (string) $item['produk_id'],
            'price' => $item['harga'],
            'quantity' => 1,
            'name' => substr($item['nama_produk'], 0, 50)
        ];
    }
    
    $payload = [
        'transaction_details' => [
            'order_id' => $order_ref,
            'gross_amount' => $total_harga
        ],
        'item_details' => $midtrans_items,
        'customer_details' => [
            'first_name' => $_SESSION['name'],
            'email' => 'customer@example.com'
        ]
    ];
    
    $midtransBaseUrl = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true' 
        ? 'https://app.midtrans.com' 
        : 'https://app.sandbox.midtrans.com';
    $ch = curl_init();
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
    
    if (isset($responseData->token)) {
        $snapToken = $responseData->token;
        
        // Clear cart on successful token generation
        db_execute($conn, "DELETE FROM keranjang WHERE user_id = ?", ["i", $user_id]);
    } else {
        // Rollback transaksi if Midtrans fails
        db_execute($conn, "DELETE FROM transaksi WHERE order_ref = ?", ["s", $order_ref]);
        flash('error', 'Gagal menyambung ke payment gateway. Silakan coba lagi.');
        header("Location: checkout.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RJSStore</title>
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

    <div class="w-full max-w-lg">
        <div class="text-center mb-6">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#42B549">
                    <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                </div>
                <span class="text-xl font-bold text-gray-800">RJS<span style="color:#42B549">Store</span></span>
            </div>
            <p class="text-sm text-gray-500">Checkout Pesanan</p>
        </div>

        <?php if ($snapToken != ""): ?>
        <!-- Payment triggered - show loading state -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background:#E8F5E9">
                <svg class="w-8 h-8 animate-pulse" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h3 class="font-bold text-gray-800 mb-2">Memproses Pembayaran...</h3>
            <p class="text-sm text-gray-500 mb-4">Popup pembayaran akan muncul. Jangan tutup halaman ini.</p>
            <button id="pay-button" class="w-full text-white py-3 rounded-xl font-semibold text-sm transition hover:opacity-90" style="background:#42B549">
                Buka Pembayaran
            </button>
        </div>

        <?php else: ?>
        <!-- Checkout review -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                        <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ringkasan Pesanan</p>
                        <p class="font-bold text-gray-800"><?= count($cart_items) ?> Produk Digital</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                                <svg class="w-5 h-5" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= e($item['nama_produk']) ?></p>
                                <p class="text-xs text-gray-400 line-clamp-1"><?= e($item['deskripsi']) ?></p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 flex-shrink-0 ml-3"><?= rupiah($item['harga']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-700">Total Pembayaran</span>
                    <span class="font-bold text-xl" style="color:#42B549"><?= rupiah($total_harga) ?></span>
                </div>
            </div>

            <div class="p-6">
                <form method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" name="proses_checkout" value="1"
                        class="w-full text-white py-3.5 rounded-xl font-bold text-base transition hover:opacity-90 cursor-pointer" style="background:#42B549">
                        Bayar Sekarang - <?= rupiah($total_harga) ?>
                    </button>
                </form>
                <a href="keranjang.php" class="block text-center text-gray-400 hover:text-gray-600 text-sm mt-3">
                    ← Kembali ke Keranjang
                </a>
            </div>
        </div>
        <?php endif; ?>

        <p class="text-center text-xs text-gray-400 mt-4">Pembayaran aman menggunakan enkripsi SSL</p>
    </div>

<?php if ($snapToken != ""): ?>
<script type="text/javascript">
    function triggerSnap() {
        window.snap.pay('<?= e($snapToken) ?>', {
            onSuccess: function(result){ window.location.href = 'pembelian.php'; },
            onPending: function(result){ window.location.href = 'pembelian.php'; },
            onError: function(result){ window.location.href = 'pembelian.php?msg=error'; },
            onClose: function(){ window.location.href = 'pembelian.php'; }
        });
    }
    window.onload = triggerSnap;
    document.getElementById('pay-button').onclick = triggerSnap;
</script>
<?php endif; ?>

</body>
</html>
