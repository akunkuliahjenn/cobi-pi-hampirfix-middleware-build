<?php
include 'config/db.php';

$cart_id = $_COOKIE['cart_id'] ?? '0';

// Ambil data keranjang
$cart_query = mysqli_query($conn, "SELECT c.*, p.nama, p.harga, p.gambar, p.stok, p.id AS produk_id FROM cart c 
    JOIN produk p ON c.produk_id = p.id 
    WHERE c.cart_id='$cart_id'");

$total = 0;
$cart_items = [];

while ($item = mysqli_fetch_assoc($cart_query)) {
    $item['subtotal'] = $item['qty'] * $item['harga'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

if (count($cart_items) === 0) {
    header("Location: cart.php");
    exit();
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);

    // Validasi stok
    $stok_error = [];
    foreach ($cart_items as $item) {
        if ($item['stok'] <= 0) {
            $stok_error[] = "Produk <strong>{$item['nama']}</strong> sedang habis stok.";
        } elseif ($item['qty'] > $item['stok']) {
            $stok_error[] = "Jumlah <strong>{$item['nama']}</strong> melebihi stok tersedia ({$item['stok']}).";
        }
    }

    if (empty($stok_error)) {
        mysqli_begin_transaction($conn);
        $transaksi_berhasil = true;

        foreach ($cart_items as $item) {
            $produk_id = $item['produk_id'];
            $qty = $item['qty'];
            $result = mysqli_query($conn, "SELECT stok FROM produk WHERE id = $produk_id FOR UPDATE");
            $produk = mysqli_fetch_assoc($result);

            if (!$produk || $produk['stok'] < $qty) {
                $transaksi_berhasil = false;
                $error_message = "Stok tidak mencukupi untuk <strong>{$item['nama']}</strong>.";
                break;
            }
        }

        if ($transaksi_berhasil) {
            $insert_order = mysqli_query($conn, "INSERT INTO orders 
                (customer_name, phone_number, address, total, order_date, status) 
                VALUES ('$nama', '$telepon', '$alamat', $total, NOW(), 'pending')");

            if ($insert_order) {
                $order_id = mysqli_insert_id($conn); // Dapatkan ID pesanan terakhir

                foreach ($cart_items as $item) {
                    $produk_id = $item['produk_id'];
                    $qty = $item['qty'];
                    $harga = $item['harga'];

                    // Simpan ke order_items
                    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES ($order_id, $produk_id, $qty, $harga)");

                    // Kurangi stok produk
                    mysqli_query($conn, "UPDATE produk SET stok = stok - $qty WHERE id = $produk_id");
                }

                // Hapus keranjang
                mysqli_query($conn, "DELETE FROM cart WHERE cart_id = '$cart_id'");
                mysqli_commit($conn);

                // Redirect ke halaman sukses dengan order id
                header("Location: checkout-success.php?id=$order_id");
                exit();
            } else {
                mysqli_rollback($conn);
                $error_message = "Gagal menyimpan data pesanan.";
            }
        } else {
            mysqli_rollback($conn);
        }
    } else {
        $error_message = implode("<br>", $stok_error);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Cobi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-6 text-emerald-700">🛒 Checkout</h2>

        <?php if ($error_message): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto mb-6">
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-emerald-100 text-left">
                        <th class="p-2">Produk</th>
                        <th class="p-2">Qty</th>
                        <th class="p-2">Harga</th>
                        <th class="p-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr class="border-b">
                            <td class="p-2"><?= htmlspecialchars($item['nama']) ?></td>
                            <td class="p-2"><?= $item['qty'] ?></td>
                            <td class="p-2">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td class="p-2">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-100">
                        <td colspan="3" class="p-2 text-right">Total:</td>
                        <td class="p-2">Rp <?= number_format($total, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block font-medium">Nama</label>
                <input type="text" name="nama" required class="w-full border px-3 py-2 rounded">
            </div>
            <div>
                <label class="block font-medium">Alamat</label>
                <textarea name="alamat" required class="w-full border px-3 py-2 rounded"></textarea>
            </div>
            <div>
                <label class="block font-medium">Telepon</label>
                <input type="text" name="telepon" required class="w-full border px-3 py-2 rounded">
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">
                Proses Checkout
            </button>
        </form>
    </div>
</body>
</html>
