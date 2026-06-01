<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Audit Log</h1>
    <p class="text-sm text-gray-500 mt-1">Riwayat semua aksi admin di sistem</p>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Admin</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Target</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><?= e(date('d M Y H:i', strtotime($log['created_at']))) ?></td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= e($log['admin_name']) ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $actionColors = [
                            'login'          => 'bg-green-50 text-green-700',
                            'login_failed'   => 'bg-red-50 text-red-700',
                            'tambah_produk'  => 'bg-blue-50 text-blue-700',
                            'hapus_produk'   => 'bg-red-50 text-red-700',
                            'tambah_user'    => 'bg-blue-50 text-blue-700',
                            'update_user'    => 'bg-yellow-50 text-yellow-700',
                            'hapus_user'     => 'bg-red-50 text-red-700',
                            'update_status_transaksi' => 'bg-purple-50 text-purple-700',
                            'update_profile' => 'bg-yellow-50 text-yellow-700',
                            'change_password'=> 'bg-orange-50 text-orange-700',
                            'tambah_preset'  => 'bg-blue-50 text-blue-700',
                            'resolve_garansi'=> 'bg-purple-50 text-purple-700',
                        ];
                        $badgeClass = $actionColors[$log['action']] ?? 'bg-gray-50 text-gray-700';
                        ?>
                        <span class="text-xs font-bold px-2 py-1 rounded-lg <?= $badgeClass ?>"><?= e($log['action']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600"><?= e(($log['target_type'] ?? '') . ($log['target_id'] ? ' #' . $log['target_id'] : '')) ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate"><?= e($log['detail'] ?? '-') ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?= e($log['ip_address'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= pagination_render($paging) ?>
</div>
<?php endif; ?>
