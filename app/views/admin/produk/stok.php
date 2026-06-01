<!-- Breadcrumb -->
<nav class="mb-4">
    <ol class="flex items-center space-x-2 text-sm text-gray-600">
        <li><a href="<?= url('/admin-produk') ?>" class="hover:text-green-600">Produk</a></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-800">Kelola Stok</li>
    </ol>
</nav>

<!-- Header with variant switcher -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-800"><?= e($varian['nama_produk']) ?></h1>
        <div class="flex items-center gap-3 mt-2">
            <!-- Variant Switcher Dropdown -->
            <select onchange="if(this.value) window.location.href=this.value" class="text-sm px-3 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 outline-none focus:border-green-500 cursor-pointer font-medium">
                <?php foreach ($all_variants as $av): ?>
                <option value="<?= url('/admin-produk/stok/' . (int)$av['id']) ?>" <?= (int)$av['id'] === (int)$varian['id'] ? 'selected' : '' ?>>
                    <?= e($av['label']) ?> — <?= rupiah($av['harga']) ?> (<?= $av['stok_count'] ?> stok)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <span class="text-sm font-bold px-3 py-1.5 rounded-lg" style="background:#E8F5E9; color:#2E7D32">Tersedia: <?= $available ?></span>
        <span class="text-sm font-medium px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600">Total: <?= count($stocks) ?></span>
        <button onclick="openModal('tambah-stok')" class="px-4 py-2 text-white text-sm font-semibold rounded-lg hover:opacity-90 transition" style="background:#42B549">+ Tambah Stok</button>
    </div>
</div>

<!-- Stock List -->
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <?php if (empty($stocks)): ?>
    <div class="p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <p class="text-gray-500 text-sm">Belum ada stok. Klik "+ Tambah Stok" untuk menambahkan akun.</p>
    </div>
    <?php else: ?>
    <div class="max-h-[60vh] overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email / Username</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Password</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($stocks as $stok): ?>
                <tr class="hover:bg-gray-50 transition">
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
    <?php endif; ?>
</div>

<!-- Modal Tambah Stok -->
<div id="modal-tambah-stok" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah-stok')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-lg pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h2 class="font-bold text-gray-800">Tambah Stok Akun</h2>
                <button onclick="closeModal('tambah-stok')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <!-- Tabs: Single vs Bulk -->
                <div class="flex gap-2 mb-5">
                    <button type="button" id="tab-single" onclick="switchTab('single')" class="px-4 py-2 text-sm font-semibold rounded-lg bg-green-100 text-green-700">Satu Akun</button>
                    <button type="button" id="tab-bulk" onclick="switchTab('bulk')" class="px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-600">Bulk Paste</button>
                </div>

                <!-- Single Form -->
                <form method="POST" id="form-single" class="space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="tambah_single">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Email / Username</label>
                        <input type="text" name="account_email" placeholder="akun@email.com" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                        <input type="text" name="account_password" placeholder="password" required>
                    </div>
                    <button type="submit" class="w-full py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">+ Tambah Stok</button>
                </form>

                <!-- Bulk Form -->
                <form method="POST" id="form-bulk" class="space-y-3 hidden">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="tambah_bulk">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Data Akun</label>
                        <textarea name="bulk_data" rows="8" placeholder="Format: email:password (satu per baris)&#10;&#10;Contoh:&#10;user1@gmail.com:pass123&#10;user2@gmail.com:pass456&#10;user3@gmail.com:pass789" required></textarea>
                        <p class="text-xs text-gray-400 mt-1">Separator: <code>:</code> <code>|</code> atau tab</p>
                    </div>
                    <button type="submit" class="w-full py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Tambah Bulk</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(type) {
    var modal = document.getElementById('modal-' + type);
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function(){
        var c = modal.querySelector('.modal-content');
        c.style.transform = 'scale(1)';
        c.style.opacity = '1';
    });
}
function closeModal(type) {
    var modal = document.getElementById('modal-' + type);
    var c = modal.querySelector('.modal-content');
    c.style.transform = 'scale(0.95)';
    c.style.opacity = '0';
    setTimeout(function(){ modal.classList.add('hidden'); document.body.style.overflow = ''; }, 200);
}
function switchTab(tab) {
    var single = document.getElementById('form-single');
    var bulk = document.getElementById('form-bulk');
    var tabSingle = document.getElementById('tab-single');
    var tabBulk = document.getElementById('tab-bulk');
    if (tab === 'single') {
        single.classList.remove('hidden');
        bulk.classList.add('hidden');
        tabSingle.className = 'px-4 py-2 text-sm font-semibold rounded-lg bg-green-100 text-green-700';
        tabBulk.className = 'px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-600';
    } else {
        single.classList.add('hidden');
        bulk.classList.remove('hidden');
        tabBulk.className = 'px-4 py-2 text-sm font-semibold rounded-lg bg-blue-100 text-blue-700';
        tabSingle.className = 'px-4 py-2 text-sm font-semibold rounded-lg bg-gray-100 text-gray-600';
    }
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal('tambah-stok'); });
</script>
