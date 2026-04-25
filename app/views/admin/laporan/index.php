<h1 class="text-xl font-bold text-gray-800 mb-5">Laporan Keuangan</h1>

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-blue-500">
        <p class="text-xs text-gray-500 font-medium tracking-wide">JUMLAH ITEM TERJUAL</p>
        <h2 class="text-3xl font-bold text-gray-800 mt-2"><?= $total_item; ?></h2>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm border-l-4 border-l-green-500">
        <p class="text-xs text-gray-500 font-medium tracking-wide">JUMLAH PENDAPATAN</p>
        <h2 class="text-3xl font-bold text-gray-800 mt-2"><?= rupiah($total_pendapatan); ?></h2>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h2 class="text-sm font-bold mb-4 text-gray-800">Grafik Pendapatan Harian</h2>
        <div class="relative h-64 w-full">
            <canvas id="grafikHarian"></canvas>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h2 class="text-sm font-bold mb-4 text-gray-800">Grafik Pendapatan Bulanan</h2>
        <div class="relative h-64 w-full">
            <canvas id="grafikBulanan"></canvas>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currencyFormatter = function(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        };

        const ctxHarian = document.getElementById('grafikHarian').getContext('2d');
        new Chart(ctxHarian, {
            type: 'line',
            data: {
                labels: <?= $tanggal_arr; ?>,
                datasets: [{
                    label: 'Pendapatan Harian (Rp)',
                    data: <?= $pendapatan_harian_arr; ?>,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#ffffff',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return currencyFormatter(context.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: currencyFormatter }
                    }
                }
            }
        });

        const ctxBulanan = document.getElementById('grafikBulanan').getContext('2d');
        new Chart(ctxBulanan, {
            type: 'bar',
            data: {
                labels: <?= $bulan_arr; ?>,
                datasets: [{
                    label: 'Pendapatan Bulanan (Rp)',
                    data: <?= $pendapatan_bulanan_arr; ?>,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) { return currencyFormatter(context.parsed.y); }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: currencyFormatter }
                    }
                }
            }
        });
    });
</script>
