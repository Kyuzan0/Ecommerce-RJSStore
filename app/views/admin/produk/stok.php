<!-- Breadcrumb -->
<nav class="mb-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/admin-produk') ?>" class="hover:text-green-600">Produk</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Kelola Stok</li>
    </ol>
</nav>

<div class="ds-page-header">
    <div class="ds-page-header__row">
        <div class="min-w-0">
            <h1 class="ds-page-title truncate"><?= e($varian['nama_produk']) ?></h1>
            <p class="ds-page-subtitle">Varian: <span class="font-semibold"><?= e($varian_label) ?></span> — <?= rupiah($varian['harga']) ?></p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-sm font-bold px-3 py-1.5 rounded-lg" style="background:#E8F5E9; color:#2E7D32">Tersedia: <?= $available ?></span>
            <span class="text-sm font-medium px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600">Total: <?= count($stocks) ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
    <!-- Left: Add Stock -->
    <div class="lg:col-span-1 space-y-4">
        <!-- Single Add -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Tambah Satu Akun</h3>
            <form method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah_single">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Email / Username</label>
                    <input type="text" name="account_email" placeholder="akun@email.com" required class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                    <input type="text" name="account_password" placeholder="password" required class="w-full">
                </div>
                <button type="submit" class="w-full py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">+ Tambah Stok</button>
            </form>
        </div>

        <!-- Bulk Add -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 text-sm mb-3">Tambah Bulk (Paste)</h3>
            <form method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah_bulk">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Data Akun</label>
                    <textarea name="bulk_data" rows="8" placeholder="Format: email:password (satu per baris)&#10;&#10;Contoh:&#10;user1@gmail.com:pass123&#10;user2@gmail.com:pass456&#10;user3@gmail.com:pass789" required class="w-full"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Separator yang didukung: <code>:</code> <code>|</code> atau tab</p>
                </div>
                <button type="submit" class="w-full py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Tambah Bulk</button>
            </form>
        </div>
    </div>

    <!-- Right: Stock List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">Daftar Stok (<?= count($stocks) ?>)</h3>
            </div>
            <?php if (empty($stocks)): ?>
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-500 text-sm">Belum ada stok. Tambahkan akun di atas.</p>
            </div>
            <?php else: ?>
            <!-- Desktop table -->
            <div class="ds-table-wrap max-h-[600px] overflow-y-auto hidden md:block">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Password</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach ($stocks as $stok): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700"><?= e($stok['account_email']) ?></td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700"><?= e($stok['account_password']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($stok['status'] === 'available'): ?>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg" style="background:#E8F5E9; color:#2E7D32">Tersedia</span>
                                <?php else: ?>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg" style="background:#FFEBEE; color:#C62828">Terjual</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($stok['status'] === 'available'): ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Hapus stok ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="hapus_stok">
                                    <input type="hidden" name="stok_id" value="<?= $stok['id'] ?>">
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </form>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Mobile card list -->
            <div class="md:hidden p-3 space-y-2 max-h-[600px] overflow-y-auto">
                <?php foreach ($stocks as $stok): ?>
                <div class="bg-gray-50/50 border border-gray-100 rounded-xl p-3">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="font-mono text-xs text-gray-700 break-all"><?= e($stok['account_email']) ?></p>
                        <?php if ($stok['status'] === 'available'): ?>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-lg flex-shrink-0" style="background:#E8F5E9; color:#2E7D32">Tersedia</span>
                        <?php else: ?>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-lg flex-shrink-0" style="background:#FFEBEE; color:#C62828">Terjual</span>
                        <?php endif; ?>
                    </div>
                    <p class="font-mono text-xs text-gray-500 break-all mb-2"><?= e($stok['account_password']) ?></p>
                    <?php if ($stok['status'] === 'available'): ?>
                    <form method="POST" class="text-right" onsubmit="return confirm('Hapus stok ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="hapus_stok">
                        <input type="hidden" name="stok_id" value="<?= $stok['id'] ?>">
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Hapus</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
