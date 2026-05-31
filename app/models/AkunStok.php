<?php

class AkunStok extends BaseModel
{
    protected string $table = 'akun_stok';

    /**
     * Get available stock count for a variant.
     */
    public function countAvailable(int $varianId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM akun_stok WHERE varian_id = ? AND status = 'available'",
            [$varianId]
        );
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Get all stock for a variant (admin view).
     */
    public function getByVarian(int $varianId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM akun_stok WHERE varian_id = ? ORDER BY status ASC, id DESC",
            [$varianId]
        );
    }

    /**
     * Add a single stock item.
     */
    public function addStock(int $varianId, string $email, string $password): int
    {
        return $this->create([
            'varian_id'        => $varianId,
            'account_email'    => $email,
            'account_password' => $password,
            'status'           => 'available',
        ]);
    }

    /**
     * Bulk add stock items.
     * $items: array of ['email' => '...', 'password' => '...']
     */
    public function addBulk(int $varianId, array $items): int
    {
        $count = 0;
        foreach ($items as $item) {
            $email = trim($item['email'] ?? '');
            $pass = trim($item['password'] ?? '');
            if ($email === '' || $pass === '') continue;

            $this->db->execute(
                "INSERT INTO akun_stok (varian_id, account_email, account_password, status) VALUES (?, ?, ?, 'available')",
                [$varianId, $email, $pass]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Assign one available stock to a transaction.
     * Returns the assigned stock row, or null if out of stock.
     */
    public function assignToTransaction(int $varianId, int $transaksiId): ?array
    {
        // Lock and pick one available stock (FIFO — oldest first)
        $stock = $this->db->fetchOne(
            "SELECT id, account_email, account_password FROM akun_stok
             WHERE varian_id = ? AND status = 'available'
             ORDER BY id ASC LIMIT 1 FOR UPDATE",
            [$varianId]
        );

        if (!$stock) {
            return null;
        }

        $this->db->execute(
            "UPDATE akun_stok SET status = 'sold', transaksi_id = ?, sold_at = NOW() WHERE id = ?",
            [$transaksiId, $stock['id']]
        );

        return $stock;
    }

    /**
     * Get the stock assigned to a specific transaction.
     */
    public function getByTransaksi(int $transaksiId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM akun_stok WHERE transaksi_id = ?",
            [$transaksiId]
        );
    }

    /**
     * Release stock back to available (e.g. on refund/cancellation).
     */
    public function releaseByTransaksi(int $transaksiId): bool
    {
        return $this->db->execute(
            "UPDATE akun_stok SET status = 'available', transaksi_id = NULL, sold_at = NULL WHERE transaksi_id = ?",
            [$transaksiId]
        );
    }

    /**
     * Delete a specific stock item (admin, only if available).
     */
    public function deleteIfAvailable(int $id): bool
    {
        return $this->db->execute(
            "DELETE FROM akun_stok WHERE id = ? AND status = 'available'",
            [$id]
        );
    }
}
