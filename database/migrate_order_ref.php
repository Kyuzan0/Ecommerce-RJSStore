<?php
/**
 * Migration: Add order_ref column to transaksi table
 * Run once via browser: http://localhost/ecommerce/database/migrate_order_ref.php
 */
include __DIR__ . '/../config/koneksi.php';

$sql = "ALTER TABLE `transaksi` ADD COLUMN `order_ref` varchar(50) DEFAULT NULL AFTER `status`";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "Column 'order_ref' added to transaksi table successfully.<br>";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column') !== false) {
        echo "Column 'order_ref' already exists. Skipping.<br>";
    } else {
        echo "Error: " . $error . "<br>";
    }
}

// Add index on order_ref for webhook lookups
$sql2 = "ALTER TABLE `transaksi` ADD INDEX `idx_order_ref` (`order_ref`)";
$result2 = mysqli_query($conn, $sql2);
if ($result2) {
    echo "Index on 'order_ref' added successfully.<br>";
} else {
    $error2 = mysqli_error($conn);
    if (strpos($error2, 'Duplicate key') !== false) {
        echo "Index already exists. Skipping.<br>";
    } else {
        echo "Index error: " . $error2 . "<br>";
    }
}

// Backfill existing transaksi: set order_ref = id for old single-item orders
$sql3 = "UPDATE `transaksi` SET `order_ref` = CAST(`id` AS CHAR) WHERE `order_ref` IS NULL";
mysqli_query($conn, $sql3);
echo "Backfilled order_ref for existing transactions.<br>";
echo "<br>Done! You can now use the checkout system.";
