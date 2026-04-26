<div class="flex items-center gap-4 mb-2">
    <form method="GET" action="" class="relative flex-1">
        <?php if (isset($_GET['role']) && $_GET['role'] !== ''): ?><input type="hidden" name="role" value="<?= e($_GET['role']) ?>"><?php endif; ?>
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama atau email pengguna..." class="w-full py-2 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500 transition" style="padding-left: 2.5rem; padding-right: 2.5rem;">
        <?php if ($search !== ''): ?>
        <a href="<?= url('/admin-user') . (isset($_GET['role']) && $_GET['role'] !== '' ? '?role=' . e($_GET['role']) : '') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </a>
        <?php endif; ?>
    </form>
    <button onclick="openModal('tambah')" class="inline-flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition shrink-0" style="background:#42B549">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah User
    </button>
</div>
<?php
$q_param = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="flex items-center gap-2 mb-3 flex-wrap">
    <a href="<?= url('/admin-user') . '?' . ltrim($q_param, '&') ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_role === '' ? 'ring-2 ring-offset-1 ring-gray-300' : 'hover:opacity-80' ?>" style="color:#374151; background:#E5E7EB">Semua <span class="ml-1 opacity-70"><?= $total_users ?></span></a>
    <a href="<?= url('/admin-user') . '?role=admin' . $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_role === 'admin' ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:#1565C0; background:#E3F2FD; <?= $current_role === 'admin' ? 'ring-color:#1565C0' : '' ?>">Admin <span class="ml-1 opacity-70"><?= $role_counts['admin'] ?? 0 ?></span></a>
    <a href="<?= url('/admin-user') . '?role=customer' . $q_param ?>" class="text-xs font-bold px-3 py-1.5 rounded-lg transition <?= $current_role === 'customer' ? 'ring-2 ring-offset-1' : 'hover:opacity-80' ?>" style="color:#2E7D32; background:#E8F5E9; <?= $current_role === 'customer' ? 'ring-color:#2E7D32' : '' ?>">Customer <span class="ml-1 opacity-70"><?= $role_counts['customer'] ?? 0 ?></span></a>
</div>

<!-- Modal Tambah User -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-6 rounded-full" style="background:#42B549"></div>
                    <h2 class="font-bold text-gray-800">Tambah User Baru</h2>
                </div>
                <button onclick="closeModal('tambah')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah_user">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama</label><input type="text" name="name" placeholder="Nama lengkap" required></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Email</label><input type="email" name="email" placeholder="email@contoh.com" required></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Password</label><input type="password" name="password" placeholder="Minimal 6 karakter" required></div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Role</label>
                        <select name="role" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('tambah')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">Tambah User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('edit')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-6 rounded-full" style="background:#1976D2"></div>
                    <h2 class="font-bold text-gray-800">Edit User: <span id="edit-title" style="color:#1976D2"></span></h2>
                </div>
                <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit-id">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Nama</label><input type="text" name="name" id="edit-nama" required></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Email</label><input type="email" name="email" id="edit-email" required></div>
                    <div><label class="block text-xs font-semibold text-gray-500 mb-1.5">Password Baru</label><input type="password" name="password" id="edit-password" placeholder="Kosongkan jika tidak diubah"><p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah password</p></div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Role</label>
                        <select name="role" id="edit-role" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-green-500" style="transition:border 0.15s">
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 flex-1 flex flex-col overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</th>
                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (count($users) == 0): ?>
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada pengguna ditemukan.</td></tr>
            <?php endif;
            foreach($users as $r){ ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:<?= $r['role']=='admin' ? '#1565C0' : '#42B549' ?>">
                            <?= strtoupper(substr(e($r['name']),0,1)) ?>
                        </div>
                        <span class="font-semibold text-gray-800"><?= e($r['name']); ?></span>
                    </div>
                </td>
                <td class="px-5 py-4 text-gray-500"><?= e($r['email']); ?></td>
                <td class="px-5 py-4">
                    <?php if($r['role']=='admin'): ?>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E3F2FD; color:#1565C0">ADMIN</span>
                    <?php else: ?>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">CUSTOMER</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-4">
                    <span class="text-sm font-semibold text-gray-700"><?= (int) $r['jumlah_transaksi'] ?></span>
                    <span class="text-xs text-gray-400 ml-1">transaksi</span>
                </td>
                <td class="px-5 py-4 text-center">
                    <?php if($r['id'] != $this->auth->id()){ ?>
                        <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['role']), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                        <form method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="hapus_user">
                            <input type="hidden" name="user_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                        </form>
                    <?php } else { ?>
                        <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['role']), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                        <span class="text-xs text-gray-400 italic">Kamu</span>
                    <?php } ?>
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
function openEdit(id, nama, email, role) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nama').value = nama;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value = role;
    document.getElementById('edit-password').value = '';
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
