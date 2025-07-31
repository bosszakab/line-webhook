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
-- Database: `webhook_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `message_type` varchar(50) NOT NULL COMMENT 'ประเภทข้อความ เช่น text, image, sticker',
  `message_text` text DEFAULT NULL COMMENT 'เนื้อหาข้อความ (ถ้ามี)',
  `timestamp` bigint(20) DEFAULT NULL COMMENT 'Timestamp จาก Webhook (ถ้ามี)',
  `reply_token` varchar(255) DEFAULT NULL COMMENT 'Reply Token สำหรับตอบกลับ (เฉพาะ LINE)',
  `webhook_event_id` varchar(255) DEFAULT NULL COMMENT 'Event ID จาก Webhook (ถ้ามี)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อความทั้งหมดที่ได้รับจาก Webhook';

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `platform`, `user_id`, `message_type`, `message_text`, `timestamp`, `reply_token`, `webhook_event_id`, `created_at`) VALUES
(1, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'ยภยพยะ', 1753887347721, 'ae0c4da5aba84a128a35d29517412c22', '01K1DXA5G2NV9EYMK4SH7Y7P8Q', '2025-07-30 14:55:44'),
(2, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', '่่่ะ่ะ่ั', 1753887356424, 'bf763d5a19254c5e9d2ef4b034ed11ab', '01K1DXAE03ES4NR0XX0TMN1WJQ', '2025-07-30 14:55:53'),
(3, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'usuhehdid', 1753887381115, '4dbc5f5620da45ea93cd8e2fac02fddd', '01K1DXB5SSM1VSYBA4G66D41GD', '2025-07-30 14:56:18'),
(4, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'สวัสดีครับ', 1753887529836, '860c4479d8ed49f5966009d72b462b10', '01K1DXFQ92KDFA8H0FSY5ZZB8X', '2025-07-30 14:58:47'),
(5, 'line', 'Uc1782d782435f0ce556ed12c1b159e96', 'text', 'จุ๊กกรู๊!!!!!!', 1753888835643, 'd0991e5995544170baab4ac374178aac', '01K1DYQJFCN1DZ6DK0BFRADCKZ', '2025-07-30 15:20:32'),
(6, 'line', 'Uc1782d782435f0ce556ed12c1b159e96', 'text', 'Gg', 1753888938067, '3e37664a77904a9e8aa0276fe79513d6', '01K1DYTP8QQK3SKVB69DAGK265', '2025-07-30 15:22:15'),
(7, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'ยยำนดสด', 1753889218082, '8758b708c0d4475aa34493be4a40c3e7', '01K1DZ380VK573EZYRJ8MCDGZK', '2025-07-30 15:26:55'),
(8, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'ึึรรร', 1753889241824, '0d540e7250ca4a7bbd99d3dcdf9950f3', '01K1DZ3Z6ZM0S1ZPA3ZBT2ZYAF', '2025-07-30 15:27:19'),
(9, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'นสกนกาดาเ', 1753898281233, '0de61c4a79b3468e8e541cb306569c0e', '01K1E7QTCRX7K331RZZSVY92BG', '2025-07-30 17:57:59'),
(10, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'นำนีดเ่', 1753905031641, '9bf1cfcae86e412ba5fac516d6f4bebf', '01K1EE5TYMPD9XBRCS256DRJ7F', '2025-07-30 19:50:29'),
(11, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'ถ้าั่้ี', 1753905046625, 'dc5943ee27ad4660a58a75d42e5eb5b4', '01K1EE69C1ZYS1H1JTG3GY9H56', '2025-07-30 19:50:44'),
(12, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', 'text', 'รำราพ่เ่เ', 1753908096948, '252432fa480244bcaa40c4c8e3d21244', '01K1EH3CDCRN4HHPS49XYFJ0TV', '2025-07-30 20:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `platform` varchar(50) NOT NULL COMMENT 'แพลตฟอร์ม เช่น line, facebook, custom',
  `user_id` varchar(255) NOT NULL COMMENT 'ID ของผู้ใช้จากแพลตฟอร์มนั้นๆ',
  `first_seen` datetime NOT NULL COMMENT 'วันและเวลาที่พบผู้ใช้ครั้งแรก',
  `last_seen` datetime NOT NULL COMMENT 'วันและเวลาล่าสุดที่มีการใช้งาน',
  `message_count` int(11) DEFAULT 0 COMMENT 'จำนวนข้อความทั้งหมดจากผู้ใช้',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'เวลาที่สร้าง record นี้'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางเก็บข้อมูลผู้ใช้งานจากแพลตฟอร์มต่างๆ';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `platform`, `user_id`, `first_seen`, `last_seen`, `message_count`, `created_at`) VALUES
(1, 'line', 'Ue5400ed980db9dd4954282695dcbe8c5', '2025-07-30 21:55:44', '2025-07-31 03:41:35', 10, '2025-07-30 14:55:44'),
(5, 'line', 'Uc1782d782435f0ce556ed12c1b159e96', '2025-07-30 22:20:32', '2025-07-30 22:22:15', 2, '2025-07-30 15:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` int(11) NOT NULL,
  `raw_data` text DEFAULT NULL COMMENT 'ข้อมูล JSON ดิบที่ได้รับ',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP Address ของผู้ส่ง',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent ของผู้ส่ง',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางสำหรับเก็บ Log ข้อมูลดิบจาก Webhook';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_platform` (`platform`,`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_platform` (`platform`,`user_id`(140)),
  ADD KEY `idx_platform` (`platform`),
  ADD KEY `idx_last_seen` (`last_seen`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
