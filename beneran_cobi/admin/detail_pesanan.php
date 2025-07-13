<?php
include '../config/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id");
$order = mysqli_fetch_assoc($order_query);
if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

$items_query = mysqli_query($conn, "
    SELECT oi.quantity, oi.price, p.nama 
    FROM order_items oi
    JOIN produk p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");

$status = strtolower(trim($order['status']));
$valid_statuses = ['pending', 'diproses', 'dikirim', 'selesai', 'batal'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Detail Pesanan #<?= htmlspecialchars($order['id']) ?> - Admin Cobi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'sidebar.php'; ?>

    <div class="ml-64 p-8 min-h-screen bg-gray-100">
        <div class="max-w-5xl bg-white p-6 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold text-emerald-700 mb-4">Detail Pesanan #<?= htmlspecialchars($order['id']) ?></h1>

            <div class="mb-6 text-gray-700 space-y-1">
                <p><strong>Nama Pelanggan:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Telepon:</strong> <?= htmlspecialchars($order['phone_number']) ?></p>
                <p><strong>Alamat:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                <p><strong>Total:</strong> Rp <?= number_format($order['total'], 0, ',', '.') ?></p>
                <p><strong>Tanggal Pesan:</strong> <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
                <p><strong>Status:</strong>
                    <?php
                        if (in_array($status, $valid_statuses)) {
                            echo '<span class="font-semibold capitalize">' . $status . '</span>';
                        } else {
                            echo '<span class="text-red-500 font-semibold">Belum ditentukan</span>';
                        }
                    ?>
                </p>
            </div>

            <h2 class="text-xl font-semibold text-emerald-700 mb-2">Produk dalam Pesanan</h2>
            <div class="overflow-x-auto">
                <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                    <thead class="bg-emerald-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 border">Produk</th>
                            <th class="px-4 py-2 border">Jumlah</th>
                            <th class="px-4 py-2 border">Harga</th>
                            <th class="px-4 py-2 border">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border"><?= htmlspecialchars($item['nama']) ?></td>
                            <td class="px-4 py-2 border text-center"><?= (int)$item['quantity'] ?></td>
                            <td class="px-4 py-2 border">Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td class="px-4 py-2 border">Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
