<div class="ds-toolbar">
    <form method="GET" action="" class="relative ds-toolbar__search">
        <?php if (isset($_GET['tipe'])): ?><input type="hidden" name="tipe" value="<?= e($_GET['tipe']) ?>"><?php endif; ?>
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari produk..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
        <?php if ($search !== ''): ?>
        <a href="<?= url('/admin-produk') . (isset($_GET['tipe']) ? '?tipe=' . e($_GET['tipe']) : '') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <?php endif; ?>
    </form>
    <button onclick="openTambahModal()" class="ds-toolbar__action px-4 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Produk</span>
    </button>
</div>
<?php
$q_param = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="ds-filter-row">
    <a href="<?= url('/admin-produk') . '?' . ltrim($q_param, '&') ?>" class="ds-chip ds-chip--neutral ds-chip--filter <?= $current_tipe === '' ? 'is-active' : '' ?>">Semua <span class="ds-chip__count"><?= $total_produk ?></span></a>
    <?php foreach (tipe_produk_list() as $key => $cfg): $count = $tipe_counts[$key] ?? 0; $variant = $cfg['chip'] ?? 'neutral'; ?>
    <a href="<?= url('/admin-produk') . '?tipe=' . urlencode($key) . $q_param ?>" class="ds-chip ds-chip--<?= e($variant) ?> ds-chip--filter <?= $current_tipe === $key ? 'is-active' : '' ?>"><?= e($cfg['label']) ?> <span class="ds-chip__count"><?= $count ?></span></a>
    <?php endforeach; ?>
</div>

<!-- Modal Tambah Produk -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0" style="background:#42B549"></div>
                    <h2 class="font-bold text-gray-800 truncate">Tambah Produk Baru</h2>
                </div>
                <button onclick="closeModal('tambah')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="ds-modal-grid">
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
                        <div id="tambah-file-wrap"><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload" class="w-full"><p class="text-xs text-gray-400 mt-1" id="tambah-file-hint">Wajib untuk produk non-akun</p></div>
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
                        <div class="flex items-center justify-between mb-2 gap-2 flex-wrap">
                            <label class="block text-xs font-semibold text-gray-500">Varian Akun</label>
                            <button type="button" onclick="addVarianRow('tambah')" class="text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#E8F5E9; color:#2E7D32">+ Tambah Varian</button>
                        </div>
                        <div id="tambah-varian-list" class="space-y-3"></div>
                        <p class="text-xs text-gray-400 mt-2">Atur durasi & harga per varian. Kredensial akun dikelola di halaman Stok setelah produk dibuat.</p>
                    </div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" placeholder="Deskripsi produk..." required rows="3" class="w-full"></textarea></div>
                </div>
                <div class="ds-modal-footer flex-shrink-0">
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
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0" style="background:#1976D2"></div>
                    <h2 class="font-bold text-gray-800 truncate">Edit: <span id="edit-title" style="color:#1976D2"></span></h2>
                </div>
                <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_produk" id="edit-id">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="ds-modal-grid">
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
                        <div id="edit-file-wrap"><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload" class="w-full"><p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ganti file</p></div>
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
                        <div class="flex items-center justify-between mb-2 gap-2 flex-wrap">
                            <label class="block text-xs font-semibold text-gray-500">Varian Akun</label>
                            <button type="button" onclick="addVarianRow('edit')" class="text-xs font-semibold px-3 py-1.5 rounded-lg" style="background:#E3F2FD; color:#1565C0">+ Tambah Varian</button>
                        </div>
                        <div id="edit-varian-list" class="space-y-3"></div>
                        <p class="text-xs text-gray-400 mt-2">Atur durasi & harga per varian. Kredensial akun dikelola di halaman Stok.</p>
                    </div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" id="edit-deskripsi" required rows="3" class="w-full"></textarea></div>
                </div>
                <div class="ds-modal-footer flex-shrink-0">
                    <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Toast -->
