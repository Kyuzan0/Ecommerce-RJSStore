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
     * Get recent logs for admin view, with optional role filtering.
     */
    public function getRecent(int $limit = 50, int $offset = 0, ?string $roleFilter = null): array
    {
        $where = "";
        $params = [];
        if ($roleFilter !== null && $roleFilter !== '') {
            if ($roleFilter === 'system') {
                $where = "WHERE a.user_id = 0";
            } else {
                $where = "WHERE u.role = ?";
                $params[] = $roleFilter;
            }
        }

        return $this->db->fetchAll(
            "SELECT a.*, COALESCE(u.name, 'System') AS user_name, u.role AS user_role
             FROM activity_log a
             LEFT JOIN users u ON a.user_id = u.id
             {$where}
             ORDER BY a.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public function countAll(?string $roleFilter = null): int
    {
        $where = "";
        $params = [];
        if ($roleFilter !== null && $roleFilter !== '') {
            if ($roleFilter === 'system') {
                $where = "WHERE a.user_id = 0";
            } else {
                $where = "WHERE EXISTS (SELECT 1 FROM users u WHERE u.id = a.user_id AND u.role = ?)";
                $params[] = $roleFilter;
            }
        }

        $row = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log a {$where}", $params);
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Get activity log counts by role for filtering UI.
     */
    public function getRoleCounts(): array
    {
        $counts = [
            'all'      => $this->countAll(),
            'admin'    => 0,
            'customer' => 0,
            'system'   => 0,
        ];

        // System count
        $rowSystem = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log WHERE user_id = 0");
        $counts['system'] = $rowSystem ? (int) $rowSystem['total'] : 0;

        // Admin & Customer count
        $rows = $this->db->fetchAll(
            "SELECT u.role, COUNT(*) AS total
             FROM activity_log a
             JOIN users u ON a.user_id = u.id
             GROUP BY u.role"
        );
        foreach ($rows as $row) {
            $r = strtolower($row['role']);
            if (isset($counts[$r])) {
                $counts[$r] = (int) $row['total'];
            }
        }

        return $counts;
    }
}
