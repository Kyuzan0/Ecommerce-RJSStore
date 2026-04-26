<?php
/**
 * Helper functions for RJSStore MVC
 */

// ============================================================
// ENVIRONMENT VARIABLES
// ============================================================

function env_load(): array
{
    static $env = null;
    if ($env !== null) return $env;

    $env = [];
    $envFile = BASE_PATH . '/.env';

    if (!file_exists($envFile)) return $env;

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        $pos = strpos($line, '=');
        if ($pos === false) continue;

        $key   = trim(substr($line, 0, $pos));
        $value = trim(substr($line, $pos + 1));

        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        $env[$key] = $value;
    }

    return $env;
}

function env(string $key, string $default = ''): string
{
    $env = env_load();
    return $env[$key] ?? $default;
}

// ============================================================
// CONFIG HELPER
// ============================================================

function config(string $file): array
{
    static $cache = [];
    if (!isset($cache[$file])) {
        $cache[$file] = require BASE_PATH . '/config/' . $file . '.php';
    }
    return $cache[$file];
}

// ============================================================
// URL & PATH HELPERS
// ============================================================

function url(string $path = ''): string
{
    static $baseUrl = null;
    if ($baseUrl === null) {
        $baseUrl = rtrim(config('app')['base_url'], '/');
    }
    if ($path === '') return $baseUrl;
    return $baseUrl . '/' . ltrim($path, '/');
}

function base_path(string $path = ''): string
{
    if ($path === '') return BASE_PATH;
    return BASE_PATH . '/' . ltrim($path, '/');
}

function public_path(string $path = ''): string
{
    if ($path === '') return BASE_PATH . '/public';
    return BASE_PATH . '/public/' . ltrim($path, '/');
}

// ============================================================
// CSRF PROTECTION
// ============================================================

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_validate(): bool
{
    $token         = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';

    if (empty($token) || empty($session_token)) {
        return false;
    }

    $valid = hash_equals($session_token, $token);
    unset($_SESSION['csrf_token']);
    return $valid;
}

// ============================================================
// FLASH MESSAGES
// ============================================================

function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get(string $type): ?string
{
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

function flash_render(): string
{
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

function rupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_tanggal(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }
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

function paginate(Database $db, string $count_sql, array $params = [], int $per_page = 12): array
{
    $row   = $db->fetchOne($count_sql, $params);
    $total = $row ? (int) reset($row) : 0;

    $page        = max(1, (int) ($_GET['page'] ?? 1));
    $total_pages = max(1, (int) ceil($total / $per_page));
    $page        = min($page, $total_pages);
    $offset      = ($page - 1) * $per_page;

    return [
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => $total,
        'total_pages' => $total_pages,
        'offset'      => $offset,
        'limit'       => $per_page,
    ];
}

function pagination_render(array $paging): string
{
    if ($paging['total_pages'] <= 1) return '';

    $page        = $paging['page'];
    $total_pages = $paging['total_pages'];

    $query_params = $_GET;
    unset($query_params['page']);
    $base = '?' . (empty($query_params) ? '' : http_build_query($query_params) . '&');

    $link_cls = 'px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 hover:shadow-sm active:scale-95 transition-all duration-150 cursor-pointer';
    $active_cls = 'px-3 py-2 rounded-lg text-sm font-bold text-white shadow-sm';
    $disabled_cls = 'px-3 py-2 rounded-lg text-sm font-medium text-gray-300 cursor-default';
    $dots_cls = 'px-2 py-2 text-gray-400 text-sm';

    $html = '<div class="border-t border-gray-100 pt-4 pb-1 mt-auto"><div class="flex items-center justify-center gap-1">';

    // Prev arrow — always rendered for consistent width
    if ($page > 1) {
        $html .= '<a href="' . $base . 'page=' . ($page - 1) . '" class="' . $link_cls . '">&laquo;</a>';
    } else {
        $html .= '<span class="' . $disabled_cls . '">&laquo;</span>';
    }

    // Fixed-width sliding window: always show exactly $max_visible page slots
    // Slots: [1] [...] [a] [b] [c] [d] [e] [...] [last]
    // The middle window is always 5 buttons; total slots = 9 (including 1, last, 2 ellipsis)
    $max_visible = 5;

    if ($total_pages <= $max_visible + 4) {
        // Few pages — show all, no ellipsis needed
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                $html .= '<span class="' . $active_cls . '" style="background:#42B549">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $base . 'page=' . $i . '" class="' . $link_cls . '">' . $i . '</a>';
            }
        }
    } else {
        // Calculate the middle window boundaries
        $half = (int) floor($max_visible / 2);
        $win_start = $page - $half;
        $win_end   = $page + $half;

        // Clamp window to valid range
        if ($win_start < 1) {
            $win_start = 1;
            $win_end   = $max_visible;
        }
        if ($win_end > $total_pages) {
            $win_end   = $total_pages;
            $win_start = $total_pages - $max_visible + 1;
        }

        $show_left_dots  = ($win_start > 2);
        $show_right_dots = ($win_end < $total_pages - 1);

        // First page
        if ($win_start > 1) {
            if (1 == $page) {
                $html .= '<span class="' . $active_cls . '" style="background:#42B549">1</span>';
            } else {
                $html .= '<a href="' . $base . 'page=1" class="' . $link_cls . '">1</a>';
            }
        }

        // Left ellipsis
        if ($show_left_dots) {
            $html .= '<span class="' . $dots_cls . '">...</span>';
        }

        // Middle window
        for ($i = $win_start; $i <= $win_end; $i++) {
            if ($i == $page) {
                $html .= '<span class="' . $active_cls . '" style="background:#42B549">' . $i . '</span>';
            } else {
                $html .= '<a href="' . $base . 'page=' . $i . '" class="' . $link_cls . '">' . $i . '</a>';
            }
        }

        // Right ellipsis
        if ($show_right_dots) {
            $html .= '<span class="' . $dots_cls . '">...</span>';
        }

        // Last page
        if ($win_end < $total_pages) {
            if ($total_pages == $page) {
                $html .= '<span class="' . $active_cls . '" style="background:#42B549">' . $total_pages . '</span>';
            } else {
                $html .= '<a href="' . $base . 'page=' . $total_pages . '" class="' . $link_cls . '">' . $total_pages . '</a>';
            }
        }
    }

    // Next arrow — always rendered for consistent width
    if ($page < $total_pages) {
        $html .= '<a href="' . $base . 'page=' . ($page + 1) . '" class="' . $link_cls . '">&raquo;</a>';
    } else {
        $html .= '<span class="' . $disabled_cls . '">&raquo;</span>';
    }

    $html .= '</div>';
    $html .= '<p class="text-center text-xs text-gray-400 mt-2 pb-4">Menampilkan ' . (($paging['offset']) + 1) . '-' . min($paging['offset'] + $paging['per_page'], $paging['total']) . ' dari ' . $paging['total'] . '</p>';
    $html .= '</div>';

    return $html;
}

