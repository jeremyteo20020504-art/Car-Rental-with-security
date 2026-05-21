-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 03:44 AM
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
-- Database: `bookingall`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookinglist`
--

CREATE TABLE `bookinglist` (
  `booking_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking1_id` varchar(255) DEFAULT NULL,
  `booking2_id` varchar(255) DEFAULT NULL,
  `booking3_id` varchar(255) DEFAULT NULL,
  `booking4_id` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `dropoff_location` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `notify_status` tinyint(1) DEFAULT 0,
  `selected_addons` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_addons`)),
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookinglist`
--

INSERT INTO `bookinglist` (`booking_id`, `booking_date`, `booking1_id`, `booking2_id`, `booking3_id`, `booking4_id`, `start_date`, `end_date`, `pickup_location`, `dropoff_location`, `customer_name`, `customer_email`, `notify_status`, `selected_addons`, `total_price`) VALUES
(14, '2026-01-23', '1 - Honda Odyssey', '1 - Honda Odyssey', '1 - Honda Odyssey', '', '2026-01-23 16:00:00', '2026-01-23 16:00:00', NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(15, '2026-01-27', '3 - Honda Odyssey real', '4 - Honda Odyssey fake', '1 - Honda Odyssey', '1 - Honda Odyssey', '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'aewdrtty', 'malaysia', NULL, NULL, 0, NULL, 0.00),
(16, '2026-01-27', '1 - Honda Odyssey', '2 - Honda Odyssey', NULL, NULL, '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'singapore', 'singapore', NULL, 'admin@admin.com', 0, NULL, 0.00),
(18, '2026-01-27', '3 - Honda Odyssey real', NULL, NULL, NULL, '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'singapore', 'def', 'abc', 'abc@gmail.com', 1, NULL, 0.00),
(19, '2026-01-27', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-27 19:00:00', '2026-01-27 19:00:00', 'singapore', 'singapore', 'sfd', 'fds@gmail.com', 1, NULL, 0.00),
(21, '2026-01-28', '1 - Honda Odyssey', NULL, NULL, NULL, '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'singapore', 'malaysia', 'abc', 'abc@gmail.com', 1, NULL, 0.00),
(22, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(23, '2026-01-28', '1 - Honda Odyssey', '3 - Honda Odyssey real', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(25, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(26, '2026-01-28', NULL, NULL, NULL, NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '', '', 0, NULL, 0.00),
(27, '2026-01-30', '1 - Honda Odyssey', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(28, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(29, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(30, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(31, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(32, '2026-01-28', NULL, '1 - Honda Odyssey', NULL, NULL, '2026-01-28 16:00:00', '2026-01-28 16:00:00', 'singapore', '456', 'test', 'admin@admin.com', 0, NULL, 0.00),
(33, '2026-02-02', '4 - Honda Odyssey fake', '2 - Honda Odyssey', NULL, NULL, '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'south', 'west', 'a', 'b@gmail.com', 0, NULL, 0.00),
(34, '2026-02-03', NULL, '5 - Honda Odyssey weird', '2 - Honda Odyssey', NULL, '2026-02-03 10:00:00', '2026-02-03 10:00:00', 'Rental Shop', 'MRT station', 'Someone', 'someone@gmail.com', 1, NULL, 0.00),
(35, '2026-02-04', NULL, '5 - Honda Odyssey weird', NULL, NULL, '2026-02-04 20:00:00', '2026-02-04 21:00:00', 'a', 'a', 'a', 'jeremyteo27@gmail.com', 0, NULL, 0.00),
(36, '2026-02-04', '1 - Honda Odyssey', '5 - Honda Odyssey weird', '1 - Honda Odyssey', NULL, '1970-01-01 01:00:00', '1970-01-01 01:00:00', 'a', 'b', 'c', 'a@bc.com', 0, NULL, 0.00),
(37, '2026-02-04', '1 - Honda Odyssey', '1 - Honda Odyssey', '1 - Honda Odyssey', '5 - Honda Odyssey weird', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(38, '2026-02-04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(39, '2026-02-04', '2 - Honda Odyssey', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(40, '2026-02-05', '2 - Honda Odyssey', NULL, NULL, NULL, '2026-03-04 20:00:00', '2026-03-06 22:00:00', 'test', 'test', 'tester', 'test@email.com', 0, NULL, 0.00),
(41, '2026-02-09', '1 - Honda Odyssey', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(42, '2026-02-11', '1 - Honda Odyssey', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(44, '2026-02-12', NULL, NULL, NULL, NULL, '2026-02-12 20:00:00', '2026-02-12 21:00:00', NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(45, '2026-02-24', NULL, '20 - Subaru Forester', NULL, NULL, '2026-02-25 01:00:00', '2026-02-26 04:00:00', 'Dhoby Ghaut', 'RedHill', 'Jeremy', 'jeremyteo27@gmail.com', 0, NULL, 0.00),
(46, '2026-02-24', '1 - Honda Odyssey', '28 - Honda City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(47, '2026-02-24', '28 - Honda City', '28 - Honda City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(48, '2026-02-27', '22 - BYD Seal', NULL, NULL, NULL, '2026-03-02 01:00:00', '2026-03-04 01:00:00', 'Airport', 'Airport', 'Jeremy', 'jeremyteo27@gmail.com', 1, NULL, 0.00),
(49, '2026-02-27', '22 - BYD Seal', '25 - VolkSwagon Beetle', NULL, NULL, '2026-03-02 01:00:00', '2026-03-04 01:00:00', 'Airport', 'Airport', 'Jeremy', 'jeremyteo27@gmail.com', 1, NULL, 0.00),
(50, '2026-03-02', NULL, NULL, NULL, NULL, '2026-03-04 18:00:00', '2026-03-05 18:00:00', 'start point', 'end point', 'Jeremy', 'jeremyteo27@gmail.com', 1, NULL, 0.00),
(51, '2026-03-02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(52, '2026-03-02', '29 - Honda Civic R', NULL, NULL, NULL, '2026-03-03 01:00:00', '2026-03-04 01:00:00', 'start', 'end', 'Jeremy', 'jeremyteo27@gmail.com', 1, NULL, 0.00),
(53, '2026-03-02', NULL, NULL, NULL, NULL, '2026-04-02 15:00:00', '2026-03-12 15:00:00', NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(54, '2026-03-03', NULL, '20 - Subaru Forester', NULL, NULL, '2026-03-04 09:00:00', '2026-03-06 09:00:00', 'dhoby', 'marina', 'valid name', 'jeremyteo27@gmail.com', 0, NULL, 0.00),
(55, '2026-03-03', '24 - BYD Song Pro', '22 - BYD Seal', '1 - Honda Odyssey', '1 - Honda Odyssey', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.00),
(56, '2026-03-10', '21 - Subaru XV', '25 - VolkSwagon Beetle', NULL, NULL, '2026-03-11 02:00:00', '2026-03-13 04:00:00', 'abc', 'def', 'Jeremy', 'jeremyteo27@gmail.com', 0, NULL, 0.00),
(57, '2026-03-10', '21 - Subaru XV', '25 - VolkSwagon Beetle', NULL, NULL, '2026-03-14 02:00:00', '2026-03-15 04:00:00', 'abc', 'def', 'Jeremy', 'jeremyteo27@gmail.com', 0, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `pend_booking`
--

CREATE TABLE `pend_booking` (
  `id` int(11) NOT NULL,
  `pend_booking_id` int(11) NOT NULL,
  `selected_details` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_time` datetime NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `returned_at` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pend_booking`
--

INSERT INTO `pend_booking` (`id`, `pend_booking_id`, `selected_details`, `created_at`, `updated_time`, `latitude`, `longitude`, `returned_at`, `status`) VALUES
(4, 16, '{\n    \"cars\": [\n        \"1 - Honda Odyssey\",\n        \"2 - Honda Odyssey\"\n    ],\n    \"start_date\": \"1970-01-01 01:00:00\",\n    \"end_date\": \"1970-01-01 01:00:00\",\n    \"pickup\": \"singapore\",\n    \"dropoff\": \"singapore\",\n    \"total_price\": 700\n}', '2026-01-27 10:29:16', '2026-01-27 18:38:45', 0.0000000, 0.0000000, NULL, 'active'),
(5, 18, '{\n    \"cars\": [],\n    \"start_date\": \"1970-01-01 01:00:00\",\n    \"end_date\": \"1970-01-01 01:00:00\",\n    \"pickup\": \"\",\n    \"dropoff\": \"\",\n    \"customer_name\": \"\",\n    \"customer_email\": \"\",\n    \"notify_status\": 0,\n    \"addons\": [],\n    \"total_price\": 0\n}', '2026-01-27 11:46:51', '2026-01-27 19:46:51', 0.0000000, 0.0000000, NULL, 'overdue'),
(6, 19, '{\n    \"cars\": [\n        \"1 - Honda Odyssey\"\n    ],\n    \"start_date\": \"2026-01-27 19:00:00\",\n    \"end_date\": \"2026-01-27 19:00:00\",\n    \"pickup\": \"singapore\",\n    \"dropoff\": \"singapore\",\n    \"customer_name\": \"sfd\",\n    \"customer_email\": \"fds@gmail.com\",\n    \"notify_status\": 1,\n    \"total_price\": 200\n}', '2026-01-27 11:53:30', '2026-01-27 19:53:30', 0.0000000, 0.0000000, NULL, 'inactive'),
(7, 20, '{\n    \"cars\": [\n        \"1 - Honda Odyssey\"\n    ],\n    \"start_date\": \"1970-01-01 01:00:00\",\n    \"end_date\": \"1970-01-01 01:00:00\",\n    \"pickup\": \"singapore\",\n    \"dropoff\": \"abc\",\n    \"customer_name\": \"jer\",\n    \"customer_email\": \"asd@gmail.com\",\n    \"notify_status\": 1,\n    \"total_price\": 200\n}', '2026-01-28 04:56:37', '2026-01-28 13:11:20', 1.3729792, 103.7500416, '2026-01-29 17:39:54', 'overdue'),
(8, 21, '{\n    \"cars\": [\n        \"1 - Honda Odyssey\"\n    ],\n    \"start_date\": \"1970-01-01 01:00:00\",\n    \"end_date\": \"1970-01-01 01:00:00\",\n    \"pickup\": \"singapore\",\n    \"dropoff\": \"malaysia\",\n    \"customer_name\": \"abc\",\n    \"customer_email\": \"abc@gmail.com\",\n    \"notify_status\": 1,\n    \"selected_addons\": [\n        \"phone_mount\"\n    ],\n    \"car_total\": 200,\n    \"addon_total\": 15,\n    \"grand_total\": 215\n}', '2026-01-28 05:52:54', '2026-01-28 14:02:13', 1.3729792, 103.7500416, '2026-01-29 17:40:21', 'pending'),
(9, 22, '{\n    \"cars\": [\n        \"1 - Honda Odyssey\"\n    ],\n    \"start_date\": \"2026-01-28 16:00:00\",\n    \"end_date\": \"2026-01-28 16:00:00\",\n    \"pickup\": \"singapore\",\n    \"dropoff\": \"456\",\n    \"customer_name\": \"test\",\n    \"customer_email\": \"admin@admin.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [],\n    \"car_total\": 200,\n    \"addon_total\": 0,\n    \"grand_total\": 200\n}', '2026-01-28 08:21:15', '2026-01-28 16:21:15', 1.3697024, 103.7500416, '2026-02-04 15:42:28', 'active'),
(11, 33, '{\n    \"cars\": [\n        \"4 - Honda Odyssey fake\",\n        \"2 - Honda Odyssey\"\n    ],\n    \"start_date\": \"1970-01-01 01:00:00\",\n    \"end_date\": \"1970-01-01 01:00:00\",\n    \"pickup\": \"south\",\n    \"dropoff\": \"west\",\n    \"customer_name\": \"a\",\n    \"customer_email\": \"b@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [],\n    \"car_total\": 700,\n    \"addon_total\": 0,\n    \"grand_total\": 700\n}', '2026-02-02 08:21:32', '2026-02-02 16:29:10', 0.0000000, 0.0000000, NULL, 'active'),
(12, 34, '{\r\n    \"cars\": [\r\n        \"5 - Honda Odyssey weird\",\r\n        \"1 - Honda Odyssey\"\r\n    ],\r\n    \"start_date\": \"2026-02-10 09:00:00\",\r\n    \"end_date\": \"2026-02-10 09:00:00\",\r\n    \"pickup\": \"Rental Shop\",\r\n    \"dropoff\": \"MRT station\",\r\n    \"customer_name\": \"Someone\",\r\n    \"customer_email\": \"jeremyteo27@gmail.com\",\r\n    \"notify_status\": 1,\r\n    \"selected_addons\": [],\r\n    \"car_total\": 700,\r\n    \"addon_total\": 0,\r\n    \"grand_total\": 700\r\n}', '2026-02-03 01:55:23', '2026-02-03 09:55:23', 1.3697024, 103.7500416, '2026-02-04 16:25:07', 'pending'),
(13, 35, '{\n    \"cars\": [\n        \"5 - Honda Odyssey weird\",\n        \"1 - Honda Odyssey\",\n        \"1 - Honda Odyssey\"\n    ],\n    \"start_date\": \"2026-02-04 17:00:00\",\n    \"end_date\": \"2026-02-04 21:00:00\",\n    \"pickup\": \"a\",\n    \"dropoff\": \"a\",\n    \"customer_name\": \"a\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [],\n    \"car_total\": 600,\n    \"addon_total\": 0,\n    \"grand_total\": 600\n}', '2026-02-04 08:50:00', '2026-02-04 19:46:27', 0.0000000, 0.0000000, NULL, 'inactive'),
(14, 36, '{\r\n    \"cars\": [\r\n        \"1 - Honda Odyssey\",\r\n        \"5 - Honda Odyssey weird\"\r\n    ],\r\n    \"start_date\": \"1970-01-01 01:00:00\",\r\n    \"end_date\": \"1970-01-01 01:00:00\",\r\n    \"pickup\": \"a\",\r\n    \"dropoff\": \"b\",\r\n    \"customer_name\": \"c\",\r\n    \"customer_email\": \"jeremyteo27@gmail.com\",\r\n    \"notify_status\": 0,\r\n    \"selected_addons\": [],\r\n    \"car_total\": 400,\r\n    \"addon_total\": 0,\r\n    \"grand_total\": 400\r\n}', '2026-02-04 09:02:51', '2026-02-04 17:02:51', 1.3795328, 103.7500416, '2026-02-10 16:37:38', 'returned'),
(15, 40, '{\n    \"cars\": [\n        \"2 - Honda Odyssey\"\n    ],\n    \"start_date\": \"2026-03-04 20:00:00\",\n    \"end_date\": \"2026-03-06 22:00:00\",\n    \"pickup\": \"test\",\n    \"dropoff\": \"test\",\n    \"customer_name\": \"tester\",\n    \"customer_email\": \"test@email.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [],\n    \"car_total\": 0,\n    \"addon_total\": 0,\n    \"grand_total\": 0\n}', '2026-02-05 09:36:17', '2026-03-02 15:07:54', 1.3729792, 103.7500416, '2026-02-05 19:39:56', 'pending'),
(16, 45, '{\n    \"cars\": [\n        \"20 - Subaru Forester\"\n    ],\n    \"start_date\": \"2026-02-25 01:00:00\",\n    \"end_date\": \"2026-02-26 04:00:00\",\n    \"pickup\": \"Dhoby Ghaut\",\n    \"dropoff\": \"RedHill\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [\n        \"insurance\"\n    ],\n    \"car_total\": 1,\n    \"addon_total\": 35,\n    \"grand_total\": 36\n}', '2026-02-24 02:21:51', '2026-02-24 10:24:55', 0.0000000, 0.0000000, NULL, 'pending'),
(17, 48, '{\n    \"cars\": [\n        \"22 - BYD Seal\"\n    ],\n    \"start_date\": \"2026-03-02 01:00:00\",\n    \"end_date\": \"2026-03-04 01:00:00\",\n    \"pickup\": \"Airport\",\n    \"dropoff\": \"Airport\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 1,\n    \"selected_addons\": [\n        \"prepaid\"\n    ],\n    \"car_total\": 715,\n    \"addon_total\": 10,\n    \"grand_total\": 725\n}', '2026-02-27 12:41:38', '2026-02-27 20:41:38', 1.3271040, 103.7860864, '2026-02-27 20:51:22', 'returned'),
(18, 49, '{\n    \"cars\": [\n        \"22 - BYD Seal\",\n        \"25 - VolkSwagon Beetle\"\n    ],\n    \"start_date\": \"2026-03-02 01:00:00\",\n    \"end_date\": \"2026-03-04 01:00:00\",\n    \"pickup\": \"Airport\",\n    \"dropoff\": \"Airport\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 1,\n    \"selected_addons\": [],\n    \"car_total\": 1632,\n    \"addon_total\": 0,\n    \"grand_total\": 1632\n}', '2026-02-27 12:52:04', '2026-02-27 20:52:04', 0.0000000, 0.0000000, NULL, 'pending'),
(19, 52, '{\n    \"cars\": [\n        \"29 - Honda Civic R\"\n    ],\n    \"start_date\": \"2026-03-03 01:00:00\",\n    \"end_date\": \"2026-03-04 01:00:00\",\n    \"pickup\": \"start\",\n    \"dropoff\": \"end\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 1,\n    \"selected_addons\": [],\n    \"car_total\": 282,\n    \"addon_total\": 0,\n    \"grand_total\": 282\n}', '2026-03-02 06:57:15', '2026-03-02 15:13:10', 1.3271040, 103.7860864, '2026-03-02 15:00:22', 'returned'),
(20, 54, '{\n    \"cars\": [\n        \"20 - Subaru Forester\"\n    ],\n    \"start_date\": \"2026-03-04 09:00:00\",\n    \"end_date\": \"2026-03-06 09:00:00\",\n    \"pickup\": \"dhoby\",\n    \"dropoff\": \"marina\",\n    \"customer_name\": \"valid name\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [\n        \"phone_mount\",\n        \"child_seat\",\n        \"insurance\",\n        \"prepaid\",\n        \"driver\"\n    ],\n    \"car_total\": 1234,\n    \"addon_total\": 105,\n    \"grand_total\": 1339\n}', '2026-03-03 01:59:21', '2026-03-03 10:00:12', 0.0000000, 0.0000000, NULL, 'pending'),
(21, 56, '{\n    \"cars\": [\n        \"21 - Subaru XV\",\n        \"25 - VolkSwagon Beetle\"\n    ],\n    \"start_date\": \"2026-03-11 02:00:00\",\n    \"end_date\": \"2026-03-13 04:00:00\",\n    \"pickup\": \"abc\",\n    \"dropoff\": \"def\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [\n        \"phone_mount\",\n        \"child_seat\",\n        \"insurance\"\n    ],\n    \"car_total\": 1417,\n    \"addon_total\": 70,\n    \"grand_total\": 1487\n}', '2026-03-10 05:31:50', '2026-03-10 13:31:50', 1.3118860, 103.8527963, '2026-03-10 13:34:31', 'returned'),
(22, 57, '{\n    \"cars\": [\n        \"21 - Subaru XV\",\n        \"25 - VolkSwagon Beetle\"\n    ],\n    \"start_date\": \"2026-03-14 02:00:00\",\n    \"end_date\": \"2026-03-15 04:00:00\",\n    \"pickup\": \"abc\",\n    \"dropoff\": \"def\",\n    \"customer_name\": \"Jeremy\",\n    \"customer_email\": \"jeremyteo27@gmail.com\",\n    \"notify_status\": 0,\n    \"selected_addons\": [],\n    \"car_total\": 1417,\n    \"addon_total\": 0,\n    \"grand_total\": 1417\n}', '2026-03-10 05:35:04', '2026-03-10 13:35:04', 0.0000000, 0.0000000, NULL, 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookinglist`
--
ALTER TABLE `bookinglist`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_booking1` (`booking1_id`),
  ADD KEY `fk_booking2` (`booking2_id`),
  ADD KEY `fk_booking3` (`booking3_id`),
  ADD KEY `fk_booking4` (`booking4_id`);

--
-- Indexes for table `pend_booking`
--
ALTER TABLE `pend_booking`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookinglist`
--
ALTER TABLE `bookinglist`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `pend_booking`
--
ALTER TABLE `pend_booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
