<?php
session_start();
include '../includes/db.php';

// Cek apakah admin sudah login (opsional, tambahkan jika punya sistem login admin)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header("Location: login.php");
//     exit();
// }

$query = "SELECT * FROM orders ORDER BY order_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan - Admin</title>
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            text-align: left;
        }
        a {
            color: #0d9488;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        h2 {
            color: #0f766e;
            margin-bottom: 10px;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📦 Daftar Pesanan</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>WA</th>
                    <th>Alamat</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone_number']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($row['order_date'])) ?></td>
                            <td>Rp<?= number_format($row['total'], 0, ',', '.') ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td><a href="detail_pesanan.php?id=<?= $row['id'] ?>">Lihat Detail</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">Belum ada pesanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
