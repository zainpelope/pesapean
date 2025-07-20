-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 20, 2025 at 06:58 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `a`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatmessage`
--

CREATE TABLE `chatmessage` (
  `id_message` int(11) NOT NULL,
  `id_chatRooms` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `pesan` text,
  `waktu_kirim` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `chatmessage`
--

INSERT INTO `chatmessage` (`id_message`, `id_chatRooms`, `sender_id`, `pesan`, `waktu_kirim`) VALUES
(1, 1, 14, 'as', '2025-07-19 16:12:51'),
(2, 1, 15, 'apa loh', '2025-07-19 16:13:17'),
(3, 2, 14, 'akun brooo', '2025-07-19 23:57:51'),
(4, 2, 7, 'ok msayank', '2025-07-19 23:58:23');

-- --------------------------------------------------------

--
-- Table structure for table `chatrooms`
--

CREATE TABLE `chatrooms` (
  `id_chatRooms` int(11) NOT NULL,
  `id_sapi` int(11) DEFAULT NULL,
  `user1_id` int(11) DEFAULT NULL,
  `user2_id` int(11) DEFAULT NULL,
  `chat_type` varchar(50) DEFAULT NULL,
  `nama_pengirim` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `chatrooms`
--

INSERT INTO `chatrooms` (`id_chatRooms`, `id_sapi`, `user1_id`, `user2_id`, `chat_type`, `nama_pengirim`, `createdAt`, `updatedAt`) VALUES
(1, 35, 14, 15, 'sapi_chat', NULL, '2025-07-19 16:01:51', '2025-07-19 16:01:51'),
(2, NULL, 14, 7, 'admin_chat', NULL, '2025-07-19 23:57:44', '2025-07-19 23:57:44');

-- --------------------------------------------------------

--
-- Table structure for table `data_sapi`
--

CREATE TABLE `data_sapi` (
  `id_sapi` int(11) NOT NULL,
  `id_macamSapi` int(11) DEFAULT NULL,
  `foto_sapi` varchar(255) DEFAULT NULL,
  `harga_sapi` int(11) DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `alamat_pemilik` varchar(255) DEFAULT NULL,
  `nomor_pemilik` varchar(20) DEFAULT NULL,
  `email_pemilik` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('jantan','betina') DEFAULT NULL,
  `id_user_penjual` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `data_sapi`
--

INSERT INTO `data_sapi` (`id_sapi`, `id_macamSapi`, `foto_sapi`, `harga_sapi`, `nama_pemilik`, `alamat_pemilik`, `nomor_pemilik`, `email_pemilik`, `createdAt`, `updatedAt`, `latitude`, `longitude`, `jenis_kelamin`, `id_user_penjual`) VALUES
(35, 2, 'pi.jpeg', 345345, 'sd', 'sad', '3424', 'a@gmail.com', '2025-07-18 19:29:48', '2025-07-18 19:29:48', '-7.1622537', '113.4830011', 'betina', 15),
(36, 3, 'pi.jpeg', 546, 'Zainullah', 'sd', 'sad', 's@gmail.com', '2025-07-18 19:30:22', '2025-07-18 19:30:22', '-7.1622537', '113.4830011', 'jantan', 15),
(37, 4, 'pi.jpeg', 345432, 'gggfhgf', 'wqe', '5464646546456456', 'dia@gmail.com', '2025-07-18 19:31:10', '2025-07-18 19:31:10', '-7.1622537', '113.4830011', 'betina', 15),
(38, 5, 'sa.jpeg', 345, 'dsf', 'df', 'sdf', 's@gmail.com', '2025-07-18 19:31:32', '2025-07-18 19:31:32', '-7.1622537', '113.4830011', 'jantan', 15),
(39, 5, 'sa.jpeg', 21321, 'sad', 'sad', 'sad', 's@gmail.com', '2025-07-19 22:55:55', '2025-07-19 22:55:55', '-7.1622537', '113.4830011', 'jantan', 15),
(40, 5, 'pi.jpeg', 234324, 'dsf', 'df', 'sdf', 'q@gmail.com', '2025-07-19 23:02:57', '2025-07-19 23:02:57', '-7.1622537', '113.4830011', 'betina', 15),
(41, 4, 'pisa.jpg', 3434, 'sd', 'sd', 's', 'fauzan@gmail.com', '2025-07-19 23:13:49', '2025-07-19 23:13:49', '-7.1622537', '113.4830011', 'jantan', 15);

-- --------------------------------------------------------

--
-- Table structure for table `generasidua`
--

CREATE TABLE `generasidua` (
  `id` int(11) NOT NULL,
  `sapiSonok` int(11) DEFAULT NULL,
  `namaPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `namaIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisKakekPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `namaNenekIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `generasisatu`
--

