<?php
include '../config/db.php';
include 'sidebar.php';

// Update status jika ada permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
    header("Location: orders.php?updated=1");
    exit();
}

// Ambil data pesanan
$query = mysqli_query($conn, "SELECT * FROM orders ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan - Admin Cobi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Hanya 1x ml-64 -->
<div class="ml-64 p-6">
    <div class="w-full bg-white p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-grey-700">Daftar Pesanan</h1>
        </div>


        <?php if (isset($_GET['updated'])): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-300">
                ✅ Status pesanan berhasil diperbarui.
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($query) === 0): ?>
            <p class="text-gray-500">Belum ada pesanan yang masuk.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-emerald-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2 border">ID</th>
                            <th class="px-4 py-2 border">Nama Pelanggan</th>
                            <th class="px-4 py-2 border">Total</th>
                            <th class="px-4 py-2 border">Tanggal</th>
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($query)): ?>
                            <tr class="hover:bg-gray-50 border-b">
                                <td class="px-4 py-2 border text-center"><?= $order['id'] ?></td>
                                <td class="px-4 py-2 border"><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td class="px-4 py-2 border">Rp <?= number_format($order['total'], 0, ',', '.') ?></td>
                                <td class="px-4 py-2 border"><?= date('d M Y H:i', strtotime($order['order_date'])) ?></td>
                                <td class="px-4 py-2 border">
                                    <form method="post" class="flex items-center gap-2">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <select name="status" class="border border-gray-300 px-2 py-1 rounded text-sm">
                                            <?php
                                            $statuses = ['pending', 'diproses', 'dikirim', 'selesai', 'batal'];
                                            foreach ($statuses as $status_option):
                                                $selected = $order['status'] === $status_option ? 'selected' : '';
                                            ?>
                                                <option value="<?= $status_option ?>" <?= $selected ?>>
                                                    <?= ucfirst($status_option) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="bg-emerald-600 text-white text-sm px-3 py-1 rounded hover:bg-emerald-700 transition">
                                            Ubah
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 border text-center">
                                    <a href="detail_pesanan.php?id=<?= $order['id'] ?>" class="text-emerald-600 hover:underline">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