<div id="bulk-action-bar" class="ds-bulkbar-wrap hidden" style="opacity:0; transition:opacity 0.2s">
    <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-3 px-3 sm:px-4 py-2 sm:py-3 rounded-2xl shadow-lg border border-gray-200 bg-white w-full sm:w-auto">
        <span class="text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap"><span id="selected-count">0</span> produk dipilih</span>
        <div class="hidden sm:block w-px h-5 bg-gray-200"></div>
        <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
            <button type="button" onclick="deselectAll()" class="px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer whitespace-nowrap">Batal<span class="hidden sm:inline"> Pilih</span></button>
            <button type="button" onclick="bulkDelete()" class="inline-flex items-center gap-1 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold rounded-lg text-white transition cursor-pointer hover:opacity-90 whitespace-nowrap" style="background:#C62828">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Hapus<span class="hidden sm:inline"> Terpilih</span></span>
            </button>
        </div>
    </div>
</div>

<form id="bulk-delete-form" method="POST" class="hidden">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="hapus_bulk">
    <div id="bulk-delete-ids"></div>
</form>

<div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden">
    <div class="ds-table-wrap ds-table-wrap--wide hidden md:block">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-3 py-1.5 text-center w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></th>
                    <th class="px-5 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                    <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Produk</th>
                    <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                    <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                    <th class="px-5 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (count($products) == 0): ?>
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Tidak ada produk ditemukan.</td></tr>
                <?php endif;
                $stokModel = new AkunStok();
                foreach($products as $i => $r){ ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-3 text-center"><input type="checkbox" name="produk_ids[]" value="<?= $r['id'] ?>" class="row-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></td>
                    <td class="px-5 py-3 text-center text-sm text-gray-500 font-medium"><?= $paging['offset'] + $i + 1 ?></td>
                    <td class="px-5 py-3">
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
                        <?php if (($r['tipe_produk'] ?? '') === 'Akun' && !empty($r['varian'])): ?>
                            <span class="text-xs text-gray-500"><?= count($r['varian']) ?> varian</span>
                        <?php elseif (!empty($r['file_upload'])): ?>
                            <a href="<?= url('/admin-produk/file/' . (int)$r['id']); ?>" target="_blank" class="text-xs font-medium hover:underline" style="color:#1976D2">Lihat File</a>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if (($r['tipe_produk'] ?? '') === 'Akun' && !empty($r['varian'])): ?>
                        <button type="button" onclick='openStokModalProduk(<?= htmlspecialchars(json_encode($r["nama_produk"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(array_map(function($v) use ($stokModel){ return ["id"=>(int)$v["id"],"label"=>$v["durasi"].(!empty($v["paket"])?" - ".$v["paket"]:""),"stok"=>$stokModel->countAvailable((int)$v["id"])]; }, $r["varian"])), ENT_QUOTES) ?>)' class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#E3F2FD; color:#1565C0">Stok</button>
                        <?php endif; ?>
                        <button onclick='openEdit(<?= $r["id"] ?>, <?= htmlspecialchars(json_encode($r["nama_produk"]), ENT_QUOTES) ?>, <?= (int)$r["harga"] ?>, <?= htmlspecialchars(json_encode($r["tipe_produk"] ?? "Lainnya"), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["deskripsi"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["varian"] ?? []), ENT_QUOTES) ?>)' class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                        <form method="POST" class="inline" data-confirm="Hapus produk ini?">
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
    </div>

    <!-- Mobile card list -->
    <div class="md:hidden p-3 space-y-3">
        <?php if (count($products) == 0): ?>
        <p class="text-center text-sm text-gray-500 py-8">Tidak ada produk ditemukan.</p>
        <?php endif;
        foreach($products as $i => $r): ?>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 relative">
            <input type="checkbox" name="produk_ids[]" value="<?= $r['id'] ?>" class="row-checkbox absolute top-4 right-4 w-5 h-5 rounded border-gray-300 cursor-pointer accent-green-600">
            <div class="pr-8 mb-3">
                <p class="text-xs text-gray-400 mb-0.5">#<?= $paging['offset'] + $i + 1 ?></p>
                <p class="font-semibold text-gray-800 text-sm leading-tight"><?= e($r['nama_produk']); ?></p>
                <p class="text-xs text-gray-500 line-clamp-2 mt-1"><?= e($r['deskripsi']); ?></p>
            </div>
            <div class="flex items-center flex-wrap gap-x-4 gap-y-2 text-xs mb-3">
                <?= tipe_produk_badge($r['tipe_produk'] ?? 'Lainnya') ?>
                <span class="font-bold" style="color:#42B549"><?= rupiah($r['harga']); ?></span>
                <span class="flex items-center gap-1 text-gray-500">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?= $r['avg_rating']; ?> <span class="text-gray-400">(<?= $r['total_rating'] ?>)</span>
                </span>
            </div>
            <?php if (($r['tipe_produk'] ?? '') === 'Akun' && !empty($r['varian'])): ?>
                <div class="text-xs space-y-1 mb-3 pb-3 border-b border-gray-100">
                    <?php foreach ($r['varian'] as $v):
                        $stokModel = new AkunStok();
                        $stokCount = $stokModel->countAvailable((int) $v['id']);
                    ?>
                    <div class="flex items-center justify-between <?= $stokCount > 0 ? 'text-green-600' : 'text-red-500' ?>">
                        <span><?= e($v['durasi'] . (!empty($v['paket']) ? ' ' . $v['paket'] : '')) ?></span>
                        <span class="font-medium"><?= $stokCount ?> stok</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($r['file_upload'])): ?>
                <a href="<?= url('/admin-produk/file/' . (int)$r['id']); ?>" target="_blank" class="block text-xs font-medium hover:underline mb-3" style="color:#1976D2">Lihat File →</a>
            <?php endif; ?>
            <div class="flex gap-2 pt-1">
                <?php if (($r['tipe_produk'] ?? '') === 'Akun' && !empty($r['varian'])): ?>
                <button type="button" onclick='openStokModalProduk(<?= htmlspecialchars(json_encode($r["nama_produk"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(array_map(function($v) use ($stokModel){ return ["id"=>(int)$v["id"],"label"=>$v["durasi"].(!empty($v["paket"])?" - ".$v["paket"]:""),"stok"=>$stokModel->countAvailable((int)$v["id"])]; }, $r["varian"])), ENT_QUOTES) ?>)' class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#E3F2FD; color:#1565C0">Stok</button>
                <?php endif; ?>
                <button onclick='openEdit(<?= $r["id"] ?>, <?= htmlspecialchars(json_encode($r["nama_produk"]), ENT_QUOTES) ?>, <?= (int)$r["harga"] ?>, <?= htmlspecialchars(json_encode($r["tipe_produk"] ?? "Lainnya"), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["deskripsi"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r["varian"] ?? []), ENT_QUOTES) ?>)' class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                <form method="POST" class="flex-1" data-confirm="Hapus produk ini?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="hapus">
                    <input type="hidden" name="produk_id" value="<?= $r['id'] ?>">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
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
            addVarianRow('edit', {
                durasi: v.durasi || '',
                paket: v.paket || '',
                harga: v.harga || 0
            });
        });
    }

    toggleAkunFields('edit');
    openModal('edit');
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
    row.className = 'varian-row border border-gray-200 rounded-xl p-3 grid grid-cols-3 gap-3 relative';
    row.innerHTML =
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Durasi</label>' + durasiField + '</div>' +
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Paket (opsional)</label>' + paketField + '</div>' +
        '<div><label class="block text-[11px] font-semibold text-gray-400 mb-1">Harga (Rp)</label>' +
        '<input type="text" name="varian_harga[]" placeholder="0" value="' + hargaVal + '" oninput="this.value=this.value.replace(/\\D/g,\'\')===\'\'?\'\':Number(this.value.replace(/\\D/g,\'\')).toLocaleString(\'id-ID\')"></div>' +
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

