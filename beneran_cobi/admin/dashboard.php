<?php
include '../config/db.php';
include 'sidebar.php'; // tetap include di awal supaya tidak lupa
// kode PHP untuk data statistik (sama seperti kamu kirim)
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders"))['total'] ?? 0;
$total_terjual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) AS total FROM order_items"))['total'] ?? 0;
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) AS total FROM orders"))['total'] ?? 0;
$year = date('Y');
$sales_data = array_fill(1, 12, 0);
$result = mysqli_query($conn, "
    SELECT MONTH(order_date) AS bulan, SUM(total) AS omzet
    FROM orders
    WHERE YEAR(order_date) = '$year'
    GROUP BY MONTH(order_date)
");
while ($row = mysqli_fetch_assoc($result)) {
    $sales_data[(int)$row['bulan']] = (int)$row['omzet'];
}
$query_pie = "
    SELECT p.nama, SUM(oi.quantity) AS total_terjual
    FROM order_items oi
    JOIN produk p ON oi.product_id = p.id
    GROUP BY oi.product_id
    HAVING total_terjual > 0
    ORDER BY total_terjual DESC
    LIMIT 5
";
$result_pie = mysqli_query($conn, $query_pie);
$produk_labels = [];
$produk_qty = [];
while ($row = mysqli_fetch_assoc($result_pie)) {
    $produk_labels[] = $row['nama'];
    $produk_qty[] = (int)$row['total_terjual'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin | Cobi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-6 min-h-screen">
        <h1 class="text-3xl font-bold text-gray-700 mb-6">Dashboard Admin </h1>

        <!-- Statistik -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white shadow rounded-2xl p-4">
                <h2 class="text-sm text-gray-500">Total Produk</h2>
                <p class="text-2xl font-bold text-grey-600"><?= htmlspecialchars($total_produk) ?></p>
            </div>
            <div class="bg-white shadow rounded-2xl p-4">
                <h2 class="text-sm text-gray-500">Total Pesanan</h2>
                <p class="text-2xl font-bold text-grey-600"><?= htmlspecialchars($total_orders) ?></p>
            </div>
            <div class="bg-white shadow rounded-2xl p-4">
                <h2 class="text-sm text-gray-500">Total Produk Terjual</h2>
                <p class="text-2xl font-bold text-grey-600"><?= htmlspecialchars($total_terjual) ?></p>
            </div>
            <div class="bg-white shadow rounded-2xl p-4">
                <h2 class="text-sm text-gray-500">Total Revenue</h2>
                <p class="text-2xl font-bold text-grey-600">Rp <?= number_format($total_revenue, 0, ',', '.') ?></p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Pie Chart Produk Terlaris -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4"> 5 Produk Terlaris</h3>
                <canvas id="pieChart" height="150"></canvas>
            </div>

            <!-- Line Chart Sales Overview -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4"> Sales Overview (<?= $year ?>)</h3>
                <canvas id="lineChart" height="150"></canvas>
            </div>
        </div>

    </main>

    <script>
        const salesData = <?= json_encode(array_values($sales_data)) ?>;
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        const produkLabels = <?= json_encode($produk_labels) ?>;
        const produkQty = <?= json_encode($produk_qty) ?>;

        const pieColors = [
            '#F87171', '#FBBF24', '#60A5FA', '#A78BFA', '#10B981',
            '#FB7185', '#F472B6', '#38BDF8', '#4ADE80', '#E879F9'
        ];

        // Pie Chart Produk Terlaris
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: produkLabels,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: produkQty,
                    backgroundColor: pieColors.slice(0, produkLabels.length),
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Line Chart Sales Overview
        const lineCtx = document.getElementById('lineChart').getContext('2d');

        const gradient = lineCtx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Omzet Bulanan (Rp)',
                    data: salesData,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: 'rgba(5, 150, 105, 1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#10B981',
                    pointRadius: 5,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
