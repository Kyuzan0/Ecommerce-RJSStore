<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Klaim Garansi</h1>
    <p class="text-sm text-gray-500 mt-1">Kelola klaim garansi dari pelanggan</p>
</div>

<div class="flex gap-2 mb-4">
    <a href="<?= url('/admin-garansi') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg <?= empty($current_status) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">Semua</a>
    <a href="<?= url('/admin-garansi?status=pending') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg <?= $current_status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600' ?>">Pending</a>
    <a href="<?= url('/admin-garansi?status=approved') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg <?= $current_status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">Approved</a>
    <a href="<?= url('/admin-garansi?status=rejected') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg <?= $current_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' ?>">Rejected</a>
</div>

<?php if (empty($claims)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <p class="text-gray-500">Tidak ada klaim garansi.</p>
</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($claims as $klaim): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-bold text-gray-800"><?= e($klaim['nama_user']) ?></span>
                    <span class="text-xs text-gray-400"><?= e($klaim['email']) ?></span>
                </div>
                <p class="text-sm text-gray-700 mb-1"><strong>Produk:</strong> <?= e($klaim['nama_produk']) ?> <?= !empty($klaim['durasi']) ? '(' . e($klaim['durasi'] . (!empty($klaim['paket']) ? ' ' . $klaim['paket'] : '')) . ')' : '' ?></p>
                <p class="text-sm text-gray-600 mb-2"><strong>Alasan:</strong> <?= e($klaim['alasan']) ?></p>
                <p class="text-xs text-gray-400"><?= format_tanggal($klaim['created_at']) ?></p>
            </div>
            <div class="text-right">
                <?php if ($klaim['status'] === 'pending'): ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-orange-100 text-orange-700">Pending</span>
                    <form method="POST" class="mt-3 space-y-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="klaim_id" value="<?= $klaim['id'] ?>">
                        <input type="text" name="admin_note" placeholder="Catatan (opsional)" class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg">
                        <div class="flex gap-2">
                            <button name="action" value="approve" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition">Setujui</button>
                            <button name="action" value="reject" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">Tolak</button>
                        </div>
                    </form>
                <?php elseif ($klaim['status'] === 'approved'): ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-green-100 text-green-700">Disetujui</span>
                <?php else: ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-red-100 text-red-700">Ditolak</span>
                <?php endif; ?>
                <?php if (!empty($klaim['admin_note'])): ?>
                    <p class="text-xs text-gray-500 mt-2 italic"><?= e($klaim['admin_note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= pagination_render($paging) ?>
<?php endif; ?>
