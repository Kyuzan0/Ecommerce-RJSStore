<?php
define('BASE_PATH', __DIR__);
require 'helpers/functions.php';
require 'app/core/Database.php';

$db = Database::getInstance();
$count = $db->fetchOne('SELECT COUNT(*) as t FROM transaksi t JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id')['t'];
echo "Total Join: $count\n";

$paging = paginate($db, 'SELECT COUNT(*) as t FROM transaksi t JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id', [], 15);
print_r($paging);
