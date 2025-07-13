<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$cart_id = $_COOKIE['cart_id'] ?? '';

if (!$cart_id) {
    echo "Keranjang kosong!";
    exit();
}

$customer_name = $_POST['customer_name'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$address = $_POST['address'] ?? '';

if (!$customer_name || !$phone_number || !$address) {
    echo "Data pelanggan tidak lengkap.";
    exit();
}

// Ambil isi cart dari DB
$cart = mysqli_query($conn, "SELECT c.*, p.harga, p.stok FROM cart c JOIN produk p ON c.produk_id = p.id WHERE c.cart_id = '$cart_id'");

if (mysqli_num_rows($cart) == 0) {
    echo "Keranjang kosong!";
    exit();
}

// Cek stok tersedia dan hitung total
$total = 0;
while ($item = mysqli_fetch_assoc($cart)) {
    if ($item['qty'] > $item['stok']) {
        echo "Stok produk ID {$item['produk_id']} tidak mencukupi.";
        exit();
    }
    $total += $item['qty'] * $item['harga'];
}

// Insert ke tabel orders
$stmt = $conn->prepare("INSERT INTO orders (customer_name, phone_number, address, total, order_date, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
$stmt->bind_param("sssd", $customer_name, $phone_number, $address, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert detail order ke order_items
$stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

mysqli_data_seek($cart, 0); // Reset result pointer
while ($item = mysqli_fetch_assoc($cart)) {
    $stmt_item->bind_param("iiid", $order_id, $item['produk_id'], $item['qty'], $item['harga']);
    $stmt_item->execute();

    // Kurangi stok produk
    $conn->query("UPDATE produk SET stok = stok - {$item['qty']} WHERE id = {$item['produk_id']}");
}

// Hapus isi cart dari DB
$conn->query("DELETE FROM cart WHERE cart_id = '$cart_id'");

// Redirect ke halaman sukses dengan order id
header("Location: checkout-success.php?id=$order_id");
exit();
