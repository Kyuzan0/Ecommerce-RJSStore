<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.star-rating input { display: none; }
.star-rating label {
    cursor: pointer;
    font-size: 2rem;
    color: #d1d5db;
    transition: color 0.2s;
    padding: 0 4px;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f59e0b;
}
@media (max-width: 480px) {
    .star-rating label { font-size: 2.25rem; padding: 0 2px; }
}
</style>

<div class="max-w-2xl mx-auto">
    <!-- Rating Card -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-8">
        <h1 class="ds-page-title mb-2">Beri Rating Produk</h1>
        <p class="text-sm text-gray-600 mb-5">Bagikan pengalaman Anda dengan produk ini</p>

        <!-- Product Info -->
        <div class="bg-gray-50 rounded-xl p-4 mb-5">
            <h3 class="font-semibold text-gray-800"><?= e($transaksi['nama_produk']) ?></h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Dibeli: <?= format_tanggal($transaksi['tanggal']) ?></p>
        </div>

        <!-- Rating Form -->
        <form method="POST" action="<?= url('/customer/rating/' . $transaksi['id']) ?>" class="space-y-5">
            <?= csrf_field() ?>

            <!-- Star Rating -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5">★</label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4">★</label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3">★</label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2">★</label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1">★</label>
                </div>
            </div>

            <!-- Review Text -->
            <div>
                <label for="ulasan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Ulasan (Opsional)
                </label>
                <textarea id="ulasan"
                          name="ulasan"
                          rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Ceritakan pengalaman Anda dengan produk ini..."></textarea>
            </div>

            <!-- Submit Button -->
            <div class="ds-form-actions">
                <a href="<?= url('/customer/pembelian') ?>"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-3 px-6 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-xl font-semibold transition-colors">
                    Kirim Rating
                </button>
            </div>
        </form>
    </div>
</div>
