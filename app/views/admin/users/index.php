<div class="ds-toolbar">
    <form method="GET" action="" class="relative ds-toolbar__search">
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
    <button onclick="openModal('tambah')" class="ds-toolbar__action px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Tambah User
    </button>
</div>
<?php
$q_param = $search !== '' ? '&q=' . urlencode($search) : '';
?>
<div class="ds-filter-row">
    <a href="<?= url('/admin-user') . '?' . ltrim($q_param, '&') ?>" class="ds-chip ds-chip--neutral ds-chip--filter <?= $current_role === '' ? 'is-active' : '' ?>">Semua <span class="ds-chip__count"><?= $total_users ?></span></a>
    <a href="<?= url('/admin-user') . '?role=admin' . $q_param ?>" class="ds-chip ds-chip--accent ds-chip--filter <?= $current_role === 'admin' ? 'is-active' : '' ?>">Admin <span class="ds-chip__count"><?= $role_counts['admin'] ?? 0 ?></span></a>
    <a href="<?= url('/admin-user') . '?role=customer' . $q_param ?>" class="ds-chip ds-chip--success ds-chip--filter <?= $current_role === 'customer' ? 'is-active' : '' ?>">Customer <span class="ds-chip__count"><?= $role_counts['customer'] ?? 0 ?></span></a>
</div>

<!-- Modal Tambah User -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('tambah')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0" style="background:#42B549"></div>
                    <h2 class="font-bold text-gray-800 truncate">Tambah User Baru</h2>
                </div>
                <button onclick="closeModal('tambah')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="tambah_user">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="ds-modal-grid">
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
                </div>
                <div class="ds-modal-footer">
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
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-2xl pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0" style="background:#1976D2"></div>
                    <h2 class="font-bold text-gray-800 truncate">Edit: <span id="edit-title" style="color:#1976D2"></span></h2>
                </div>
                <button onclick="closeModal('edit')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" class="flex flex-col flex-1 overflow-hidden">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit-id">
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="ds-modal-grid">
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
                </div>
                <div class="ds-modal-footer">
                    <button type="button" onclick="closeModal('edit')" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-white rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#1976D2">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail User -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('detail')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 pointer-events-none">
        <div class="ds-modal-shell bg-white shadow-xl border border-gray-100 max-w-md pointer-events-auto modal-content" style="transform:scale(0.95);opacity:0;transition:transform 0.25s cubic-bezier(0.21,1.02,0.73,1),opacity 0.2s">
            <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-2 h-6 rounded-full flex-shrink-0 bg-blue-500"></div>
                    <h2 class="font-bold text-gray-800 truncate">Detail Pengguna</h2>
                </div>
                <button onclick="closeModal('detail')" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 text-sm text-gray-700">
                <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                    <div id="detail-avatar" class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                        U
                    </div>
                    <div>
                        <h3 id="detail-name" class="font-bold text-gray-800 text-base leading-tight"></h3>
                        <p id="detail-email" class="text-xs text-gray-500 mt-0.5"></p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Role</span>
                        <span id="detail-role" class="text-xs font-bold px-2.5 py-1 rounded-full"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Status Keamanan</span>
                        <span id="detail-status" class="text-xs font-bold"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Total Transaksi</span>
                        <span class="text-xs font-bold text-gray-800"><span id="detail-transaksi">0</span> transaksi</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Tanggal Daftar</span>
                        <span id="detail-registered" class="text-xs font-medium text-gray-600"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">IP Daftar</span>
                        <span id="detail-register-ip" class="text-xs font-mono text-gray-600"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Aktivitas Terakhir</span>
                        <span id="detail-last-active" class="text-xs font-medium text-gray-600"></span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-semibold text-gray-400 uppercase">IP Terakhir</span>
                        <span id="detail-last-ip" class="text-xs font-mono text-gray-600"></span>
                    </div>
                </div>
            </div>

            <div class="ds-modal-footer flex-shrink-0">
                <button type="button" onclick="closeModal('detail')" class="w-full px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Toast -->
