-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20260902.697a6874b0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 20, 2026 at 02:12 PM
-- Server version: 9.6.0
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spending-diary`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_a_accounts`
--
CREATE TABLE `tb_a_accounts` (
  `Username` varchar(100) NOT NULL,
  `Password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_a_accounts`
--

INSERT INTO `tb_a_accounts` (`Username`, `Password`, `Name`) VALUES
('aldi_ardyansah', '$2y$12$1zf7bX3vcsqhEgbHIBoI9OnJ342.Dl6lxM7nEoLyd3zReY4p2RAfS', 'Aldi Dwi Ardyansah'),
('rima_aviyani', '$2y$12$lW3ds9EAzMvsgDHK.sKXPugmT8kP5vYKYjeAZc52kpwH0RYUCHG3C', 'Rima Aviyani');

-- --------------------------------------------------------

--
-- Table structure for table `tb_b_spending`
--
CREATE TABLE `tb_b_spending` (
  `ID` int UNSIGNED NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Date` varchar(100) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Category` varchar(100) NOT NULL,
  `Amount` varchar(100) NOT NULL,
  `Notes` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tb_b_spending`
--

INSERT INTO `tb_b_spending` (`ID`, `Username`, `Date`, `Description`, `Category`, `Amount`, `Notes`) VALUES
(5, 'aldi_ardyansah', '2026-09-20', 'Silverqueen', 'Food & Drinks', '11000', ''),
(6, 'aldi_ardyansah', '2026-09-20', 'Le Minerale', 'Food & Drinks', '4000', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_a_accounts`
--
ALTER TABLE `tb_a_accounts`
  ADD PRIMARY KEY (`Username`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Username_2` (`Username`);

--
-- Indexes for table `tb_b_spending`
--
ALTER TABLE `tb_b_spending`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_b_spending`
--
ALTER TABLE `tb_b_spending`
  MODIFY `ID` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
