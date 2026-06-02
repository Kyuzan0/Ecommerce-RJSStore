<?php

class ActivityLog extends BaseModel
{
    protected string $table = 'activity_log';

    /**
     * Record a user/system action.
     */
    public static function log(string $action, ?string $targetType = null, ?int $targetId = null, ?string $detail = null, ?int $forceUserId = null): void
    {
        $userId = $forceUserId ?? ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0 && !in_array($action, ['login_failed', 'register'])) return;

        $ip = self::getRealIp();
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO activity_log (user_id, action, target_type, target_id, detail, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $action, $targetType, $targetId, $detail, $ip]
        );
    }

    /**
     * Get the real client IP address, accounting for proxies (Cloudflare, Traefik, Nginx).
     */
    private static function getRealIp(): string
    {
        // Cloudflare
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // Standard proxy header (Traefik, Nginx, load balancers)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Can contain multiple IPs: "client, proxy1, proxy2" — take the first
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        // Alternative header
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        // Fallback
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get recent logs for admin view.
     */
    public function getRecent(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, COALESCE(u.name, 'System') AS user_name, u.role AS user_role
             FROM activity_log a
             LEFT JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT {$limit} OFFSET {$offset}"
        );
    }

    public function countAll(): int
    {
        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log");
        return $row ? (int) $row['total'] : 0;
    }
}
