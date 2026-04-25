<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <div class="mb-7">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">Masuk ke RJSStore</h1>
                <p class="text-sm text-gray-500">Nikmati belanja produk digital dengan mudah</p>
            </div>

            <?= flash_render() ?>

            <form method="POST" action="<?= url('/auth/login') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <?php if (!empty($next)): ?>
                    <input type="hidden" name="redirect_next" value="<?= e($next) ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" required placeholder="Masukkan email kamu"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition focus:outline-none focus:border-[#42B549] focus:ring-2 focus:ring-[#42B549]/15">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" required placeholder="Masukkan kata sandi"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm transition focus:outline-none focus:border-[#42B549] focus:ring-2 focus:ring-[#42B549]/15">
                </div>

                <button type="submit"
                    class="w-full text-white py-3 rounded-xl font-semibold transition text-sm mt-2 hover:opacity-90" style="background:#42B549">
                    Masuk
                </button>
            </form>

            <div class="flex items-center gap-3 my-6 text-gray-400 text-[13px]">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span>atau</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <p class="text-center text-sm text-gray-600">
                Belum punya akun?
                <a href="<?= url('/auth/register') ?>" class="font-semibold hover:underline ml-1" style="color:#42B549">Daftar Sekarang</a>
            </p>
        </div>
        <p class="text-center text-xs text-gray-400 mt-5">Dengan masuk, kamu menyetujui Syarat &amp; Ketentuan serta Kebijakan Privasi RJSStore</p>
    </div>
</div>
