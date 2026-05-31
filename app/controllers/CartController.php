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
        $varianId = (int) ($this->getInput('varian_id', 0));
        $varianId = $varianId > 0 ? $varianId : null;

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

        // Account products require a variant selection
        if ($produk['tipe_produk'] === 'Akun') {
            $hasVariants = $this->db->fetchOne(
                "SELECT COUNT(*) AS c FROM produk_varian WHERE produk_id = ?",
                [$produkId]
            );
            if ($hasVariants && (int) $hasVariants['c'] > 0) {
                if ($varianId === null) {
                    $this->json(['success' => false, 'message' => 'Silakan pilih varian terlebih dahulu.']);
                    return;
                }
                // Verify variant belongs to product
                $variant = $this->db->fetchOne(
                    "SELECT id FROM produk_varian WHERE id = ? AND produk_id = ?",
                    [$varianId, $produkId]
                );
                if (!$variant) {
                    $this->json(['success' => false, 'message' => 'Varian tidak valid.']);
                    return;
                }
            } else {
                $varianId = null;
            }
        } else {
            // Non-account products never carry a variant
            $varianId = null;
        }

        if ($this->isLoggedIn()) {
            $userId = $this->userId();

            // Check if already purchased
            $transaksi = new Transaksi();
            if ($transaksi->isPurchased($userId, $produkId)) {
                $this->json(['success' => false, 'message' => 'Kamu sudah pernah membeli produk ini.']);
                return;
            }

            // Check if already in cart (same variant)
            if ($this->keranjangModel->isInCart($userId, $produkId, $varianId)) {
                $this->json(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
                return;
            }

            $this->keranjangModel->addItem($userId, $produkId, $varianId);
        } else {
            // Guest: add to session cart
            foreach ($_SESSION['cart'] as $item) {
                $itemVarian = isset($item['varian_id']) ? (int) $item['varian_id'] : null;
                if ((int) $item['produk_id'] === $produkId && $itemVarian === $varianId) {
                    $this->json(['success' => false, 'message' => 'Produk sudah ada di keranjang.']);
                    return;
                }
            }
            $_SESSION['cart'][] = ['produk_id' => $produkId, 'varian_id' => $varianId];
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
        $varianId = (int) ($this->getInput('varian_id', 0));
        $varianId = $varianId > 0 ? $varianId : null;

        if ($produkId <= 0) {
            $this->json(['success' => false, 'message' => 'Produk tidak valid.']);
            return;
        }

        if ($this->isLoggedIn()) {
            $this->keranjangModel->removeItem($this->userId(), $produkId, $varianId);
        } else {
            $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) use ($produkId, $varianId) {
                $itemVarian = isset($item['varian_id']) ? (int) $item['varian_id'] : null;
                return !((int) $item['produk_id'] === $produkId && $itemVarian === $varianId);
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
                $varianLabel = '';
                if (!empty($row['durasi'])) {
                    $varianLabel = $row['durasi'];
                    if (!empty($row['paket'])) {
                        $varianLabel .= ' - ' . $row['paket'];
                    }
                }
                $items[] = [
                    'produk_id'       => (int) $row['produk_id'],
                    'varian_id'       => isset($row['varian_id']) ? (int) $row['varian_id'] : null,
                    'varian_label'    => $varianLabel,
                    'nama_produk'     => $row['nama_produk'],
                    'harga'           => (int) $row['harga'],
                    'harga_formatted' => rupiah($row['harga']),
                    'deskripsi'       => mb_substr($row['deskripsi'] ?? '', 0, 60),
                    'tipe_produk'     => $row['tipe_produk'] ?? 'Lainnya',
                ];
                $total += (int) $row['harga'];
            }
        } else {
            foreach ($_SESSION['cart'] as $cartItem) {
                $produkId = (int) $cartItem['produk_id'];
                $varianId = isset($cartItem['varian_id']) && $cartItem['varian_id'] !== null
                    ? (int) $cartItem['varian_id']
                    : null;

                $produk = $this->db->fetchOne(
                    "SELECT id, nama_produk, harga, deskripsi, tipe_produk FROM produk WHERE id = ?",
                    [$produkId]
                );
                if (!$produk) {
                    continue;
                }

                $harga = (int) $produk['harga'];
                $varianLabel = '';
                if ($varianId !== null) {
                    $variant = $this->db->fetchOne(
                        "SELECT durasi, paket, harga FROM produk_varian WHERE id = ? AND produk_id = ?",
                        [$varianId, $produkId]
                    );
                    if ($variant) {
                        $harga = (int) $variant['harga'];
                        $varianLabel = $variant['durasi'];
                        if (!empty($variant['paket'])) {
                            $varianLabel .= ' - ' . $variant['paket'];
                        }
                    }
                }

                $items[] = [
                    'produk_id'       => (int) $produk['id'],
                    'varian_id'       => $varianId,
                    'varian_label'    => $varianLabel,
                    'nama_produk'     => $produk['nama_produk'],
                    'harga'           => $harga,
                    'harga_formatted' => rupiah($harga),
                    'deskripsi'       => mb_substr($produk['deskripsi'] ?? '', 0, 60),
                    'tipe_produk'     => $produk['tipe_produk'] ?? 'Lainnya',
                ];
                $total += $harga;
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
