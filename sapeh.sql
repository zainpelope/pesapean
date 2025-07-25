-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 21, 2025 at 09:51 AM
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
-- Database: `sapeh`
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
  `kesehatan` varchar(100) DEFAULT NULL
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
  `kesehatan` varchar(100) DEFAULT NULL,
  `nama_sapi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(1, 'admin', 'admin@gmail.com', 'admin', '2025-07-18 04:19:24', '2025-07-18 04:19:24', 1),
(2, 'Penjual', 'penjual@gmail.com', 'a', '2025-07-18 19:25:19', NULL, 2),
(3, 'pembeli', 'pembeli@gmail.com', 'a', '2025-07-18 19:21:42', '2025-07-18 19:21:42', 3);

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
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chatrooms`
--
ALTER TABLE `chatrooms`
  MODIFY `id_chatRooms` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_sapi`
--
ALTER TABLE `data_sapi`
  MODIFY `id_sapi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
  MODIFY `id_lelang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `macamsapi`
--
ALTER TABLE `macamsapi`
  MODIFY `id_macamSapi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penawaran`
--
ALTER TABLE `penawaran`
  MODIFY `id_penawaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sapipotong`
--
ALTER TABLE `sapipotong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sapisonok`
--
ALTER TABLE `sapisonok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sapitangghek`
--
ALTER TABLE `sapitangghek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
