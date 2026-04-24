<?php
$page_title = 'Riwayat Pembelian';
$active_page = 'pembelian';
include '../includes/customer_header.php';

$user_id = (int) $_SESSION['id'];

// Handle client-side payment error redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
    flash('error', 'Pembayaran gagal. Silakan coba lagi.');
    header("Location: pembelian.php");
    exit;
}

include '../includes/customer_sidebar.php';
?>
        <?= flash_render() ?>

        <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <a href="dashboard.php" class="hover:text-green-600">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-gray-700">Riwayat Pembelian</span>
        </div>

        <h1 class="text-xl font-bold text-gray-800 mb-5">Riwayat Pembelian</h1>

        <?php $filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua'; ?>
        <div class="flex gap-2 mb-5">
            <a href="pembelian.php" class="px-4 py-2 rounded-xl text-sm font-semibold <?= $filter == 'semua' ? 'text-white' : 'text-gray-600 bg-white border border-gray-200 hover:bg-gray-50'; ?>" <?= $filter == 'semua' ? 'style="background:#42B549"' : ''; ?>>Semua Pesanan</a>
            <a href="pembelian.php?filter=pending" class="px-4 py-2 rounded-xl text-sm font-medium <?= $filter == 'pending' ? 'text-white' : 'text-gray-600 bg-white border border-gray-200 hover:bg-gray-50'; ?>" <?= $filter == 'pending' ? 'style="background:#42B549"' : ''; ?>>Menunggu Bayar</a>
            <a href="pembelian.php?filter=success" class="px-4 py-2 rounded-xl text-sm font-medium <?= $filter == 'success' ? 'text-white' : 'text-gray-600 bg-white border border-gray-200 hover:bg-gray-50'; ?>" <?= $filter == 'success' ? 'style="background:#42B549"' : ''; ?>>Berhasil</a>
        </div>

        <div class="space-y-3">
            <?php
            $count_sql = "SELECT COUNT(*) as c FROM transaksi t WHERE t.user_id = ?";
            $count_params = ["i", $user_id];
            
            if ($filter == 'pending') {
                $count_sql .= " AND t.status = 'pending'";
            } elseif ($filter == 'success') {
                $count_sql .= " AND t.status = 'success'";
            }
            $paging = paginate($conn, $count_sql, $count_params, 10);
            
            $sql = "SELECT t.id as transaksi_id, t.tanggal, t.order_ref, p.nama_produk, p.harga, t.status, t.rating 
                FROM transaksi t 
                JOIN produk p ON t.produk_id = p.id
                WHERE t.user_id = ?";
            $params = ["i", $user_id];
            
            if ($filter == 'pending') {
                $sql .= " AND t.status = 'pending'";
            } elseif ($filter == 'success') {
                $sql .= " AND t.status = 'success'";
            }
            $sql .= " ORDER BY t.tanggal DESC, t.id DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset'];
            $transactions = db_query($conn, $sql, $params);
            
            if(count($transactions) > 0) {
                // Group by order_ref for display
                $grouped = [];
                foreach ($transactions as $row) {
                    $key = $row['order_ref'] ?: 'single_' . $row['transaksi_id'];
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'order_ref' => $row['order_ref'],
                            'tanggal' => $row['tanggal'],
                            'status' => $row['status'],
                            'items' => []
                        ];
                    }
                    $grouped[$key]['items'][] = $row;
                }
                
                foreach($grouped as $group_key => $group) {
                    $total_group = 0;
                    foreach ($group['items'] as $item) {
                        $total_group += $item['harga'];
                    }
                    $is_multi = count($group['items']) > 1;
            ?>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs text-gray-500"><?= date('d M Y', strtotime($group['tanggal'])); ?></span>
                        <?php if ($is_multi): ?>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"><?= count($group['items']) ?> produk</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($group['status'] == 'pending'): ?>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#FFF8E1; color:#F57F17">MENUNGGU BAYAR</span>
                    <?php elseif ($group['status'] == 'success'): ?>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">BERHASIL</span>
                    <?php else: ?>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700"><?= strtoupper(e($group['status'])); ?></span>
                    <?php endif; ?>
                </div>

                <?php foreach ($group['items'] as $row): ?>
                <div class="px-5 py-4 flex items-center justify-between <?= count($group['items']) > 1 ? 'border-b border-gray-50' : '' ?>">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#E8F5E9">
                            <svg class="w-7 h-7" style="color:#42B549" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0-4h5v2H8V9z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800"><?= e($row['nama_produk']); ?></p>
                            <p class="text-sm font-bold mt-0.5" style="color:#42B549"><?= rupiah($row['harga']); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($row['status'] == 'success'): ?>
                    <div class="flex items-center gap-2">
                        <?php if ($row['rating'] == 0): ?>
                            <a href="beri_nilai.php?id=<?= $row['transaksi_id']; ?>" class="px-4 py-2 rounded-xl text-xs font-semibold border border-yellow-500 text-yellow-600 hover:bg-yellow-50 transition">
                                Beri Nilai
                            </a>
                        <?php else: ?>
                            <span class="text-xs text-yellow-500 font-bold bg-yellow-50 px-3 py-1.5 rounded-lg flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <?= $row['rating']; ?>/5
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if ($group['status'] == 'pending' || $group['status'] == 'success'): ?>
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-700">Total:</span>
                        <span class="text-sm font-bold" style="color:#42B549"><?= rupiah($total_group); ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if ($group['status'] == 'pending'): ?>
                            <?php
                            // Build pay link
                            if ($group['order_ref'] && strpos($group['order_ref'], 'ORD-') === 0) {
                                $pay_url = 'bayar.php?ref=' . urlencode($group['order_ref']);
                            } else {
                                $pay_url = 'bayar.php?id=' . $group['items'][0]['transaksi_id'];
                            }
                            ?>
                            <a href="<?= $pay_url ?>"
                               class="px-5 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90" style="background:#42B549">
                                Bayar Sekarang
                            </a>
                        <?php elseif ($group['status'] == 'success'): ?>
                            <a href="download.php" class="px-5 py-2 rounded-xl text-sm font-semibold border-2 hover:bg-gray-50 transition" style="border-color:#42B549; color:#42B549">
                                Download
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php } } else { ?>
            <div class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full flex items-center justify-center" style="background:#E8F5E9">
                    <svg class="w-10 h-10" style="color:#42B549" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 mb-2">Belum ada pesanan</h3>
                <p class="text-gray-400 text-sm mb-5">Kamu belum pernah melakukan transaksi pembelian.</p>
                <a href="produk.php" class="inline-block px-6 py-3 rounded-xl text-white font-semibold text-sm hover:opacity-90 transition" style="background:#42B549">Mulai Belanja</a>
            </div>
            <?php } ?>
        </div>

        <?= pagination_render($paging) ?>

<?php include '../includes/customer_footer.php'; ?>
