<?php
$host = "localhost:3306"; // atau port lain sesuai yang digunakan
$user = "root";       // Jika menggunakan XAMPP atau MAMP, default username adalah 'root'
$pass = "";           // Password biasanya kosong jika kamu menggunakan XAMPP/MAMP
$db   = "cobi_db";    // Nama database yang kamu buat di phpMyAdmin

// Koneksi ke MySQL
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
