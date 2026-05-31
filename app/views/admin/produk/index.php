<div class="flex items-center gap-4 mb-2">
    <form method="GET" action="" class="relative flex-1">
        <?php if (isset($_GET['tipe'])): ?><input type="hidden" name="tipe" value="<?= e($_GET['tipe']) ?>"><?php endif; ?>
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama produk atau deskripsi..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
        <?php if ($search !== ''): ?>
        <a href="<?= url('/admin-produk') . (isset($_GET['tipe']) ? '?tipe=' . e($_GET['tipe']) : '') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <?php endif; ?>
    </form>
    <button onclick="openTambahModal()" class="inline-flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition shrink-0" style="background:#42B549">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Produk
    </button>
</div>
<?php
$q_param = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="flex items-center gap-2 mb-3 flex-wrap">
    <a href="<?= url('/admin-produk') . '?' . ltrim($q_param, '&') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_tipe === '' ? 'ring-2 ring-offset-1 ring-gray-300' : 'hover:opacity-80' ?>" style="color:#374151; background:#E5E7EB">Semua <span class="ml-1 opacity-70"><?= $total_produk ?></span></a>
    <?php foreach (tipe_produk_list() as $key => $cfg): $count = $tipe_counts[$key] ?? 0; ?>
    <a href="<?= url('/admin-produk') . '?tipe=' . urlencode($key) . $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_tipe === $key ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:<?= $cfg['color'] ?>; background:<?= $cfg['bg'] ?>; <?= $current_tipe === $key ? 'ring-color:'.$cfg['color'] : '' ?>"><?= e($cfg['label']) ?> <span class="ml-1 opacity-70"><?= $count ?></span></a>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah Produk -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-6 rounded-full" style="background:#42B549"></div>
                    <h2 class="font-bold text-gray-800">Tambah Produk Baru</h2>
                </div>
                <button onclick="closeModal('tambah')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah">
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Produk</label><input type="text" name="nama_produk" placeholder="Nama produk" required></div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Harga (Rp)</label><input type="hidden" name="harga" id="tambah-harga-raw" value="0"><input type="text" id="tambah-harga-display" placeholder="0" required oninput="formatHargaInput(this, 'tambah-harga-raw')"></div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tipe Produk</label>
                            <select name="tipe_produk" id="tambah-tipe" onchange="toggleAkunFields('tambah')" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                <?php foreach (tipe_produk_list() as $key => $cfg): ?>
                                <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="tambah-file-wrap"><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload"><p class="text-xs text-gray-400 mt-1" id="tambah-file-hint">Wajib untuk produk non-akun</p></div>
                    </div>
                    <div id="tambah-akun-wrap" class="hidden">
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Layanan (preset)</label>
                            <select id="tambah-preset" onchange="applyPreset('tambah')" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                <option value="">— Pilih layanan untuk saran durasi & paket —</option>
                                <?php foreach ($akun_presets as $key => $cfg): ?>
                                <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-gray-500">Varian Akun</label>
                            <button type="button" onclick="addVarianRow('tambah')" class="text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#E8F5E9; color:#2E7D32">+ Tambah Varian</button>
                        </div>
                        <div id="tambah-varian-list" class="space-y-3"></div>
                        <p class="text-xs text-gray-400 mt-2">Pilih layanan agar durasi & paket muncul otomatis. Kredensial hanya ditampilkan ke pembeli setelah pembayaran berhasil.</p>
                    </div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" placeholder="Deskripsi produk..." required rows="3"></textarea></div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 flex-shrink-0 bg-white">
                    <button type="button" onclick="closeModal('tambah')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('edit')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-6 rounded-full" style="background:#1976D2"></div>
                    <h2 class="font-bold text-gray-800">Edit Produk: <span id="edit-title" style="color:#1976D2"></span></h2>
                </div>
                <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_produk" id="edit-id">
                <div class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Produk</label><input type="text" name="nama_produk" id="edit-nama" required></div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Harga (Rp)</label><input type="hidden" name="harga" id="edit-harga-raw" value="0"><input type="text" id="edit-harga-display" required oninput="formatHargaInput(this, 'edit-harga-raw')"></div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tipe Produk</label>
                            <select name="tipe_produk" id="edit-tipe" onchange="toggleAkunFields('edit')" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                <?php foreach (tipe_produk_list() as $key => $cfg): ?>
                                <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="edit-file-wrap"><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload"><p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ganti file</p></div>
                    </div>
                    <div id="edit-akun-wrap" class="hidden">
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Layanan (preset)</label>
                            <select id="edit-preset" onchange="applyPreset('edit')" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                <option value="">— Pilih layanan untuk saran durasi & paket —</option>
                                <?php foreach ($akun_presets as $key => $cfg): ?>
                                <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-gray-500">Varian Akun</label>
                            <button type="button" onclick="addVarianRow('edit')" class="text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#E3F2FD; color:#1565C0">+ Tambah Varian</button>
                        </div>
                        <div id="edit-varian-list" class="space-y-3"></div>
                        <p class="text-xs text-gray-400 mt-2">Pilih layanan agar durasi & paket muncul otomatis. Kredensial hanya ditampilkan ke pembeli setelah pembayaran berhasil.</p>
                    </div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" id="edit-deskripsi" required rows="3"></textarea></div>
                </div>
                <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 flex-shrink-0 bg-white">
                    <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Toast -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 z-50 hidden" style="transform:translateX(-50%) translateY(20px); opacity:0; transition:transform 0.3s cubic-bezier(0.21,1.02,0.73,1), opacity 0.2s">
    <div class="flex items-center gap-4 px-5 py-3 rounded-2xl shadow-lg border border-gray-200 bg-white">
        <span class="text-sm font-semibold text-gray-700"><span id="selected-count">0</span> produk dipilih</span>
        <div class="w-px h-5 bg-gray-200"></div>
        <button type="button" onclick="deselectAll()" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer">Batal Pilih</button>
        <button type="button" onclick="bulkDelete()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg text-white transition cursor-pointer hover:opacity-90" style="background:#C62828">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Hapus Terpilih
        </button>
    </div>
