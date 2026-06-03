<!-- Page Header: Title, Stats, and Filters (All merged in 1 row, no cards) -->
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-6">
    <!-- Title & Simple Stats -->
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex-shrink-0">Log Aktivitas</h1>
        <div class="hidden sm:flex items-center gap-3 text-xs text-gray-400 dark:text-gray-500 border-l border-gray-200 dark:border-gray-700 pl-3">
            <span>Total Log: <strong class="text-gray-700 dark:text-gray-300"><?= number_format($summary_stats['total']) ?></strong></span>
            <span class="text-gray-350 dark:text-gray-700">•</span>
            <span>Login: <strong class="text-emerald-600 dark:text-emerald-450"><?= number_format($summary_stats['login_today']) ?></strong></span>
            <span class="text-gray-350 dark:text-gray-700">•</span>
            <span>Checkout: <strong class="text-purple-600 dark:text-purple-450"><?= number_format($summary_stats['checkout_today']) ?></strong></span>
            <span class="text-gray-350 dark:text-gray-700">•</span>
            <span>Failed: <strong class="text-rose-600 dark:text-rose-455"><?= number_format($summary_stats['failed_login']) ?></strong></span>
        </div>
    </div>
    
    <!-- Search & Advanced Filter Form (Borderless, flat, and 1 row) -->
    <form method="GET" action="<?= url('/admin-activity-log') ?>" class="flex flex-row flex-wrap items-center gap-2 lg:justify-end w-full lg:w-auto">
        <!-- Search Global -->
        <div class="relative w-44 sm:w-56">
            <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari user, IP, aksi..." class="w-full pl-8 pr-7 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-xs focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition dark:text-gray-200">
            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <?php if ($filters['search'] !== ''): ?>
                <a href="<?= url('/admin-activity-log') . '?' . http_build_query(array_merge($filters, ['search' => ''])) ?>" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-650">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Role -->
        <select name="role" onchange="this.form.submit()" class="px-2 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer">
            <option value="">Role: Semua</option>
            <option value="admin" <?= $filters['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="customer" <?= $filters['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
            <option value="system" <?= $filters['role'] === 'system' ? 'selected' : '' ?>>System</option>
        </select>
        
        <!-- Aksi (Action) -->
        <select name="action" onchange="this.form.submit()" class="px-2 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer max-w-[120px]">
            <option value="">Aksi: Semua</option>
            <?php foreach ($distinct_actions as $act): ?>
                <option value="<?= e($act) ?>" <?= $filters['action'] === $act ? 'selected' : '' ?>><?= e($act) ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Tanggal (Date) -->
        <select name="date" onchange="this.form.submit()" class="px-2 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer">
            <option value="">Tanggal: Semua</option>
            <option value="today" <?= $filters['date'] === 'today' ? 'selected' : '' ?>>Hari Ini</option>
            <option value="week" <?= $filters['date'] === 'week' ? 'selected' : '' ?>>7 Hari Terakhir</option>
            <option value="month" <?= $filters['date'] === 'month' ? 'selected' : '' ?>>30 Hari Terakhir</option>
        </select>
        
        <!-- IP -->
        <select name="ip" onchange="this.form.submit()" class="px-2 py-1.5 border border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 outline-none focus:ring-2 focus:ring-green-500 cursor-pointer max-w-[120px]">
            <option value="">IP: Semua</option>
            <?php foreach ($distinct_ips as $ip_addr): ?>
                <option value="<?= e($ip_addr) ?>" <?= $filters['ip'] === $ip_addr ? 'selected' : '' ?>><?= e($ip_addr) ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Reset Filter Button -->
        <?php if (array_filter($filters) !== []): ?>
            <a href="<?= url('/admin-activity-log') ?>" class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline px-1">
                Reset
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Security Status Bar (Flat, borderless) -->
<div class="mb-2 p-4 rounded-xl flex items-center justify-between gap-3 transition-colors <?php echo $summary_stats['failed_login'] > 0 ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'; ?>">
    <div class="flex items-center gap-3">
        <?php if ($summary_stats['failed_login'] > 0): ?>
            <!-- Warning Shield Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-xs font-semibold">
                Status Keamanan: Terdeteksi <?= number_format($summary_stats['failed_login']) ?> kali percobaan login gagal di sistem log.
            </div>
        <?php else: ?>
            <!-- Success Shield Icon -->
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div class="text-xs font-semibold">
                Status Keamanan: Sistem aman dan aktif. Tidak ada aktivitas mencurigakan yang terdeteksi hari ini.
            </div>
        <?php endif; ?>
    </div>
    <span class="text-[10px] uppercase font-bold tracking-wider px-2.5 py-0.5 rounded-full <?php echo $summary_stats['failed_login'] > 0 ? 'bg-rose-500/20' : 'bg-emerald-500/20'; ?>">
        <?php echo $summary_stats['failed_login'] > 0 ? 'Perhatian' : 'Aman'; ?>
    </span>
</div>

<?php if (empty($logs)): ?>
<div class="p-12 text-center">
    <p class="text-gray-500 dark:text-gray-400">Belum ada aktivitas tercatat.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden mt-2 shadow-sm">
    <div class="ds-table-wrap ds-table-wrap--wide">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="w-[20%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                    <th class="w-[15%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                    <th class="w-[12%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="w-[20%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    <th class="w-[18%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</th>
                    <th class="w-[15%] px-5 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Severity</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($logs as $log): ?>
                <?php
                // Get role details
                $role = $log['user_role'] ?? ($log['user_id'] == 0 ? 'System' : 'Unknown');
                $roleVariants = [
                    'admin'    => 'rose',
                    'customer' => 'neutral',
                    'system'   => 'accent',
                ];
                $roleVariant = $roleVariants[strtolower($role)] ?? 'neutral';

                // Get action variant
                $actionVariants = [
                    'login'                   => 'success',
                    'login_failed'            => 'danger',
                    'register'                => 'accent',
                    'checkout'                => 'tertiary',
                    'tambah_produk'           => 'accent',
                    'hapus_produk'            => 'rose',
                    'tambah_user'             => 'accent',
                    'update_user'             => 'warning',
                    'hapus_user'              => 'rose',
                    'update_status_transaksi' => 'tertiary',
                    'update_profile'          => 'warning',
                    'change_password'         => 'warning',
                    'tambah_preset'           => 'success',
                    'resolve_garansi'         => 'tertiary',
                    'rating_produk'           => 'warning',
                ];
                $badgeVariant = $actionVariants[$log['action']] ?? 'neutral';

                // Get severity
                $sev = ActivityLog::getSeverity($log['action']);

                // Prepare JSON data for detailed modal view
                $logJson = htmlspecialchars(json_encode([
                    'user'           => $log['user_name'],
                    'role'           => ucfirst($role),
                    'role_variant'   => $roleVariant,
                    'action'         => $log['action'],
                    'action_variant' => $badgeVariant,
                    'target'         => ($log['target_type'] ?? '') . ($log['target_id'] ? ' #' . $log['target_id'] : ''),
                    'ip'             => $log['ip_address'] ?? '-',
                    'time'           => date('d M Y H:i:s', strtotime($log['created_at'])),
                    'severity'       => $sev['label'],
                    'severity_color' => $sev['color'],
                    'detail'         => $log['detail'] ?? '-'
                ]), ENT_QUOTES, 'UTF-8');
                ?>
                 <tr onclick="openLogModal(<?= $logJson ?>)" class="hover:bg-gray-50 dark:hover:bg-gray-750/50 transition cursor-pointer">
                    <!-- Column 1: Waktu -->
                    <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300 font-medium align-middle">
                        <?= e(date('d M Y H:i', strtotime($log['created_at']))) ?>
                    </td>
                    <!-- Column 2: Pengguna -->
                    <td class="px-5 py-3 whitespace-nowrap text-sm font-semibold text-gray-800 dark:text-gray-150 align-middle">
                        <?= e($log['user_name']) ?>
                    </td>
                    <!-- Column 3: Role -->
                    <td class="px-5 py-3 whitespace-nowrap align-middle">
                        <span class="ds-chip ds-chip--<?= $roleVariant ?> rounded-full">
                            <?= e(ucfirst($role)) ?>
                        </span>
                    </td>
                    <!-- Column 4: Aksi -->
                    <td class="px-5 py-4 whitespace-nowrap align-middle">
                        <span class="ds-chip ds-chip--<?= $badgeVariant ?>"><?= e($log['action']) ?></span>
                    </td>
                    <!-- Column 5: IP Address -->
                    <td class="px-5 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-mono align-middle">
                        <?= e($log['ip_address'] ?? '-') ?>
                    </td>
                    <!-- Column 6: Severity -->
                    <td class="px-5 py-4 whitespace-nowrap align-middle">
                        <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-<?= $sev['color'] ?>-600 dark:text-<?= $sev['color'] ?>-400">
                            <span class="w-1.5 h-1.5 rounded-full <?= $sev['bg'] ?>"></span>
                            <?= $sev['label'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Custom Pagination Footer -->
    <?php
    $page = $paging['page'];
    $total_pages = $paging['total_pages'];
    $query_params = $_GET;
    unset($query_params['page']);
    $base = '?' . (empty($query_params) ? '' : http_build_query($query_params) . '&');
    ?>
    <div class="border-t border-gray-100 px-5 py-2 flex flex-col sm:flex-row items-center justify-between gap-4 mt-auto">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span>Menampilkan <?= $paging['offset'] + 1 ?>–<?= min($paging['offset'] + $paging['per_page'], $paging['total']) ?> dari <?= $paging['total'] ?> aktivitas</span>
            <span class="hidden sm:inline text-gray-300 dark:text-gray-700">|</span>
            <div class="flex items-center gap-1.5">
                <span>Tampilkan:</span>
                <select onchange="location.href = this.value" class="px-2 py-1 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-[11px] font-semibold text-gray-700 dark:text-gray-300 outline-none cursor-pointer focus:ring-1 focus:ring-green-500">
                    <?php foreach ([10, 50, 100] as $l): 
                        $params = $_GET;
                        $params['limit'] = $l;
                        $params['page'] = 1; // reset page when changing limit
                        $url = '?' . http_build_query($params);
                        $isSelected = ($paging['per_page'] === $l) ? 'selected' : '';
                    ?>
                        <option value="<?= $url ?>" <?= $isSelected ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <?php if ($page > 1): ?>
                <a href="<?= $base ?>page=<?= $page - 1 ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-95 transition-all">
                    ◀ Sebelumnya
                </a>
            <?php else: ?>
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 dark:text-gray-600 border border-gray-150 dark:border-gray-800 cursor-default">
                    ◀ Sebelumnya
                </span>
            <?php endif; ?>
            
            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">
                Hal <?= $page ?> dari <?= $total_pages ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
                <a href="<?= $base ?>page=<?= $page + 1 ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-95 transition-all">
                    Berikutnya ▶
                </a>
            <?php else: ?>
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 dark:text-gray-600 border border-gray-150 dark:border-gray-800 cursor-default">
                    Berikutnya ▶
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Detail Modal -->
<div id="modal-log-detail" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeLogModal()"></div>
    <div class="relative flex items-center justify-center min-h-full p-4 pointer-events-none">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 w-full max-w-lg pointer-events-auto modal-content overflow-hidden relative flex flex-col max-h-[90vh]" style="transform:scale(0.95);opacity:0;transition:transform 0.25s ease-out, opacity 0.2s">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white text-lg">Detail Aktivitas</h3>
                <button onclick="closeLogModal()" class="text-gray-450 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-3 gap-y-3 text-sm">
                    <div class="text-gray-400 dark:text-gray-550 font-medium">User</div>
                    <div class="col-span-2 text-gray-850 dark:text-gray-100 font-semibold" id="detail-user"></div>

                    <div class="text-gray-400 dark:text-gray-550 font-medium">Role</div>
                    <div class="col-span-2" id="detail-role"></div>

                    <div class="text-gray-400 dark:text-gray-550 font-medium">Aksi</div>
                    <div class="col-span-2" id="detail-aksi"></div>

                    <div class="text-gray-400 dark:text-gray-550 font-medium">Target</div>
                    <div class="col-span-2 text-gray-800 dark:text-gray-200 font-semibold" id="detail-target"></div>

                    <div class="text-gray-400 dark:text-gray-550 font-medium">IP Address</div>
                    <div class="col-span-2 font-mono text-gray-700 dark:text-gray-300" id="detail-ip"></div>

                    <div class="text-gray-400 dark:text-gray-550 font-medium">Timestamp</div>
                    <div class="col-span-2 text-gray-700 dark:text-gray-300" id="detail-time"></div>
                    
                    <div class="text-gray-400 dark:text-gray-550 font-medium">Severity</div>
                    <div class="col-span-2" id="detail-severity"></div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Detail Pesan</div>
                    <div class="bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-850 rounded-xl p-4 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-all leading-relaxed" id="detail-msg"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openLogModal(data) {
    document.getElementById('detail-user').textContent = data.user;
    
    // Role chip
    document.getElementById('detail-role').innerHTML = '<span class="ds-chip ds-chip--' + data.role_variant + ' rounded-full">' + data.role + '</span>';
    
    // Action chip
    document.getElementById('detail-aksi').innerHTML = '<span class="ds-chip ds-chip--' + data.action_variant + '">' + data.action + '</span>';
    
    document.getElementById('detail-target').textContent = data.target || '-';
    document.getElementById('detail-ip').textContent = data.ip;
    document.getElementById('detail-time').textContent = data.time;
    
    // Severity indicator
    document.getElementById('detail-severity').innerHTML = '<span class="inline-flex items-center gap-1.5 font-semibold text-xs text-' + data.severity_color + '-600 dark:text-' + data.severity_color + '-400"><span class="w-1.5 h-1.5 rounded-full bg-' + data.severity_color + '-500"></span>' + data.severity + '</span>';
    
    document.getElementById('detail-msg').textContent = data.detail;

    var modal = document.getElementById('modal-log-detail');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function() {
        var c = modal.querySelector('.modal-content');
        c.style.transform = 'scale(1)';
        c.style.opacity = '1';
    });
}

function closeLogModal() {
    var modal = document.getElementById('modal-log-detail');
    var c = modal.querySelector('.modal-content');
    c.style.transform = 'scale(0.95)';
    c.style.opacity = '0';
    setTimeout(function() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLogModal();
    }
});
</script>
