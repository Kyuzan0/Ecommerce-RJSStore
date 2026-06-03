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
     * Get recent logs for admin view, with optional filters.
     */
    public function getRecent(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        list($where, $params) = $this->buildWhereClause($filters);

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

    public function countAll(array $filters = []): int
    {
        list($where, $params) = $this->buildWhereClause($filters);

        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total 
             FROM activity_log a 
             LEFT JOIN users u ON a.user_id = u.id 
             {$where}",
            $params
        );
        return $row ? (int) $row['total'] : 0;
    }

    /**
     * Build standard SQL WHERE clause and params from filters.
     */
    private function buildWhereClause(array $filters): array
    {
        $whereClause = [];
        $params = [];

        // Role filter
        if (!empty($filters['role'])) {
            if ($filters['role'] === 'system') {
                $whereClause[] = "a.user_id = 0";
            } else {
                $whereClause[] = "u.role = ?";
                $params[] = $filters['role'];
            }
        }

        // Action filter
        if (!empty($filters['action'])) {
            $whereClause[] = "a.action = ?";
            $params[] = $filters['action'];
        }

        // IP filter
        if (!empty($filters['ip'])) {
            $whereClause[] = "a.ip_address = ?";
            $params[] = $filters['ip'];
        }

        // Date filter
        if (!empty($filters['date'])) {
            if ($filters['date'] === 'today') {
                $whereClause[] = "a.created_at >= ?";
                $params[] = date('Y-m-d 00:00:00');
            } elseif ($filters['date'] === 'week') {
                $whereClause[] = "a.created_at >= ?";
                $params[] = date('Y-m-d 00:00:00', strtotime('-7 days'));
            } elseif ($filters['date'] === 'month') {
                $whereClause[] = "a.created_at >= ?";
                $params[] = date('Y-m-d 00:00:00', strtotime('-30 days'));
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $searchLike = '%' . $filters['search'] . '%';
            $subClauses = [
                "a.action LIKE ?",
                "a.ip_address LIKE ?",
                "a.detail LIKE ?",
                "a.target_type LIKE ?",
                "u.name LIKE ?"
            ];
            for ($i = 0; $i < 5; $i++) {
                $params[] = $searchLike;
            }
            if (is_numeric($filters['search'])) {
                $subClauses[] = "a.target_id = ?";
                $params[] = (int)$filters['search'];
            }
            $whereClause[] = "(" . implode(" OR ", $subClauses) . ")";
        }

        $where = count($whereClause) > 0 ? "WHERE " . implode(" AND ", $whereClause) : "";
        return [$where, $params];
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

    /**
     * Get distinct actions list for filtering.
     */
    public function getDistinctActions(): array
    {
        $rows = $this->db->fetchAll("SELECT DISTINCT action FROM activity_log ORDER BY action ASC");
        return array_column($rows, 'action');
    }

    /**
     * Get distinct client IP addresses for filtering.
     */
    public function getDistinctIps(): array
    {
        $rows = $this->db->fetchAll("SELECT DISTINCT ip_address FROM activity_log WHERE ip_address IS NOT NULL AND ip_address != '' ORDER BY ip_address ASC LIMIT 30");
        return array_column($rows, 'ip_address');
    }

    /**
     * Get dashboard summary stats for activity logs.
     */
    public function getSummaryStats(): array
    {
        $todayStart = date('Y-m-d 00:00:00');
        
        $rowTotal = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log");
        $total = $rowTotal ? (int)$rowTotal['total'] : 0;
        
        $rowLogin = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log WHERE action = 'login' AND created_at >= ?", [$todayStart]);
        $loginToday = $rowLogin ? (int)$rowLogin['total'] : 0;
        
        $rowCheckout = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log WHERE action = 'checkout' AND created_at >= ?", [$todayStart]);
        $checkoutToday = $rowCheckout ? (int)$rowCheckout['total'] : 0;
        
        $rowFailed = $this->db->fetchOne("SELECT COUNT(*) AS total FROM activity_log WHERE action = 'login_failed'");
        $failedLogin = $rowFailed ? (int)$rowFailed['total'] : 0;
        
        return [
            'total'          => $total,
            'login_today'    => $loginToday,
            'checkout_today' => $checkoutToday,
            'failed_login'   => $failedLogin
        ];
    }

    /**
     * Determine severity level based on action name.
     */
    public static function getSeverity(string $action): array
    {
        $action = strtolower($action);
        
        if (strpos($action, 'failed') !== false || strpos($action, 'error') !== false) {
            return ['label' => 'ERROR', 'color' => 'rose', 'bg' => 'bg-rose-500'];
        }
        if (strpos($action, 'hapus') !== false || strpos($action, 'delete') !== false) {
            return ['label' => 'WARNING', 'color' => 'amber', 'bg' => 'bg-amber-500'];
        }
        if (strpos($action, 'critical') !== false) {
            return ['label' => 'CRITICAL', 'color' => 'purple', 'bg' => 'bg-purple-500'];
        }
        
        return ['label' => 'INFO', 'color' => 'emerald', 'bg' => 'bg-emerald-500'];
    }
}
