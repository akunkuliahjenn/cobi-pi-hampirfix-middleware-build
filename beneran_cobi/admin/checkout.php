<?php
include 'config/db.php';
$cart_id = $_COOKIE['cart_id'] ?? '0';

$items = mysqli_query($conn, "SELECT * FROM cart WHERE cart_id='$cart_id'");

while ($item = mysqli_fetch_assoc($items)) {
    $produk_id = $item['produk_id'];
    $qty = $item['qty'];

    mysqli_query($conn, "UPDATE produk SET stok = stok - $qty WHERE id = $produk_id AND stok >= $qty");
}

mysqli_query($conn, "DELETE FROM cart WHERE cart_id='$cart_id'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Cobi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center">
    <div class="bg-white p-10 rounded shadow text-center">
        <h1 class="text-2xl font-bold text-emerald-600 mb-4">Checkout Berhasil!</h1>
        <p class="text-gray-700">Terima kasih telah berbelanja di <strong>Cobi</strong> 😊</p>
        <a href="index.php" class="mt-4 inline-block bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded">Kembali ke Beranda</a>
    </div>
</body>
</html>
