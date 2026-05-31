<?php

class GaransiKlaim extends BaseModel
{
    protected string $table = 'garansi_klaim';

    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT g.*, t.produk_id, p.nama_produk, v.durasi, v.paket
             FROM garansi_klaim g
             JOIN transaksi t ON g.transaksi_id = t.id
             JOIN produk p ON t.produk_id = p.id
             LEFT JOIN produk_varian v ON t.varian_id = v.id
             WHERE g.user_id = ?
             ORDER BY g.created_at DESC",
            [$userId]
        );
    }

    public function getAllAdmin(?string $status = null, int $limit = 20, int $offset = 0): array
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where .= " AND g.status = ?";
            $params[] = $status;
        }
        return $this->db->fetchAll(
            "SELECT g.*, u.name AS nama_user, u.email, p.nama_produk, v.durasi, v.paket
             FROM garansi_klaim g
             JOIN users u ON g.user_id = u.id
             JOIN transaksi t ON g.transaksi_id = t.id
             JOIN produk p ON t.produk_id = p.id
             LEFT JOIN produk_varian v ON t.varian_id = v.id
             WHERE {$where}
             ORDER BY g.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function countAdmin(?string $status = null): int
    {
        $where = '1=1';
        $params = [];
        if ($status) {
            $where .= " AND status = ?";
            $params[] = $status;
        }
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM garansi_klaim WHERE {$where}", $params);
        return $row ? (int) $row['total'] : 0;
    }

    public function submitKlaim(int $transaksiId, int $userId, string $alasan): int
    {
        return $this->create([
            'transaksi_id' => $transaksiId,
            'user_id'      => $userId,
            'alasan'       => $alasan,
        ]);
    }

    public function resolve(int $id, string $status, ?string $adminNote = null): bool
    {
        return $this->db->execute(
            "UPDATE garansi_klaim SET status = ?, admin_note = ?, resolved_at = NOW() WHERE id = ?",
            [$status, $adminNote, $id]
        );
    }

    public function hasActiveKlaim(int $transaksiId): bool
    {
        $row = $this->db->fetchOne(
            "SELECT id FROM garansi_klaim WHERE transaksi_id = ? AND status = 'pending'",
            [$transaksiId]
        );
        return $row !== null;
    }
}
