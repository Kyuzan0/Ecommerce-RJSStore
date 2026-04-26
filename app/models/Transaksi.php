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
            "SELECT p.id AS produk_id, p.nama_produk, p.harga, p.file_upload, p.tipe_produk, p.deskripsi, MAX(t.tanggal) AS tanggal
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.user_id = ? AND t.status = 'success'
             GROUP BY p.id
             ORDER BY tanggal DESC
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
     * Get total amount spent by a user (successful transactions only).
     */
    public function getTotalSpentByUser(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(p.harga), 0) AS total
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

    // ============================================================
    // LAPORAN (Report) Methods
    // ============================================================

    /**
     * Get filtered stats for laporan page.
     */
    public function getFilteredStats(?string $startDate, ?string $endDate): array
    {
        $where = "t.status = 'success'";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }

        $main = $this->db->fetchOne(
            "SELECT COUNT(t.id) AS jml, COALESCE(SUM(p.harga), 0) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}",
            $params
        );

        $avgDaily = $this->db->fetchOne(
            "SELECT COALESCE(AVG(daily_total), 0) AS avg_daily FROM (
                SELECT SUM(p.harga) AS daily_total
                FROM transaksi t
                JOIN produk p ON t.produk_id = p.id
                WHERE {$where}
                GROUP BY DATE(t.tanggal)
            ) AS sub",
            $params
        );

        $pending = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'pending'"
            . ($startDate ? " AND tanggal >= ?" : "")
            . ($endDate ? " AND tanggal <= ?" : ""),
            array_filter([$startDate, $endDate])
        );

        $topProduct = $this->db->fetchOne(
            "SELECT p.nama_produk, COUNT(t.id) AS jml
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             GROUP BY p.id, p.nama_produk
             ORDER BY jml DESC
             LIMIT 1",
            $params
        );

        // Growth % — compare current period vs previous period of same length
        $growth = null;
        if ($startDate && $endDate) {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            $days = (int) $start->diff($end)->days;

            $prevEnd = (clone $start)->modify('-1 day')->format('Y-m-d');
            $prevStart = (clone $start)->modify('-' . ($days + 1) . ' days')->format('Y-m-d');

            $current = (int) ($main['total'] ?? 0);

            $prev = $this->db->fetchOne(
                "SELECT COALESCE(SUM(p.harga), 0) AS total
                 FROM transaksi t
                 JOIN produk p ON t.produk_id = p.id
                 WHERE t.status = 'success' AND t.tanggal >= ? AND t.tanggal <= ?",
                [$prevStart, $prevEnd]
            );
            $prevTotal = (int) ($prev['total'] ?? 0);

            if ($prevTotal > 0) {
                $growth = round((($current - $prevTotal) / $prevTotal) * 100, 1);
            } elseif ($current > 0) {
                $growth = 100.0;
            } else {
                $growth = 0.0;
            }
        }

        return [
            'total_item'       => (int) ($main['jml'] ?? 0),
            'total_pendapatan' => (int) ($main['total'] ?? 0),
            'avg_daily'        => (int) ($avgDaily['avg_daily'] ?? 0),
            'pending_count'    => (int) ($pending['total'] ?? 0),
            'top_product'      => $topProduct['nama_produk'] ?? '-',
            'top_product_qty'  => (int) ($topProduct['jml'] ?? 0),
            'growth'           => $growth,
        ];
    }

    /**
     * Get daily revenue with optional date filter.
     */
    public function getFilteredDailyRevenue(?string $startDate, ?string $endDate): array
    {
        $where = "t.status = 'success'";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }

        return $this->db->fetchAll(
            "SELECT DATE(t.tanggal) AS tanggal, SUM(p.harga) AS pendapatan
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             GROUP BY DATE(t.tanggal)
             ORDER BY tanggal ASC",
            $params
        );
    }

    /**
     * Get monthly revenue with optional date filter.
     */
    public function getFilteredMonthlyRevenue(?string $startDate, ?string $endDate): array
    {
        $where = "t.status = 'success'";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }

        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') AS bulan, SUM(p.harga) AS pendapatan
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             GROUP BY bulan
             ORDER BY bulan ASC",
            $params
        );
    }

    /**
     * Get previous period revenue for comparison chart.
     */
    public function getPreviousPeriodRevenue(string $startDate, string $endDate, string $groupBy = 'daily'): array
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $days = (int) $start->diff($end)->days;

        $prevEnd = (clone $start)->modify('-1 day')->format('Y-m-d');
        $prevStart = (clone $start)->modify('-' . ($days + 1) . ' days')->format('Y-m-d');

        if ($groupBy === 'monthly') {
            return $this->db->fetchAll(
                "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') AS bulan, SUM(p.harga) AS pendapatan
                 FROM transaksi t
                 JOIN produk p ON t.produk_id = p.id
                 WHERE t.status = 'success' AND t.tanggal >= ? AND t.tanggal <= ?
                 GROUP BY bulan
                 ORDER BY bulan ASC",
                [$prevStart, $prevEnd]
            );
        }

        return $this->db->fetchAll(
            "SELECT DATE(t.tanggal) AS tanggal, SUM(p.harga) AS pendapatan
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE t.status = 'success' AND t.tanggal >= ? AND t.tanggal <= ?
             GROUP BY DATE(t.tanggal)
             ORDER BY tanggal ASC",
            [$prevStart, $prevEnd]
        );
    }

    /**
     * Get top products by sales count.
     */
    public function getTopProducts(?string $startDate, ?string $endDate, int $limit = 10): array
    {
        $where = "t.status = 'success'";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }

        return $this->db->fetchAll(
            "SELECT p.id, p.nama_produk, p.harga, p.tipe_produk, COUNT(t.id) AS jml_terjual, SUM(p.harga) AS total_pendapatan
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             GROUP BY p.id, p.nama_produk, p.harga, p.tipe_produk
             ORDER BY jml_terjual DESC
             LIMIT {$limit}",
            $params
        );
    }

    /**
     * Get breakdown by product type (tipe_produk).
     */
    public function getProductTypeBreakdown(?string $startDate, ?string $endDate): array
    {
        $where = "t.status = 'success'";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }

        return $this->db->fetchAll(
            "SELECT p.tipe_produk, COUNT(t.id) AS jml, SUM(p.harga) AS total
             FROM transaksi t
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             GROUP BY p.tipe_produk
             ORDER BY total DESC",
            $params
        );
    }

    /**
     * Get status breakdown (success/pending/failed).
     */
    public function getStatusBreakdown(?string $startDate, ?string $endDate): array
    {
        $where = "1=1";
        $params = [];

        if ($startDate) {
            $where .= " AND tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND tanggal <= ?";
            $params[] = $endDate;
        }

        return $this->db->fetchAll(
            "SELECT status, COUNT(*) AS jml
             FROM transaksi
             WHERE {$where}
             GROUP BY status
             ORDER BY jml DESC",
            $params
        );
    }

    /**
     * Get filtered transaction list for detail table.
     */
    public function getLaporanList(?string $startDate, ?string $endDate, ?string $search, ?string $status, int $limit = 15, int $offset = 0): array
    {
        $where = "1=1";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }
        if ($search) {
            $like = '%' . $search . '%';
            $where .= " AND (u.name LIKE ? OR p.nama_produk LIKE ? OR t.order_ref LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status) {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }

        return $this->db->fetchAll(
            "SELECT t.id, t.tanggal, t.status, t.order_ref, u.name AS nama_user, p.nama_produk, p.harga, p.tipe_produk
             FROM transaksi t
             JOIN users u ON t.user_id = u.id
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             ORDER BY t.tanggal DESC, t.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /**
     * Count filtered transaction list.
     */
    public function countLaporanList(?string $startDate, ?string $endDate, ?string $search, ?string $status): int
    {
        $where = "1=1";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }
        if ($search) {
            $like = '%' . $search . '%';
            $where .= " AND (u.name LIKE ? OR p.nama_produk LIKE ? OR t.order_ref LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        if ($status) {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }

        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM transaksi t
             JOIN users u ON t.user_id = u.id
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}",
            $params
        );
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Get all transactions for CSV export.
     */
    public function getExportData(?string $startDate, ?string $endDate, ?string $status): array
    {
        $where = "1=1";
        $params = [];

        if ($startDate) {
            $where .= " AND t.tanggal >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where .= " AND t.tanggal <= ?";
            $params[] = $endDate;
        }
        if ($status) {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }

        return $this->db->fetchAll(
            "SELECT t.id, t.order_ref, t.tanggal, t.status, u.name AS nama_user, u.email, p.nama_produk, p.harga, p.tipe_produk
             FROM transaksi t
             JOIN users u ON t.user_id = u.id
             JOIN produk p ON t.produk_id = p.id
             WHERE {$where}
             ORDER BY t.tanggal DESC, t.id DESC",
            $params
        );
    }
}
