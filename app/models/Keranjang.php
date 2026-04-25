<?php

class Keranjang extends BaseModel
{
    protected string $table = 'keranjang';

    /**
     * Get cart items for a user (with product details).
     */
    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT k.produk_id, p.nama_produk, p.harga, p.deskripsi, p.tipe_produk
             FROM keranjang k
             JOIN produk p ON k.produk_id = p.id
             WHERE k.user_id = ?
             ORDER BY k.created_at DESC",
            [$userId]
        );
    }

    /**
     * Add item to cart.
     */
    public function addItem(int $userId, int $produkId): bool
    {
        return $this->db->execute(
            "INSERT INTO keranjang (user_id, produk_id) VALUES (?, ?)",
            [$userId, $produkId]
        );
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $userId, int $produkId): bool
    {
        return $this->db->execute(
            "DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?",
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
     * Check if product is in user's cart.
     */
    public function isInCart(int $userId, int $produkId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?",
            [$userId, $produkId]
        );
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
     */
    public function mergeGuestCart(int $userId, array $guestCart): int
    {
        $mergedCount = 0;
        foreach ($guestCart as $cartItem) {
            $produkId = (int) $cartItem['produk_id'];

            // Skip if already in DB cart
            if ($this->isInCart($userId, $produkId)) continue;

            // Skip if already purchased
            $purchased = $this->db->fetchOne(
                "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')",
                [$userId, $produkId]
            );
            if ($purchased) continue;

            // Verify product exists
            $produk = $this->db->fetchOne("SELECT id FROM produk WHERE id = ?", [$produkId]);
            if (!$produk) continue;

            $this->addItem($userId, $produkId);
            $mergedCount++;
        }
        return $mergedCount;
    }
}
