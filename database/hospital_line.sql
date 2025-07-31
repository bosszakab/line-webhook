-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 30, 2025 at 10:45 PM
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
-- Database: `hospital_line`
--

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `patient_hn` varchar(50) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `line_user_id` varchar(255) NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_hn`, `patient_name`, `line_user_id`, `registered_at`) VALUES
(5, '250723-03', 'Mr.boss', 'Ue5400ed980db9dd4954282695dcbe8c5', '2025-07-23 15:12:23'),
(7, '250723-05', 'ไอลดาss', 'Uc1782d782435f0ce556ed12c1b159e96', '2025-07-30 18:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_notifications`
--

CREATE TABLE `scheduled_notifications` (
  `id` int(11) NOT NULL,
  `patient_user_id` varchar(255) NOT NULL COMMENT 'LINE User ID ของคนไข้',
  `message` text NOT NULL COMMENT 'ข้อความที่จะส่ง',
  `send_at` datetime NOT NULL COMMENT 'เวลาที่ตั้งค่าให้ส่ง',
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending' COMMENT 'สถานะการส่ง',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `scheduled_notifications`
--

INSERT INTO `scheduled_notifications` (`id`, `patient_user_id`, `message`, `send_at`, `status`, `error_message`, `created_at`) VALUES
(1, 'bosszakab2017', 'dasd', '2025-07-23 12:34:00', 'failed', NULL, '2025-07-23 05:37:36'),
(2, 'bosszakab2017', 'ไกฟหกหก', '2025-07-23 12:38:00', 'failed', NULL, '2025-07-23 05:37:55'),
(3, 'bosszakab2017', 'กหฟกห', '2025-07-23 12:38:00', 'failed', NULL, '2025-07-23 05:38:09'),
(4, 'bosszakab2017', 'asdasd', '2025-07-23 12:46:00', 'failed', NULL, '2025-07-23 05:44:30'),
(5, 'bosszakab2017', 'dasdawd', '2025-07-23 12:49:00', 'failed', NULL, '2025-07-23 05:48:32'),
(6, 'fatinkitty7', 'test 123', '2025-07-23 13:30:00', 'failed', NULL, '2025-07-23 06:29:43'),
(7, 'fatinkitty7', 'test test', '2025-07-23 13:32:00', 'failed', NULL, '2025-07-23 06:31:09'),
(8, 'fatinkitty7', 'teat12', '2025-07-23 13:33:00', 'failed', NULL, '2025-07-23 06:32:26'),
(9, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dsadwsd', '2025-07-23 14:03:00', 'failed', NULL, '2025-07-23 07:02:20'),
(10, 'Ufbac5968003a84a73e569c8fde01f8c5', 'สวัสดีฮะ', '2025-07-23 14:54:00', 'failed', NULL, '2025-07-23 07:54:02'),
(11, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dasdwa', '2025-07-23 16:03:00', 'failed', NULL, '2025-07-23 09:02:33'),
(12, 'Ufbac5968003a84a73e569c8fde01f8c5', 'wdadasdwad', '2025-07-23 16:04:00', 'failed', NULL, '2025-07-23 09:03:05'),
(13, 'Ufbac5968003a84a73e569c8fde01f8c5', 'wdadasdwad', '2025-07-23 16:04:00', 'failed', NULL, '2025-07-23 09:03:11'),
(14, 'Ufbac5968003a84a73e569c8fde01f8c5', 'wdadasdwad', '2025-07-23 16:04:00', 'failed', NULL, '2025-07-23 09:03:24'),
(15, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dasda', '2025-07-23 16:05:00', 'failed', NULL, '2025-07-23 09:03:37'),
(16, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dasda', '2025-07-23 16:05:00', 'failed', NULL, '2025-07-23 09:03:42'),
(17, 'Ufbac5968003a84a73e569c8fde01f8c5', 'wasadasdsad', '2025-07-23 16:20:00', 'failed', NULL, '2025-07-23 09:18:07'),
(18, 'bosszakab2017', 'asdsad', '2025-07-23 16:18:00', 'failed', NULL, '2025-07-23 09:19:00'),
(19, 'Ufbac5968003a84a73e569c8fde01f8c5', 'wadasdawd', '2025-07-23 16:19:00', 'failed', NULL, '2025-07-23 09:19:19'),
(20, 'Ufbac5968003a84a73e569c8fde01f8c5', 'afsfdfdsdf', '2025-07-23 16:28:00', 'failed', NULL, '2025-07-23 09:28:07'),
(21, 'Ufbac5968003a84a73e569c8fde01f8c5', 'ฟดหดหด', '2025-07-23 17:09:00', 'failed', NULL, '2025-07-23 10:09:47'),
(22, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dassawd', '2025-07-23 17:26:00', 'failed', NULL, '2025-07-23 10:26:45'),
(23, 'U31b524243b604fb494301d63812f4879', 'Helloworld', '2025-07-23 17:47:00', 'failed', NULL, '2025-07-23 10:47:33'),
(24, 'Ufbac5968003a84a73e569c8fde01f8c5', 'Helloworld', '2025-07-23 20:34:00', 'failed', NULL, '2025-07-23 13:34:30'),
(25, 'Ufbac5968003a84a73e569c8fde01f8c5', 'sdaafsadfdsf', '2025-07-23 20:41:00', 'failed', NULL, '2025-07-23 13:41:08'),
(26, 'Ufbac5968003a84a73e569c8fde01f8c5', 'dadawdas', '2025-07-23 20:46:00', 'failed', NULL, '2025-07-23 13:46:47'),
(27, 'Ufbac5968003a84a73e569c8fde01f8c5', 'rtgrtgf', '2025-07-23 01:48:00', 'failed', NULL, '2025-07-23 13:48:36'),
(28, 'Ufbac5968003a84a73e569c8fde01f8c5', 'weqwdas', '2025-07-23 22:04:00', 'failed', 'User has not added the bot as friend', '2025-07-23 15:04:45'),
(29, 'Ue5400ed980db9dd4954282695dcbe8c5', 'dasawd', '2025-07-23 22:12:00', 'sent', '', '2025-07-23 15:12:28'),
(30, 'Ue5400ed980db9dd4954282695dcbe8c5', 'wsdasdasd', '2025-07-23 22:13:00', 'sent', '', '2025-07-23 15:12:54'),
(31, 'Ue5400ed980db9dd4954282695dcbe8c5', 'aesfdsfsdf', '2025-07-23 22:33:00', 'sent', '', '2025-07-23 15:31:47'),
(32, 'Ue5400ed980db9dd4954282695dcbe8c5', 'jqiowjdljsadj', '2025-07-23 22:36:00', 'sent', '', '2025-07-23 15:34:31'),
(33, 'U26823e066007d1d71da7795f041dc1f6', 'กไฟหกฟหกไ', '2025-07-24 00:57:00', 'failed', 'User has not added the bot as friend', '2025-07-23 17:57:56'),
(34, 'Uc1782d782435f0ce556ed12c1b159e96', 'สวัสดี', '2025-07-31 01:45:00', 'sent', '', '2025-07-30 18:45:38'),
(35, 'Uc1782d782435f0ce556ed12c1b159e96', 'สวัสดี', '2025-07-31 01:45:00', 'sent', '', '2025-07-30 19:23:48'),
(36, 'Ue5400ed980db9dd4954282695dcbe8c5', 'กฟกหฟ', '2025-07-31 03:14:00', 'sent', '', '2025-07-30 20:14:02'),
(37, 'Ue5400ed980db9dd4954282695dcbe8c5', 'กฟหกฟหก', '2025-07-31 03:23:00', 'sent', '', '2025-07-30 20:23:55'),
(38, 'Ue5400ed980db9dd4954282695dcbe8c5', 'กฟหกหฟก', '2025-07-31 03:25:00', 'sent', '', '2025-07-30 20:24:09'),
(39, 'Ue5400ed980db9dd4954282695dcbe8c5', 'ปแหกดหก', '2025-07-31 03:27:00', 'sent', '', '2025-07-30 20:25:48'),
(40, 'Uc1782d782435f0ce556ed12c1b159e96', 'ฟกหกหฟกไ', '2025-07-31 03:27:00', 'sent', '', '2025-07-30 20:25:55'),
(41, 'Ue5400ed980db9dd4954282695dcbe8c5', 'หกฟไกฟหก', '2025-07-31 03:33:00', 'sent', '', '2025-07-30 20:33:52'),
(42, 'Ue5400ed980db9dd4954282695dcbe8c5', 'กไฟหกห', '2025-07-31 03:40:00', 'sent', '', '2025-07-30 20:40:49'),
(43, 'Ue5400ed980db9dd4954282695dcbe8c5', 'ไกฟหกหก', '2025-07-31 03:42:00', 'sent', '', '2025-07-30 20:41:00'),
(44, 'Ue5400ed980db9dd4954282695dcbe8c5', 'ไำกฟหก', '2025-07-31 03:45:00', 'pending', NULL, '2025-07-30 20:42:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `line_user_id` (`line_user_id`);

--
-- Indexes for table `scheduled_notifications`
--
ALTER TABLE `scheduled_notifications`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `scheduled_notifications`
--
ALTER TABLE `scheduled_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
