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
    // First check system environment variables (Docker/Dokploy)
    $sysEnv = getenv($key);
    if ($sysEnv !== false) {
        return $sysEnv;
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    // Fallback to .env file
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

function paginate(Database $db, string $count_sql, array $params = [], int $default_per_page = 12): array
{
    $row   = $db->fetchOne($count_sql, $params);
    $total = $row ? (int) reset($row) : 0;

    // Check dynamic limit parameter
    $per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : $default_per_page;
    if (!in_array($per_page, [10, 50, 100])) {
        $per_page = $default_per_page;
    }

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
    if ($paging['total_pages'] <= 1 && (!isset($_GET['limit']) || !in_array((int)$_GET['limit'], [10, 50, 100]))) return '';

    $page        = $paging['page'];
    $total_pages = $paging['total_pages'];
    $isAdmin     = (strpos($_SERVER['REQUEST_URI'], '/admin-') !== false);

    $query_params = $_GET;
    unset($query_params['page']);
    $base = '?' . (empty($query_params) ? '' : http_build_query($query_params) . '&');

    $link_cls = 'px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 hover:shadow-sm active:scale-95 transition-all duration-150 cursor-pointer';
    $active_cls = 'px-3 py-2 rounded-lg text-sm font-bold text-white shadow-sm';
    $disabled_cls = 'px-3 py-2 rounded-lg text-sm font-medium text-gray-300 cursor-default';
    $dots_cls = 'px-2 py-2 text-gray-400 text-sm';

    $html = '<div class="border-t border-gray-100 pt-4 pb-4 px-4 mt-auto flex flex-col sm:flex-row items-center justify-between gap-4">';
    
    // Group pagination status and limit dropdown on the left
    $html .= '<div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 order-2 sm:order-1">';
    $html .= '<p class="text-xs text-gray-400">Menampilkan ' . (($paging['offset']) + 1) . '-' . min($paging['offset'] + $paging['per_page'], $paging['total']) . ' dari ' . $paging['total'] . '</p>';
    
    // Render limit dropdown on admin pages only
    if (strpos($_SERVER['REQUEST_URI'], '/admin-') !== false) {
        $html .= '<span class="hidden sm:inline text-gray-300 dark:text-gray-700">|</span>';
        $html .= '<div class="flex items-center gap-1.5">';
        $html .= '<span class="text-xs text-gray-400">Tampilkan:</span>';
        $html .= '<select onchange="location.href = this.value" class="px-2 py-1 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-[11px] font-semibold text-gray-700 dark:text-gray-300 outline-none cursor-pointer focus:ring-1 focus:ring-green-500">';
        foreach ([10, 50, 100] as $l) {
            $params = $_GET;
            $params['limit'] = $l;
            $params['page'] = 1;
            $url = '?' . http_build_query($params);
            $isSelected = ($paging['per_page'] === $l) ? 'selected' : '';
            $html .= '<option value="' . $url . '" ' . $isSelected . '>' . $l . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    if ($isAdmin) {
        $html .= '<div class="flex items-center gap-4 order-1 sm:order-2">';
        if ($page > 1) {
            $html .= '<a href="' . $base . 'page=' . ($page - 1) . '" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-95 transition-all">◀ Sebelumnya</a>';
        } else {
            $html .= '<span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 dark:text-gray-600 border border-gray-150 dark:border-gray-800 cursor-default">◀ Sebelumnya</span>';
        }

        $html .= '<span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Hal ' . $page . ' dari ' . $total_pages . '</span>';

        if ($page < $total_pages) {
            $html .= '<a href="' . $base . 'page=' . ($page + 1) . '" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-95 transition-all">Berikutnya ▶</a>';
        } else {
            $html .= '<span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 dark:text-gray-600 border border-gray-150 dark:border-gray-800 cursor-default">Berikutnya ▶</span>';
        }
        $html .= '</div>';
    } else {
        $html .= '<div class="flex items-center justify-center gap-1 order-1 sm:order-2">';

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
    }
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
    // 'chip' = design-system chip variant (see public/assets/css/dark-mode.css → .ds-chip--*)
    // 'color' / 'bg' kept for backward compatibility (used by JS-rendered hero gradient,
    // category icon backgrounds in product cards, etc). Those legacy hex values are
    // remapped to design tokens via attribute selectors in the stylesheet.
    return [
        'Akun'     => ['label' => 'Akun',     'chip' => 'accent',    'color' => '#1565C0', 'bg' => '#E3F2FD'],
        'Ebook'    => ['label' => 'Ebook',    'chip' => 'tertiary',  'color' => '#6A1B9A', 'bg' => '#F3E5F5'],
        'Game'     => ['label' => 'Game',     'chip' => 'warning',   'color' => '#E65100', 'bg' => '#FFF3E0'],
        'Software' => ['label' => 'Software', 'chip' => 'success',   'color' => '#2E7D32', 'bg' => '#E8F5E9'],
        'Template' => ['label' => 'Template', 'chip' => 'rose',      'color' => '#AD1457', 'bg' => '#FCE4EC'],
        'Lainnya'  => ['label' => 'Lainnya',  'chip' => 'neutral',   'color' => '#42B549', 'bg' => '#E8F5E9'],
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
    $variant = $cfg['chip'] ?? 'neutral';
    return '<span class="ds-chip ds-chip--' . $variant . '">' . e($cfg['label']) . '</span>';
}

// ============================================================
// TRANSACTION STATUS HELPERS
// ============================================================

function status_transaksi_list(): array
{
    return [
        'pending'   => ['label' => 'Pending',   'chip' => 'warning', 'color' => '#E65100', 'bg' => '#FFF3E0'],
        'success'   => ['label' => 'Success',   'chip' => 'success', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
        'failed'    => ['label' => 'Failed',    'chip' => 'danger',  'color' => '#C62828', 'bg' => '#FFEBEE'],
        'cancelled' => ['label' => 'Cancelled', 'chip' => 'neutral', 'color' => '#6B7280', 'bg' => '#F3F4F6'],
    ];
}

function status_transaksi_config(string $status): array
{
    $list = status_transaksi_list();
    return $list[$status] ?? ['label' => ucfirst($status), 'chip' => 'neutral', 'color' => '#374151', 'bg' => '#F3F4F6'];
}

function status_transaksi_badge(string $status): string
{
    $cfg = status_transaksi_config($status);
    $variant = $cfg['chip'] ?? 'neutral';
    return '<span class="ds-chip ds-chip--' . $variant . '">' . e($cfg['label']) . '</span>';
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

// ============================================================
// AKUN PRODUCT PRESETS
// ============================================================

/**
 * Fallback presets (used only if DB table doesn't exist yet).
 * In production, presets are managed via admin panel (akun_preset table).
 */
function akun_presets_fallback(): array
{
    return [
        'Lainnya' => [
            'label'  => 'Lainnya (custom)',
            'durasi' => ['1 Bulan', '3 Bulan', '6 Bulan', '12 Bulan', 'Lifetime'],
            'paket'  => [],
        ],
    ];
}

// ============================================================
// ENCRYPTION HELPERS (AES-256-CBC)
// ============================================================

/**
 * Encrypt a string using AES-256-CBC with the APP_KEY.
 * Returns base64-encoded ciphertext (iv + encrypted).
 */
function encrypt_value(string $plaintext): string
{
    $key = env('APP_KEY', '');
    if ($key === '' || $plaintext === '') return $plaintext;

    $key = hash('sha256', $key, true);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt a base64-encoded AES-256-CBC ciphertext.
 * Returns plaintext, or the original value if decryption fails (backward compat).
 */
function decrypt_value(string $ciphertext): string
{
    $key = env('APP_KEY', '');
    if ($key === '' || $ciphertext === '') return $ciphertext;

    $key = hash('sha256', $key, true);
    $data = base64_decode($ciphertext, true);
    if ($data === false || strlen($data) < 17) return $ciphertext; // Not encrypted (legacy)

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted !== false ? $decrypted : $ciphertext; // Fallback to raw if decrypt fails
}

/**
 * Decrypts structured or raw account info.
 * E.g., parses:
 * "Email: <encrypted_email>
 * Password: <encrypted_password>"
 * or a single encrypted string.
 */
function decrypt_account_info(string $account_info): string
{
    if (empty($account_info)) {
        return '';
    }

    $lines = explode("\n", $account_info);
    $decrypted_lines = [];

    foreach ($lines as $line) {
        $line = rtrim($line, "\r");
        if ($line === '') {
            $decrypted_lines[] = '';
            continue;
        }

        if (strpos($line, ':') !== false) {
            list($label, $value) = explode(':', $line, 2);
            $trimmed_value = trim($value);
            $decrypted_value = decrypt_value($trimmed_value);
            $decrypted_lines[] = $label . ': ' . $decrypted_value;
        } else {
            $decrypted_lines[] = decrypt_value($line);
        }
    }

    return implode("\n", $decrypted_lines);
}

