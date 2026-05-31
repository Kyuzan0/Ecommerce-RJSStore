<?php

class Notifikasi extends BaseModel
{
    protected string $table = 'notifikasi';

    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    public function countUnread(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total FROM notifikasi WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return $row ? (int) $row['total'] : 0;
    }

    public function markAsRead(int $id, int $userId): bool
    {
        return $this->db->execute(
            "UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );
    }

    public function markAllRead(int $userId): bool
    {
        return $this->db->execute(
            "UPDATE notifikasi SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    public static function send(int $userId, string $judul, string $pesan, string $tipe = 'info', ?string $link = null): void
    {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO notifikasi (user_id, judul, pesan, tipe, link) VALUES (?, ?, ?, ?, ?)",
            [$userId, $judul, $pesan, $tipe, $link]
        );
    }
}