</div>

<form id="bulk-delete-form" method="POST" class="hidden">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="hapus_bulk">
    <div id="bulk-delete-ids"></div>
</form>

<div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-3 py-3 text-center w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Produk</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (count($products) == 0): ?>
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Tidak ada produk ditemukan.</td></tr>
                <?php endif;
                foreach($products as $i => $r){ ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-3 text-center"><input type="checkbox" name="produk_ids[]" value="<?= $r['id'] ?>" class="row-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></td>
                    <td class="px-5 py-3 text-center text-sm text-gray-500 font-medium"><?= $paging['offset'] + $i + 1 ?></td>
                    <td class="px-5 py-3"> <!-- ini untuk mengatur baris di tabel -->
                        <p class="font-semibold text-gray-800 text-sm"><?= e($r['nama_produk']); ?></p>
                        <p class="text-xs text-gray-500 max-w-xs truncate"><?= e($r['deskripsi']); ?></p>
                    </td>
                    <td class="px-5 py-4"><?= tipe_produk_badge($r['tipe_produk'] ?? 'Lainnya') ?></td>
                    <td class="px-5 py-4 font-bold" style="color:#42B549"><?= rupiah($r['harga']); ?></td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs font-semibold text-gray-700"><?= $r['avg_rating']; ?></span>
                            <span class="text-xs text-gray-400">(<?= $r['total_rating'] ?>)</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <?php if (($r['tipe_produk'] ?? '') === 'Akun'): ?>
                            <span class="text-xs font-medium text-gray-500">Info Akun</span>
                        <?php elseif (!empty($r['file_upload'])): ?>
                            <a href="<?= url('/admin-produk/file/' . (int)$r['id']); ?>" target="_blank" class="text-xs font-medium hover:underline" style="color:#1976D2">Lihat File</a>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button onclick='openEdit(<?= $r["id"] ?>, <?= htmlspecialchars(json_encode($r["nama_produk"]), ENT_QUOTES) ?>, <?= (int)$r["harga"] ?>, <?= htmlspecialchars(json_encode($r["tipe_produk"] ?? "Lainnya"), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["deskripsi"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["varian"] ?? []), ENT_QUOTES) ?>)' class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                        <form method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="produk_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?= pagination_render($paging) ?>
</div>

<script>
var AKUN_PRESETS = <?= json_encode($akun_presets) ?>;

function formatHargaInput(el, hiddenId) {
    var raw = el.value.replace(/\D/g, '');
    document.getElementById(hiddenId).value = raw;
    if (raw === '') { el.value = ''; return; }
    el.value = Number(raw).toLocaleString('id-ID');
}
function formatHargaValue(val) {
    if (!val || val == 0) return '';
    return Number(val).toLocaleString('id-ID');
}
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
function openTambahModal() {
    // Reset the add form to a clean state
    var form = document.querySelector('#modal-tambah form');
    if (form) form.reset();
    document.getElementById('tambah-harga-raw').value = '0';
    var presetSel = document.getElementById('tambah-preset');
    if (presetSel) presetSel.value = '';
    document.getElementById('tambah-varian-list').innerHTML = '';
    openModal('tambah');
    toggleAkunFields('tambah');
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
function openEdit(id, nama, harga, tipe, deskripsi, variants) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-harga-raw').value = harga;
    document.getElementById('edit-harga-display').value = formatHargaValue(harga);
    document.getElementById('edit-tipe').value = tipe;
    document.getElementById('edit-deskripsi').value = deskripsi;
    document.getElementById('edit-title').textContent = nama;

    // Reset preset selector (existing values render as text/custom)
    var editPreset = document.getElementById('edit-preset');
    if (editPreset) editPreset.value = '';

    // Populate variant rows
    var list = document.getElementById('edit-varian-list');
    list.innerHTML = '';
    if (Array.isArray(variants) && variants.length > 0) {
        variants.forEach(function(v) {
            var creds = parseAccountInfo(v.account_info || '');
            addVarianRow('edit', {
                durasi: v.durasi || '',
                paket: v.paket || '',
                harga: v.harga || 0,
                email: creds.email,
                password: creds.password
            });
        });
    }

    toggleAkunFields('edit');
    openModal('edit');
}

// Parse "Email: x\nPassword: y" into {email, password}
function parseAccountInfo(text) {
    var email = '', password = '';
    String(text).split(/\r?\n/).forEach(function(line) {
        var m = line.match(/^\s*Email\s*:\s*(.*)$/i);
        if (m) { email = m[1].trim(); return; }
        var p = line.match(/^\s*Password\s*:\s*(.*)$/i);
        if (p) { password = p[1].trim(); }
    });
    return { email: email, password: password };
}

// Build one variant editor row.
// If a preset is active for this prefix, durasi & paket render as dropdowns
// (with a "Custom" escape hatch); otherwise as free text inputs.
function addVarianRow(prefix, data) {
    data = data || {};
    var list = document.getElementById(prefix + '-varian-list');
    var presetKey = document.getElementById(prefix + '-preset') ? document.getElementById(prefix + '-preset').value : '';
    var preset = presetKey && AKUN_PRESETS[presetKey] ? AKUN_PRESETS[presetKey] : null;

    var hargaVal = data.harga ? Number(data.harga).toLocaleString('id-ID') : '';

    var durasiField = preset && preset.durasi && preset.durasi.length
        ? buildSelectField('varian_durasi[]', preset.durasi, data.durasi)
        : '<input type="text" name="varian_durasi[]" placeholder="1 Bulan" value="' + escapeAttr(data.durasi) + '">';

    var paketField;
    if (preset && preset.paket && preset.paket.length) {
        paketField = buildSelectField('varian_paket[]', preset.paket, data.paket, true);
    } else {
        paketField = '<input type="text" name="varian_paket[]" placeholder="Individual / Family" value="' + escapeAttr(data.paket) + '">';
    }

    var row = document.createElement('div');
    row.className = 'varian-row border border-gray-200 rounded-xl p-3 grid grid-cols-2 gap-3 relative';
    row.innerHTML =
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Durasi</label>' + durasiField + '</div>' +
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Paket (opsional)</label>' + paketField + '</div>' +
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Harga (Rp)</label>' +
        '<input type="text" name="varian_harga[]" placeholder="0" value="' + hargaVal + '" oninput="this.value=this.value.replace(/\\D/g,\'\')===\'\'?\'\':Number(this.value.replace(/\\D/g,\'\')).toLocaleString(\'id-ID\')"></div>' +
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Email / Username</label>' +
        '<input type="text" name="varian_email[]" placeholder="akun@email.com" value="' + escapeAttr(data.email) + '"></div>' +
        '<div class="col-span-2"><label class="block text-[11px] font-semibold text-gray-400 mb-1">Password</label>' +
        '<input type="text" name="varian_password[]" placeholder="password akun" value="' + escapeAttr(data.password) + '"></div>' +
        '<button type="button" onclick="this.closest(\'.varian-row\').remove()" class="absolute -top-2 -right-2 w-6 h-6 flex items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600" title="Hapus varian">&times;</button>';
    list.appendChild(row);
}

// Build a <select> with preset options + selected value + a "custom" text fallback.
function buildSelectField(name, options, selected, optional) {
    selected = selected || '';
    var opts = '';
    if (optional) {
        opts += '<option value="">— Tidak ada —</option>';
    }
    var found = false;
    options.forEach(function(o) {
        var sel = (o === selected) ? ' selected' : '';
        if (o === selected) found = true;
        opts += '<option value="' + escapeAttr(o) + '"' + sel + '>' + escapeAttr(o) + '</option>';
    });
    // If the stored value isn't in preset options, keep it as a selected custom option
    if (selected && !found) {
        opts += '<option value="' + escapeAttr(selected) + '" selected>' + escapeAttr(selected) + ' (custom)</option>';
    }
    opts += '<option value="__custom__">+ Ketik manual…</option>';
    return '<select name="' + name + '" onchange="handleVarianSelect(this)">' + opts + '</select>';
}

// When "+ Ketik manual" is chosen, swap the select for a text input.
function handleVarianSelect(sel) {
    if (sel.value !== '__custom__') return;
    var name = sel.getAttribute('name');
    var input = document.createElement('input');
    input.type = 'text';
    input.name = name;
    input.placeholder = 'Ketik manual';
    sel.replaceWith(input);
    input.focus();
}

// Apply a service preset: rebuild existing variant rows using the preset options,
// preserving any data already typed.
function applyPreset(prefix) {
    var list = document.getElementById(prefix + '-varian-list');
    if (!list) return;

    // Capture existing row data
    var existing = [];
    list.querySelectorAll('.varian-row').forEach(function(row) {
        existing.push({
            durasi: getFieldVal(row, 'varian_durasi[]'),
            paket: getFieldVal(row, 'varian_paket[]'),
            harga: getFieldVal(row, 'varian_harga[]').replace(/\D/g, ''),
            email: getFieldVal(row, 'varian_email[]'),
            password: getFieldVal(row, 'varian_password[]')
        });
    });

    list.innerHTML = '';
    // Only rebuild rows that already exist; don't auto-create new ones
    existing.forEach(function(d) { addVarianRow(prefix, d); });
}

function getFieldVal(row, name) {
    var el = row.querySelector('[name="' + name + '"]');
    return el ? el.value : '';
}

function escapeAttr(val) {
    return String(val == null ? '' : val).replace(/"/g, '&quot;');
}

// Toggle between file upload and account variants based on product type
function toggleAkunFields(prefix) {
    var tipe = document.getElementById(prefix + '-tipe').value;
    var akunWrap = document.getElementById(prefix + '-akun-wrap');
    var fileWrap = document.getElementById(prefix + '-file-wrap');
    var isAkun = (tipe === 'Akun');

    if (isAkun) {
        akunWrap.classList.remove('hidden');
        fileWrap.classList.add('hidden');
        // Variant rows appear only when admin clicks "+ Tambah Varian"
    } else {
        akunWrap.classList.add('hidden');
        fileWrap.classList.remove('hidden');
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('tambah');
        closeModal('edit');
    }
});

// Bulk selection
var selectAll = document.getElementById('select-all');
var rowCheckboxes = document.querySelectorAll('.row-checkbox');
var bulkBar = document.getElementById('bulk-action-bar');
var selectedCountEl = document.getElementById('selected-count');

function updateBulkBar() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    var count = checked.length;
    selectedCountEl.textContent = count;
    if (count > 0) {
        bulkBar.classList.remove('hidden');
        requestAnimationFrame(function() {
            bulkBar.style.opacity = '1';
            bulkBar.style.transform = 'translateX(-50%) translateY(0)';
        });
    } else {
        bulkBar.style.opacity = '0';
        bulkBar.style.transform = 'translateX(-50%) translateY(20px)';
        setTimeout(function() { bulkBar.classList.add('hidden'); }, 250);
    }
    selectAll.checked = rowCheckboxes.length > 0 && count === rowCheckboxes.length;
    selectAll.indeterminate = count > 0 && count < rowCheckboxes.length;
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
        updateBulkBar();
    });
}
rowCheckboxes.forEach(function(cb) {
    cb.addEventListener('change', updateBulkBar);
});

function deselectAll() {
    selectAll.checked = false;
    selectAll.indeterminate = false;
    rowCheckboxes.forEach(function(cb) { cb.checked = false; });
    updateBulkBar();
}

function bulkDelete() {
    var checked = document.querySelectorAll('.row-checkbox:checked');
    var count = checked.length;
    if (count === 0) return;
    if (!confirm('Hapus ' + count + ' produk yang dipilih? Tindakan ini tidak dapat dibatalkan.')) return;

    var container = document.getElementById('bulk-delete-ids');
    container.innerHTML = '';
    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'produk_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    document.getElementById('bulk-delete-form').submit();
}
</script>
