<?php
$page_title = 'Download Produk';
$active_page = 'download';
include '../includes/customer_header.php';

$user_id = (int) $_SESSION['id'];

include '../includes/customer_sidebar.php';
?>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="dashboard.php" class="hover:text-green-600">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Area Download</span>
        </div>
        <h1 class="text-xl font-bold text-gray-800 mb-5">Produk Digitalku</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $paging = paginate($conn, "SELECT COUNT(*) as c FROM transaksi t WHERE t.user_id = ? AND t.status = 'success'", ["i", $user_id], 12);
            $downloads = db_query($conn, "
                SELECT p.nama_produk, p.file_upload, t.tanggal 
                FROM transaksi t 
                JOIN produk p ON t.produk_id = p.id
                WHERE t.user_id = ? AND t.status = 'success'
                ORDER BY t.tanggal DESC
                LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'] . "
            ", ["i", $user_id]);
            
            if(count($downloads) > 0) {
                foreach($downloads as $row) {
            ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition group">
                <div class="h-2 w-full" style="background:linear-gradient(90deg,#42B549,#66BB6A)"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                            <svg class="w-6 h-6" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">✓ Lunas</span>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-1 line-clamp-2"><?= e($row['nama_produk']); ?></h3>
                    <p class="text-xs text-gray-400 mb-5">Dibeli: <?= date('d M Y', strtotime($row['tanggal'])); ?></p>
                    <a href="../uploads/<?= e($row['file_upload']); ?>"
                       download="<?= e($row['nama_produk']); ?>"
                       class="flex items-center justify-center gap-2 w-full text-white py-3 rounded-xl text-sm font-semibold hover:opacity-90 transition" style="background:#42B549">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download File
                    </a>
                </div>
            </div>
            <?php } } else { ?>
            <div class="col-span-full bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-10 h-10" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Belum Ada File</h3>
                <p class="text-gray-400 text-sm mb-5">Kamu belum memiliki produk digital yang bisa diunduh.</p>
                <a href="produk.php" class="inline-block px-6 py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition" style="background:#42B549">Lihat Katalog Produk</a>
            </div>
            <?php } ?>
        </div>

        <?= pagination_render($paging) ?>

<?php include '../includes/customer_footer.php'; ?>