// Apply a service preset: generate variant rows for all combinations of durasi × paket.
// Admin only needs to fill harga + credentials.
function applyPreset(prefix) {
    var presetKey = document.getElementById(prefix + '-preset').value;
    if (!presetKey || !AKUN_PRESETS[presetKey]) return;

    var preset = AKUN_PRESETS[presetKey];
    var list = document.getElementById(prefix + '-varian-list');
    list.innerHTML = '';

    var durasiList = preset.durasi || [];
    var paketList = preset.paket || [];

    if (paketList.length === 0) {
        // No paket tiers (e.g. Steam) — one row per durasi
        durasiList.forEach(function(d) {
            addVarianRow(prefix, { durasi: d, paket: '', harga: 0, email: '', password: '' });
        });
    } else {
        // Generate durasi × paket combinations
        durasiList.forEach(function(d) {
            paketList.forEach(function(p) {
                addVarianRow(prefix, { durasi: d, paket: p, harga: 0, email: '', password: '' });
            });
        });
    }
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
        });
    } else {
        bulkBar.style.opacity = '0';
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
    window.showCustomConfirm('Konfirmasi Hapus', 'Hapus ' + count + ' produk yang dipilih? Tindakan ini tidak dapat dibatalkan.', function() {
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
    });
}
</script>

