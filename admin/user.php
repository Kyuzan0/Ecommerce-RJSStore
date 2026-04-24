<?php
$page_title = 'Kelola User';
$active_page = 'user';
include '../includes/admin_header.php';

if (isset($_POST['hapus_user']) && csrf_validate()) {
    $id = (int) ($_POST['user_id'] ?? 0);
    if ($id != (int) $_SESSION['id']) {
        db_execute($conn, "DELETE FROM users WHERE id = ?", ["i", $id]);
        flash('success', 'Pengguna berhasil dihapus.');
    } else {
        flash('error', 'Tidak bisa menghapus akun sendiri.');
    }
    header("Location: user.php");
    exit;
}

include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <?= flash_render() ?>
        <h1 class="text-xl font-bold text-gray-800 mb-5">Daftar Pengguna</h1>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-bold text-gray-800">Semua Pengguna</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php 
                    $paging = paginate($conn, "SELECT COUNT(*) as c FROM users", [], 15);
                    $users = db_query($conn, "SELECT * FROM users ORDER BY role ASC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset']); 
                    foreach($users as $r){ ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:<?= $r['role']=='admin' ? '#1565C0' : '#42B549' ?>">
                                    <?= strtoupper(substr(e($r['name']),0,1)) ?>
                                </div>
                                <span class="font-semibold text-gray-800"><?= e($r['name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= e($r['email']); ?></td>
                        <td class="px-6 py-4">
                            <?php if($r['role']=='admin'): ?>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E3F2FD; color:#1565C0">ADMIN</span>
                            <?php else: ?>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">CUSTOMER</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($r['id'] != $_SESSION['id']){ ?>
                                <form method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="hapus_user" value="1" class="text-xs font-semibold px-3 py-1.5 rounded-lg transition cursor-pointer" style="background:#FFEBEE; color:#C62828">Hapus</button>
                                </form>
                            <?php } else { ?>
                                <span class="text-xs text-gray-400 italic">Kamu</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?= pagination_render($paging) ?>
        </div>

<?php include '../includes/admin_footer.php'; ?>
