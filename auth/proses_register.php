<?php
session_start();
include '../config/koneksi.php';
include_once '../config/helpers.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Validasi password
if ($password != $confirm) {
    flash('error', 'Password tidak sama!');
    header("Location: register.php");
    exit;
}

// Cek email sudah ada atau belum
$existing = db_query_one($conn, "SELECT id FROM users WHERE email = ?", ["s", $email]);
if ($existing) {
    flash('error', 'Email sudah terdaftar!');
    header("Location: register.php");
    exit;
}

// Simpan ke database (default role = customer)
$hash = password_hash($password, PASSWORD_DEFAULT);

$success = db_execute($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')", ["sss", $name, $email, $hash]);

if ($success) {
    flash('success', 'Registrasi berhasil! Silakan login.');
    header("Location: login.php");
} else {
    flash('error', 'Terjadi kesalahan!');
    header("Location: register.php");
}
exit;
?>
