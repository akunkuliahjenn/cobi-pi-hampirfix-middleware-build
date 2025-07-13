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

    if ($produk) {
        // Hapus gambar produk
        if (file_exists("../images/" . $produk['gambar'])) {
            unlink("../images/" . $produk['gambar']);
        }

        // Hapus data produk dari database
        $query = "DELETE FROM produk WHERE id='$id'";
        if (mysqli_query($conn, $query)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Gagal menghapus produk: " . mysqli_error($conn);
        }
    } else {
        header("Location: dashboard.php");
        exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Produk - Cobi</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="navbar">
        <h1>Hapus Produk</h1>
        <a href="dashboard.php">Kembali ke Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <h2>Apakah Anda yakin ingin menghapus produk ini?</h2>
        <p>Nama Produk: <?php echo $produk['nama']; ?></p>
        <img src="../images/<?php echo $produk['gambar']; ?>" alt="<?php echo $produk['nama']; ?>" width="100"><br>
        <a href="hapus_produk.php?id=<?php echo $produk['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Ya, Hapus</a> | 
        <a href="dashboard.php">Batal</a>
    </div>
</body>