<!-- Modal Kelola Stok -->
<div id="modal-stok" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('stok')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content max-h-[90vh] flex flex-col overflow-hidden" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0" style="background:#1565C0"></div>
                    <div class="min-w-0">
                        <h2 class="font-bold text-gray-800 truncate">Kelola Stok: <span id="stok-modal-title" style="color:#1565C0"></span></h2>
                        <p class="text-xs text-gray-500" id="stok-modal-subtitle">Pilih varian untuk mengelola stok</p>
                    </div>
                </div>
                <button onclick="closeModal('stok')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <!-- Variant switcher -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Varian Akun</label>
                    <select id="stok-varian-switch" onchange="onStokVarianChange()" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s"></select>
                </div>

                <!-- Add stock tabs -->
                <div class="flex gap-2 mb-4">
                    <button type="button" id="stok-tab-single" onclick="switchStokTab('single')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700">Satu Akun</button>
                    <button type="button" id="stok-tab-bulk" onclick="switchStokTab('bulk')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600">Bulk Paste</button>
                    <span id="stok-badge" class="ml-auto text-xs font-bold px-2 py-1 rounded-lg" style="background:#E8F5E9; color:#2E7D32"></span>
                </div>

                <!-- Single form -->
                <div id="stok-form-single" class="flex gap-2 mb-4">
                    <input type="text" id="stok-email" placeholder="Email / Username" class="flex-1 text-sm">
                    <input type="text" id="stok-pass" placeholder="Password" class="flex-1 text-sm">
                    <button type="button" onclick="addSingleStok()" class="px-4 py-2 text-white text-xs font-semibold rounded-lg hover:opacity-90 transition flex-shrink-0" style="background:#42B549">+</button>
                </div>

                <!-- Bulk form -->
                <div id="stok-form-bulk" class="mb-4 hidden">
                    <textarea id="stok-bulk" rows="4" placeholder="email:password (satu per baris)" class="text-xs font-mono"></textarea>
                    <button type="button" onclick="addBulkStok()" class="mt-2 w-full py-2 text-white text-xs font-semibold rounded-lg hover:opacity-90 transition" style="background:#1976D2">Tambah Bulk</button>
                </div>

                <!-- Stock list -->
                <div id="stok-list" class="border border-gray-100 rounded-xl overflow-hidden">
                    <div id="stok-loading" class="p-6 text-center text-gray-400 text-sm">Memuat...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var STOK_VARIAN_ID = 0;
var STOK_VARIANTS = [];
var STOK_PRODUK_NAME = '';
var STOK_API_BASE = '<?= url("/api/admin/stok") ?>';

