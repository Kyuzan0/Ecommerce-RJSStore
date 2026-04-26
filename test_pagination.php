<?php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/helpers/functions.php';
require BASE_PATH . '/app/core/Database.php';

$db = Database::getInstance();

// Exact same query as AdminTransaksiController
$join = " JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id";
$count_sql = "SELECT COUNT(*) as c FROM transaksi t" . $join;
$row = $db->fetchOne($count_sql, []);
echo "Count row: ";
print_r($row);

$total = $row ? (int) reset($row) : 0;
echo "Total: $total\n";
echo "Total pages (15/page): " . ceil($total / 15) . "\n";

// Check if any transaksi have missing FK references
$orphans = $db->fetchAll("SELECT t.id, t.user_id, t.produk_id FROM transaksi t LEFT JOIN users u ON t.user_id = u.id LEFT JOIN produk p ON t.produk_id = p.id WHERE u.id IS NULL OR p.id IS NULL", []);
echo "Orphan transaksi (missing user/produk): " . count($orphans) . "\n";
if (count($orphans) > 0) print_r($orphans);
