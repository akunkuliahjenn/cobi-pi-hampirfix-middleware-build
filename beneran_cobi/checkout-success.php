<?php
include 'config/db.php';

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit();
}

$query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id");
$order = mysqli_fetch_assoc($query);

if (!$order) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

// Ambil detail produk yang dipesan
$items_query = mysqli_query($conn, "
    SELECT oi.quantity, oi.price, p.nama 
    FROM order_items oi
    JOIN produk p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Pesanan Berhasil - Cobi</title>
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
        .success-animation {
            animation: successPulse 2s ease-in-out infinite;
        }
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #22c55e;
            animation: confetti-fall 3s linear infinite;
        }
        @keyframes confetti-fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Confetti Animation -->
    <div class="confetti" style="left: 10%; animation-delay: 0s; background: #22c55e;"></div>
    <div class="confetti" style="left: 20%; animation-delay: 0.5s; background: #4ade80;"></div>
    <div class="confetti" style="left: 30%; animation-delay: 1s; background: #86efac;"></div>
    <div class="confetti" style="left: 40%; animation-delay: 1.5s; background: #22c55e;"></div>
    <div class="confetti" style="left: 50%; animation-delay: 2s; background: #4ade80;"></div>
    <div class="confetti" style="left: 60%; animation-delay: 0.3s; background: #86efac;"></div>
    <div class="confetti" style="left: 70%; animation-delay: 0.8s; background: #22c55e;"></div>
    <div class="confetti" style="left: 80%; animation-delay: 1.3s; background: #4ade80;"></div>
    <div class="confetti" style="left: 90%; animation-delay: 1.8s; background: #86efac;"></div>
    
    <div class="max-w-2xl w-full relative z-10">
        
        <!-- Success Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 lg:p-12 text-center">
            
            <!-- Success Icon -->
            <div class="success-animation w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Success Message -->
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                🎉 Pesanan Berhasil!
            </h1>
            <p class="text-xl text-gray-600 mb-8">
                Terima kasih telah berbelanja di <span class="text-primary-600 font-semibold">Cobi</span>
            </p>

            <!-- Order Details -->
            <div class="bg-gray-50 rounded-2xl p-6 mb-8 text-left">
                <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center">Detail Pesanan #<?= $order_id ?></h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nama:</span>
                        <span class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Telepon:</span>
                        <span class="font-medium"><?= htmlspecialchars($order['phone_number']) ?></span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-gray-600">Alamat:</span>
                        <span class="font-medium text-right max-w-xs"><?= nl2br(htmlspecialchars($order['address'])) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal:</span>
                        <span class="font-medium"><?= date('d M Y H:i', strtotime($order['order_date'])) ?></span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex justify-between">
                        <span class="text-gray-900 font-semibold">Total Bayar:</span>
                        <span class="text-2xl font-bold text-primary-600">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-gray-50 rounded-2xl p-6 mb-8 text-left">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Produk yang Dipesan</h3>
                <div class="space-y-2">
                    <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700"><?= htmlspecialchars($item['nama']) ?></span>
                            <span class="text-gray-600"><?= $item['quantity'] ?>x Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Status Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-blue-800 font-semibold">Status: Menunggu Konfirmasi</span>
                </div>
                <p class="text-blue-700 text-sm">
                    Pesanan Anda sedang diproses. Kami akan menghubungi Anda melalui WhatsApp untuk konfirmasi pembayaran dan pengiriman.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="https://wa.me/6285353008171?text=Halo,%20saya%20ingin%20konfirmasi%20pesanan%20%23<?= $order_id ?>" 
                   target="_blank"
                   class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-xl font-semibold transition-colors flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.905 3.688"/>
                    </svg>
                    <span>Hubungi via WhatsApp</span>
                </a>
                
                <a href="index.php" 
                   class="border-2 border-primary-600 text-primary-600 hover:bg-primary-600 hover:text-white px-8 py-3 rounded-xl font-semibold transition-colors">
                    Kembali ke Beranda
                </a>
            </div>

        </div>

        <!-- Additional Info -->
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Butuh bantuan? Hubungi kami di 
                <a href="https://wa.me/6285353008171" class="text-primary-600 hover:underline font-medium">WhatsApp</a>
            </p>
        </div>

    </div>

</body>
</html>