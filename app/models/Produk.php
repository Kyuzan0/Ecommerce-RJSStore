<?php

class Produk extends BaseModel
{
    protected string $table = 'produk';

    /**
     * Search products with pagination (fixes N+1 rating query).
     */
    public function search(string $keyword, int $limit, int $offset): array
    {
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            return $this->db->fetchAll(
                "SELECT p.*, 
                        COALESCE(r.avg_rating, 0) AS avg_rating, 
                        COALESCE(r.total_reviews, 0) AS total_reviews
                 FROM produk p
                 LEFT JOIN (
                     SELECT produk_id, AVG(rating) AS avg_rating, COUNT(id) AS total_reviews
                     FROM transaksi WHERE rating > 0 GROUP BY produk_id
                 ) r ON p.id = r.produk_id
                 WHERE p.nama_produk LIKE ? OR p.deskripsi LIKE ?
                 ORDER BY p.id DESC
                 LIMIT {$limit} OFFSET {$offset}",
                [$like, $like]
            );
        }

        return $this->db->fetchAll(
            "SELECT p.*, 
                    COALESCE(r.avg_rating, 0) AS avg_rating, 
                    COALESCE(r.total_reviews, 0) AS total_reviews
             FROM produk p
             LEFT JOIN (
                 SELECT produk_id, AVG(rating) AS avg_rating, COUNT(id) AS total_reviews
                 FROM transaksi WHERE rating > 0 GROUP BY produk_id
             ) r ON p.id = r.produk_id
             ORDER BY p.id DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    /**
     * Count products matching search.
     */
    public function countSearch(string $keyword): int
    {
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $row = $this->db->fetchOne(
                "SELECT COUNT(*) AS total FROM produk WHERE nama_produk LIKE ? OR deskripsi LIKE ?",
                [$like, $like]
            );
        } else {
            $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM produk");
        }
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
