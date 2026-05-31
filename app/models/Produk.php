<?php

class Produk extends BaseModel
{
    protected string $table = 'produk';

    /**
     * Search products with pagination (fixes N+1 rating query).
     */
    public function search(string $keyword, int $limit, int $offset, ?string $tipe = null, string $sort = 'terbaru'): array
    {
        $where = '1=1';
        $params = [];

        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $where .= " AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        if ($tipe !== null && $tipe !== '') {
            $where .= " AND p.tipe_produk = ?";
            $params[] = $tipe;
        }

        $orderBy = match ($sort) {
            'harga_asc'  => 'p.harga ASC',
            'harga_desc' => 'p.harga DESC',
            'rating'     => 'avg_rating DESC',
            default      => 'p.id DESC',
        };

        return $this->db->fetchAll(
            "SELECT p.*,
                    COALESCE(r.avg_rating, 0) AS avg_rating,
                    COALESCE(r.total_reviews, 0) AS total_reviews
             FROM produk p
             LEFT JOIN (
                 SELECT produk_id, AVG(rating) AS avg_rating, COUNT(id) AS total_reviews
                 FROM transaksi WHERE rating > 0 GROUP BY produk_id
             ) r ON p.id = r.produk_id
             WHERE {$where}
             ORDER BY {$orderBy}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /**
     * Count products matching search + filter.
     */
    public function countSearch(string $keyword, ?string $tipe = null): int
    {
        $where = '1=1';
        $params = [];

        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $where .= " AND (nama_produk LIKE ? OR deskripsi LIKE ?)";
            $params[] = $like;
            $params[] = $like;
        }

        if ($tipe !== null && $tipe !== '') {
            $where .= " AND tipe_produk = ?";
            $params[] = $tipe;
        }

        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM produk WHERE {$where}", $params);
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Find single product with rating info.
     */
    public function findWithRating(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT p.*, 
                    COALESCE(r.avg_rating, 0) AS avg_rating, 
                    COALESCE(r.total_reviews, 0) AS total_reviews
             FROM produk p
             LEFT JOIN (
                 SELECT produk_id, AVG(rating) AS avg_rating, COUNT(id) AS total_reviews
                 FROM transaksi WHERE rating > 0 GROUP BY produk_id
             ) r ON p.id = r.produk_id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Get reviews for a product.
     */
    public function getReviews(int $produkId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT t.rating, t.ulasan, t.tanggal, u.name AS nama_user
             FROM transaksi t
             JOIN users u ON t.user_id = u.id
             WHERE t.produk_id = ? AND t.rating > 0 AND t.ulasan != ''
             ORDER BY t.tanggal DESC
             LIMIT {$limit}",
            [$produkId]
        );
    }

    /**
     * Get latest reviews across all products (for homepage).
     */
    public function getLatestReviews(int $limit = 6): array
    {
        return $this->db->fetchAll(
            "SELECT t.rating, t.ulasan, t.tanggal, u.name AS nama_user, p.nama_produk
             FROM transaksi t
             JOIN users u ON t.user_id = u.id
             JOIN produk p ON t.produk_id = p.id
             WHERE t.rating > 0 AND t.ulasan != ''
             ORDER BY t.tanggal DESC
             LIMIT {$limit}"
        );
    }
}
