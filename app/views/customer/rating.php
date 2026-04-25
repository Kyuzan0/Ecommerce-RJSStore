<style>
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}
.star-rating input {
    display: none;
}
.star-rating label {
    cursor: pointer;
    font-size: 2rem;
    color: #d1d5db;
    transition: color 0.2s;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f59e0b;
}
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Rating Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Beri Rating Produk</h1>
            <p class="text-gray-600 mb-6">Bagikan pengalaman Anda dengan produk ini</p>
            
            <!-- Product Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800"><?= e($transaksi['nama_produk']) ?></h3>
                <p class="text-sm text-gray-500">Dibeli: <?= format_tanggal($transaksi['tanggal']) ?></p>
            </div>
            
            <!-- Rating Form -->
            <form method="POST" action="<?= url('/customer/rating/' . $transaksi['id']) ?>">
                <?= csrf_field() ?>
                
                <!-- Star Rating -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Rating</label>
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
                <div class="mb-6">
                    <label for="ulasan" class="block text-sm font-semibold text-gray-700 mb-2">
                        Ulasan (Opsional)
                    </label>
                    <textarea id="ulasan" 
                              name="ulasan" 
                              rows="4" 
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Ceritakan pengalaman Anda dengan produk ini..."></textarea>
                </div>
                
                <!-- Submit Button -->
                <div class="flex space-x-3">
                    <button type="submit" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition-colors">
                        Kirim Rating
                    </button>
                    <a href="<?= url('/customer/pembelian') ?>" 
                       class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 text-center py-3 rounded-lg font-semibold transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
