-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jul 19, 2025 at 01:58 AM
-- Server version: 8.0.35
-- PHP Version: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatMessage`
--

CREATE TABLE `chatMessage` (
  `id_message` int NOT NULL,
  `id_chatRooms` int NOT NULL,
  `sender_id` int NOT NULL,
  `pesan` text,
  `waktu_kirim` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chatMessage`
--

INSERT INTO `chatMessage` (`id_message`, `id_chatRooms`, `sender_id`, `pesan`, `waktu_kirim`) VALUES
(1, 1, 8, 'halooo', '2025-07-19 06:48:29'),
(2, 1, 8, 'halooo', '2025-07-19 06:48:29'),
(3, 1, 8, 'halooo', '2025-07-19 06:48:31'),
(4, 2, 7, 'saya adalah sapi', '2025-07-19 06:53:46'),
(5, 2, 2, 'iya ada aapa sayang', '2025-07-19 06:55:52'),
(6, 1, 2, 'ada apa broo ada yang bisa sya bantu?', '2025-07-19 06:56:42'),
(7, 1, 2, 'ada apa broo ada yang bisa sya bantu?', '2025-07-19 06:56:56'),
(8, 1, 8, 'iya ngutang dulu 100', '2025-07-19 06:58:12'),
(9, 1, 8, 'iya ngutang dulu 100', '2025-07-19 06:58:17'),
(10, 1, 2, 'bangkek luh bro', '2025-07-19 07:33:50'),
(11, 1, 2, 'kerjalah bro jangan ngutang terus', '2025-07-19 07:46:16'),
(12, 2, 7, 'sdsdsdsad', '2025-07-19 08:38:21'),
(13, 2, 7, 'mantaaap', '2025-07-19 08:52:09'),
(14, 2, 7, 'mantaaap', '2025-07-19 08:52:14');

-- --------------------------------------------------------

--
-- Table structure for table `chatRooms`
--