<div id="bulk-action-bar" class="ds-bulkbar-wrap hidden" style="opacity:0; transition:opacity 0.2s">
    <div class="flex items-center justify-between sm:justify-start gap-2 sm:gap-3 px-3 sm:px-4 py-2 sm:py-3 rounded-2xl shadow-lg border border-gray-200 bg-white w-full sm:w-auto">
        <span class="text-xs sm:text-sm font-semibold text-gray-700 whitespace-nowrap"><span id="selected-count">0</span> pengguna dipilih</span>
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
    <div class="ds-table-wrap hidden md:block">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-3 py-1.5 text-center w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600"></th>
                <th class="px-5 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-5 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info</th>
                <th class="px-5 py-1.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaksi</th>
                <th class="px-5 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (count($users) == 0): ?>
            <tr><td colspan="9" class="px-6 py-8 text-center text-gray-500">Tidak ada pengguna ditemukan.</td></tr>
            <?php endif;
            foreach($users as $i => $r){ ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-3 py-3 text-center">
                    <?php if($r['id'] != $this->auth->id()): ?>
                    <input type="checkbox" name="user_ids[]" value="<?= $r['id'] ?>" class="row-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer accent-green-600">
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-center text-sm text-gray-500 font-medium"><?= $paging['offset'] + $i + 1 ?></td>
                <td class="px-5 py-3">
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
                <td class="px-5 py-4 text-center">
                    <?php
                    $isOnline = !empty($r['last_active_at']) && strtotime($r['last_active_at']) > (time() - 300); // 5 menit
                    ?>
                    <?php if ($isOnline): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>Online</span>
                    <?php else: ?>
                        <span class="text-xs text-gray-400">Offline</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-4 font-mono text-xs text-gray-500">
                    <?= e($r['last_ip'] ?? '-') ?>
                </td>
                <td class="px-5 py-4">
                    <span class="text-sm font-semibold text-gray-700"><?= (int) $r['jumlah_transaksi'] ?></span>
                    <span class="text-xs text-gray-400 ml-1">transaksi</span>
                </td>
                <td class="px-5 py-4 text-center">
                    <button onclick="openDetail(<?= htmlspecialchars(json_encode([
                        'name' => $r['name'],
                        'email' => $r['email'],
                        'role' => $r['role'],
                        'jumlah_transaksi' => (int)$r['jumlah_transaksi'],
                        'created_at' => !empty($r['created_at']) ? date('d M Y H:i', strtotime($r['created_at'])) : '-',
                        'register_ip' => $r['register_ip'] ?? '-',
                        'last_active_at' => !empty($r['last_active_at']) ? date('d M Y H:i', strtotime($r['last_active_at'])) : '-',
                        'last_ip' => $r['last_ip'] ?? '-',
                        'is_online' => $isOnline
                    ]), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400">Detail</button>
                    <?php if($r['id'] != $this->auth->id()){ ?>
                        <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['role']), ENT_QUOTES) ?>)" class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg mr-1 transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                        <form method="POST" class="inline" data-confirm="Hapus user ini?">
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
    </div>

    <!-- Mobile card list -->
    <div class="md:hidden p-3 space-y-3">
        <?php if (count($users) == 0): ?>
        <p class="text-center text-sm text-gray-500 py-8">Tidak ada pengguna ditemukan.</p>
        <?php endif;
        foreach($users as $i => $r): ?>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 relative">
            <?php if($r['id'] != $this->auth->id()): ?>
            <input type="checkbox" name="user_ids[]" value="<?= $r['id'] ?>" class="row-checkbox absolute top-4 right-4 w-5 h-5 rounded border-gray-300 cursor-pointer accent-green-600">
            <?php endif; ?>
            <div class="flex items-center gap-3 mb-3 pr-8">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0" style="background:<?= $r['role']=='admin' ? '#1565C0' : '#42B549' ?>">
                    <?= strtoupper(substr(e($r['name']),0,1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-800 text-sm truncate"><?= e($r['name']); ?></p>
                    <p class="text-xs text-gray-500 truncate"><?= e($r['email']); ?></p>
                </div>
            </div>
            <div class="flex items-center flex-wrap gap-2 mb-3 text-xs">
                <?php if($r['role']=='admin'): ?>
                    <span class="font-bold px-2.5 py-1 rounded-full" style="background:#E3F2FD; color:#1565C0">ADMIN</span>
                <?php else: ?>
                    <span class="font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">CUSTOMER</span>
                <?php endif; ?>
                <span class="text-gray-500"><?= (int) $r['jumlah_transaksi'] ?> transaksi</span>
            </div>
            <div class="flex gap-2 pt-3 border-t border-gray-100">
                <button onclick="openDetail(<?= htmlspecialchars(json_encode([
                    'name' => $r['name'],
                    'email' => $r['email'],
                    'role' => $r['role'],
                    'jumlah_transaksi' => (int)$r['jumlah_transaksi'],
                    'created_at' => !empty($r['created_at']) ? date('d M Y H:i', strtotime($r['created_at'])) : '-',
                    'register_ip' => $r['register_ip'] ?? '-',
                    'last_active_at' => !empty($r['last_active_at']) ? date('d M Y H:i', strtotime($r['last_active_at'])) : '-',
                    'last_ip' => $r['last_ip'] ?? '-',
                    'is_online' => !empty($r['last_active_at']) && strtotime($r['last_active_at']) > (time() - 300)
                ]), ENT_QUOTES) ?>)" class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400">Detail</button>
                <?php if($r['id'] != $this->auth->id()): ?>
                    <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['role']), ENT_QUOTES) ?>)" class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit</button>
                    <form method="POST" class="flex-1" data-confirm="Hapus user ini?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="hapus_user">
                        <input type="hidden" name="user_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                    </form>
                <?php else: ?>
                    <button onclick="openEdit(<?= $r['id'] ?>, <?= htmlspecialchars(json_encode($r['name']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['email']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($r['role']), ENT_QUOTES) ?>)" class="flex-1 inline-flex items-center justify-center gap-1 text-xs font-semibold py-2 rounded-lg transition cursor-pointer" style="background:#FFF8E1; color:#F57F17">Edit Profil Saya</button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
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
function openDetail(user) {
    document.getElementById('detail-name').textContent = user.name;
    document.getElementById('detail-email').textContent = user.email;
    
    var avatar = document.getElementById('detail-avatar');
    avatar.textContent = user.name.charAt(0).toUpperCase();
    avatar.style.background = user.role === 'admin' ? '#1565C0' : '#42B549';
    
    var roleBadge = document.getElementById('detail-role');
    if (user.role === 'admin') {
        roleBadge.textContent = 'ADMIN';
        roleBadge.style.background = '#E3F2FD';
        roleBadge.style.color = '#1565C0';
    } else {
        roleBadge.textContent = 'CUSTOMER';
        roleBadge.style.background = '#E8F5E9';
        roleBadge.style.color = '#2E7D32';
    }
    
    var statusEl = document.getElementById('detail-status');
    if (user.is_online) {
        statusEl.className = 'inline-flex items-center gap-1 text-xs font-semibold text-green-700';
        statusEl.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>Online';
    } else {
        statusEl.className = 'text-xs text-gray-400';
        statusEl.textContent = 'Offline';
    }
    
    document.getElementById('detail-transaksi').textContent = user.jumlah_transaksi;
    document.getElementById('detail-registered').textContent = user.created_at;
    document.getElementById('detail-register-ip').textContent = user.register_ip;
    document.getElementById('detail-last-active').textContent = user.last_active_at;
    document.getElementById('detail-last-ip').textContent = user.last_ip;
    
    openModal('detail');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('tambah');
        closeModal('edit');
        closeModal('detail');
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
    window.showCustomConfirm('Konfirmasi Hapus', 'Hapus ' + count + ' pengguna yang dipilih? Tindakan ini tidak dapat dibatalkan.', function() {
        var container = document.getElementById('bulk-delete-ids');
        container.innerHTML = '';
        checked.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        document.getElementById('bulk-delete-form').submit();
    });
}
</script>
