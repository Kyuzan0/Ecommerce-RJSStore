<?php

class Keranjang extends BaseModel
{
    protected string $table = 'keranjang';

    /**
     * Get cart items for a user (with product + variant details).
     * Price and account come from the variant when present, else the product.
     */
    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT k.produk_id,
                    k.varian_id,
                    p.nama_produk,
                    COALESCE(v.harga, p.harga) AS harga,
                    p.deskripsi,
                    p.tipe_produk,
                    v.durasi,
                    v.paket
             FROM keranjang k
             JOIN produk p ON k.produk_id = p.id
             LEFT JOIN produk_varian v ON k.varian_id = v.id
             WHERE k.user_id = ?
             ORDER BY k.created_at DESC",
            [$userId]
        );
    }

    /**
     * Add item to cart (variant-aware).
     */
    public function addItem(int $userId, int $produkId, ?int $varianId = null): bool
    {
        return $this->db->execute(
            "INSERT INTO keranjang (user_id, produk_id, varian_id) VALUES (?, ?, ?)",
            [$userId, $produkId, $varianId]
        );
    }

    /**
     * Remove item from cart. When variant given, removes that specific variant row.
     */
    public function removeItem(int $userId, int $produkId, ?int $varianId = null): bool
    {
        if ($varianId !== null) {
            return $this->db->execute(
                "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ? AND varian_id = ?",
                [$userId, $produkId, $varianId]
            );
        }
        return $this->db->execute(
            "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ? AND varian_id IS NULL",
            [$userId, $produkId]
        );
    }

    /**
     * Clear all items for a user.
     */
    public function clearByUser(int $userId): bool
    {
        return $this->db->execute(
            "DELETE FROM keranjang WHERE user_id = ?",
            [$userId]
        );
    }

    /**
     * Check if product/variant is in user's cart.
     */
    public function isInCart(int $userId, int $produkId, ?int $varianId = null): bool
    {
        if ($varianId !== null) {
            $row = $this->db->fetchOne(
                "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ? AND varian_id = ?",
                [$userId, $produkId, $varianId]
            );
        } else {
            $row = $this->db->fetchOne(
                "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ? AND varian_id IS NULL",
                [$userId, $produkId]
            );
        }
        return $row !== null;
    }

    /**
     * Get cart count for a user.
     */
    public function getCartCount(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM keranjang WHERE user_id = ?",
            [$userId]
        );
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Merge guest session cart into DB cart.
     * Guest cart items may carry an optional 'varian_id'.
     */
    public function mergeGuestCart(int $userId, array $guestCart): int
    {
        $mergedCount = 0;
        foreach ($guestCart as $cartItem) {
            $produkId = (int) $cartItem['produk_id'];
            $varianId = isset($cartItem['varian_id']) && $cartItem['varian_id'] !== null
                ? (int) $cartItem['varian_id']
                : null;

            // Skip if already in DB cart
            if ($this->isInCart($userId, $produkId, $varianId)) continue;

            // Skip if already purchased
            $purchased = $this->db->fetchOne(
                "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')",
                [$userId, $produkId]
            );
            if ($purchased) continue;

            // Verify product exists
            $produk = $this->db->fetchOne("SELECT id FROM produk WHERE id = ?", [$produkId]);
            if (!$produk) continue;

            // If a variant is given, verify it belongs to the product
            if ($varianId !== null) {
                $variant = $this->db->fetchOne(
                    "SELECT id FROM produk_varian WHERE id = ? AND produk_id = ?",
                    [$varianId, $produkId]
                );
                if (!$variant) continue;
            }

            $this->addItem($userId, $produkId, $varianId);
            $mergedCount++;
        }
        return $mergedCount;
    }
}
