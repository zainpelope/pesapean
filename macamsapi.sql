-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 06, 2025 at 04:50 PM
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
-- Database: `db_sapi`
--

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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `macamsapi`
--
ALTER TABLE `macamsapi`
  ADD PRIMARY KEY (`id_macamSapi`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `macamsapi`
--
ALTER TABLE `macamsapi`
  MODIFY `id_macamSapi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
