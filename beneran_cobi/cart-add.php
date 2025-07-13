<?php
include 'config/db.php';
$cart_id = $_COOKIE['cart_id'] ?? '0'; // Ambil cart_id dari cookie

$cart = mysqli_query($conn, "SELECT c.*, p.nama, p.harga, p.gambar 
    FROM cart c 
    JOIN produk p ON c.produk_id = p.id 
    WHERE c.cart_id = '$cart_id'");

$total = 0;

if (mysqli_num_rows($cart) > 0) {
    while ($item = mysqli_fetch_assoc($cart)) {
        $sub = $item['qty'] * $item['harga'];
        $total += $sub;
    }
} else {
    echo "Keranjang Anda kosong.";
}


if (isset($_GET['id'])) {
    $produk_id = $_GET['id'];
    $qty = 1; // Misalnya, jika tidak ada input jumlah, set 1 sebagai default

    // Periksa apakah produk sudah ada dalam keranjang
    $check_query = mysqli_query($conn, "SELECT * FROM cart WHERE cart_id = '$cart_id' AND produk_id = '$produk_id'");
    
    if (mysqli_num_rows($check_query) > 0) {
        // Jika produk sudah ada, update qty
        $update_query = mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE cart_id = '$cart_id' AND produk_id = '$produk_id'");
    } else {
        // Jika produk belum ada, tambahkan ke cart
        $insert_query = mysqli_query($conn, "INSERT INTO cart (cart_id, produk_id, qty) VALUES ('$cart_id', '$produk_id', '$qty')");
    }

    // Redirect ke halaman keranjang setelah menambahkan produk
    header("Location: cart.php");
    exit();
}



$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Cobi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-emerald-700">🛒 Keranjang Belanja</h1>

        <?php if (mysqli_num_rows($cart) === 0): ?>
            <p class="text-gray-600">Keranjang Anda kosong.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-emerald-100 text-left">
                            <th class="p-2">Produk</th>
                            <th class="p-2">Qty</th>
                            <th class="p-2">Harga</th>
                            <th class="p-2">Subtotal</th>
                            <th class="p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($cart)): ?>
                            <?php
                                $sub = $item['qty'] * $item['harga'];
                                $total += $sub;
                            ?>
                            <tr class="border-b">
                                <td class="p-2 flex items-center gap-3">
                                    <img src="images/<?= $item['gambar'] ?>" alt="<?= $item['nama'] ?>" class="w-16 h-16 object-cover rounded shadow">
                                    <?= htmlspecialchars($item['nama']) ?>
                                </td>
                                <td class="p-2"><?= $item['qty'] ?></td>
                                <td class="p-2">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                <td class="p-2">Rp <?= number_format($sub, 0, ',', '.') ?></td>
                                <td class="p-2">
                                    <a href="cart-delete.php?id=<?= $item['id'] ?>" class="text-red-500 hover:underline">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold bg-gray-100">
                            <td colspan="3" class="p-2 text-right">Total:</td>
                            <td class="p-2">Rp <?= number_format($total, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-between">
                <a href="index.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">← Lanjut Belanja</a>
                <a href="checkout.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Checkout</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
