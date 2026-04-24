<?php
$page_title = 'Transaksi';
$active_page = 'transaksi';
include '../includes/admin_header.php';
include '../includes/admin_header_html.php';
include '../includes/admin_sidebar.php';
?>
        <h1 class="text-xl font-bold text-gray-800 mb-5">Riwayat Transaksi</h1>
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">Semua Transaksi</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pembeli</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Penilaian Pembeli</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php
                    $paging = paginate($conn, "SELECT COUNT(*) as c FROM transaksi", [], 15);
                    $rows = db_query($conn, "SELECT t.tanggal, u.name, p.nama_produk, t.status, t.rating, t.ulasan FROM transaksi t JOIN users u ON t.user_id = u.id JOIN produk p ON t.produk_id = p.id ORDER BY t.tanggal DESC LIMIT " . $paging['limit'] . " OFFSET " . $paging['offset']);
                    foreach($rows as $r) { ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-500"><?= e($r['tanggal']); ?></td>
                        <td class="px-6 py-4 font-medium text-gray-800"><?= e($r['name']); ?></td>
                        <td class="px-6 py-4 font-semibold text-gray-800"><?= e($r['nama_produk']); ?></td>
                        <td class="px-6 py-4">
                            <?php
                            $status = strtolower($r['status']);
                            if($status == 'success') echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#E8F5E9; color:#2E7D32">BERHASIL</span>';
                            elseif($status == 'pending') echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#FFF8E1; color:#F57F17">PENDING</span>';
                            else echo '<span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">' . e(strtoupper($r['status'])) . '</span>';
                            ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($r['rating'] > 0): ?>
                                <div class="flex items-center gap-1 mb-1">
                                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-bold text-gray-700"><?= (int)$r['rating'] ?>/5</span>
                                </div>
                                <?php if(!empty($r['ulasan'])): ?>
                                    <p class="text-xs text-gray-500 italic max-w-xs" title="<?= e($r['ulasan']) ?>">"<?= e($r['ulasan']) ?>"</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?= pagination_render($paging) ?>
        </div>

<?php include '../includes/admin_footer.php'; ?>