// ============================================================
// AUTH HELPERS (convenience wrappers)
// ============================================================

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_name(): string
{
    return $_SESSION['user_name'] ?? '';
}

// ============================================================
// PRODUCT TYPE HELPERS
// ============================================================

function tipe_produk_list(): array
{
    return [
        'Akun'     => ['label' => 'Akun',     'color' => '#1565C0', 'bg' => '#E3F2FD'],
        'Ebook'    => ['label' => 'Ebook',    'color' => '#6A1B9A', 'bg' => '#F3E5F5'],
        'Game'     => ['label' => 'Game',     'color' => '#E65100', 'bg' => '#FFF3E0'],
        'Software' => ['label' => 'Software', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
        'Template' => ['label' => 'Template', 'color' => '#AD1457', 'bg' => '#FCE4EC'],
        'Lainnya'  => ['label' => 'Lainnya',  'color' => '#42B549', 'bg' => '#E8F5E9'],
    ];
}

function tipe_produk_config(string $tipe): array
{
    $list = tipe_produk_list();
    return $list[$tipe] ?? $list['Lainnya'];
}

function tipe_produk_badge(string $tipe): string
{
    $cfg = tipe_produk_config($tipe);
    return '<span class="text-xs font-bold px-2 py-1 rounded-lg" style="color:' . $cfg['color'] . '; background:' . $cfg['bg'] . '">' . e($cfg['label']) . '</span>';
}

// ============================================================
// TRANSACTION STATUS HELPERS
// ============================================================

function status_transaksi_list(): array
{
    return [
        'pending'   => ['label' => 'Pending',   'color' => '#E65100', 'bg' => '#FFF3E0'],
        'success'   => ['label' => 'Success',   'color' => '#2E7D32', 'bg' => '#E8F5E9'],
        'cancelled' => ['label' => 'Cancelled', 'color' => '#C62828', 'bg' => '#FFEBEE'],
    ];
}

function status_transaksi_config(string $status): array
{
    $list = status_transaksi_list();
    return $list[$status] ?? ['label' => ucfirst($status), 'color' => '#374151', 'bg' => '#F3F4F6'];
}

function status_transaksi_badge(string $status): string
{
    $cfg = status_transaksi_config($status);
    return '<span class="text-xs font-bold px-2 py-1 rounded-lg" style="color:' . $cfg['color'] . '; background:' . $cfg['bg'] . '">' . e($cfg['label']) . '</span>';
}

// ============================================================
// JSON RESPONSE
// ============================================================

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
