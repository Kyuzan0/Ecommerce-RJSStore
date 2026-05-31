<!-- Back Link -->
<div class="mb-4">
    <a href="<?= url('/customer/dashboard') ?>" class="inline-flex items-center text-gray-600 hover:text-green-600 text-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Dashboard
    </a>
</div>

<!-- Page Header -->
<div class="ds-page-header">
    <h1 class="ds-page-title">Profile Saya</h1>
</div>

<!-- Profile Card -->
<div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 mb-4 sm:mb-6">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: #42B549;">
            <span class="text-xl sm:text-2xl font-bold text-white">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </span>
        </div>
        <div class="min-w-0">
            <h2 class="text-base sm:text-lg font-semibold text-gray-800 truncate"><?= e($user['name']) ?></h2>
            <p class="text-sm text-gray-500 truncate"><?= e($user['email']) ?></p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Update Profile Form -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Informasi Profile</h3>

        <form method="POST" action="<?= url('/customer/profile') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="<?= e($user['name']) ?>"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="<?= e($user['email']) ?>"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button type="submit"
                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <!-- Update Password Form -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">Ubah Password</h3>

        <form method="POST" action="<?= url('/customer/profile') ?>" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_password">

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Saat Ini</label>
                <input type="password"
                       id="current_password"
                       name="current_password"
                       required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                <input type="password"
                       id="new_password"
                       name="new_password"
                       required
                       minlength="6"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password"
                       id="confirm_password"
                       name="confirm_password"
                       required
                       minlength="6"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button type="submit"
                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-semibold transition-colors">
                Ubah Password
            </button>
        </form>
    </div>
</div>