CREATE TABLE `chatRooms` (
  `id_chatRooms` int NOT NULL,
  `id_sapi` int DEFAULT NULL,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  `chat_type` enum('sapi_chat','admin_chat') NOT NULL DEFAULT 'sapi_chat',
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chatRooms`
--

INSERT INTO `chatRooms` (`id_chatRooms`, `id_sapi`, `user1_id`, `user2_id`, `chat_type`, `createdAt`, `updatedAt`) VALUES
(1, 33, 2, 8, 'sapi_chat', '2025-07-19 06:48:12', '2025-07-19 06:48:12'),
(2, 33, 2, 7, 'sapi_chat', '2025-07-19 06:53:36', '2025-07-19 06:53:36'),
(3, 33, 2, 2, 'sapi_chat', '2025-07-19 06:57:18', '2025-07-19 06:57:18'),
(4, NULL, 1, 7, 'admin_chat', '2025-07-19 08:47:43', '2025-07-19 08:47:43');

-- --------------------------------------------------------

--
-- Table structure for table `data_sapi`
--

CREATE TABLE `data_sapi` (
  `id_sapi` int NOT NULL,
  `id_macamSapi` int DEFAULT NULL,
  `foto_sapi` varchar(255) DEFAULT NULL,
  `harga_sapi` int DEFAULT NULL,
  `nama_pemilik` varchar(100) DEFAULT NULL,
  `alamat_pemilik` varchar(255) DEFAULT NULL,
  `nomor_pemilik` varchar(20) DEFAULT NULL,
  `email_pemilik` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('jantan','betina') DEFAULT NULL,
  `id_user_penjual` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `data_sapi`
--

INSERT INTO `data_sapi` (`id_sapi`, `id_macamSapi`, `foto_sapi`, `harga_sapi`, `nama_pemilik`, `alamat_pemilik`, `nomor_pemilik`, `email_pemilik`, `createdAt`, `updatedAt`, `latitude`, `longitude`, `jenis_kelamin`, `id_user_penjual`) VALUES
(33, 2, 'pisa.jpg', 9999, 'Zainullah', 'k', 'potong', 'fauzan@gmail.com', '2025-07-18 05:34:18', '2025-07-18 05:34:18', '-7.1622537', '113.4830011', 'jantan', 2),
(34, 3, 'spongebob-squarepants-live-wallpaper-45.jpg', 432324, 'fdsfds', 'df', 'dsf', 'dia@gmail.com', '2025-07-18 22:20:04', '2025-07-18 22:20:04', '-7.1622537', '113.4830011', 'jantan', 2);

-- --------------------------------------------------------

--
-- Table structure for table `generasidua`
--

CREATE TABLE `generasidua` (
  `id` int NOT NULL,
  `sapiSonok` int DEFAULT NULL,
  `namaPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `namaIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `jenisKakekPejantanGenerasiDua` varchar(100) DEFAULT NULL,
  `namaNenekIndukGenerasiDua` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `generasisatu`
--

CREATE TABLE `generasisatu` (
  `id` int NOT NULL,
  `sapiSonok` int DEFAULT NULL,
  `namaPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `jenisPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `namaIndukGenerasiSatu` varchar(100) DEFAULT NULL,
  `jenisIndukGenerasiSatu` varchar(100) DEFAULT NULL,
  `namaKakekPejantanGenerasiSatu` varchar(100) DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `home`
--

CREATE TABLE `home` (
  `id_home` int NOT NULL,
  `sejarah` text,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `lelang`
--

CREATE TABLE `lelang` (
  `id_lelang` int NOT NULL,
  `id_sapi` int DEFAULT NULL,
  `harga_awal` int DEFAULT NULL,
  `harga_tertinggi` int DEFAULT NULL,
  `id_penawaranTertinggi` int DEFAULT NULL,
  `batas_waktu` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `approved_by_admin` tinyint(1) DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `id_admin_approver` int DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `lelang`
--

INSERT INTO `lelang` (`id_lelang`, `id_sapi`, `harga_awal`, `harga_tertinggi`, `id_penawaranTertinggi`, `batas_waktu`, `status`, `approved_by_admin`, `approved_at`, `id_admin_approver`, `createdAt`, `updatedAt`, `id_user`) VALUES
(21, 33, 234, 435345353, 22, '2025-07-15 22:37:00', 'Lewat', 1, '2025-07-18 05:40:20', 1, '2025-07-18 05:37:30', '2025-07-19 05:57:21', 2);

-- --------------------------------------------------------

--
-- Table structure for table `macamsapi`
--

CREATE TABLE `macamsapi` (
  `id_macamSapi` int NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

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
-- Table structure for table `penawaran`
--

CREATE TABLE `penawaran` (
  `id_penawaran` int NOT NULL,
  `id_lelang` int DEFAULT NULL,
  `harga_tawaran` int DEFAULT NULL,
  `waktu_tawaran` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `penawaran`
--

INSERT INTO `penawaran` (`id_penawaran`, `id_lelang`, `harga_tawaran`, `waktu_tawaran`, `id_user`) VALUES
(21, 21, 76576567, '2025-07-17 23:05:11', 7),
(22, 21, 435345353, '2025-07-19 05:57:05', 8);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` int NOT NULL,
  `nama_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

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
  `id_sapi` int NOT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `ketahanan_fisik` varchar(100) DEFAULT NULL,
  `kecepatan_lari` varchar(100) DEFAULT NULL,
  `penghargaan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `sapikerap`
--

INSERT INTO `sapikerap` (`id_sapi`, `nama_sapi`, `ketahanan_fisik`, `kecepatan_lari`, `penghargaan`) VALUES
(33, 'sapi kerap', 'sd', 'sd', 'a');

-- --------------------------------------------------------

--
-- Table structure for table `sapipotong`
--

CREATE TABLE `sapipotong` (
  `id` int NOT NULL,
  `id_sapi` int DEFAULT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `berat_badan` varchar(50) DEFAULT NULL,
  `persentase_daging` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sapisonok`
--

CREATE TABLE `sapisonok` (
  `id` int NOT NULL,
  `id_sapi` int DEFAULT NULL,
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
  `generasiSatu` int DEFAULT NULL,
  `generasiDua` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `sapiTangghek`
--

CREATE TABLE `sapiTangghek` (
  `id` int NOT NULL,
  `id_sapi` int DEFAULT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `tinggi_badan` varchar(50) DEFAULT NULL,
  `panjang_badan` varchar(50) DEFAULT NULL,
  `lingkar_dada` varchar(50) DEFAULT NULL,
  `bobot_badan` varchar(50) DEFAULT NULL,
  `intensitas_latihan` varchar(100) DEFAULT NULL,
  `jarak_latihan` varchar(100) DEFAULT NULL,
  `prestasi` varchar(100) DEFAULT NULL,
  `kesehatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `sapiTangghek`
--

INSERT INTO `sapiTangghek` (`id`, `id_sapi`, `nama_sapi`, `tinggi_badan`, `panjang_badan`, `lingkar_dada`, `bobot_badan`, `intensitas_latihan`, `jarak_latihan`, `prestasi`, `kesehatan`) VALUES
(2, 34, NULL, '34', '4', '34', '34', '34', '3', '34', '34');

-- --------------------------------------------------------

--
-- Table structure for table `sapiternak`
--

CREATE TABLE `sapiternak` (
  `id_sapi` int NOT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL,
  `kesuburan` varchar(100) DEFAULT NULL,
  `riwayat_kesehatan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updateAt` datetime DEFAULT NULL,
  `id_role` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `createdAt`, `updateAt`, `id_role`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', '2025-07-17 21:19:46', '2025-07-17 21:19:46', 1),
(2, 'penjual', 'penjual@gmail.com', 'penjual', '2025-07-17 21:24:12', '2025-07-17 21:24:12', 2),
(7, 'Zainullah', 'zain.alapolaa@gmail.com', 'zain', '2025-07-18 04:19:24', '2025-07-18 04:19:24', 3),
(8, 'a', 'a@gmail.com', 'a', '2025-07-18 22:56:49', '2025-07-18 22:56:49', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatMessage`
--
ALTER TABLE `chatMessage`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `fk_message_chatroom` (`id_chatRooms`),
  ADD KEY `fk_message_sender` (`sender_id`);

--
-- Indexes for table `chatRooms`
--
ALTER TABLE `chatRooms`
  ADD PRIMARY KEY (`id_chatRooms`),
  ADD KEY `fk_chatroom_sapi` (`id_sapi`),
  ADD KEY `fk_chatroom_user1` (`user1_id`),
  ADD KEY `fk_chatroom_user2` (`user2_id`);

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
-- Indexes for table `sapiTangghek`
--
ALTER TABLE `sapiTangghek`
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
-- AUTO_INCREMENT for table `chatMessage`
--
ALTER TABLE `chatMessage`
  MODIFY `id_message` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `chatRooms`
--
ALTER TABLE `chatRooms`
  MODIFY `id_chatRooms` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `data_sapi`
--
ALTER TABLE `data_sapi`
  MODIFY `id_sapi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `generasidua`
--
ALTER TABLE `generasidua`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `generasisatu`
--
ALTER TABLE `generasisatu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `home`
--
ALTER TABLE `home`
  MODIFY `id_home` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lelang`
--
ALTER TABLE `lelang`
  MODIFY `id_lelang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `macamsapi`
--
ALTER TABLE `macamsapi`
  MODIFY `id_macamSapi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `penawaran`
--
ALTER TABLE `penawaran`
  MODIFY `id_penawaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sapipotong`
--
ALTER TABLE `sapipotong`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sapisonok`
--
ALTER TABLE `sapisonok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sapiTangghek`
--
ALTER TABLE `sapiTangghek`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chatMessage`
--
ALTER TABLE `chatMessage`
  ADD CONSTRAINT `fk_message_chatroom` FOREIGN KEY (`id_chatRooms`) REFERENCES `chatRooms` (`id_chatRooms`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `chatRooms`
--
ALTER TABLE `chatRooms`
  ADD CONSTRAINT `fk_chatroom_sapi` FOREIGN KEY (`id_sapi`) REFERENCES `data_sapi` (`id_sapi`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chatroom_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chatroom_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

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
-- Constraints for table `sapiTangghek`
--
ALTER TABLE `sapiTangghek`
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