// Open the stock modal for a product. Renders a variant switcher inside the
// modal so the admin can jump between variants without closing it.
function openStokModalProduk(produkName, variants) {
    STOK_PRODUK_NAME = produkName;
    STOK_VARIANTS = Array.isArray(variants) ? variants : [];
    document.getElementById('stok-modal-title').textContent = produkName;

    renderStokVarianOptions();

    if (STOK_VARIANTS.length > 0) {
        STOK_VARIAN_ID = STOK_VARIANTS[0].id;
        document.getElementById('stok-varian-switch').value = STOK_VARIAN_ID;
        document.getElementById('stok-modal-subtitle').textContent = STOK_VARIANTS[0].label;
    } else {
        STOK_VARIAN_ID = 0;
        document.getElementById('stok-modal-subtitle').textContent = 'Belum ada varian';
    }

    switchStokTab('single');
    openModal('stok');
    loadStokList();
}

// (Re)build the variant <option> list, including the available stock count.
function renderStokVarianOptions() {
    var sel = document.getElementById('stok-varian-switch');
    var current = sel.value;
    sel.innerHTML = '';
    STOK_VARIANTS.forEach(function(v) {
        var opt = document.createElement('option');
        opt.value = v.id;
        var stok = (v.stok != null) ? v.stok : 0;
        opt.textContent = v.label + '  (' + stok + ' stok)';
        sel.appendChild(opt);
    });
    if (current) sel.value = current;
}

function onStokVarianChange() {
    var sel = document.getElementById('stok-varian-switch');
    STOK_VARIAN_ID = parseInt(sel.value, 10) || 0;
    var v = STOK_VARIANTS.find(function(x) { return x.id == STOK_VARIAN_ID; });
    document.getElementById('stok-modal-subtitle').textContent = v ? v.label : '';
    document.getElementById('stok-email').value = '';
    document.getElementById('stok-pass').value = '';
    var bulk = document.getElementById('stok-bulk');
    if (bulk) bulk.value = '';
    loadStokList();
}

function loadStokList() {
    var container = document.getElementById('stok-list');
    container.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm">Memuat...</div>';

    fetch(STOK_API_BASE + '/' + STOK_VARIAN_ID)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) { container.innerHTML = '<p class="p-4 text-red-500 text-sm">Gagal memuat.</p>'; return; }
            document.getElementById('stok-badge').textContent = 'Tersedia: ' + data.available + ' / ' + data.total;

            // Keep the dropdown count in sync with the latest available stock.
            var cur = STOK_VARIANTS.find(function(x) { return x.id == STOK_VARIAN_ID; });
            if (cur) { cur.stok = data.available; renderStokVarianOptions(); document.getElementById('stok-varian-switch').value = STOK_VARIAN_ID; }

            if (data.stocks.length === 0) {
                container.innerHTML = '<p class="p-4 text-center text-gray-400 text-sm">Belum ada stok.</p>';
                return;
            }

            var hasAvailable = data.stocks.some(function(s) { return s.status === 'available'; });
            var header = '';
            if (hasAvailable) {
                header = '<div class="flex justify-end px-3 py-2 border-b border-gray-100"><button onclick="deleteAllStok()" class="text-[11px] font-semibold text-red-500 hover:text-red-700">Hapus Semua Tersedia</button></div>';
            }

            var html = header + '<div class="overflow-x-auto"><table class="w-full text-xs" style="min-width:100%"><thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left" style="max-width:180px">Email</th><th class="px-3 py-2 text-left" style="max-width:140px">Password</th><th class="px-3 py-2 text-center whitespace-nowrap w-16">Status</th><th class="px-3 py-2 text-center whitespace-nowrap w-14">Aksi</th></tr></thead><tbody>';
            data.stocks.forEach(function(s) {
                var statusBadge = s.status === 'available'
                    ? '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-green-50 text-green-700">Tersedia</span>'
                    : '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-700">Terjual</span>';
                var aksi = s.status === 'available'
                    ? '<button onclick="deleteStok(' + s.id + ')" class="text-[10px] font-semibold text-red-500 hover:text-red-700 whitespace-nowrap">Hapus</button>'
                    : '<span class="text-gray-300">—</span>';
                var masked = '••••••••';
                html += '<tr class="border-t border-gray-50"><td class="px-3 py-2 font-mono truncate" style="max-width:180px" title="' + escapeAttr(s.account_email) + '">' + escapeAttr(s.account_email) + '</td><td class="px-3 py-2 font-mono truncate" style="max-width:140px"><span class="stok-pass-mask">' + masked + '</span><span class="stok-pass-real hidden">' + escapeAttr(s.account_password) + '</span> <button type="button" onclick="togglePassStok(this)" class="text-[10px] text-blue-500 hover:text-blue-700 ml-1" title="Lihat/Sembunyikan">👁</button></td><td class="px-3 py-2 text-center">' + statusBadge + '</td><td class="px-3 py-2 text-center">' + aksi + '</td></tr>';
            });
            html += '</tbody></table></div>';
            container.innerHTML = html;
        })
        .catch(function() { container.innerHTML = '<p class="p-4 text-red-500 text-sm">Error.</p>'; });
}

