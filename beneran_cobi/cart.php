<?php
session_start();
include 'config/db.php';

$cart_id = $_COOKIE['cart_id'] ?? '0';

$checkout_success = false;
$error_message = '';

// ==============================
// Handle Update Qty
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $cart_item_id = intval($_POST['cart_item_id']);
    $qty = intval($_POST['qty']);

    if ($qty < 1) {
        $_SESSION['error'] = "Jumlah minimal adalah 1.";
    } else {
        $cek = mysqli_query($conn, "
            SELECT c.produk_id, p.stok 
            FROM cart c 
            JOIN produk p ON c.produk_id = p.id 
            WHERE c.id = $cart_item_id
        ");
        if ($data = mysqli_fetch_assoc($cek)) {
            if ($qty <= $data['stok']) {
                mysqli_query($conn, "UPDATE cart SET qty = $qty WHERE id = $cart_item_id");
                $_SESSION['success'] = "Jumlah berhasil diperbarui.";
            } else {
                $_SESSION['error'] = "Jumlah melebihi stok tersedia ({$data['stok']}).";
            }
        } else {
            $_SESSION['error'] = "Data tidak ditemukan.";
        }
    }
    header("Location: cart.php");
    exit;
}

// ==============================
// Handle Checkout
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $cart = mysqli_query($conn, "
        SELECT c.*, p.nama, p.harga, p.stok 
        FROM cart c 
        JOIN produk p ON c.produk_id = p.id 
        WHERE c.cart_id='$cart_id'
    ");

    $total = 0;
    $stok_error = [];

    $items = [];
    while ($item = mysqli_fetch_assoc($cart)) {
        $subtotal = $item['qty'] * $item['harga'];
        $total += $subtotal;

        if ($item['stok'] == 0) {
            $stok_error[] = "Produk '{$item['nama']}' sedang habis stok.";
        } elseif ($item['qty'] > $item['stok']) {
            $stok_error[] = "Jumlah produk <strong>{$item['nama']}</strong> melebihi stok tersedia ({$item['stok']}).";
        }

        $items[] = [
            'produk_id' => $item['produk_id'],
            'qty' => $item['qty'],
            'harga' => $item['harga'],
        ];
    }

    if (!empty($stok_error)) {
        $error_message = implode('<br>', $stok_error);
    } else {
        if ($total > 0) {
            $insert_order = mysqli_query($conn, "
                INSERT INTO orders (customer_name, phone_number, address, total, order_date, status)
                VALUES ('$nama', '$telepon', '$alamat', $total, NOW(), 'pending')
            ");
            if ($insert_order) {
                $order_id = mysqli_insert_id($conn);

                foreach ($items as $item) {
                    $produk_id = $item['produk_id'];
                    $qty = $item['qty'];
                    $harga = $item['harga'];

                    // 1. Insert ke order_items
                    mysqli_query($conn, "
                        INSERT INTO order_items (order_id, product_id, quantity, price)
                        VALUES ($order_id, $produk_id, $qty, $harga)
                    ");

                    // 2. Kurangi stok produk
                    mysqli_query($conn, "
                        UPDATE produk SET stok = stok - $qty WHERE id = $produk_id
                    ");
                }

                // 3. Hapus keranjang
                mysqli_query($conn, "DELETE FROM cart WHERE cart_id='$cart_id'");
                setcookie('cart_id', '', time() - 3600, '/');

                // 4. Redirect
                header("Location: checkout-success.php?id=$order_id");
                exit;
            } else {
                $error_message = "Gagal menyimpan pesanan.";
            }
        }
    }
}

// Ambil data cart
$cart = mysqli_query($conn, "
    SELECT c.*, p.nama, p.harga, p.gambar, p.stok
    FROM cart c 
    JOIN produk p ON c.produk_id = p.id 
    WHERE c.cart_id='$cart_id'
");
$total = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Cobi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'primary': {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-2">
                <span class="text-xl font-bold text-primary-600">Cobi</span>
            </div>
            <a href="index.php" class="text-primary-600 hover:text-primary-700 font-medium flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali Belanja</span>
            </a>
        </div>
    </div>
</header>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">🛒 Keranjang Belanja</h1>
            <p class="text-gray-600">Review produk yang akan Anda beli</p>
        </div>

        <!-- Alert Messages -->
        <?php if ($error_message): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div><?= $error_message ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span><?= htmlspecialchars($_SESSION['error']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php elseif (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?= htmlspecialchars($_SESSION['success']) ?></span>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (mysqli_num_rows($cart) === 0): ?>
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-4.5M9 7h6m0 0V5a2 2 0 012 2v0a2 2 0 01-2 2H9V7z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-2">Keranjang Anda Kosong</h3>
                <p class="text-gray-600 mb-8">Belum ada produk yang ditambahkan ke keranjang</p>
                <a href="index.php" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-xl font-semibold transition-colors">
                    Mulai Belanja
                </a>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-xl font-semibold text-gray-900">Produk dalam Keranjang</h2>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            <?php while ($item = mysqli_fetch_assoc($cart)): ?>
                                <?php
                                    $sub = $item['qty'] * $item['harga'];
                                    $total += $sub;
                                ?>
                                <div class="p-6">
                                    <div class="flex items-center space-x-4">
                                        
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0">
                                            <img src="images/<?= htmlspecialchars($item['gambar']) ?>" 
                                                 alt="<?= htmlspecialchars($item['nama']) ?>" 
                                                 class="w-20 h-20 object-cover rounded-xl">
                                        </div>

                                        <!-- Product Info -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <?= htmlspecialchars($item['nama']) ?>
                                            </h3>
                                            <p class="text-primary-600 font-semibold">
                                                Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Stok tersedia: <?= $item['stok'] ?>
                                            </p>
                                        </div>

                                        <!-- Quantity Controls -->
                                        <div class="flex items-center space-x-3">
                                            <form method="POST" class="flex items-center space-x-2">
                                                <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                                <input type="hidden" name="update_qty" value="1">
                                                <div class="flex items-center border border-gray-300 rounded-lg">
                                                    <input type="number" name="qty" value="<?= $item['qty'] ?>" 
                                                           min="1" max="<?= $item['stok'] ?>"
                                                           class="w-16 text-center py-2 border-0 focus:ring-0 focus:outline-none">
                                                </div>
                                                <button type="submit" 
                                                        class="bg-primary-600 hover:bg-primary-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                                    Update
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Subtotal & Remove -->
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900 mb-2">
                                                Rp <?= number_format($sub, 0, ',', '.') ?>
                                            </p>
                                            <a href="cart-delete.php?id=<?= $item['id'] ?>" 
                                               class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors"
                                               onclick="return confirm('Hapus produk dari keranjang?')">
                                                Hapus
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Checkout -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                        
                        <!-- Order Summary -->
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Ringkasan Pesanan</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Ongkos Kirim</span>
                                <span class="text-green-600">Gratis</span>
                            </div>
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between text-lg font-semibold text-gray-900">
                                    <span>Total</span>
                                    <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Checkout Form -->
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="nama" required 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                       placeholder="Masukkan nama lengkap">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                                <input type="text" name="telepon" required 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                       placeholder="Contoh: 08123456789">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Pengiriman</label>
                                <textarea name="alamat" required rows="3"
                                          class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                          placeholder="Masukkan alamat lengkap untuk pengiriman"></textarea>
                            </div>

                            <button type="submit" name="checkout" 
                                    class="w-full bg-primary-600 hover:bg-primary-700 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg">
                                Checkout Sekarang
                            </button>
                        </form>

                        <!-- Continue Shopping -->
                        <div class="mt-6 text-center">
                            <a href="index.php" class="text-primary-600 hover:text-primary-700 font-medium">
                                ← Lanjut Belanja
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>