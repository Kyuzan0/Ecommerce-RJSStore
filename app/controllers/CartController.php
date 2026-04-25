<?php

class CartController extends BaseController
{
    private Keranjang $keranjangModel;

    public function __construct()
    {
        parent::__construct();
        $this->keranjangModel = new Keranjang();

        // Initialize session cart for guests
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    private function isLoggedIn(): bool
    {
        return $this->auth->isCustomer();
    }

    private function userId(): int
    {
        return $this->auth->id() ?? 0;
    }

    /**
     * Parse request body (supports both JSON and form data).
     */
    private function getInput(string $key, $default = null)
    {
        // Check form data first
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // Try JSON body
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $json = json_decode(file_get_contents('php://input'), true);
            return $json[$key] ?? $default;
        }

        return $default;
    }

    /**
     * API: Add to cart (POST).
     */
    public function apiAdd(): void
    {
        $produkId = (int) ($this->getInput('produk_id', 0));
        if ($produkId <= 0) {
            $this->json(['success' => false, 'message' => 'Produk tidak valid.']);
            return;
        }

        // Verify product exists
        $produk = $this->db->fetchOne(
            "SELECT id, nama_produk, harga, deskripsi, tipe_produk FROM produk WHERE id = ?",
            [$produkId]
        );
        if (!$produk) {
            $this->json(['success' => false, 'message' => 'Produk tidak ditemukan.']);
            return;
        }

        if ($this->isLoggedIn()) {
            $userId = $this->userId();

            // Check if already purchased
            $transaksi = new Transaksi();
            if ($transaksi->isPurchased($userId, $produkId)) {
                $this->json(['success' => false, 'message' => 'Kamu sudah pernah membeli produk ini.']);
                return;
            }

            // Check if already in cart
            if ($this->keranjangModel->isInCart($userId, $produkId)) {
                $this->json(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
                return;
            }

            $this->keranjangModel->addItem($userId, $produkId);
        } else {
            // Guest: add to session cart
            foreach ($_SESSION['cart'] as $item) {
                if ((int) $item['produk_id'] === $produkId) {
                    $this->json(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
                    return;
                }
            }
            $_SESSION['cart'][] = ['produk_id' => $produkId];
        }

        $cartData = $this->getCartData();
        $this->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang!',
            'cart'    => $cartData,
        ]);
    }

    /**
     * API: Remove from cart (POST).
     */
    public function apiRemove(): void
    {
        $produkId = (int) ($this->getInput('produk_id', 0));
        if ($produkId <= 0) {
            $this->json(['success' => false, 'message' => 'Produk tidak valid.']);
            return;
        }

        if ($this->isLoggedIn()) {
            $this->keranjangModel->removeItem($this->userId(), $produkId);
        } else {
            $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) use ($produkId) {
                return (int) $item['produk_id'] !== $produkId;
            }));
        }

        $cartData = $this->getCartData();
        $this->json([
            'success' => true,
            'message' => 'Produk dihapus dari keranjang.',
            'cart'    => $cartData,
        ]);
    }

    /**
     * API: Clear cart (POST).
     */
    public function apiClear(): void
    {
        if ($this->isLoggedIn()) {
            $this->keranjangModel->clearByUser($this->userId());
        } else {
            $_SESSION['cart'] = [];
        }

        $this->json([
            'success' => true,
            'message' => 'Keranjang dikosongkan.',
            'cart'    => ['items' => [], 'total' => 0, 'count' => 0, 'total_formatted' => 'Rp 0'],
        ]);
    }

    /**
     * API: Get cart data (GET).
     */
    public function apiGet(): void
    {
        $cartData = $this->getCartData();
        $this->json([
            'success' => true,
            'cart'    => $cartData,
        ]);
    }

    /**
     * Build cart data array.
     */
    private function getCartData(): array
    {
        $items = [];
        $total = 0;

        if ($this->isLoggedIn()) {
            $rows = $this->keranjangModel->getByUser($this->userId());
            foreach ($rows as $row) {
                $items[] = [
                    'produk_id'       => (int) $row['produk_id'],
                    'nama_produk'     => $row['nama_produk'],
                    'harga'           => (int) $row['harga'],
                    'harga_formatted' => rupiah($row['harga']),
                    'deskripsi'       => mb_substr($row['deskripsi'], 0, 60),
                    'tipe_produk'     => $row['tipe_produk'] ?? 'Lainnya',
                ];
                $total += (int) $row['harga'];
            }
        } else {
            foreach ($_SESSION['cart'] as $cartItem) {
                $produk = $this->db->fetchOne(
                    "SELECT id, nama_produk, harga, deskripsi, tipe_produk FROM produk WHERE id = ?",
                    [(int) $cartItem['produk_id']]
                );
                if ($produk) {
                    $items[] = [
                        'produk_id'       => (int) $produk['id'],
                        'nama_produk'     => $produk['nama_produk'],
                        'harga'           => (int) $produk['harga'],
                        'harga_formatted' => rupiah($produk['harga']),
                        'deskripsi'       => mb_substr($produk['deskripsi'], 0, 60),
                        'tipe_produk'     => $produk['tipe_produk'] ?? 'Lainnya',
                    ];
                    $total += (int) $produk['harga'];
                }
            }
        }

        return [
            'items'           => $items,
            'total'           => $total,
            'total_formatted' => rupiah($total),
            'count'           => count($items),
        ];
    }
}