function addSingleStok() {
    var email = document.getElementById('stok-email').value.trim();
    var pass = document.getElementById('stok-pass').value.trim();
    if (!email || !pass) return;

    var fd = new FormData();
    fd.append('varian_id', STOK_VARIAN_ID);
    fd.append('mode', 'single');
    fd.append('account_email', email);
    fd.append('account_password', pass);

    fetch(STOK_API_BASE + '/add', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('stok-email').value = '';
                document.getElementById('stok-pass').value = '';
                loadStokList();
                if (typeof window.showToast === 'function') window.showToast('success', data.message);
            } else {
                if (typeof window.showToast === 'function') window.showToast('error', data.message);
            }
        });
}

function addBulkStok() {
    var bulk = document.getElementById('stok-bulk').value.trim();
    if (!bulk) return;

    var fd = new FormData();
    fd.append('varian_id', STOK_VARIAN_ID);
    fd.append('mode', 'bulk');
    fd.append('bulk_data', bulk);

    fetch(STOK_API_BASE + '/add', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('stok-bulk').value = '';
                loadStokList();
                if (typeof window.showToast === 'function') window.showToast('success', data.message);
            } else {
                if (typeof window.showToast === 'function') window.showToast('error', data.message);
            }
        });
}

function deleteStok(id) {
    window.showCustomConfirm('Konfirmasi Hapus', 'Hapus stok ini?', function() {
        var fd = new FormData();
        fd.append('stok_id', id);

        fetch(STOK_API_BASE + '/delete', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) loadStokList();
            });
    });
}

function deleteAllStok() {
    window.showCustomConfirm('Konfirmasi Hapus', 'Hapus SEMUA stok tersedia untuk varian ini?', function() {
        var fd = new FormData();
        fd.append('varian_id', STOK_VARIAN_ID);

        fetch(STOK_API_BASE + '/delete-all', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    loadStokList();
                    if (typeof window.showToast === 'function') window.showToast('success', data.message);
                }
            });
    });
}

function togglePassStok(btn) {
    var td = btn.parentElement;
    var mask = td.querySelector('.stok-pass-mask');
    var real = td.querySelector('.stok-pass-real');
    if (real.classList.contains('hidden')) {
        real.classList.remove('hidden');
        mask.classList.add('hidden');
        btn.textContent = '🙈';
    } else {
        real.classList.add('hidden');
        mask.classList.remove('hidden');
        btn.textContent = '👁';
    }
}

function switchStokTab(tab) {
    var single = document.getElementById('stok-form-single');
    var bulk = document.getElementById('stok-form-bulk');
    var tabS = document.getElementById('stok-tab-single');
    var tabB = document.getElementById('stok-tab-bulk');
    if (tab === 'single') {
        single.classList.remove('hidden'); bulk.classList.add('hidden');
        tabS.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-700';
        tabB.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600';
    } else {
        single.classList.add('hidden'); bulk.classList.remove('hidden');
        tabB.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-100 text-blue-700';
        tabS.className = 'px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-600';
    }
}
</script>
