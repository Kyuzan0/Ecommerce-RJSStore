<?php

class Transaksi extends BaseModel
{
    protected string $table = 'transaksi';

    /**
     * Create orders for multiple cart items with shared order_ref.
     * Uses DB transaction for atomicity.
     */
    public function createOrder(int $userId, array $cartItems, string $orderRef): bool
    {
        $this->db->beginTransaction();
        try {
            $tanggal = date('Y-m-d');
            foreach ($cartItems as $item) {
                $this->db->execute(
                    "INSERT INTO transaksi (user_id, produk_id, tanggal, status, order_ref) VALUES (?, ?, ?, 'pending', ?)",
                    [$userId, (int) $item['produk_id'], $tanggal, $orderRef]
                );
            }
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Get transactions by order_ref.
     */
    public function getByOrderRef(string $orderRef, int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, p.nama_produk, p.harga
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.order_ref = ? AND t.user_id = ?",
            [$orderRef, $userId]
        );
    }

    /**
     * Get transactions for a user with optional status filter.
     */
    public function getByUser(int $userId, ?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT t.*, p.nama_produk, p.harga, p.file_upload, p.tipe_produk
                FROM transaksi t
                JOIN produk p ON t.produk_id = p.id
                WHERE t.user_id = ?";
        $params = [$userId];

        if ($status !== null) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY t.tanggal DESC, t.id DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count transactions for a user.
     */
    public function countByUser(int $userId, ?string $status = null): int
    {
        $sql = "SELECT COUNT(*) AS total FROM transaksi WHERE user_id = ?";
        $params = [$userId];

        if ($status !== null) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $row = $this->db->fetchOne($sql, $params);
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Update status by order_ref.
     */
    public function updateStatusByRef(string $orderRef, string $status): bool
    {
        return $this->db->execute(
            "UPDATE transaksi SET status = ? WHERE order_ref = ?",
            [$status, $orderRef]
        );
    }

    /**
     * Update status by ID.
     */
    public function updateStatusById(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Set rating and review.
     */
    public function setRating(int $id, int $userId, int $rating, string $ulasan): bool
    {
        return $this->db->execute(
            "UPDATE transaksi SET rating = ?, ulasan = ? WHERE id = ? AND user_id = ?",
            [$rating, $ulasan, $id, $userId]
        );
    }

    /**
     * Get pending transactions by order_ref for re-payment.
     */
    public function getPendingByRef(string $orderRef, int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT t.id, t.order_ref, p.nama_produk, p.harga
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.order_ref = ? AND t.user_id = ? AND t.status = 'pending'",
            [$orderRef, $userId]
        );
    }

    /**
     * Get pending transaction by ID for re-payment (legacy single).
     */
    public function getPendingById(int $id, int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT t.*, p.nama_produk, p.harga
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.id = ? AND t.user_id = ? AND t.status = 'pending'",
            [$id, $userId]
        );
    }

    /**
     * Get downloadable products for a user.
     */
    public function getDownloadable(int $userId, int $limit = 20, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT p.id AS produk_id, p.nama_produk, p.file_upload, p.tipe_produk, p.deskripsi
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.user_id = ? AND t.status = 'success'
             GROUP BY p.id
             ORDER BY MAX(t.tanggal) DESC
             LIMIT {$limit} OFFSET {$offset}",
            [$userId]
        );
    }

    /**
     * Count downloadable products.
     */
    public function countDownloadable(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT p.id) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.user_id = ? AND t.status = 'success'",
            [$userId]
        );
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Check if user already purchased a product.
     */
    public function isPurchased(int $userId, int $produkId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM transaksi WHERE user_id = ? AND produk_id = ? AND status IN ('pending', 'success')",
            [$userId, $produkId]
        );
        return $row !== null;
    }

    /**
     * Get admin transaction list with user and product info.
     */
    public function getAdminList(?string $search = null, ?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT t.*, u.name AS nama_user, p.nama_produk, p.harga
                FROM transaksi t
                JOIN users u ON t.user_id = u.id
                JOIN produk p ON t.produk_id = p.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (u.name LIKE ? OR p.nama_produk LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY t.id DESC LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count admin transactions.
     */
    public function countAdminList(?string $search = null, ?string $status = null): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM transaksi t
                JOIN users u ON t.user_id = u.id
                JOIN produk p ON t.produk_id = p.id
                WHERE 1=1";
        $params = [];

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (u.name LIKE ? OR p.nama_produk LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }

        $row = $this->db->fetchOne($sql, $params);
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Get daily revenue for last N days.
     */
    public function getDailyRevenue(int $days = 7): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(t.tanggal) AS tanggal, SUM(p.harga) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.status = 'success' AND t.tanggal >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(t.tanggal)
             ORDER BY tanggal ASC",
            [$days]
        );
    }

    /**
     * Get monthly revenue for current year.
     */
    public function getMonthlyRevenue(): array
    {
        return $this->db->fetchAll(
            "SELECT MONTH(t.tanggal) AS bulan, SUM(p.harga) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.status = 'success' AND YEAR(t.tanggal) = YEAR(CURDATE())
             GROUP BY MONTH(t.tanggal)
             ORDER BY bulan ASC"
        );
    }

    /**
     * Get summary stats for admin dashboard.
     */
    public function getSummaryStats(): array
    {
        $totalSold = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'success'"
        );
        $totalRevenue = $this->db->fetchOne(
            "SELECT SUM(p.harga) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.status = 'success'"
        );
        return [
            'total_sold'    => $totalSold ? (int) $totalSold['total'] : 0,
            'total_revenue' => $totalRevenue ? (int) ($totalRevenue['total'] ?? 0) : 0,
        ];
    }
}
