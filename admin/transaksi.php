<?php
$page_title = 'Kelola Transaksi';
$active_page = 'transaksi';
$extra_css = 'input[type=text],input[type=email],input[type=password],input[type=number],textarea,input[type=file],select { width:100%; padding:10px 14px; border:1px solid #e5e7eb; border-radius:10px; font-size:14px; outline:none; transition:border 0.15s; } input:focus,textarea:focus,select:focus { border-color:#42B549; box-shadow:0 0 0 3px rgba(66,181,73,0.12); }';
include '../includes/admin_header.php';

if (isset($_POST['update_status']) && csrf_validate()) {
    $id = (int) ($_POST['transaksi_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    if ($status !== '' && in_array($status, ['pending', 'success', 'cancelled'])) {
        db_execute($conn, "UPDATE transaksi SET status = ? WHERE id = ?", ["si", $status, $id]);
        flash('success', 'Status transaksi berhasil diupdate!');
    }
    header("Location: transaksi.php");
    exit;
}
if (isset($_POST['hapus_transaksi']) && csrf_validate()) {
    $id = (int) ($_POST['transaksi_id'] ?? 0);
    db_execute($conn, "DELETE FROM transaksi WHERE id = ?", ["i", $id]);
    flash('success', 'Transaksi berhasil dihapus.');
    header("Location: transaksi.php");
    exit;
}

include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <?= flash_render() ?>
        <div class="flex items-center gap-4 mb-3">
            <form method="GET" action="" class="relative flex-1">
                <?php if (isset($_GET['status']) && $_GET['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($_GET['status']) ?>"><?php endif; ?>
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="q" value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" placeholder="Cari nama pembeli atau produk..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
                <?php if (isset($_GET['q']) && $_GET['q'] !== ''): ?>
                <a href="transaksi.php<?= isset($_GET['status']) && $_GET['status'] !== '' ? '?status=' . e($_GET['status']) : '' ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
                <?php endif; ?>
            </form>
        </div>
        <?php
        $status_counts = [];
        $rows_status = db_query($conn, "SELECT LOWER(status) as status, COUNT(*) as total FROM transaksi GROUP BY LOWER(status)", []);
        foreach ($rows_status as $rs) { $status_counts[$rs['status']] = (int) $rs['total']; }
        $total_transaksi = array_sum($status_counts);
        $current_status = strtolower($_GET['status'] ?? '');
        $q_param = isset($_GET['q']) && $_GET['q'] !== '' ? '&q=' . urlencode($_GET['q']) : '';

        $status_tabs = [
            'success' => ['label' => 'Berhasil', 'color' => '#2E7D32', 'bg' => '#E8F5E9'],
            'pending' => ['label' => 'Pending', 'color' => '#F57F17', 'bg' => '#FFF8E1'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => '#C62828', 'bg' => '#FFEBEE'],
        ];
        ?>
        <div class="flex items-center gap-2 mb-5 flex-wrap">
            <a href="?<?= ltrim($q_param, '&') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_status === '' ? 'ring-2 ring-offset-1 ring-gray-300' : 'hover:opacity-80' ?>" style="color:#374151; background:#E5E7EB">Semua <span class="ml-1 opacity-70"><?= $total_transaksi ?></span></a>
            <?php foreach ($status_tabs as $key => $cfg): $count = $status_counts[$key] ?? 0; ?>
            <a href="?status=<?= $key ?><?= $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_status === $key ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:<?= $cfg['color'] ?>; background:<?= $cfg['bg'] ?>; <?= $current_status === $key ? 'ring-color:'.$cfg['color'] : '' ?>"><?= $cfg['label'] ?> <span class="ml-1 opacity-70"><?= $count ?></span></a>
            <?php endforeach; ?>
        </div>

        <!-- Modal Edit Status -->
        <div id="modal-edit" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('edit')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-md pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-6 rounded-full" style="background:#1976D2"></div>
                            <h2 class="font-bold text-gray-800">Update Status: <span id="edit-title" style="color:#1976D2"></span></h2>
                        </div>
                        <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" class="p-6 space-y-4">
                        <?= csrf_field() ?>
                        <input type="hidden" name="transaksi_id" id="edit-id">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Status Transaksi</label>
                            <select name="status" id="edit-status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                                <option value="pending">Pending</option>
                                <option value="success">Berhasil</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                            <button type="submit" name="update_status" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Transaksi</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pembeli</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penilaian</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php
                    $search = $_GET['q'] ?? '';
                    $filter_status = strtolower($_GET['status'] ?? '');
                    $where_clauses = [];
                    $params = [];
                    $types = "";

                    if ($filter_status !== '' && in_array($filter_status, ['pending', 'success', 'cancelled'])) {
                        $where_clauses[] = "LOWER(t.status) = ?";
                        $params[] = $filter_status;
                        $types .= "s";
                    }
                    if ($search !== '') {
                        $where_clauses[] = "(u.name LIKE ? OR p.nama_produk LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $types .= "ss";
                    }

                    $where = "";
                    $join = " JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id";
                    if (!empty($where_clauses)) {
                        $where = " WHERE " . implode(" AND ", $where_clauses);
                        array_unshift($params, $types);
                    }

                    $paging = paginate($conn, "SELECT COUNT(*) as c FROM transaksi t" . $join . $where, $params, 15);
                    $rows = db_query($conn, "SELECT t.*, u.name, p.nama_produk FROM transaksi t" . $join . $where . " ORDER BY t.tanggal DESC, t.id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'], $params);
                    if (count($rows) == 0): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada transaksi ditemukan.</td></tr>
                    <?php endif;
                    foreach($rows as $r) { ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500"><?= format_tanggal($r['tanggal']); ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:#42B549">
                                    <?= strtoupper(substr(e($r['name']),0,1)) ?>
                                </div>
                                <span class="font-medium text-gray-800"><?= e($r['name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-800"><?= e($r['nama_produk']); ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $status = strtolower($r['status']);
                            if($status == 'success') echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">BERHASIL</span>';
                            elseif($status == 'pending') echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#FFF8E1; color:#F57F17">PENDING</span>';
                            elseif($status == 'cancelled') echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#FFEBEE; color:#C62828">DIBATALKAN</span>';
                            else echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">' . e(strtoupper($r['status'])) . '</span>';
                            ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($r['rating'] > 0): ?>
                                <div class="flex items-center gap-1 mb-1">
                                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-bold text-gray-700"><?= (int)$r['rating'] ?>/5</span>
                                </div>
                                <?php if(!empty($r['ulasan'])): ?>
                                    <p class="text-xs text-gray-500 italic max-w-xs truncate" title="<?= e($r['ulasan']) ?>">"<?= e($r['ulasan']) ?>"</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name'] . ' - ' . $r['nama_produk']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(strtolower($r['status'])), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus transaksi ini?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="transaksi_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="hapus_transaksi" value="1" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?= pagination_render($paging) ?>
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
function openEdit(id, title, status) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-status').value = status;
    document.getElementById('edit-title').textContent = title;
    openModal('edit');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('edit');
    }
});
</script>
<?php include '../includes/admin_footer.php'; ?>
