<!-- Back Link -->
<div class="mb-6">
    <a href="<?= url('/customer/dashboard') ?>" class="inline-flex items-center text-gray-600 hover:text-green-600">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Dashboard
    </a>
</div>

<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Profile Saya</h1>
</div>

<!-- Profile Card -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center mb-6">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mr-4" style="background-color: #42B549;">
            <span class="text-2xl font-bold text-white">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </span>
        </div>
        <div>
            <h2 class="text-xl font-semibold text-gray-800"><?= e($user['name']) ?></h2>
            <p class="text-gray-600"><?= e($user['email']) ?></p>
        </div>
    </div>
</div>

<!-- Update Profile Form -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Profile</h3>
    
    <form method="POST" action="<?= url('/customer/profile') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_profile">
        
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="<?= e($user['name']) ?>" 
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   value="<?= e($user['email']) ?>" 
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            Simpan Perubahan
        </button>
    </form>
</div>

<!-- Update Password Form -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h3>
    
    <form method="POST" action="<?= url('/customer/profile') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_password">
        
        <div class="mb-4">
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
            <input type="password" 
                   id="current_password" 
                   name="current_password" 
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <div class="mb-4">
            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
            <input type="password" 
                   id="new_password" 
                   name="new_password" 
                   required
                   minlength="6"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
        </div>
        
        <div class="mb-4">
            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
            <input type="password" 
                   id="confirm_password" 
                   name="confirm_password" 
                   required
                   minlength="6"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
        </div>
        
        <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
            Ubah Password
        </button>
    </form>
</div>
