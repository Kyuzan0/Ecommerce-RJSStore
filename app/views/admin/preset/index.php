<div class="ds-page-header">
    <div class="ds-page-header__row">
        <div>
            <h1 class="ds-page-title">Kelola Preset Layanan</h1>
            <p class="ds-page-subtitle">Atur daftar durasi dan paket untuk produk tipe Akun</p>
        </div>
        <button onclick="openModal('tambah-preset')" class="ds-toolbar__action px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Preset
        </button>
    </div>
</div>

<?php if (empty($presets)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    <p class="text-gray-500 font-medium">Belum ada preset layanan</p>
    <p class="text-sm text-gray-400 mt-1">Tambahkan preset agar admin lebih mudah membuat produk akun</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($presets as $preset): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-gray-800"><?= e($preset['nama_layanan']) ?></h3>
            <div class="flex gap-1">
                <button onclick='openEditPreset(<?= $preset["id"] ?>, <?= htmlspecialchars(json_encode($preset["nama_layanan"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(implode(", ", $preset["durasi_arr"])), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(implode(", ", $preset["paket_arr"])), ENT_QUOTES) ?>)' class="p-1.5 rounded-lg hover:bg-yellow-50 text-yellow-600 transition" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <form method="POST" class="inline" onsubmit="return confirm('Hapus preset ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" name="preset_id" value="<?= $preset['id'] ?>">
                    <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="space-y-2">
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Durasi</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    <?php foreach ($preset['durasi_arr'] as $d): ?>
                    <span class="text-xs px-2 py-0.5 rounded-lg bg-green-50 text-green-700 font-medium"><?= e($d) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (!empty($preset['paket_arr'])): ?>
            <div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Paket</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    <?php foreach ($preset['paket_arr'] as $p): ?>
                    <span class="text-xs px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-medium"><?= e($p) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Tambah Preset -->
<div id="modal-tambah-preset" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah-preset')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-lg pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800 truncate">Tambah Preset Layanan</h2>
                <button onclick="closeModal('tambah-preset')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex-shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Layanan</label>
                        <input type="text" name="nama_layanan" placeholder="Contoh: Spotify, Netflix, dll" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Durasi (pisahkan dengan koma)</label>
                        <input type="text" name="durasi" placeholder="1 Bulan, 3 Bulan, 6 Bulan, 12 Bulan" required>
                        <p class="text-xs text-gray-400 mt-1">Contoh: 1 Bulan, 3 Bulan, 6 Bulan, 12 Bulan, Lifetime</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Paket (pisahkan dengan koma, kosongkan jika tidak ada)</label>
                        <input type="text" name="paket" placeholder="Individual, Family, Student">
                        <p class="text-xs text-gray-400 mt-1">Contoh: Individual, Duo, Family, Student. Kosongkan jika layanan tidak punya tier paket.</p>
                    </div>
                </div>
                <div class="ds-modal-footer">
                    <button type="button" onclick="closeModal('tambah-preset')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Preset -->
<div id="modal-edit-preset" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('edit-preset')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-lg pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-800 truncate">Edit Preset Layanan</h2>
                <button onclick="closeModal('edit-preset')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex-shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="preset_id" id="edit-preset-id">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Layanan</label>
                        <input type="text" name="nama_layanan" id="edit-preset-nama" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Durasi (pisahkan dengan koma)</label>
                        <input type="text" name="durasi" id="edit-preset-durasi" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Paket (pisahkan dengan koma, kosongkan jika tidak ada)</label>
                        <input type="text" name="paket" id="edit-preset-paket">
                    </div>
                </div>
                <div class="ds-modal-footer">
                    <button type="button" onclick="closeModal('edit-preset')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan</button>
                </div>
            </form>
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
    setTimeout(function(){
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
}
function openEditPreset(id, nama, durasi, paket) {
    document.getElementById('edit-preset-id').value = id;
    document.getElementById('edit-preset-nama').value = nama;
    document.getElementById('edit-preset-durasi').value = durasi;
    document.getElementById('edit-preset-paket').value = paket;
    openModal('edit-preset');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('tambah-preset');
        closeModal('edit-preset');
    }
});
</script>
