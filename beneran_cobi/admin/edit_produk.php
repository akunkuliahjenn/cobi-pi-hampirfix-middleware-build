<?php
session_start();
include '../config/db.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah id produk ada
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data produk berdasarkan ID
    $query = "SELECT * FROM produk WHERE id='$id'";
    $result = mysqli_query($conn, $query);
    $produk = mysqli_fetch_assoc($result);

    if (!$produk) {
        header("Location: dashboard.php");
        exit();
    }

    // Proses update produk
    if (isset($_POST['submit'])) {
        $nama = $_POST['nama'];
        $harga = $_POST['harga'];
        $deskripsi = $_POST['deskripsi'];
        $stok = $_POST['stok'];

        // Cek apakah gambar baru di-upload
        if ($_FILES['gambar']['name']) {
            $gambar = $_FILES['gambar']['name'];
            $gambar_tmp = $_FILES['gambar']['tmp_name'];
            $gambar_path = "../images/" . $gambar;

            if (move_uploaded_file($gambar_tmp, $gambar_path)) {
                // Hapus gambar lama jika ada
                if (file_exists("../images/" . $produk['gambar'])) {
                    unlink("../images/" . $produk['gambar']);
                }
            } else {
                $error = "Gagal mengupload gambar!";
            }
        } else {
            $gambar = $produk['gambar'];
        }

        // Update data produk
        if (!isset($error)) {
            $query = "UPDATE produk SET nama='$nama', harga='$harga', deskripsi='$deskripsi', stok='$stok', gambar='$gambar' WHERE id='$id'";
            if (mysqli_query($conn, $query)) {
                $success = "Produk berhasil diperbarui!";
                // Refresh data produk
                $result = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id'");
                $produk = mysqli_fetch_assoc($result);
            } else {
                $error = "Gagal memperbarui produk: " . mysqli_error($conn);
            }
        }
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Cobi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 flex">

    <?php include 'sidebar.php'; ?>

    <div class="flex-1 p-6 overflow-auto ml-64">
        <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-bold mb-4 text-emerald-700">Edit Produk</h2>

            <?php if (isset($success)): ?>
                <p class="mb-4 text-green-600 font-medium"><?= $success; ?></p>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <p class="mb-4 text-red-600 font-medium"><?= $error; ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="nama" class="block mb-1 font-medium">Nama Produk</label>
                    <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($produk['nama']); ?>" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="harga" class="block mb-1 font-medium">Harga Produk</label>
                    <input type="number" name="harga" id="harga" value="<?= htmlspecialchars($produk['harga']); ?>" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="stok" class="block mb-1 font-medium">Stok Produk</label>
                    <input type="number" name="stok" id="stok" value="<?= htmlspecialchars($produk['stok']); ?>" required class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="deskripsi" class="block mb-1 font-medium">Deskripsi Produk</label>
                    <textarea name="deskripsi" id="deskripsi" required rows="4" class="w-full border border-gray-300 p-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500"><?= htmlspecialchars($produk['deskripsi']); ?></textarea>
                </div>

                <div>
                    <label for="gambar" class="block mb-1 font-medium">Gambar Produk (Kosongkan jika tidak mengganti)</label>
                    <input type="file" name="gambar" id="gambar" class="w-full">
                    <img src="../images/<?= htmlspecialchars($produk['gambar']); ?>" alt="<?= htmlspecialchars($produk['nama']); ?>" class="mt-2 rounded shadow w-32">
                </div>

                <div>
                    <button type="submit" name="submit" class="bg-emerald-600 text-white px-6 py-2 rounded hover:bg-emerald-700 transition">Perbarui Produk</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
