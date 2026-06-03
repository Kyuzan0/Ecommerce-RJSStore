<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Log Aktivitas</h1>
</div>

<!-- Filter Chips -->
<div class="ds-filter-row mb-6">
    <a href="<?= url('/admin-activity-log') ?>" class="ds-chip ds-chip--neutral ds-chip--filter <?= $current_role === '' ? 'is-active' : '' ?>">
        Semua <span class="ds-chip__count"><?= $role_counts['all'] ?></span>
    </a>
    <a href="<?= url('/admin-activity-log') . '?role=admin' ?>" class="ds-chip ds-chip--rose ds-chip--filter <?= $current_role === 'admin' ? 'is-active' : '' ?>">
        Admin <span class="ds-chip__count"><?= $role_counts['admin'] ?></span>
    </a>
    <a href="<?= url('/admin-activity-log') . '?role=customer' ?>" class="ds-chip ds-chip--neutral ds-chip--filter <?= $current_role === 'customer' ? 'is-active' : '' ?>">
        Customer <span class="ds-chip__count"><?= $role_counts['customer'] ?></span>
    </a>
    <a href="<?= url('/admin-activity-log') . '?role=system' ?>" class="ds-chip ds-chip--accent ds-chip--filter <?= $current_role === 'system' ? 'is-active' : '' ?>">
        System <span class="ds-chip__count"><?= $role_counts['system'] ?></span>
    </a>
</div>

<?php if (empty($logs)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <p class="text-gray-500">Belum ada aktivitas tercatat.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="max-h-[65vh] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pengguna</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Target</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= e($log['user_name']) ?></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <?php
                        $role = $log['user_role'] ?? ($log['user_id'] == 0 ? 'System' : 'Unknown');
                        $roleVariants = [
                            'admin'    => 'rose',
                            'customer' => 'neutral',
                            'system'   => 'accent',
                        ];
                        $roleVariant = $roleVariants[strtolower($role)] ?? 'neutral';
                        ?>
                        <span class="ds-chip ds-chip--<?= $roleVariant ?> rounded-full">
                            <?= e(ucfirst($role)) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php
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
                        ?>
                        <span class="ds-chip ds-chip--<?= $badgeVariant ?>"><?= e($log['action']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600"><?= e(($log['target_type'] ?? '') . ($log['target_id'] ? ' #' . $log['target_id'] : '')) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="<?= e($log['detail'] ?? '-') ?>"><?= e($log['detail'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?= e($log['ip_address'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination_render($paging) ?>
</div>
<?php endif; ?>
