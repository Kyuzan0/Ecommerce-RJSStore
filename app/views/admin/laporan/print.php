<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan — RJSStore</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; padding: 24px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #222; padding-bottom: 12px; }
        .header h1 { font-size: 18px; font-weight: 700; margin-bottom: 2px; }
        .header p { font-size: 11px; color: #555; }
        .stats { display: flex; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
        .stat-box { flex: 1; min-width: 120px; border: 1px solid #ddd; border-radius: 6px; padding: 10px 12px; }
        .stat-box .label { font-size: 9px; text-transform: uppercase; color: #777; letter-spacing: 0.5px; margin-bottom: 2px; }
        .stat-box .value { font-size: 16px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th { background: #f5f5f5; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #555; padding: 8px 10px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 6px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
        tr:nth-child(even) { background: #fafafa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-failed { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .no-print { text-align: center; margin-bottom: 16px; }
        .no-print button { padding: 8px 24px; font-size: 13px; font-weight: 600; color: #fff; background: #42B549; border: none; border-radius: 8px; cursor: pointer; }
        .no-print button:hover { opacity: 0.9; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            @page { margin: 15mm; size: A4 landscape; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>Laporan Penjualan</h1>
        <p>RJSStore &mdash; <?= e($periodLabel) ?></p>
        <p style="margin-top:2px;font-size:10px;color:#888">Dicetak: <?= date('d M Y H:i') ?></p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="label">Item Terjual</div>
            <div class="value"><?= number_format($stats['total_item']) ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Total Pendapatan</div>
            <div class="value"><?= rupiah($stats['total_pendapatan']) ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Rata-rata Harian</div>
            <div class="value"><?= rupiah($stats['avg_daily']) ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Transaksi Pending</div>
            <div class="value"><?= number_format($stats['pending_count']) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Order Ref</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Email</th>
                <th>Produk</th>
                <th>Tipe</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rows)): ?>
            <tr><td colspan="9" class="text-center" style="padding:20px;color:#999">Tidak ada data transaksi.</td></tr>
            <?php else: ?>
            <?php foreach ($rows as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td style="font-family:monospace;font-size:10px"><?= e($row['order_ref'] ?? '-') ?></td>
                <td><?= format_tanggal($row['tanggal']) ?></td>
                <td><?= e($row['nama_user']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e($row['nama_produk']) ?></td>
                <td><?= ucfirst(e($row['tipe_produk'] ?? '-')) ?></td>
                <td class="text-right"><?= rupiah((int) $row['harga']) ?></td>
                <td class="text-center">
                    <?php
                    $badgeClass = match($row['status']) {
                        'success' => 'badge-success',
                        'pending' => 'badge-pending',
                        'failed'  => 'badge-failed',
                        default   => '',
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= ucfirst(e($row['status'])) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Laporan ini digenerate otomatis oleh sistem RJSStore &mdash; <?= date('d M Y H:i') ?>
    </div>
</body>
</html>
