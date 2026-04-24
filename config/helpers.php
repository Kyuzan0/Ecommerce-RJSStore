<?php
/**
 * Helper functions for RJSStore
 * - Environment variable loader
 * - DB query helpers (prepared statements)
 * - CSRF protection
 * - Flash messages
 * - Formatting utilities
 */

// ============================================================
// ENVIRONMENT VARIABLES
// ============================================================

/**
 * Load .env file and return values as associative array.
 * Caches result in static variable so file is only read once per request.
 * 
 * @return array  Key-value pairs from .env file
 */
function env_load(): array {
    static $env = null;
    if ($env !== null) return $env;
    
    $env = [];
    $envFile = __DIR__ . '/../.env';
    
    if (!file_exists($envFile)) return $env;
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        
        $key = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));
        
        // Remove surrounding quotes if present
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        
        $env[$key] = $value;
    }
    
    return $env;
}

/**
 * Get an environment variable value.
 * 
 * Usage:
 *   $dbHost = env('DB_HOST', 'localhost');
 *   $serverKey = env('MIDTRANS_SERVER_KEY');
 * 
 * @param string $key      Environment variable name
 * @param string $default  Default value if not found
 * @return string
 */
function env(string $key, string $default = ''): string {
    $env = env_load();
    return $env[$key] ?? $default;
}

// ============================================================
// DB HELPERS - Prepared Statement Wrappers
// ============================================================

/**
 * Execute a prepared SELECT query and return all rows.
 * 
 * Usage:
 *   $users = db_query($conn, "SELECT * FROM users WHERE role = ?", ["s", "admin"]);
 *   $all   = db_query($conn, "SELECT * FROM produk ORDER BY id DESC");
 * 
 * @param mysqli $conn   Database connection
 * @param string $sql    SQL with ? placeholders
 * @param array  $params [types_string, val1, val2, ...] e.g. ["si", "admin", 1]
 *                       Empty array or omit for queries without params
 * @return array         Array of associative arrays
 */
