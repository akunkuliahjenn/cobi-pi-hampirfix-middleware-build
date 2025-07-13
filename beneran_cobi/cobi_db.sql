-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 01, 2025 at 08:55 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cobi_db`
--
CREATE DATABASE IF NOT EXISTS `cobi_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cobi_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3'),
(3, 'admin1', 'e00cf25ad42683b3df678c61f42c6bda');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cart_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `produk_id`, `qty`, `created_at`, `cart_id`) VALUES
(78, 10, 1, '2025-06-17 12:11:38', '0');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','diproses','dikirim','selesai','batal') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `phone_number`, `address`, `total`, `order_date`, `status`) VALUES
(1, 'Nandana Rasendriya', '087787123108', 'JL.Mandor Rt05/Rw 05 No.124', '27000.00', '2025-05-15 20:06:40', 'diproses'),
(4, 'Yoga Wiratama', '087787123108', 'Cikarang', '600000.00', '2025-05-15 23:42:59', 'selesai'),
(5, 'Deryl Hannah Idly Nasution', '1321321', 'adasd', '200000.00', '2025-05-29 20:44:49', 'selesai'),
(6, 'Wahyu Hidayat', '08123456', 'Kebayoran', '412000.00', '2025-05-30 02:21:21', 'diproses'),
(7, 'Rahmalia ', '123131321', 'asdadsa', '77000.00', '2025-05-30 07:02:06', 'dikirim'),
(8, 'asd', '2', 'asd', '90000.00', '2025-06-23 22:37:47', 'pending'),
(9, 'asd', '123', 'asdsad', '290131.00', '2025-06-23 22:51:20', 'batal'),
(10, 'Nandana Rasendriya', '087787123108', 'JL.Mandor Rt05/Rw 05 No.124\r\n-', '154000.00', '2025-06-24 20:15:37', 'pending'),
(11, 'Nandana Rasendriya', '087787123108', 'JL.Mandor Rt05/Rw 05 No.124\r\n-', '90000.00', '2025-06-25 12:26:25', 'pending'),
(12, 'vbnm,', '576465', 'wekjfwhew', '240131.00', '2025-06-26 14:53:10', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(2, 4, 5, 3, '200000.00'),
(3, 5, 5, 1, '200000.00'),
(4, 6, 3, 1, '27000.00'),
(5, 6, 8, 5, '77000.00'),
(6, 7, 8, 1, '77000.00'),
(7, 8, 10, 1, '90000.00'),
(8, 9, 10, 1, '90000.00'),
(9, 9, 9, 1, '123131.00'),
(10, 9, 8, 1, '77000.00'),
(11, 10, 8, 2, '77000.00'),
(12, 11, 10, 1, '90000.00'),
(13, 12, 9, 1, '123131.00'),
(14, 12, 3, 1, '27000.00'),
(15, 12, 10, 1, '90000.00');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(10) UNSIGNED NOT NULL,
  `deskripsi` text NOT NULL,
  `gambar` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `nama`, `harga`, `stok`, `deskripsi`, `gambar`) VALUES
(2, 'harun kucing', 10000, 10, 'gabisa ngomong r', 'WhatsApp Image 2025-04-18 at 1.54.19 PM.jpeg'),
(3, 'haikal', 27000, 86, 'kadal purba', 'WhatsApp Image 2025-04-09 at 10.48.41 PM.jpeg'),
(4, 'harun ac', 10000, 0, 'harun digin bat', 'WhatsApp Image 2025-04-09 at 10.39.32 PM.jpeg'),
(5, 'Delia', 200000, 6, 'pentol goreng', 'Tyler’s Mugshot.jpeg'),
(8, 'way kang serpis', 77000, 91, 'enak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gituenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\nenak kalo masih panas katanya sih gitu, tapi orangnya dingin bat yakann gimana sih, kalo kata orang mah gitu, orang kebayoran emang gitu\r\n\r\n', '12c04e93-f4ac-496f-886e-5adf71a5475c_auto_x2.jpg'),
(9, 'way kang serpis', 123131, 10, 'adasda', 'WhatsApp Image 2023-10-02 at 12.04.32 PM.jpeg'),
(10, 'Koi', 90000, 8, 'askdjhasdj', 'Clair Obscur_ Expedition 33 - Lune_.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `cart_id` varchar(100) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(30) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `cart_id`, `nama`, `alamat`, `telepon`, `total`, `created_at`) VALUES
(1, '0', NULL, NULL, NULL, 27000, '2025-05-15 19:10:14'),
(2, '0', NULL, NULL, NULL, 81000, '2025-05-15 19:37:11'),
(3, '0', NULL, NULL, NULL, 135000, '2025-05-15 19:40:47');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_item`
--

DROP TABLE IF EXISTS `transaksi_item`;
CREATE TABLE `transaksi_item` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi_item`
--
ALTER TABLE `transaksi_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaksi_item`
--
ALTER TABLE `transaksi_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi_item`
--
ALTER TABLE `transaksi_item`
  ADD CONSTRAINT `transaksi_item_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `transaksi_item_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
