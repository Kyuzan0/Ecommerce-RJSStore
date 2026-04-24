<?php
session_start();
include '../config/koneksi.php';
include_once '../config/helpers.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$user = db_query_one($conn, "SELECT * FROM users WHERE email = ?", ["s", $email]);

$authenticated = false;

if ($user && password_verify($password, $user['password'])) {
    $authenticated = true;
} elseif ($user && $user['password'] === md5($password)) {
    // Upgrade password to bcrypt
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    db_execute($conn, "UPDATE users SET password = ? WHERE id = ?", ["si", $new_hash, $user['id']]);
    $authenticated = true;
}

if ($authenticated) {
    // Save guest cart before regenerating session
    $guest_cart = $_SESSION['cart'] ?? [];
    
    // Capture redirect targets before session regeneration
    $redirect_after_login = $_SESSION['redirect_after_login'] ?? null;
    $redirect_next = $_POST['redirect_next'] ?? $_GET['next'] ?? null;
    
    session_regenerate_id(true);
    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['cart'] = []; // Clear session cart (now using DB)

    // Merge guest cart into DB cart (only for customers)
    if ($user['role'] === 'customer' && !empty($guest_cart)) {
        $user_id = (int) $user['id'];
        $merged_count = 0;
        foreach ($guest_cart as $cart_item) {
            $produk_id = (int) $cart_item['produk_id'];
            
            // Skip if already in DB cart
            $existing = db_query_one($conn, "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?", ["ii", $user_id, $produk_id]);
            if ($existing) continue;
            
            // Skip if already purchased
            $purchased = db_query_one($conn, "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')", ["ii", $user_id, $produk_id]);
            if ($purchased) continue;
            
            // Verify product exists
            $produk = db_query_one($conn, "SELECT id FROM produk WHERE id = ?", ["i", $produk_id]);
            if (!$produk) continue;
            
            db_execute($conn, "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)", ["ii", $user_id, $produk_id]);
            $merged_count++;
        }
        
        if ($merged_count > 0) {
            flash('success', $merged_count . ' produk dari keranjang tamu berhasil ditambahkan ke akun kamu.');
        }
    }

    // Determine redirect destination
    // Priority: redirect_next (from login form hidden field) > redirect_after_login (from session) > default
    if ($redirect_next === 'checkout') {
        header("Location: ../customer/checkout.php");
    } elseif ($redirect_after_login) {
        header("Location: ../" . $redirect_after_login);
    } elseif ($user['role'] == 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../customer/dashboard.php");
    }
    exit;
} else {
    // Preserve the redirect_next parameter on failed login
    $next = $_POST['redirect_next'] ?? '';
    $next_param = $next ? '?next=' . urlencode($next) : '';
    flash('error', 'Email atau password salah!');
    header("Location: login.php" . $next_param);
    exit;
}
?>