function db_query(mysqli $conn, string $sql, array $params = []): array {
    if (empty($params)) {
        $result = mysqli_query($conn, $sql);
        if (!$result) return [];
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
        return $rows;
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return [];

    $types = array_shift($params);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

/**
 * Execute a prepared SELECT and return the first row only.
 * 
 * Usage:
 *   $user = db_query_one($conn, "SELECT * FROM users WHERE id = ?", ["i", $id]);
 * 
 * @return array|null  Single associative array or null
 */
function db_query_one(mysqli $conn, string $sql, array $params = []): ?array {
    $rows = db_query($conn, $sql, $params);
    return $rows[0] ?? null;
}

/**
 * Execute a prepared INSERT/UPDATE/DELETE statement.
 * 
 * Usage:
 *   db_execute($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)", ["ssss", $name, $email, $hash, "customer"]);
 *   db_execute($conn, "DELETE FROM produk WHERE id = ?", ["i", $id]);
 * 
 * @return bool  True on success
 */
function db_execute(mysqli $conn, string $sql, array $params = []): bool {
    if (empty($params)) {
        return (bool) mysqli_query($conn, $sql);
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return false;

    $types = array_shift($params);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

/**
 * Execute INSERT and return the auto-increment ID.
 * 
 * Usage:
 *   $id = db_insert($conn, "INSERT INTO transaksi (user_id, produk_id, tanggal, status) VALUES (?, ?, ?, ?)", ["iiss", $uid, $pid, $date, "pending"]);
 * 
 * @return int  Insert ID (0 on failure)
 */
function db_insert(mysqli $conn, string $sql, array $params = []): int {
    if (db_execute($conn, $sql, $params)) {
        return (int) mysqli_insert_id($conn);
    }
    return 0;
}

/**
 * Count rows matching a query.
 * 
 * Usage:
 *   $total = db_count($conn, "SELECT COUNT(*) as c FROM users WHERE role = ?", ["s", "admin"]);
 * 
 * @return int
 */
function db_count(mysqli $conn, string $sql, array $params = []): int {
    $row = db_query_one($conn, $sql, $params);
    if ($row) {
        return (int) reset($row);
    }
    return 0;
}


// ============================================================
// CSRF PROTECTION
// ============================================================

/**
 * Generate a CSRF token and store in session.
 * Call this once per form render.
 * 
 * @return string  The token value
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden input field with the CSRF token.
 * Use inside <form> tags.
 * 
 * Usage: <?= csrf_field() ?>
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the CSRF token from POST data.
 * Regenerates token after validation to prevent reuse.
 * 
 * @return bool  True if valid
 */
function csrf_validate(): bool {
    $token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    
    if (empty($token) || empty($session_token)) {
        return false;
    }
    
    $valid = hash_equals($session_token, $token);
    
    // Regenerate token after validation
    unset($_SESSION['csrf_token']);
    
    return $valid;
}


// ============================================================
// FLASH MESSAGES
// ============================================================

/**
 * Set a flash message in session.
 * 
 * Usage:
 *   flash('success', 'Produk berhasil ditambahkan!');
 *   flash('error', 'Email sudah terdaftar!');
 * 
 * @param string $type    Message type: 'success', 'error', 'warning', 'info'
 * @param string $message The message text
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get and clear a flash message.
 * Returns null if no message of that type exists.
 * 
 * @param string $type  Message type
 * @return string|null
 */
function flash_get(string $type): ?string {
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

/**
 * Render all flash messages as hidden JSON data for toast notifications.
 * The toast JS (includes/toast.php) reads this data and shows animated toasts.
 * Automatically clears messages after rendering.
 * 
 * Usage: <?= flash_render() ?>
 */
function flash_render(): string {
    if (empty($_SESSION['flash'])) return '';
    
    $messages = [];
    foreach ($_SESSION['flash'] as $type => $message) {
        $messages[] = ['type' => $type, 'message' => $message];
    }
    
    unset($_SESSION['flash']);
    return '<div id="flash-data" data-messages="' . htmlspecialchars(json_encode($messages), ENT_QUOTES, 'UTF-8') . '" style="display:none"></div>';
}


// ============================================================
// FORMATTING UTILITIES
// ============================================================

/**
 * Format number as Indonesian Rupiah.
 * 
 * Usage: <?= rupiah(150000) ?>  → "Rp 150.000"
 */
function rupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Escape string for safe HTML output.
 * 
 * Usage: <?= e($user_input) ?>
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to Indonesian format.
 * 
 * Usage: <?= format_tanggal('2024-01-15') ?>  → "15 Januari 2024"
 */
function format_tanggal(string $date): string {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    $d = (int) date('d', $timestamp);
    $m = (int) date('m', $timestamp);
    $y = date('Y', $timestamp);
    return $d . ' ' . ($bulan[$m] ?? '') . ' ' . $y;
}


// ============================================================
// PAGINATION
// ============================================================

/**
 * Calculate pagination parameters.
 * 
 * Usage:
 *   $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk", [], 12);
 *   $products = db_query($conn, "SELECT * FROM produk LIMIT ? OFFSET ?", ["ii", $paging['limit'], $paging['offset']]);
 *   echo pagination_render($paging);
 * 
 * @param mysqli $conn       Database connection
 * @param string $count_sql  SQL to count total rows (must return single count)
 * @param array  $params     Params for count query
 * @param int    $per_page   Items per page (default 12)
 * @return array             ['page', 'per_page', 'total', 'total_pages', 'offset', 'limit']
 */
function paginate(mysqli $conn, string $count_sql, array $params = [], int $per_page = 12): array {
    $total = db_count($conn, $count_sql, $params);
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $total_pages = max(1, (int) ceil($total / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    
    return [
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'limit' => $per_page,
    ];
}

/**
 * Render pagination links as styled HTML.
 * Preserves existing query string parameters.
 * 
 * @param array $paging  Result from paginate()
 * @return string        HTML pagination
 */
function pagination_render(array $paging): string {
    if ($paging['total_pages'] <= 1) return '';
    
    $page = $paging['page'];
    $total_pages = $paging['total_pages'];
    
    // Build base URL preserving existing query params
    $query_params = $_GET;
    unset($query_params['page']);
    $base = '?' . (empty($query_params) ? '' : http_build_query($query_params) . '&');
    
    $html = '<div class="flex items-center justify-center gap-1 mt-6">';
    
    // Previous
    if ($page > 1) {
        $html .= '<a href="' . $base . 'page=' . ($page - 1) . '" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition">&laquo;</a>';
    }
    
    // Page numbers (show max 7 pages with ellipsis)
    $start = max(1, $page - 3);
    $end = min($total_pages, $page + 3);
    
    if ($start > 1) {
        $html .= '<a href="' . $base . 'page=1" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition">1</a>';
        if ($start > 2) $html .= '<span class="px-2 py-2 text-gray-400 text-sm">...</span>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            $html .= '<span class="px-3 py-2 rounded-lg text-sm font-bold text-white" style="background:#42B549">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $base . 'page=' . $i . '" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition">' . $i . '</a>';
        }
    }
    
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) $html .= '<span class="px-2 py-2 text-gray-400 text-sm">...</span>';
        $html .= '<a href="' . $base . 'page=' . $total_pages . '" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition">' . $total_pages . '</a>';
    }
    
    // Next
    if ($page < $total_pages) {
        $html .= '<a href="' . $base . 'page=' . ($page + 1) . '" class="px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition">&raquo;</a>';
    }
    
    $html .= '</div>';
    $html .= '<p class="text-center text-xs text-gray-400 mt-2">Menampilkan ' . (($paging['offset']) + 1) . '-' . min($paging['offset'] + $paging['per_page'], $paging['total']) . ' dari ' . $paging['total'] . '</p>';
    
    return $html;
}


// ============================================================
// AUTH HELPERS
// ============================================================

/**
 * Check if user is logged in with a specific role.
 * Redirects to login page if not authenticated.
 * 
 * Usage (at top of page):
 *   require_role('customer');
 *   require_role('admin');
 */
function require_role(string $role): void {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: ../auth/login.php");
        exit;
    }
}

/**
 * Get current logged-in user ID.
 * 
 * @return int|null
 */
function current_user_id(): ?int {
    return isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
}

/**
 * Get current logged-in user name.
 * 
 * @return string
 */
function current_user_name(): string {
    return $_SESSION['name'] ?? '';
}

// ============================================================
// PRODUCT TYPE HELPERS
// ============================================================

/**
 * Get all available product types with their display config.
 * Each type has: label, color (text), bg (background), icon (SVG path)
 */
function tipe_produk_list(): array {
    return [
        'Akun'     => ['label' => 'Akun',     'color' => '#1565C0', 'bg' => '#E3F2FD'],
        'Ebook'    => ['label' => 'Ebook',    'color' => '#6A1B9A', 'bg' => '#F3E5F5'],
        'Game'     => ['label' => 'Game',     'color' => '#E65100', 'bg' => '#FFF3E0'],
        'Software' => ['label' => 'Software', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
        'Template' => ['label' => 'Template', 'color' => '#AD1457', 'bg' => '#FCE4EC'],
        'Lainnya'  => ['label' => 'Lainnya',  'color' => '#42B549', 'bg' => '#E8F5E9'],
    ];
}

/**
 * Get display config for a product type. Falls back to 'Lainnya' if unknown.
 */
function tipe_produk_config(string $tipe): array {
    $list = tipe_produk_list();
    return $list[$tipe] ?? $list['Lainnya'];
}

/**
 * Render a product type badge HTML.
 */
function tipe_produk_badge(string $tipe): string {
    $cfg = tipe_produk_config($tipe);
    return '<span class="text-xs font-bold px-2 py-1 rounded-lg" style="color:' . $cfg['color'] . '; background:' . $cfg['bg'] . '">' . e($cfg['label']) . '</span>';
}