CREATE TABLE `generasisatu` (
  `id` int(11) NOT NULL,
  `sapiSonok` int(11) DEFAULT NULL,
  `namaPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `jenisPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `namaIndukGenerasiSatu` varchar(100) DEFAULT NULL,
  `jenisIndukGenerasiSatu` varchar(100) DEFAULT NULL,
  `namaKakekPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `home`
--

CREATE TABLE `home` (
  `id_home` int(11) NOT NULL,
  `sejarah` text,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `home`
--

INSERT INTO `home` (`id_home`, `sejarah`, `gambar`) VALUES
(1, 'sjdfgyudgfyudf', 'pisa.jpg'),
(2, 'Zainullah', 'pi.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `lelang`
--

CREATE TABLE `lelang` (
  `id_lelang` int(11) NOT NULL,
  `id_sapi` int(11) DEFAULT NULL,
  `harga_awal` int(11) DEFAULT NULL,
  `harga_tertinggi` int(11) DEFAULT NULL,
  `id_penawaranTertinggi` int(11) DEFAULT NULL,
  `batas_waktu` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `approved_by_admin` tinyint(1) DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `id_admin_approver` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `lelang`
--

INSERT INTO `lelang` (`id_lelang`, `id_sapi`, `harga_awal`, `harga_tertinggi`, `id_penawaranTertinggi`, `batas_waktu`, `status`, `approved_by_admin`, `approved_at`, `id_admin_approver`, `createdAt`, `updatedAt`, `id_user`) VALUES
(22, 35, 5000, 4002, 22, '2025-07-16 12:32:00', 'Selesai', 1, '2025-07-18 19:33:43', 7, '2025-07-18 19:33:11', '2025-07-19 23:56:53', 15),
(23, 37, 343, 324, 23, '2025-07-31 15:56:00', 'Aktif', 1, '2025-07-19 22:56:34', 7, '2025-07-19 22:56:19', '2025-07-19 23:55:52', 15),
(24, 36, 345, 45, NULL, '2025-07-30 16:14:00', 'Aktif', 1, '2025-07-20 06:55:23', 7, '2025-07-19 23:14:05', '2025-07-19 23:55:23', 15),
(25, 41, 4534, 43534, NULL, '2025-07-22 23:54:00', 'Aktif', 1, '2025-07-20 06:55:19', 7, '2025-07-20 06:54:51', '2025-07-19 23:55:19', 15);

-- --------------------------------------------------------

--
-- Table structure for table `macamsapi`
--

CREATE TABLE `macamsapi` (
  `id_macamSapi` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `macamsapi`
--

INSERT INTO `macamsapi` (`id_macamSapi`, `name`) VALUES
(1, 'Sapi Sonok'),
(2, 'Sapi Kerap'),
(3, 'Sapi Tangghek'),
(4, 'Sapi Ternak'),
(5, 'Sapi Potong');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_lelang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `tanggal_pembayaran` datetime DEFAULT CURRENT_TIMESTAMP,
  `status_pembayaran` enum('Menunggu Konfirmasi','Dikonfirmasi','Dibatalkan') DEFAULT 'Menunggu Konfirmasi',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_lelang`, `id_user`, `jumlah_bayar`, `metode_pembayaran`, `tanggal_pembayaran`, `status_pembayaran`, `bukti_transfer`, `createdAt`, `updatedAt`) VALUES
(1, 22, 14, '4002.00', 'Bank Transfer', '2025-07-19 23:56:09', 'Menunggu Konfirmasi', '687c93098135a_pisa.jpg', '2025-07-19 23:56:09', '2025-07-19 23:56:09');

-- --------------------------------------------------------

--
-- Table structure for table `penawaran`
--

