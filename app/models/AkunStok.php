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
        $rows = $this->db->fetchAll(
            "SELECT * FROM akun_stok WHERE varian_id = ? ORDER BY status ASC, id DESC",
            [$varianId]
        );
        foreach ($rows as &$row) {
            $row['account_email'] = decrypt_value($row['account_email']);
            $row['account_password'] = decrypt_value($row['account_password']);
        }
        return $rows;
    }

    /**
     * Add a single stock item.
     */
    public function addStock(int $varianId, string $email, string $password): int
    {
        return $this->create([
            'varian_id'        => $varianId,
            'account_email'    => encrypt_value($email),
            'account_password' => encrypt_value($password),
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
                [$varianId, encrypt_value($email), encrypt_value($pass)]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Assign one available stock to a transaction.
     * Returns the assigned stock row (decrypted), or null if out of stock.
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

        // Return decrypted values
        $stock['account_email'] = decrypt_value($stock['account_email']);
        $stock['account_password'] = decrypt_value($stock['account_password']);
        return $stock;
    }

    /**
     * Get the stock assigned to a specific transaction (decrypted).
     */
    public function getByTransaksi(int $transaksiId): ?array
    {
        $row = $this->db->fetchOne(
            "SELECT * FROM akun_stok WHERE transaksi_id = ?",
            [$transaksiId]
        );
        if ($row) {
            $row['account_email'] = decrypt_value($row['account_email']);
            $row['account_password'] = decrypt_value($row['account_password']);
        }
        return $row;
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

    /**
     * Delete ALL available stock for a variant.
     * Returns the number of deleted rows.
     */
    public function deleteAllAvailable(int $varianId): int
    {
        $count = $this->countAvailable($varianId);
        $this->db->execute(
            "DELETE FROM akun_stok WHERE varian_id = ? AND status = 'available'",
            [$varianId]
        );
        return $count;
    }
}
