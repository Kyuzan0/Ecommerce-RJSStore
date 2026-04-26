<?php
/**
 * One-time script: Clear all dummy data including customer users.
 * Run: php database/clear_dummy.php
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/helpers/functions.php';
require_once BASE_PATH . '/app/core/Database.php';

$db = Database::getInstance();

$db->query("DELETE FROM keranjang");
echo "keranjang: cleared\n";

$db->query("DELETE FROM transaksi");
echo "transaksi: cleared\n";

$db->query("DELETE FROM produk");
echo "produk: cleared\n";

$db->query("DELETE FROM users WHERE role = 'customer'");
echo "users (customer): cleared\n";

// Verify
$r = $db->fetchOne("SELECT COUNT(*) as c FROM keranjang");
echo "\nRemaining rows:\n";
echo "  keranjang: {$r['c']}\n";

$r = $db->fetchOne("SELECT COUNT(*) as c FROM transaksi");
echo "  transaksi: {$r['c']}\n";

$r = $db->fetchOne("SELECT COUNT(*) as c FROM produk");
echo "  produk: {$r['c']}\n";

$r = $db->fetchOne("SELECT COUNT(*) as c FROM users");
echo "  users: {$r['c']}\n";

$rows = $db->fetchAll("SELECT id, name, role FROM users");
echo "\nRemaining users:\n";
foreach ($rows as $u) {
    echo "  - [{$u['role']}] {$u['name']} (id={$u['id']})\n";
}

echo "\nDone!\n";
