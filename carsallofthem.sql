-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 03:43 AM
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
-- Database: `carsallofthem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_logs`
--

CREATE TABLE `admin_login_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_logs`
--

INSERT INTO `admin_login_logs` (`id`, `username`, `login_time`) VALUES
(24, 'admin', '2026-02-12 07:10:15'),
(25, 'admin', '2026-02-12 07:18:59'),
(26, 'admin', '2026-02-12 07:26:27'),
(27, 'admin', '2026-02-12 07:26:32'),
(28, 'admin', '2026-02-12 08:09:29'),
(29, 'admin', '2026-02-12 08:22:05'),
(30, 'admin', '2026-02-19 07:04:19'),
(31, 'admin', '2026-02-19 08:37:55'),
(32, 'admin', '2026-02-27 12:44:03'),
(33, 'admin', '2026-02-27 13:35:22'),
(34, 'admin', '2026-03-02 05:48:26'),
(35, 'admin', '2026-03-02 06:54:33'),
(36, 'admin', '2026-03-02 06:57:33'),
(37, 'admin', '2026-03-02 07:05:16'),
(38, 'admin', '2026-03-02 08:27:00'),
(39, 'admin', '2026-03-02 08:27:39'),
(40, 'admin', '2026-03-02 08:28:37'),
(41, 'admin', '2026-03-02 08:30:02'),
(42, 'admin', '2026-03-02 08:32:21'),
(43, 'admin', '2026-03-02 08:34:01'),
(44, 'admin', '2026-03-02 08:34:48'),
(45, 'admin', '2026-03-02 08:42:01'),
(46, 'admin', '2026-03-02 08:42:54'),
(47, 'admin', '2026-03-02 08:43:25'),
(48, 'admin', '2026-03-02 08:50:52'),
(49, 'admin', '2026-03-03 01:38:36'),
(50, 'admin', '2026-03-03 01:40:33'),
(51, 'admin', '2026-03-03 02:07:00'),
(52, 'admin', '2026-03-10 05:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `Car` varchar(100) NOT NULL,
  `Model` varchar(100) NOT NULL,
  `Year` int(11) NOT NULL,
  `Price` int(11) NOT NULL,
  `Others` varchar(255) DEFAULT NULL,
  `Stock` int(11) DEFAULT NULL,
  `Seat` int(11) DEFAULT NULL,
  `KMperL` int(11) DEFAULT NULL,
  `Transmission` varchar(50) DEFAULT NULL,
  `FuelType` varchar(50) NOT NULL,
  `Brand` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `Car`, `Model`, `Year`, `Price`, `Others`, `Stock`, `Seat`, `KMperL`, `Transmission`, `FuelType`, `Brand`) VALUES
(1, 'Honda_Odyssey.png', 'Honda Odyssey', 1994, 200, 'fadsd', 9, 2, 100, 'Manual', 'Electric', 'Japan'),
(20, 'car_6996cf69b7df72.28985301.png', 'Subaru Forester', 2026, 1234, 'Won fictional awards', 5, 4, 500, 'Auto', 'Petrol', 'Subaru'),
(21, 'car_6996d026866632.21795352.jpg', 'Subaru XV', 2018, 500, 'The new model also combines the Subaru performance essentials of superior aerodynamics and visibilit', 10, 4, 1000, 'Auto', 'Electric', 'Subaru'),
(22, 'car_6996d10a40b610.89645289.png', 'BYD Seal', 2025, 715, 'Made in China', 7, 4, 400, 'Auto', 'Electric', 'BYD'),
(23, 'car_6996d1aaeaaa38.89616164.jpg', 'BYD Song Pro', 2019, 456, 'An older model', 1, 4, 348, 'Auto', 'Electric', 'BYD'),
(24, 'car_6996d246319c52.98667161.jpg', 'BYD Song Pro', 2019, 456, 'An older model', 1, 4, 348, 'Auto', 'Electric', 'BYD'),
(25, 'car_6996d293b45902.22774560.png', 'VolkSwagon Beetle', 1966, 917, 'An older model with extremely limited stock', 1, 2, 179, 'Auto', 'Petrol', 'VolkSwagon'),
(26, 'car_6996d31585b3c4.33048143.png', 'VolkSwagon New Beetle', 2005, 912, 'Newer beetle model but still low stock', 1, 2, 359, 'Auto', 'Petrol', 'VolkSwagon'),
(27, 'car_6996d3d6bf7da1.21252170.png', 'Honda Civic', 2019, 345, 'Common car for events', 6, 5, 300, 'Auto', 'Petrol', 'Honda'),
(28, 'car_6996d4038dd7c5.73957108.jpeg', 'Honda City', 2026, 987, 'Latest model!', 10, 5, 846, 'Auto', 'Petrol', 'Honda'),
(29, 'car_69a192f1990f34.44466276.png', 'Honda Civic R', 2020, 282, 'test description', 0, 4, 123, 'Auto', 'Electric', 'Honda');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `expires_at`, `used`) VALUES
(8, 2, '7d856933364b5936230d62dc5b3d7d01bb62212d7c7088149f75234923652ec1', '2026-01-28 08:24:01', 1),
(9, 2, '7747c70a1fda5f65bebba59409ed59be10779f92f414d112e02994cf7d16e156', '2026-01-28 08:29:49', 0),
(10, 2, 'e9b0a131a400d5e3cbdab1edf3d12f0dee3b8d1cbd0d2c0b5ed2687f87f33811', '2026-02-27 14:31:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`) VALUES
(1, 'admin', '$2y$10$.lCLopuA5KEo4WybuZ33DuxP4FRR2zIFTEi5BQz9CNV3s1qLBOld6', 'admin', 'admin@admin.com'),
(2, 'Jeremy', '$2y$10$PK8BDRpdwiShJZZozaV7V.vf/KQ6.trVxHljkHKXat/QA0C99aITC', 'user', 'jeremyteo27@gmail.com'),
(3, 'Johnny', 'abcde', 'user', 'johncena@gmail.com'),
(7, 'a', '$2y$10$c7MTabZ9dqmED7XoFAL46.qRNJ8.Ocanzgl1QLbtjroOq3BU5tOXK', 'user', 'a@gmail.com'),
(9, 'Jeremy', '$2y$10$PK8BDRpdwiShJZZozaV7V.vf/KQ6.trVxHljkHKXat/QA0C99aITC', 'user', 'test@fake.com'),
(10, 'jeremy', '$2y$10$6Mj1/i9FMy8w9yAFJgJUpuBGJYTUzVftWPwZ2PT4BOyrmKpt41Nw2', 'user', 'jeremy27@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login_logs`
--
ALTER TABLE `admin_login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
