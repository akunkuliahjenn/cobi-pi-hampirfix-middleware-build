<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Proses upload dan penyimpanan produk
if (isset($_POST['submit'])) {
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];
    $stok = $_POST['stok'];

    $gambar = $_FILES['gambar']['name'];
    $gambar_tmp = $_FILES['gambar']['tmp_name'];
    $gambar_path = "../images/" . $gambar;

    if (move_uploaded_file($gambar_tmp, $gambar_path)) {
        $query = "INSERT INTO produk (nama, harga, deskripsi, gambar, stok) VALUES ('$nama', '$harga', '$deskripsi', '$gambar', '$stok')";
        if (mysqli_query($conn, $query)) {
            $success = "Produk berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan produk: " . mysqli_error($conn);
        }
    } else {
        $error = "Gagal mengupload gambar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Tambah Produk - Cobi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 flex">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 p-8 overflow-auto ml-64">
        <h1 class="text-3xl font-bold mb-6 text-emerald-700">Tambah Produk</h1>

        <?php if (isset($success)): ?>
            <p class="mb-4 text-green-600 font-medium"><?= $success; ?></p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <p class="mb-4 text-red-600 font-medium"><?= $error; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="max-w-xl bg-white p-6 rounded shadow space-y-5">
            <div>
                <label for="nama" class="block mb-1 font-medium">Nama Produk</label>
                <input type="text" name="nama" id="nama" required
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" />
            </div>

            <div>
                <label for="harga" class="block mb-1 font-medium">Harga Produk</label>
                <input type="number" name="harga" id="harga" required
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" />
            </div>

            <div>
                <label for="stok" class="block mb-1 font-medium">Stok Produk</label>
                <input type="number" name="stok" id="stok" required
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" />
            </div>

            <div>
                <label for="deskripsi" class="block mb-1 font-medium">Deskripsi Produk</label>
                <textarea name="deskripsi" id="deskripsi" required rows="4"
                    class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
            </div>

            <div>
                <label for="gambar" class="block mb-1 font-medium">Gambar Produk</label>
                <input type="file" name="gambar" id="gambar" required class="w-full" />
            </div>

            <div>
                <button type="submit" name="submit"
                    class="bg-emerald-600 text-white px-6 py-2 rounded hover:bg-emerald-700 transition">Tambah Produk</button>
            </div>
        </form>
    </div>

</body>
</html>
