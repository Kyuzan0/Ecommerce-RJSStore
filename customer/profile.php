<?php
$page_title = 'Settings Profile';
$active_page = 'profile';
include '../includes/customer_header.php';

$id = (int) $_SESSION['id'];

// Update profil
if (isset($_POST['update_profile']) && csrf_validate()) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $existing = db_query_one($conn, "SELECT id FROM users WHERE email = ? AND id != ?", ["si", $email, $id]);
    if ($existing) {
        flash('error', 'Email sudah digunakan oleh akun lain.');
    } else {
        db_execute($conn, "UPDATE users SET name = ?, email = ? WHERE id = ?", ["ssi", $name, $email, $id]);
        $_SESSION['name'] = $name;
        flash('success', 'Profil berhasil diperbarui.');
    }
    header("Location: profile.php");
    exit;
}

// Update password
if (isset($_POST['update_password']) && csrf_validate()) {
    $current_input = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $data_pw = db_query_one($conn, "SELECT password FROM users WHERE id = ?", ["i", $id]);
    
    $password_valid = false;
    if ($data_pw) {
        if (password_verify($current_input, $data_pw['password'])) {
            $password_valid = true;
        } elseif ($data_pw['password'] === md5($current_input)) {
            $password_valid = true;
        }
    }
    
    if (!$password_valid) {
        flash('error', 'Password lama tidak sesuai.');
    } elseif (strlen($new) < 6) {
        flash('error', 'Password baru minimal 6 karakter.');
    } elseif ($new != $confirm) {
        flash('error', 'Konfirmasi password tidak cocok.');
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        db_execute($conn, "UPDATE users SET password = ? WHERE id = ?", ["si", $new_hash, $id]);
        flash('success', 'Password berhasil diperbarui.');
    }
    header("Location: profile.php");
    exit;
}

$user = db_query_one($conn, "SELECT * FROM users WHERE id = ?", ["i", $id]);

include '../includes/customer_sidebar.php';
?>
        <div class="flex items-center gap-3 mb-6">
            <a href="dashboard.php" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-800">Settings Profile</h1>
        </div>

        <?= flash_render() ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informasi Profil -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#E8F5E9">
                        <svg class="w-5 h-5" style="color:#42B549" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Informasi Profil</h2>
                        <p class="text-xs text-gray-500">Perbarui nama dan email akun Anda</p>
                    </div>
                </div>
                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama</label>
                        <input type="text" name="name" value="<?= e($user['name']); ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition" onfocus="this.style.boxShadow='0 0 0 2px #42B549'" onblur="this.style.boxShadow='none'">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?= e($user['email']); ?>" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition" onfocus="this.style.boxShadow='0 0 0 2px #42B549'" onblur="this.style.boxShadow='none'">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                        <input type="text" value="<?= ucfirst(e($user['role'])); ?>" disabled class="w-full px-3 py-2.5 border border-gray-100 rounded-xl text-sm bg-gray-50 text-gray-500">
                    </div>
                    <button type="submit" name="update_profile" class="w-full py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90" style="background:#42B549">Simpan Perubahan</button>
                </form>
            </div>

            <!-- Ubah Password -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF3E0">
                        <svg class="w-5 h-5" style="color:#E65100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-800">Ubah Password</h2>
                        <p class="text-xs text-gray-500">Pastikan menggunakan password yang kuat</p>
                    </div>
                </div>
                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Lama</label>
                        <input type="password" name="current_password" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition" onfocus="this.style.boxShadow='0 0 0 2px #42B549'" onblur="this.style.boxShadow='none'">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition" onfocus="this.style.boxShadow='0 0 0 2px #42B549'" onblur="this.style.boxShadow='none'">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none transition" onfocus="this.style.boxShadow='0 0 0 2px #42B549'" onblur="this.style.boxShadow='none'">
                    </div>
                    <button type="submit" name="update_password" class="w-full py-2.5 rounded-xl text-white text-sm font-semibold transition hover:opacity-90" style="background:#E65100">Ubah Password</button>
                </form>
            </div>
        </div>

<?php include '../includes/customer_footer.php'; ?>
