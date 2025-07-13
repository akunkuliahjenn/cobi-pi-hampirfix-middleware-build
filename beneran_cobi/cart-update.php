<?php
include 'config/db.php';

session_start(); // Gunakan session untuk menyimpan pesan error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_item_id = intval($_POST['cart_item_id']);
    $qty = intval($_POST['qty']);

    if ($qty > 0) {
        // Ambil produk_id dan stok dari produk terkait
        $cek = mysqli_query($conn, "
            SELECT c.produk_id, p.stok 
            FROM cart c 
            JOIN produk p ON c.produk_id = p.id 
            WHERE c.id = $cart_item_id
        ");

        if ($data = mysqli_fetch_assoc($cek)) {
            $stok_tersedia = $data['stok'];

            if ($qty <= $stok_tersedia) {
                // Qty valid, update ke cart
                mysqli_query($conn, "UPDATE cart SET qty = $qty WHERE id = $cart_item_id");
                $_SESSION['success'] = "Jumlah produk berhasil diperbarui.";
            } else {
                // Qty melebihi stok
                $_SESSION['error'] = "Jumlah melebihi stok yang tersedia ($stok_tersedia item).";
            }
        } else {
            $_SESSION['error'] = "Data produk tidak ditemukan.";
        }
    } else {
        $_SESSION['error'] = "Jumlah tidak boleh kurang dari 1.";
    }
}

// Redirect balik ke halaman keranjang
header("Location: cart.php");
exit;
