-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2024 at 03:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `information`
--

-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE `information` (
  `id` int(11) NOT NULL,
  `Doorno` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `information`
--

INSERT INTO `information` (`id`, `Doorno`, `name`, `address`, `details`) VALUES
(1, '7', 'Nalla Perumal', 'vannarpettai', '[]'),
(2, '2', 'raja', 'skjfdsj;', '[{\"no\":1,\"date\":\"2024-09-22\",\"time\":\"01:41\",\"amount\":\"30000\",\"type\":\"current\",\"year\":\"2010\"},{\"no\":2,\"date\":\"2024-09-22\",\"time\":\"00:41\",\"amount\":\"12121\",\"type\":\"current\",\"year\":\"2001\"}]'),
(3, '7c', 'Nalla Perumal', 'vannarpettai', '[{\"no\":1,\"date\":\"2024-09-21\",\"time\":\"00:48\",\"amount\":\"200000\",\"type\":\"water\",\"year\":\"2020\"},{\"no\":2,\"date\":\"2024-09-22\",\"time\":\"00:48\",\"amount\":\"23232\",\"type\":\"House\",\"year\":\"2001\"}]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `information`
--
ALTER TABLE `information`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `information`
--
ALTER TABLE `information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
