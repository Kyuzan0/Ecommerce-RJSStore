<?php

class AuditLog extends BaseModel
{
    protected string $table = 'audit_log';

    /**
     * Record an admin action.
     */
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?string $detail = null): void
    {
        $adminId = $_SESSION['user_id'] ?? 0;
        if ($adminId <= 0) return;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO audit_log (admin_id, action, target_type, target_id, detail, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
            [$adminId, $action, $targetType, $targetId, $detail, $ip]
        );
    }

    /**
     * Get recent logs for admin view.
     */
    public function getRecent(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, COALESCE(u.name, 'System') AS admin_name
             FROM audit_log a
             LEFT JOIN users u ON a.admin_id = u.id
             ORDER BY a.created_at DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    public function countAll(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM audit_log");
        return $row ? (int) $row['total'] : 0;
    }
}
