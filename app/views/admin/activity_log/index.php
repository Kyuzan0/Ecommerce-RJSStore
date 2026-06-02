<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Log Aktivitas</h1>
    <p class="text-sm text-gray-500 mt-1">Riwayat semua aktivitas pengguna dan sistem</p>
</div>

<?php if (empty($logs)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <p class="text-gray-500">Belum ada aktivitas tercatat.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="max-h-[70vh] overflow-y-auto">
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
                        $roleColors = [
                            'admin'    => 'bg-rose-50 text-rose-700 border border-rose-200',
                            'customer' => 'bg-gray-50 text-gray-700 border border-gray-200',
                            'system'   => 'bg-blue-50 text-blue-700 border border-blue-200',
                        ];
                        $roleClass = $roleColors[strtolower($role)] ?? 'bg-gray-50 text-gray-700 border border-gray-200';
                        ?>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium inline-flex items-center <?= $roleClass ?>">
                            <?= e(ucfirst($role)) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $actionColors = [
                            'login'                   => 'bg-green-50 text-green-700 border border-green-200',
                            'login_failed'            => 'bg-red-50 text-red-700 border border-red-200',
                            'register'                => 'bg-blue-50 text-blue-700 border border-blue-200',
                            'checkout'                => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                            'tambah_produk'           => 'bg-teal-50 text-teal-700 border border-teal-200',
                            'hapus_produk'            => 'bg-rose-50 text-rose-700 border border-rose-200',
                            'tambah_user'             => 'bg-sky-50 text-sky-700 border border-sky-200',
                            'update_user'             => 'bg-amber-50 text-amber-700 border border-amber-200',
                            'hapus_user'              => 'bg-rose-50 text-rose-700 border border-rose-200',
                            'update_status_transaksi' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                            'update_profile'          => 'bg-amber-50 text-amber-700 border border-amber-200',
                            'change_password'         => 'bg-orange-50 text-orange-700 border border-orange-200',
                            'tambah_preset'           => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                            'resolve_garansi'         => 'bg-violet-50 text-violet-700 border border-violet-200',
                        ];
                        $badgeClass = $actionColors[$log['action']] ?? 'bg-gray-50 text-gray-700 border border-gray-200';
                        ?>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-lg inline-flex items-center <?= $badgeClass ?>"><?= e($log['action']) ?></span>
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
