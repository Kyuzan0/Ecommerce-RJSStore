<div class="flex items-center gap-4 mb-2">
    <form method="GET" action="" class="relative flex-1">
        <?php if (isset($_GET['status']) && $_GET['status'] !== ''): ?><input type="hidden" name="status" value="<?= e($_GET['status']) ?>"><?php endif; ?>
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama user atau produk..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
        <?php if ($search !== ''): ?>
        <a href="<?= url('/admin-transaksi') . (isset($_GET['status']) ? '?status=' . e($_GET['status']) : '') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <?php endif; ?>
    </form>
</div>
<?php
$q_param = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="flex items-center gap-2 mb-3 flex-wrap">
    <a href="<?= url('/admin-transaksi') . '?' . ltrim($q_param, '&') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_status === '' ? 'ring-2 ring-offset-1 ring-gray-300' : 'hover:opacity-80' ?>" style="color:#374151; background:#E5E7EB">Semua <span class="ml-1 opacity-70"><?= $total_transaksi ?></span></a>
    <?php foreach (status_transaksi_list() as $key => $cfg): $count = $status_counts[$key] ?? 0; ?>
    <a href="<?= url('/admin-transaksi') . '?status=' . urlencode($key) . $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_status === $key ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:<?= $cfg['color'] ?>; background:<?= $cfg['bg'] ?>; <?= $current_status === $key ? 'ring-color:'.$cfg['color'] : '' ?>"><?= e($cfg['label']) ?> <span class="ml-1 opacity-70"><?= $count ?></span></a>
    <?php endforeach; ?>
</div>

<!-- Modal Update Status -->
<div id="modal-status" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('status')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-md pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-6 rounded-full" style="background:#1976D2"></div>
                    <h2 class="font-bold text-gray-800">Ubah Status Transaksi</h2>
                </div>
                <button onclick="closeModal('status')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="transaksi_id" id="status-id">
                <div>
                    <p class="text-sm text-gray-600 mb-3">Transaksi: <span id="status-info" class="font-semibold text-gray-800"></span></p>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Status Baru</label>
                    <select name="new_status" id="status-select" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500" style="transition:border 0.15s">
                        <?php foreach (status_transaksi_list() as $key => $cfg): ?>
                        <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('status')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Toast -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 z-50 hidden" style="transform:translateX(-50%) translateY(20px); opacity:0; transition:transform 0.3s cubic-bezier(0.21,1.02,0.73,1), opacity 0.2s">
    <div class="flex items-center gap-4 px-5 py-3 rounded-2xl shadow-lg border border-gray-200 bg-white">
        <span class="text-sm font-semibold text-gray-700"><span id="selected-count">0</span> transaksi dipilih</span>
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
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Transaksi</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (count($transactions) == 0): ?>
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">Tidak ada transaksi ditemukan.</td></tr>
                <?php endif;
                foreach($transactions as $i => $r){ ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-3 py-3 text-center"><input type="checkbox" name="transaksi_ids[]" value="<?= $r['id'] ?>" class="row-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></td>
                    <td class="px-5 py-3 text-center text-sm text-gray-500 font-medium"><?= $paging['offset'] + $i + 1 ?></td>
                    <td class="px-5 py-3">
                        <p class="font-semibold text-gray-800 text-sm"><?= e($r['nama_user']); ?></p>
                        <p class="text-xs text-gray-500"><?= e($r['order_ref'] ?? '-'); ?></p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-700 text-sm"><?= e($r['nama_produk']); ?></p>
                    </td>
                    <td class="px-5 py-4 font-bold" style="color:#42B549"><?= rupiah($r['harga']); ?></td>
                    <td class="px-5 py-4 text-sm text-gray-600"><?= format_tanggal($r['tanggal']); ?></td>
                    <td class="px-5 py-4"><?= status_transaksi_badge($r['status']) ?></td>
                    <td class="px-5 py-4 text-center">
                        <button onclick="openStatus(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['nama_user'] . ' - ' . $r['nama_produk']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['status']), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#E3F2FD; color:#1565C0">Ubah Status</button>
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
function openStatus(id, info, currentStatus) {
    document.getElementById('status-id').value = id;
    document.getElementById('status-info').textContent = info;
    document.getElementById('status-select').value = currentStatus;
    openModal('status');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('status');
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
    if (!confirm('Hapus ' + count + ' transaksi yang dipilih? Tindakan ini tidak dapat dibatalkan.')) return;

    var container = document.getElementById('bulk-delete-ids');
    container.innerHTML = '';
    checked.forEach(function(cb) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'transaksi_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    document.getElementById('bulk-delete-form').submit();
}
</script>
