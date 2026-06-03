-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 03, 2026 at 04:34 AM
-- Server version: 10.11.16-MariaDB-cll-lve
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asbfash2_loyal_customer`
--

-- --------------------------------------------------------

--
-- Table structure for table `otp_requests`
--

CREATE TABLE `otp_requests` (
  `id` bigint(20) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `purpose` enum('register','forgot') NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_requests`
--

INSERT INTO `otp_requests` (`id`, `mobile`, `otp_code`, `purpose`, `expires_at`, `is_verified`) VALUES
(6, '94740890730', '861971', 'register', '2026-06-03 02:45:31', 1),
(7, '94771134216', '250331', 'register', '2026-06-03 02:52:14', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `nic` varchar(30) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `customer_code`, `nic`, `mobile`, `password_hash`, `created_at`) VALUES
(3, '011107878', '867743219', '94740890730', '$2y$10$Y.V/MsVAMZSZgMDQdbIOSuAhTbvHUnwXAqDeVcE.2/Odv4/a9oJUS', '0000-00-00 00:00:00'),
(4, '021974826', '965661832V', '94771134216', '$2y$10$um0ZX196V0yhLV5sPPQLS.dSNhFuKVtvsvc.tCd5Gf5ZNd5.gUy8q', '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_validation` (`mobile`,`otp_code`,`is_verified`,`expires_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_customer_code` (`customer_code`),
  ADD UNIQUE KEY `idx_nic` (`nic`),
  ADD UNIQUE KEY `idx_mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
