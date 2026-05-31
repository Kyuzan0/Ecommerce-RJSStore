<nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/customer/dashboard') ?>" class="hover:text-green-600">Dashboard</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Klaim Garansi</li>
    </ol>
</nav>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Klaim Garansi</h1>
    <p class="text-sm text-gray-500 mt-1">Ajukan klaim jika akun yang dibeli bermasalah dalam masa garansi</p>
</div>

<?php if (!empty($claims)): ?>
<div class="space-y-4 mb-8">
    <?php foreach ($claims as $klaim): ?>
    <div class="bg-white rounded-lg shadow-md p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="font-semibold text-gray-800"><?= e($klaim['nama_produk']) ?> <?= !empty($klaim['durasi']) ? '<span class="text-sm text-gray-500">(' . e($klaim['durasi']) . ')</span>' : '' ?></p>
                <p class="text-sm text-gray-600 mt-1"><?= e($klaim['alasan']) ?></p>
                <p class="text-xs text-gray-400 mt-2"><?= format_tanggal($klaim['created_at']) ?></p>
            </div>
            <div>
                <?php if ($klaim['status'] === 'pending'): ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-orange-100 text-orange-700">Menunggu</span>
                <?php elseif ($klaim['status'] === 'approved'): ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-green-100 text-green-700">Disetujui</span>
                <?php else: ?>
                    <span class="text-xs font-bold px-2 py-1 rounded-lg bg-red-100 text-red-700">Ditolak</span>
                <?php endif; ?>
                <?php if (!empty($klaim['admin_note'])): ?>
                    <p class="text-xs text-gray-500 mt-2"><?= e($klaim['admin_note']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="bg-white rounded-lg shadow-md p-8 text-center mb-8">
    <p class="text-gray-500">Belum ada klaim garansi.</p>
</div>
<?php endif; ?>
