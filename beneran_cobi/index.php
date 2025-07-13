<?php
include 'config/db.php';

// Generate cart_id jika belum ada
if (!isset($_COOKIE['cart_id'])) {
    $cart_id = uniqid('cart_', true);
    setcookie('cart_id', $cart_id, time() + (86400 * 30), '/'); // 30 hari
} else {
    $cart_id = $_COOKIE['cart_id'];
}

// Hitung jumlah item di keranjang
$cart_count_query = mysqli_query($conn, "SELECT SUM(qty) as total FROM cart WHERE cart_id='$cart_id'");
$cart_count = mysqli_fetch_assoc($cart_count_query)['total'] ?? 0;

$query = "SELECT * FROM produk WHERE stok > 0 ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Data testimoni
$testimonials = [
    [
        'name' => 'Intan Maulida',
        'role' => 'Ibu Rumah Tangga',
        'location' => 'Jakarta',
        'image' => 'reviewer1.jpeg',
        'rating' => 5,
        'text' => 'Kuenya benar-benar luar biasa! Teksturnya lembut, rasanya autentik, dan presentasinya sangat cantik. Ulang tahun anak saya jadi momen yang tak terlupakan. Terima kasih Cobi!'
    ],
    [
        'name' => 'Budi Santoso',
        'role' => 'Pengusaha',
        'location' => 'Bandung',
        'image' => 'reviewer1.jpeg',
        'rating' => 5,
        'text' => 'Pelayanan sangat memuaskan! Kue untuk acara kantor kami mendapat pujian dari semua tamu. Kualitas premium dengan harga yang reasonable.'
    ],
    [
        'name' => 'Sari Dewi',
        'role' => 'Event Organizer',
        'location' => 'Surabaya',
        'image' => 'reviewer1.jpeg',
        'rating' => 5,
        'text' => 'Sudah beberapa kali order untuk event client, hasilnya selalu memuaskan. Kue tidak hanya enak tapi juga Instagram-worthy!'
    ],
    [
        'name' => 'Ahmad Rahman',
        'role' => 'Dokter',
        'location' => 'Yogyakarta',
        'image' => 'reviewer1.jpeg',
        'rating' => 5,
        'text' => 'Kue ulang tahun istri saya dari Cobi benar-benar istimewa. Rasanya homemade banget dan tidak terlalu manis. Recommended!'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Cobi – Toko Kue Premium</title>
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
    .gradient-bg {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }
    .card-hover {
      transition: all 0.3s ease;
    }
    .card-hover:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .floating-cart {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1000;
    }
  </style>
</head>
<body class="bg-gray-50">

<!-- HEADER -->
<header class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between h-16">
      <!-- Logo -->
      <div class="flex items-center space-x-2">
        <span class="text-2xl font-bold text-primary-600">Cobi</span>
      </div>

      <!-- Navigation -->
      <nav class="hidden md:flex items-center space-x-8">
        <a href="#beranda" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Beranda</a>
        <a href="#produk" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Produk</a>
        <a href="#tentang" class="text-gray-700 hover:text-primary-600 font-medium transition-colors">Tentang</a>
      </nav>

      <!-- Cart Button -->
      <div class="flex items-center space-x-4">
        <a href="cart.php" class="relative bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-4.5M9 7h6m0 0V5a2 2 0 012 2v0a2 2 0 01-2 2H9V7z"></path>
          </svg>
          <span>Keranjang</span>
          <?php if ($cart_count > 0): ?>
            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cart_count ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </div>
</header>

<!-- HERO SECTION -->
<section id="beranda" class="gradient-bg py-20 lg:py-32">
  <div class="container mx-auto px-4">
    <div class="flex flex-col lg:flex-row items-center gap-12">
      
      <!-- Text Content -->
      <div class="flex-1 text-center lg:text-left">
        <h1 class="text-4xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
          Kue Premium untuk
          <span class="text-primary-600">Momen Istimewa</span>
        </h1>
        <p class="text-xl text-gray-600 mb-8 leading-relaxed">
          Nikmati kelezatan kue artisan yang dibuat dengan bahan premium dan resep rahasia keluarga. 
          Setiap gigitan adalah pengalaman yang tak terlupakan.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
          <a href="#produk" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all transform hover:scale-105 shadow-lg">
            Lihat Produk
          </a>
          <a href="cart.php" class="border-2 border-primary-600 text-primary-600 hover:bg-primary-600 hover:text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all">
            Keranjang (<?= $cart_count ?>)
          </a>
        </div>
      </div>

      <!-- Hero Image -->
      <div class="flex-1 flex justify-center">
        <div class="relative">
          <div class="absolute inset-0 bg-primary-200 rounded-3xl transform rotate-6"></div>
          <img src="images/hero.png" alt="Kue Premium Cobi" class="relative w-80 lg:w-96 rounded-3xl shadow-2xl">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="py-16 bg-white">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <div class="text-center p-6">
        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Dibuat dengan Cinta</h3>
        <p class="text-gray-600">Setiap kue dibuat dengan perhatian detail dan cinta untuk memberikan rasa terbaik</p>
      </div>

      <div class="text-center p-6">
        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Bahan Premium</h3>
        <p class="text-gray-600">Menggunakan bahan-bahan berkualitas tinggi dan segar untuk hasil terbaik</p>
      </div>

      <div class="text-center p-6">
        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold mb-2">Selalu Fresh</h3>
        <p class="text-gray-600">Kue dibuat fresh setiap hari untuk menjamin kesegaran dan kualitas</p>
      </div>

    </div>
  </div>
</section>

<!-- PRODUK SECTION -->
<section id="produk" class="py-20 bg-gray-50">
  <div class="container mx-auto px-4">
    
    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Koleksi Kue Premium</h2>
      <p class="text-xl text-gray-600 max-w-2xl mx-auto">
        Temukan berbagai pilihan kue lezat yang dibuat khusus untuk momen istimewa Anda
      </p>
    </div>

    <!-- Products Grid -->
    <?php if (mysqli_num_rows($result) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="bg-white rounded-2xl shadow-sm card-hover overflow-hidden border border-gray-100">
            
            <!-- Product Image -->
            <div class="relative overflow-hidden">
              <img src="images/<?= htmlspecialchars($row['gambar']); ?>" 
                   alt="<?= htmlspecialchars($row['nama']); ?>" 
                   class="w-full h-56 object-cover cursor-pointer"
                   onclick="showProductDetail(<?= htmlspecialchars(json_encode($row)) ?>)">
              <?php if ($row['stok'] <= 5): ?>
                <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                  Stok Terbatas
                </div>
              <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="p-6">
              <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($row['nama']); ?></h3>
              <p class="text-gray-600 text-sm mb-2 line-clamp-2">
                <?= htmlspecialchars(substr($row['deskripsi'], 0, 80)); ?>...
                <button onclick="showProductDetail(<?= htmlspecialchars(json_encode($row)) ?>)" 
                        class="text-primary-600 hover:text-primary-700 font-medium ml-1">
                  Lihat detail
                </button>
              </p>
              
              <!-- Price and Stock -->
              <div class="flex items-center justify-between mb-4">
                <span class="text-2xl font-bold text-primary-600">
                  Rp <?= number_format($row['harga'], 0, ',', '.'); ?>
                </span>
                <span class="text-sm text-gray-500">Stok: <?= $row['stok']; ?></span>
              </div>

              <!-- Add to Cart Button -->
              <button onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama']) ?>')" 
                      class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 px-4 rounded-xl font-semibold transition-all transform hover:scale-105 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-4.5M9 7h6m0 0V5a2 2 0 012 2v0a2 2 0 01-2 2H9V7z"></path>
                </svg>
                <span>Tambah ke Keranjang</span>
              </button>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-16">
        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Produk</h3>
        <p class="text-gray-600">Produk akan segera hadir. Pantau terus untuk update terbaru!</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- TESTIMONIAL SECTION -->
<section id="tentang" class="py-20 bg-white">
  <div class="container mx-auto px-4">
    
    <!-- Section Header -->
    <div class="text-center mb-16">
      <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Kata Pelanggan Kami</h2>
      <p class="text-xl text-gray-600">Kepuasan pelanggan adalah prioritas utama kami</p>
    </div>

    <!-- Testimonial Slider -->
    <div class="max-w-4xl mx-auto">
      <div id="testimonial-container" class="bg-gradient-to-r from-primary-50 to-primary-100 rounded-3xl p-8 lg:p-12">
        <!-- Testimonial content will be populated by JavaScript -->
      </div>
      
      <!-- Navigation -->
      <div class="flex justify-center items-center mt-8 space-x-4">
        <button onclick="prevTestimonial()" class="bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        
        <div id="testimonial-dots" class="flex space-x-2">
          <!-- Dots will be populated by JavaScript -->
        </div>
        
        <button onclick="nextTestimonial()" class="bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>

  </div>
</section>

<!-- FOOTER -->
<footer class="bg-gray-900 text-gray-300">
  <div class="container mx-auto px-4 py-16">
    
    <!-- Main Footer Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mb-12">
      
      <!-- Brand Section -->
      <div class="lg:col-span-1">
        <div class="flex items-center space-x-2 mb-6">
          <span class="text-2xl font-bold text-primary-400">Cobi</span>
        </div>
        <p class="text-gray-400 leading-relaxed mb-6">
          Kami menghadirkan kue premium dengan cita rasa autentik untuk menemani setiap momen spesial Anda. 
          Dibuat dengan cinta dan bahan berkualitas tinggi.
        </p>
        <div class="flex space-x-4">
          <a href="https://www.instagram.com/cobibaked/" target="_blank" 
             class="w-10 h-10 bg-gray-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
          <a href="https://wa.me/6285353008171" target="_blank"
             class="w-10 h-10 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.905 3.688"/>
            </svg>
          </a>
          <a href="https://www.tiktok.com/@cobibaked" target="_blank"
             class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="text-lg font-semibold text-white mb-6">Menu Cepat</h4>
        <ul class="space-y-3">
          <li><a href="#beranda" class="hover:text-primary-400 transition-colors">Beranda</a></li>
          <li><a href="#produk" class="hover:text-primary-400 transition-colors">Produk</a></li>
          <li><a href="#tentang" class="hover:text-primary-400 transition-colors">Tentang Kami</a></li>
          <li><a href="cart.php" class="hover:text-primary-400 transition-colors">Keranjang</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <h4 class="text-lg font-semibold text-white mb-6">Hubungi Kami</h4>
        <div class="space-y-4">
          <div class="flex items-center space-x-3">
            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            <span>+62 853-5300-8171</span>
          </div>
          <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 text-primary-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <div>
              <p>Jl. Raya Kemang No. 123</p>
              <p>Jakarta Selatan, DKI Jakarta 12560</p>
              <a href="https://maps.google.com/?q=Jl.+Raya+Kemang+No.+123+Jakarta+Selatan" 
                 target="_blank" 
                 class="text-primary-400 hover:text-primary-300 text-sm">
                Lihat di Google Maps →
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Footer Bottom -->
    <div class="border-t border-gray-800 pt-8 text-center">
      <p class="text-gray-400">
        &copy; 2024 <span class="text-primary-400 font-semibold">Cobi</span>. Semua hak cipta dilindungi.
      </p>
    </div>

  </div>
</footer>

<!-- Floating Cart Button (Mobile) -->
<div class="floating-cart md:hidden">
  <a href="cart.php" class="bg-primary-600 hover:bg-primary-700 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg relative">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-4.5M9 7h6m0 0V5a2 2 0 012 2v0a2 2 0 01-2 2H9V7z"></path>
    </svg>
    <?php if ($cart_count > 0): ?>
      <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cart_count ?></span>
    <?php endif; ?>
  </a>
</div>

<!-- Product Detail Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-3xl shadow-xl w-full max-w-2xl relative overflow-hidden">
    <button onclick="hideProductDetail()" class="absolute top-4 right-4 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100 transition-colors">
      <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <div class="flex flex-col md:flex-row">
      <!-- Product Image -->
      <div class="md:w-1/2">
        <img id="modalProductImage" src="" alt="" class="w-full h-64 md:h-96 object-cover">
      </div>
      
      <!-- Product Info -->
      <div class="md:w-1/2 p-8">
        <h3 id="modalProductName" class="text-2xl font-bold text-gray-900 mb-4"></h3>
        <p id="modalProductPrice" class="text-3xl font-bold text-primary-600 mb-4"></p>
        <p id="modalProductStock" class="text-gray-600 mb-4"></p>
        <div class="mb-6">
          <h4 class="font-semibold text-gray-900 mb-2">Deskripsi:</h4>
          <p id="modalProductDescription" class="text-gray-700 leading-relaxed"></p>
        </div>
        <button id="modalAddToCartBtn" 
                class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 px-6 rounded-xl font-semibold transition-all transform hover:scale-105 flex items-center justify-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0V19a2 2 0 002 2h7a2 2 0 002-2v-4.5M9 7h6m0 0V5a2 2 0 012 2v0a2 2 0 01-2 2H9V7z"></path>
          </svg>
          <span>Tambah ke Keranjang</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 transform translate-x-full transition-transform duration-300">
  <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
    <span id="toast-message">Produk berhasil ditambahkan ke keranjang!</span>
  </div>
</div>

<script>
// Testimonials data
const testimonials = <?= json_encode($testimonials) ?>;
let currentTestimonial = 0;

function renderTestimonial(index) {
  const testimonial = testimonials[index];
  const container = document.getElementById('testimonial-container');
  
  container.innerHTML = `
    <div class="flex flex-col lg:flex-row items-center gap-8">
      <div class="flex-shrink-0">
        <img src="images/${testimonial.image}" 
             alt="${testimonial.name}" 
             class="w-32 h-32 lg:w-40 lg:h-40 object-cover rounded-full border-4 border-white shadow-lg">
      </div>
      <div class="flex-1 text-center lg:text-left">
        <div class="mb-4">
          <svg class="w-12 h-12 text-primary-400 mx-auto lg:mx-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h4v10h-10z"/>
          </svg>
        </div>
        <blockquote class="text-xl lg:text-2xl text-gray-800 font-medium mb-6 leading-relaxed">
          "${testimonial.text}"
        </blockquote>
        <div class="flex items-center justify-center lg:justify-start space-x-1 mb-4">
          ${Array(testimonial.rating).fill(0).map(() => `
            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          `).join('')}
        </div>
        <cite class="text-lg font-semibold text-gray-900">${testimonial.name}</cite>
        <p class="text-gray-600">${testimonial.role}, ${testimonial.location}</p>
      </div>
    </div>
  `;
}

function renderDots() {
  const dotsContainer = document.getElementById('testimonial-dots');
  dotsContainer.innerHTML = testimonials.map((_, index) => `
    <button onclick="goToTestimonial(${index})" 
            class="w-3 h-3 rounded-full transition-colors ${index === currentTestimonial ? 'bg-primary-600' : 'bg-gray-300'}">
    </button>
  `).join('');
}

function nextTestimonial() {
  currentTestimonial = (currentTestimonial + 1) % testimonials.length;
  renderTestimonial(currentTestimonial);
  renderDots();
}

function prevTestimonial() {
  currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
  renderTestimonial(currentTestimonial);
  renderDots();
}

function goToTestimonial(index) {
  currentTestimonial = index;
  renderTestimonial(currentTestimonial);
  renderDots();
}

// Auto-advance testimonials
setInterval(nextTestimonial, 5000);

// Initialize testimonials
renderTestimonial(0);
renderDots();

// Product detail modal functions
let currentProduct = null;

function showProductDetail(product) {
  currentProduct = product;
  document.getElementById('modalProductImage').src = 'images/' + product.gambar;
  document.getElementById('modalProductImage').alt = product.nama;
  document.getElementById('modalProductName').textContent = product.nama;
  document.getElementById('modalProductPrice').textContent = 'Rp ' + parseInt(product.harga).toLocaleString('id-ID');
  document.getElementById('modalProductStock').textContent = 'Stok tersedia: ' + product.stok + ' pcs';
  document.getElementById('modalProductDescription').textContent = product.deskripsi;
  
  document.getElementById('modalAddToCartBtn').onclick = function() {
    addToCart(product.id, product.nama);
  };
  
  document.getElementById('productModal').classList.remove('hidden');
  document.getElementById('productModal').classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function hideProductDetail() {
  document.getElementById('productModal').classList.remove('flex');
  document.getElementById('productModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

// Cart functions
function addToCart(productId, productName) {
  fetch('cart-add.php?id=' + productId)
    .then(response => {
      if (response.ok) {
        showToast(' ' + productName + ' berhasil ditambahkan ke keranjang!');
        hideProductDetail();
        setTimeout(() => {
          location.reload();
        }, 1500);
      } else {
        showToast('❌ Gagal menambahkan produk ke keranjang');
      }
    })
    .catch(error => {
      showToast('❌ Terjadi kesalahan');
    });
}

function showToast(message) {
  const toast = document.getElementById('toast');
  const toastMessage = document.getElementById('toast-message');
  
  toastMessage.textContent = message;
  toast.classList.remove('translate-x-full');
  
  setTimeout(() => {
    toast.classList.add('translate-x-full');
  }, 3000);
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// Close modal when clicking outside
document.getElementById('productModal').addEventListener('click', function(e) {
  if (e.target === this) {
    hideProductDetail();
  }
});
</script>

</body>
</html>