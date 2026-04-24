<?php
/**
 * Migration: Add tipe_produk column to produk table
 * Run once via browser: http://localhost/ecommerce/database/migrate_tipe_produk.php
 */
include __DIR__ . '/../config/koneksi.php';

$sql = "ALTER TABLE `produk` ADD COLUMN `tipe_produk` VARCHAR(50) NOT NULL DEFAULT 'Lainnya' AFTER `deskripsi`";

try {
    mysqli_query($conn, $sql);
    echo "Column 'tipe_produk' added to 'produk' table successfully.<br>";
} catch (mysqli_sql_exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'tipe_produk' already exists. Skipping.<br>";
    } else {
        throw $e;
    }
}
