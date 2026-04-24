<?php
$page_title = 'Kelola Produk';
$active_page = 'produk';
$extra_css = 'input[type=text],input[type=number],textarea,input[type=file],select { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus,select:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';
include '../includes/admin_header.php';

if (isset($_POST['tambah']) && csrf_validate()) {
    $nama = $_POST['nama_produk'] ?? '';
    $harga = (int) ($_POST['harga'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';
    $tipe = $_POST['tipe_produk'] ?? 'Lainnya';
    if (!array_key_exists($tipe, tipe_produk_list())) $tipe = 'Lainnya';
    $file_name = $_FILES['file_upload']['name'] ?? '';
    $file_tmp = $_FILES['file_upload']['tmp_name'] ?? '';
    if ($file_name && $file_tmp) {
        $nama_file_db = time() . "_" . $file_name;
        $file_dest = "../uploads/" . $nama_file_db;
        if (move_uploaded_file($file_tmp, $file_dest)) {
            db_execute($conn, "INSERT INTO produk (nama_produk, harga, deskripsi, tipe_produk, file_upload) VALUES (?, ?, ?, ?, ?)", ["sisss", $nama, $harga, $deskripsi, $tipe, $nama_file_db]);
            flash('success', 'Produk berhasil ditambahkan!');
        } else {
            flash('error', 'Gagal mengupload file.');
        }
    } else {
        flash('error', 'File produk wajib diupload.');
    }
    header("Location: produk.php");
    exit;
}
if (isset($_POST['update']) && csrf_validate()) {
    $id = (int) ($_POST['id_produk'] ?? 0);
    $nama = $_POST['nama_produk'] ?? '';
    $harga = (int) ($_POST['harga'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';
    $tipe = $_POST['tipe_produk'] ?? 'Lainnya';
    if (!array_key_exists($tipe, tipe_produk_list())) $tipe = 'Lainnya';
    $file_name = $_FILES['file_upload']['name'] ?? '';
    $file_tmp = $_FILES['file_upload']['tmp_name'] ?? '';
    if ($file_name != "") {
        $d_lama = db_query_one($conn, "SELECT file_upload FROM produk WHERE id = ?", ["i", $id]);
        if ($d_lama && file_exists("../uploads/" . $d_lama['file_upload'])) { unlink("../uploads/" . $d_lama['file_upload']); }
        $nama_file_db = time() . "_" . $file_name;
        move_uploaded_file($file_tmp, "../uploads/" . $nama_file_db);
        db_execute($conn, "UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, tipe_produk = ?, file_upload = ? WHERE id = ?", ["sisssi", $nama, $harga, $deskripsi, $tipe, $nama_file_db, $id]);
    } else {
        db_execute($conn, "UPDATE produk SET nama_produk = ?, harga = ?, deskripsi = ?, tipe_produk = ? WHERE id = ?", ["sissi", $nama, $harga, $deskripsi, $tipe, $id]);
    }
    flash('success', 'Produk berhasil diupdate!');
    header("Location: produk.php");
    exit;
}
if (isset($_POST['hapus_produk']) && csrf_validate()) {
    $id = (int) ($_POST['produk_id'] ?? 0);
    $data_file = db_query_one($conn, "SELECT file_upload FROM produk WHERE id = ?", ["i", $id]);
    if ($data_file && file_exists("../uploads/" . $data_file['file_upload'])) { unlink("../uploads/" . $data_file['file_upload']); }
    db_execute($conn, "DELETE FROM produk WHERE id = ?", ["i", $id]);
    flash('success', 'Produk berhasil dihapus.');
    header("Location: produk.php");
    exit;
}
include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <?= flash_render() ?>
        <div class="flex items-center gap-4 mb-3">
            <form method="GET" action="" class="relative flex-1">
                <?php if (isset($_GET['tipe'])): ?><input type="hidden" name="tipe" value="<?= e($_GET['tipe']) ?>"><?php endif; ?>
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="q" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" placeholder="Cari nama produk atau deskripsi..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
                <?php if (isset($_GET['q']) && $_GET['q'] !== ''): ?>
                <a href="produk.php<?= isset($_GET['tipe']) ? '?tipe=' . e($_GET['tipe']) : '' ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                <?php endif; ?>
            </form>
            <button onclick="openModal('tambah')" class="inline-flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition shrink-0" style="background:#42B549">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Produk
            </button>
        </div>
        <?php
        $tipe_counts = [];
        $rows_tipe = db_query($conn, "SELECT tipe_produk, COUNT(*) as total FROM produk GROUP BY tipe_produk", []);
        foreach ($rows_tipe as $rt) { $tipe_counts[$rt['tipe_produk']] = (int) $rt['total']; }
        $total_produk = array_sum($tipe_counts);
        $current_tipe = $_GET['tipe'] ?? '';
        $q_param = isset($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '';
        ?>
        <div class="flex items-center gap-2 mb-5 flex-wrap">
            <a href="?<?= ltrim($q_param, '&') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_tipe === '' ? 'ring-2 ring-offset-1 ring-gray-300' : 'hover:opacity-80' ?>" style="color:#374151; background:#E5E7EB">Semua <span class="ml-1 opacity-70"><?= $total_produk ?></span></a>
            <?php foreach (tipe_produk_list() as $key => $cfg): $count = $tipe_counts[$key] ?? 0; ?>
            <a href="?tipe=<?= urlencode($key) ?><?= $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_tipe === $key ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:<?= $cfg['color'] ?>; background:<?= $cfg['bg'] ?>; <?= $current_tipe === $key ? 'ring-color:'.$cfg['color'] : '' ?>"><?= e($cfg['label']) ?> <span class="ml-1 opacity-70"><?= $count ?></span></a>
            <?php endforeach; ?>
        </div>

        <!-- Modal Tambah Produk -->
        <div id="modal-tambah" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-6 rounded-full" style="background:#42B549"></div>
                            <h2 class="font-bold text-gray-800">Tambah Produk Baru</h2>
                        </div>
                        <button onclick="closeModal('tambah')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        <?= csrf_field() ?>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Produk</label><input type="text" name="nama_produk" placeholder="Nama produk" required></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Harga (Rp)</label><input type="hidden" name="harga" id="tambah-harga-raw" value="0"><input type="text" id="tambah-harga-display" placeholder="0" required oninput="formatHargaInput(this, 'tambah-harga-raw')"></div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tipe Produk</label>
                                <select name="tipe_produk" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                    <?php foreach (tipe_produk_list() as $key => $cfg): ?>
                                    <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload" required></div>
                        </div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" placeholder="Deskripsi produk..." required rows="3"></textarea></div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="closeModal('tambah')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                            <button type="submit" name="tambah" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">Tambah Produk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Produk -->
        <div id="modal-edit" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('edit')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-6 rounded-full" style="background:#1976D2"></div>
                            <h2 class="font-bold text-gray-800">Edit Produk: <span id="edit-title" style="color:#1976D2"></span></h2>
                        </div>
                        <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_produk" id="edit-id">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama Produk</label><input type="text" name="nama_produk" id="edit-nama" required></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Harga (Rp)</label><input type="hidden" name="harga" id="edit-harga-raw" value="0"><input type="text" id="edit-harga-display" required oninput="formatHargaInput(this, 'edit-harga-raw')"></div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tipe Produk</label>
                                <select name="tipe_produk" id="edit-tipe" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                    <?php foreach (tipe_produk_list() as $key => $cfg): ?>
                                    <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">File Produk</label><input type="file" name="file_upload"><p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ganti file</p></div>
                        </div>
                        <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi</label><textarea name="deskripsi" id="edit-deskripsi" required rows="3"></textarea></div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                            <button type="submit" name="update" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Produk</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php 
                    $search = $_GET['q'] ?? '';
                    $filter_tipe = $_GET['tipe'] ?? '';
                    $where_clauses = [];
                    $params = [];
                    $types = "";
                    
                    if ($filter_tipe !== '' && array_key_exists($filter_tipe, tipe_produk_list())) {
                        $where_clauses[] = "tipe_produk = ?";
                        $params[] = $filter_tipe;
                        $types .= "s";
                    }
                    if ($search !== '') {
                        $where_clauses[] = "(nama_produk LIKE ? OR deskripsi LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $types .= "ss";
                    }
                    
                    $where = "";
                    if (!empty($where_clauses)) {
                        $where = " WHERE " . implode(" AND ", $where_clauses);
                        array_unshift($params, $types);
                    }
                    
                    $paging = paginate($conn, "SELECT COUNT(*) as c FROM produk" . $where, $params, 10);
                    $products = db_query($conn, "SELECT * FROM produk" . $where . " ORDER BY id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'], $params); 
                    if (count($products) == 0): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada produk ditemukan.</td></tr>
                    <?php endif;
                    foreach($products as $r){ ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-800"><?= e($r['nama_produk']); ?></p>
                            <p class="text-xs text-gray-500 max-w-xs truncate"><?= e($r['deskripsi']); ?></p>
                        </td>
                        <td class="px-6 py-4"><?= tipe_produk_badge($r['tipe_produk'] ?? 'Lainnya') ?></td>
                        <td class="px-6 py-4 font-bold" style="color:#42B549"><?= rupiah($r['harga']); ?></td>
                        <td class="px-6 py-4">
                            <?php 
                            $d_rate = db_query_one($conn, "SELECT AVG(rating) as avg_rate, COUNT(id) as total FROM transaksi WHERE produk_id = ? AND rating > 0", ["i", $r['id']]);
                            $avg = $d_rate['avg_rate'] ? round($d_rate['avg_rate'], 1) : "0.0";
                            ?>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-sm font-semibold text-gray-700"><?= $avg; ?></span>
                                <span class="text-xs text-gray-400">(<?= $d_rate['total'] ?>)</span>
                            </div>
                        </td>
                        <td class="px-6 py-4"><a href="../uploads/<?= e($r['file_upload']); ?>" target="_blank" class="text-sm font-medium hover:underline" style="color:#1976D2">Lihat File</a></td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['nama_produk']), ENT_QUOTES) ?>, <?= (int)$r['harga'] ?>, <?= htmlspecialchars(json_encode($r['tipe_produk'] ?? 'Lainnya'), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['deskripsi']), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="produk_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="hapus_produk" value="1" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?= pagination_render($paging) ?>
        </div>

<script>
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
function openEdit(id, nama, harga, tipe, deskripsi) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-harga-raw').value = harga;
    document.getElementById('edit-harga-display').value = formatHargaValue(harga);
    document.getElementById('edit-tipe').value = tipe;
    document.getElementById('edit-deskripsi').value = deskripsi;
    document.getElementById('edit-title').textContent = nama;
    openModal('edit');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('tambah');
        closeModal('edit');
    }
});
</script>
<?php include '../includes/admin_footer.php'; ?>