CREATE TABLE `penawaran` (
  `id_penawaran` int(11) NOT NULL,
  `id_lelang` int(11) DEFAULT NULL,
  `harga_tawaran` int(11) DEFAULT NULL,
  `waktu_tawaran` datetime DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `penawaran`
--

INSERT INTO `penawaran` (`id_penawaran`, `id_lelang`, `harga_tawaran`, `waktu_tawaran`, `id_user`) VALUES
(22, 22, 4002, '2025-07-18 12:36:11', 14),
(23, 23, 324, '2025-07-19 23:55:52', 14);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id_role`, `nama_role`) VALUES
(1, 'Admin'),
(3, 'Pembeli'),
(2, 'Penjual');

-- --------------------------------------------------------

--
-- Table structure for table `sapikerap`
--

CREATE TABLE `sapikerap` (
  `id_sapi` int(11) NOT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `ketahanan_fisik` varchar(100) DEFAULT NULL,
  `kecepatan_lari` varchar(100) DEFAULT NULL,
  `penghargaan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sapikerap`
--

INSERT INTO `sapikerap` (`id_sapi`, `nama_sapi`, `ketahanan_fisik`, `kecepatan_lari`, `penghargaan`) VALUES
(35, 'sapi kerap', 'sapi kerap', 'sapi kerap', 'sapi kerap');

-- --------------------------------------------------------

--
-- Table structure for table `sapipotong`
--

CREATE TABLE `sapipotong` (
  `id` int(11) NOT NULL,
  `id_sapi` int(11) DEFAULT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `berat_badan` varchar(50) DEFAULT NULL,
  `persentase_daging` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sapipotong`
--

INSERT INTO `sapipotong` (`id`, `id_sapi`, `nama_sapi`, `berat_badan`, `persentase_daging`) VALUES
(1, 38, 'Hayabusa', '34', '70 %'),
(2, 39, 'sad', 'sad', 'sad'),
(3, 40, 'erf', 'dsf', 'sdf');

-- --------------------------------------------------------

--
-- Table structure for table `sapisonok`
--

CREATE TABLE `sapisonok` (
  `id` int(11) NOT NULL,
  `id_sapi` int(11) DEFAULT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `umur` varchar(50) DEFAULT NULL,
  `lingkar_dada` varchar(50) DEFAULT NULL,
  `panjang_badan` varchar(50) DEFAULT NULL,
  `tinggi_pundak` varchar(50) DEFAULT NULL,
  `tinggi_punggung` varchar(50) DEFAULT NULL,
  `panjang_wajah` varchar(50) DEFAULT NULL,
  `lebar_punggul` varchar(50) DEFAULT NULL,
  `lebar_dada` varchar(50) DEFAULT NULL,
  `tinggi_kaki` varchar(50) DEFAULT NULL,
  `kesehatan` varchar(100) DEFAULT NULL,
  `generasiSatu` int(11) DEFAULT NULL,
  `generasiDua` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sapitangghek`
--

CREATE TABLE `sapitangghek` (
  `id` int(11) NOT NULL,
  `id_sapi` int(11) DEFAULT NULL,
  `tinggi_badan` varchar(50) DEFAULT NULL,
  `panjang_badan` varchar(50) DEFAULT NULL,
  `lingkar_dada` varchar(50) DEFAULT NULL,
  `bobot_badan` varchar(50) DEFAULT NULL,
  `intensitas_latihan` varchar(100) DEFAULT NULL,
  `jarak_latihan` varchar(100) DEFAULT NULL,
  `prestasi` varchar(100) DEFAULT NULL,
  `kesehatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sapitangghek`
--

INSERT INTO `sapitangghek` (`id`, `id_sapi`, `tinggi_badan`, `panjang_badan`, `lingkar_dada`, `bobot_badan`, `intensitas_latihan`, `jarak_latihan`, `prestasi`, `kesehatan`) VALUES
(1, 36, 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek', 'Sapi Tangghek');

-- --------------------------------------------------------

--
-- Table structure for table `sapiternak`
--

CREATE TABLE `sapiternak` (
  `id_sapi` int(11) NOT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `kesuburan` varchar(100) DEFAULT NULL,
  `riwayat_kesehatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sapiternak`
--

INSERT INTO `sapiternak` (`id_sapi`, `nama_sapi`, `kesuburan`, `riwayat_kesehatan`) VALUES
(37, 'Sapi Ternak', 'Sapi Ternak', 'Sapi Ternak'),
(41, 'sdf', 'df', 'dsf');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updateAt` datetime DEFAULT NULL,
  `id_role` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `createdAt`, `updateAt`, `id_role`) VALUES
(7, 'Zainullah', 'zain.alapolaa@gmail.com', 'zain', '2025-07-18 04:19:24', '2025-07-18 04:19:24', 1),
(14, 'pembeli', 'pembeli@gmail.com', 'a', '2025-07-18 19:21:42', '2025-07-18 19:21:42', 3),
(15, 'Penjual', 'penjual@gmail.com', 'a', '2025-07-18 19:25:19', NULL, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatmessage`
--
ALTER TABLE `chatmessage`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_chatRooms` (`id_chatRooms`);

--
-- Indexes for table `chatrooms`
--
ALTER TABLE `chatrooms`
  ADD PRIMARY KEY (`id_chatRooms`),
  ADD KEY `id_sapi` (`id_sapi`),
  ADD KEY `fk_user1` (`user1_id`),
  ADD KEY `fk_user2` (`user2_id`);

--
-- Indexes for table `data_sapi`
--
ALTER TABLE `data_sapi`
  ADD PRIMARY KEY (`id_sapi`),
  ADD KEY `id_macamSapi` (`id_macamSapi`),
  ADD KEY `fk_user_penjual` (`id_user_penjual`);

--
-- Indexes for table `generasidua`
--
ALTER TABLE `generasidua`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sapiSonok` (`sapiSonok`);

--
-- Indexes for table `generasisatu`
--
ALTER TABLE `generasisatu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sapiSonok` (`sapiSonok`);

--
-- Indexes for table `home`
--
ALTER TABLE `home`
  ADD PRIMARY KEY (`id_home`);

--
-- Indexes for table `lelang`
--
ALTER TABLE `lelang`
  ADD PRIMARY KEY (`id_lelang`),
  ADD KEY `id_sapi` (`id_sapi`),
  ADD KEY `fk_lelang_user` (`id_user`);

--
-- Indexes for table `macamsapi`
--
ALTER TABLE `macamsapi`
  ADD PRIMARY KEY (`id_macamSapi`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_lelang` (`id_lelang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `penawaran`
--
ALTER TABLE `penawaran`
  ADD PRIMARY KEY (`id_penawaran`),
  ADD KEY `id_lelang` (`id_lelang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indexes for table `sapikerap`
--
ALTER TABLE `sapikerap`
  ADD PRIMARY KEY (`id_sapi`);

--
-- Indexes for table `sapipotong`
--
ALTER TABLE `sapipotong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sapi` (`id_sapi`);

--
-- Indexes for table `sapisonok`
--
ALTER TABLE `sapisonok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sapi` (`id_sapi`);

--
-- Indexes for table `sapitangghek`
--
ALTER TABLE `sapitangghek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sapi` (`id_sapi`);

--
-- Indexes for table `sapiternak`
--
ALTER TABLE `sapiternak`
  ADD PRIMARY KEY (`id_sapi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `fk_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatmessage`
--
ALTER TABLE `chatmessage`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chatrooms`
--
ALTER TABLE `chatrooms`
  MODIFY `id_chatRooms` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `data_sapi`
--
ALTER TABLE `data_sapi`
  MODIFY `id_sapi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `generasidua`
--
ALTER TABLE `generasidua`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `generasisatu`
--
ALTER TABLE `generasisatu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `home`
--
ALTER TABLE `home`
  MODIFY `id_home` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lelang`
--
ALTER TABLE `lelang`
  MODIFY `id_lelang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `macamsapi`
--
ALTER TABLE `macamsapi`
  MODIFY `id_macamSapi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penawaran`
--
ALTER TABLE `penawaran`
  MODIFY `id_penawaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sapipotong`
--
ALTER TABLE `sapipotong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sapisonok`
--
ALTER TABLE `sapisonok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sapitangghek`
--
ALTER TABLE `sapitangghek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chatmessage`
--
ALTER TABLE `chatmessage`
  ADD CONSTRAINT `chatmessage_ibfk_1` FOREIGN KEY (`id_chatRooms`) REFERENCES `chatrooms` (`id_chatRooms`);

--
-- Constraints for table `chatrooms`
--
ALTER TABLE `chatrooms`
  ADD CONSTRAINT `chatrooms_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`),
  ADD CONSTRAINT `fk_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `fk_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `data_sapi`
--
ALTER TABLE `data_sapi`
  ADD CONSTRAINT `data_sapi_ibfk_1` FOREIGN KEY (`id_macamSapi`) REFERENCES `macamsapi` (`id_macamSapi`),
  ADD CONSTRAINT `fk_user_penjual` FOREIGN KEY (`id_user_penjual`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `generasidua`
--
ALTER TABLE `generasidua`
  ADD CONSTRAINT `generasidua_ibfk_1` FOREIGN KEY (`sapiSonok`) REFERENCES `sapisonok` (`id`);

--
-- Constraints for table `generasisatu`
--
ALTER TABLE `generasisatu`
  ADD CONSTRAINT `generasisatu_ibfk_1` FOREIGN KEY (`sapiSonok`) REFERENCES `sapisonok` (`id`);

--
-- Constraints for table `lelang`
--
ALTER TABLE `lelang`
  ADD CONSTRAINT `fk_lelang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `lelang_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_lelang`) REFERENCES `lelang` (`id_lelang`),
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `penawaran`
--
ALTER TABLE `penawaran`
  ADD CONSTRAINT `penawaran_ibfk_1` FOREIGN KEY (`id_lelang`) REFERENCES `lelang` (`id_lelang`),
  ADD CONSTRAINT `penawaran_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `sapikerap`
--
ALTER TABLE `sapikerap`
  ADD CONSTRAINT `sapikerap_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `sapipotong`
--
ALTER TABLE `sapipotong`
  ADD CONSTRAINT `sapipotong_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `sapisonok`
--
ALTER TABLE `sapisonok`
  ADD CONSTRAINT `sapisonok_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `sapitangghek`
--
ALTER TABLE `sapitangghek`
  ADD CONSTRAINT `sapitangghek_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `sapiternak`
--
ALTER TABLE `sapiternak`
  ADD CONSTRAINT `sapiternak_ibfk_1` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
