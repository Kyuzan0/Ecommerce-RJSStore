<?php
/**
 * AJAX Cart Action Endpoint
 * 
 * Handles cart operations for both guests (session) and logged-in users (DB).
 * Returns JSON responses for AJAX consumption.
 * 
 * Actions: add, remove, get, clear
 * 
 * Guest cart: $_SESSION['cart'] = [ ['produk_id' => int, 'qty' => int], ... ]
 * Logged-in cart: keranjang table (qty always 1 for digital products)
 */
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../config/koneksi.php';
include_once __DIR__ . '/../config/helpers.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$is_logged_in = isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
$user_id = $is_logged_in ? (int) $_SESSION['id'] : 0;

// Initialize session cart for guests
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        handle_add($conn, $is_logged_in, $user_id);
        break;
    case 'remove':
        handle_remove($conn, $is_logged_in, $user_id);
        break;
    case 'clear':
        handle_clear($conn, $is_logged_in, $user_id);
        break;
    case 'get':
        handle_get($conn, $is_logged_in, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// ============================================================
// ADD TO CART
// ============================================================
function handle_add(mysqli $conn, bool $is_logged_in, int $user_id): void {
    $produk_id = (int) ($_POST['produk_id'] ?? 0);
    if ($produk_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak valid.']);
        return;
    }

    // Verify product exists
    $produk = db_query_one($conn, "SELECT id, nama_produk, harga, deskripsi, tipe_produk FROM produk WHERE id = ?", ["i", $produk_id]);
    if (!$produk) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan.']);
        return;
    }

    if ($is_logged_in) {
        // Check if already purchased
        $purchased = db_query_one($conn, "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')", ["ii", $user_id, $produk_id]);
        if ($purchased) {
            echo json_encode(['success' => false, 'message' => 'Kamu sudah pernah membeli produk ini.']);
            return;
        }

        // Check if already in cart
        $existing = db_query_one($conn, "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?", ["ii", $user_id, $produk_id]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
            return;
        }

        // Add to DB cart
        db_execute($conn, "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)", ["ii", $user_id, $produk_id]);
    } else {
        // Guest: add to session cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['produk_id'] === $produk_id) {
                $found = true;
                break;
            }
        }
        unset($item);

        if ($found) {
            echo json_encode(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
            return;
        }

        $_SESSION['cart'][] = ['produk_id' => $produk_id];
    }

    // Return updated cart data
    $cart_data = get_cart_data($conn, $is_logged_in, $user_id);
    echo json_encode([
        'success' => true,
        'message' => 'Produk ditambahkan ke keranjang!',
        'cart' => $cart_data
    ]);
}

// ============================================================
// REMOVE FROM CART
// ============================================================
function handle_remove(mysqli $conn, bool $is_logged_in, int $user_id): void {
    $produk_id = (int) ($_POST['produk_id'] ?? 0);
    if ($produk_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak valid.']);
        return;
    }

    if ($is_logged_in) {
        db_execute($conn, "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?", ["ii", $user_id, $produk_id]);
    } else {
        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($produk_id) {
            return $item['produk_id'] !== $produk_id;
        }));
    }

    $cart_data = get_cart_data($conn, $is_logged_in, $user_id);
    echo json_encode([
        'success' => true,
        'message' => 'Produk dihapus dari keranjang.',
        'cart' => $cart_data
    ]);
}

// ============================================================
// CLEAR CART
// ============================================================
function handle_clear(mysqli $conn, bool $is_logged_in, int $user_id): void {
    if ($is_logged_in) {
        db_execute($conn, "DELETE FROM keranjang WHERE user_id = ?", ["i", $user_id]);
    } else {
        $_SESSION['cart'] = [];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Keranjang dikosongkan.',
        'cart' => ['items' => [], 'total' => 0, 'count' => 0, 'total_formatted' => 'Rp 0']
    ]);
}

// ============================================================
// GET CART DATA
// ============================================================
function handle_get(mysqli $conn, bool $is_logged_in, int $user_id): void {
    $cart_data = get_cart_data($conn, $is_logged_in, $user_id);
    echo json_encode([
        'success' => true,
        'cart' => $cart_data
    ]);
}

// ============================================================
// HELPER: Build cart data array
// ============================================================
function get_cart_data(mysqli $conn, bool $is_logged_in, int $user_id): array {
    $items = [];
    $total = 0;

    if ($is_logged_in) {
        $rows = db_query($conn,
            "SELECT k.produk_id, p.nama_produk, p.harga, p.deskripsi, p.tipe_produk
             FROM keranjang k
             JOIN produk p ON k.produk_id = p.id
             WHERE k.user_id = ?
             ORDER BY k.created_at DESC",
            ["i", $user_id]
        );
        foreach ($rows as $row) {
            $items[] = [
                'produk_id' => (int) $row['produk_id'],
                'nama_produk' => $row['nama_produk'],
                'harga' => (int) $row['harga'],
                'harga_formatted' => rupiah($row['harga']),
                'deskripsi' => mb_substr($row['deskripsi'], 0, 60),
                'tipe_produk' => $row['tipe_produk'] ?? 'Lainnya',
            ];
            $total += (int) $row['harga'];
        }
    } else {
        foreach ($_SESSION['cart'] as $cart_item) {
            $produk = db_query_one($conn, "SELECT id, nama_produk, harga, deskripsi, tipe_produk FROM produk WHERE id = ?", ["i", $cart_item['produk_id']]);
            if ($produk) {
                $items[] = [
                    'produk_id' => (int) $produk['id'],
                    'nama_produk' => $produk['nama_produk'],
                    'harga' => (int) $produk['harga'],
                    'harga_formatted' => rupiah($produk['harga']),
                    'deskripsi' => mb_substr($produk['deskripsi'], 0, 60),
                    'tipe_produk' => $produk['tipe_produk'] ?? 'Lainnya',
                ];
                $total += (int) $produk['harga'];
            }
        }
    }

    return [
        'items' => $items,
        'total' => $total,
        'total_formatted' => rupiah($total),
        'count' => count($items),
    ];
}
