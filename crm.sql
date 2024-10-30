-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2024 at 04:10 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `conflicts`
--

CREATE TABLE `conflicts` (
  `conflict_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `conflict_reason` text DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conflicts`
--

INSERT INTO `conflicts` (`conflict_id`, `schedule_id`, `conflict_reason`, `resolved`) VALUES
(2, 10, 'B101 - it 111 (2024-10-21 13:00:00 - 2024-10-21 14:00:00) \r\n                          VS crim 1 (2024-10-21 13:00:00 - 2024-10-21 14:00:00)', 0),
(3, 10, 'B101 - it 111 (2024-10-21 13:00:00 - 2024-10-21 14:00:00) \r\n                          VS crim 1 (2024-10-21 13:00:00 - 2024-10-21 14:00:00)', 0);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_history`
--

CREATE TABLE `maintenance_history` (
  `id` int(11) NOT NULL,
  `maintenance_id` int(11) NOT NULL,
  `label` varchar(100) NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp(),
  `status` enum('EDITED','DELETED','COMPLETED') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_history`
--

INSERT INTO `maintenance_history` (`id`, `maintenance_id`, `label`, `room_code`, `changed_at`, `status`) VALUES
(1, 29, 'repairs', 'A101', '2024-10-26 20:43:42', 'EDITED'),
(2, 29, 'repairs', 'A101', '2024-10-26 20:43:47', 'DELETED'),
(3, 29, 'repairs', 'A101', '2024-10-26 20:51:39', 'DELETED'),
(4, 30, 'WIFI', 'A101', '2024-10-26 21:01:21', 'EDITED'),
(5, 32, 'REPAIRS', 'A101', '2024-10-26 21:28:10', 'COMPLETED'),
(6, 30, 'WIFI', 'A102', '2024-10-26 21:28:59', 'EDITED'),
(7, 36, 'UPGRADE', 'A101', '2024-10-28 06:42:06', 'DELETED'),
(8, 30, 'WIFI', 'A102', '2024-10-28 06:42:25', 'COMPLETED'),
(9, 31, 'DEMOLITIONS', 'A103', '2024-10-28 06:42:41', 'EDITED'),
(10, 31, 'DEMOLITIONS', 'A102', '2024-10-28 06:42:50', 'EDITED'),
(11, 37, 'LOG1', 'A101', '2024-10-30 07:06:48', 'COMPLETED'),
(12, 38, 'LOG2', 'A102', '2024-10-30 07:07:06', 'DELETED'),
(13, 39, 'LOG3', 'A103', '2024-10-30 07:07:23', 'DELETED');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` enum('room_usage','user_activity','maintenance_schedule') NOT NULL,
  `report_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_code` varchar(50) NOT NULL,
  `room_detail` varchar(50) NOT NULL,
  `room_status` enum('available','occupied','maintenance') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_code`, `room_detail`, `room_status`) VALUES
(1, 'A101', 'Television', 'available'),
(2, 'B101', 'Projector', 'available'),
(3, 'A102', 'Whiteboard', 'available'),
(4, 'C101', 'Whiteboard', 'available'),
(5, 'C102', 'Television', 'available'),
(6, 'C103', 'Television', 'available'),
(7, 'A103', 'Television', 'available'),
(8, 'C104', 'Computer', 'available'),
(10, 'C302', 'Computer', 'available'),
(11, 'A322', 'Computer', 'available'),
(12, 'A201', 'Projector', 'available'),
(13, 'B102', 'Projector', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `room_maintenance`
--

CREATE TABLE `room_maintenance` (
  `maintenance_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_status` enum('PENDING','DELETED','COMPLETED') NOT NULL,
  `label` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_maintenance`
--

INSERT INTO `room_maintenance` (`maintenance_id`, `room_id`, `maintenance_date`, `maintenance_status`, `label`) VALUES
(28, 1, '2024-10-23', 'COMPLETED', 'repairs'),
(29, 1, '2024-10-22', 'DELETED', 'repairs'),
(30, 3, '2024-10-22', 'COMPLETED', 'WIFI'),
(31, 3, '2024-10-22', 'PENDING', 'DEMOLITIONS'),
(32, 1, '2024-10-25', 'COMPLETED', 'REPAIRS'),
(33, 3, '2024-10-24', 'PENDING', 'REPAIRS'),
(34, 1, '2024-10-26', 'PENDING', 'DEMOLITION'),
(35, 8, '2024-10-28', 'PENDING', 'SHABU'),
(36, 1, '2024-10-21', 'DELETED', 'UPGRADE'),
(37, 1, '2024-10-30', 'COMPLETED', 'LOG1'),
(38, 3, '2024-10-30', 'DELETED', 'LOG2'),
(39, 7, '2024-10-30', 'DELETED', 'LOG3');

-- --------------------------------------------------------

--
-- Table structure for table `room_usage_log`
--

CREATE TABLE `room_usage_log` (
  `usage_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `usage_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `course_year` varchar(50) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `user_id`, `room_id`, `subject`, `course_year`, `start_time`, `end_time`) VALUES
(2, 4, 8, '0', '', '2024-10-02 10:00:00', '2024-10-02 12:00:00'),
(4, 4, 1, 'aszxc', '', '2024-10-03 09:00:00', '2024-10-03 11:00:00'),
(6, 4, 4, 'science', '', '2024-10-11 09:00:00', '2024-10-11 13:00:00'),
(9, 13, 3, 'math', '', '2024-10-11 16:00:00', '2024-10-11 17:00:00'),
(10, 4, 2, 'res', 'it 111', '2024-10-21 13:00:00', '2024-10-21 14:00:00'),
(12, 4, 1, 'science', 'crim 1', '2024-10-26 10:00:00', '2024-10-26 11:00:00'),
(13, 4, 3, 'was', 'it 111', '2024-10-27 12:00:00', '2024-10-27 13:00:00'),
(14, 4, 3, 'science', 'crim 1', '2024-10-27 14:00:00', '2024-10-27 16:00:00'),
(15, 4, 3, 'English', 'grade 10', '2024-10-28 06:00:00', '2024-10-28 07:00:00'),
(16, 4, 8, 'Computer', 'grade 12', '2024-10-28 09:00:00', '2024-10-28 10:00:00'),
(17, 4, 8, 'Computer', 'grade 12', '2024-11-04 09:00:00', '2024-11-04 10:00:00'),
(18, 4, 8, 'Computer', 'grade 12', '2024-11-11 09:00:00', '2024-11-11 10:00:00'),
(19, 4, 8, 'Computer', 'grade 12', '2024-11-18 09:00:00', '2024-11-18 10:00:00'),
(20, 4, 8, 'Computer', 'grade 12', '2024-11-25 09:00:00', '2024-11-25 10:00:00'),
(21, 4, 11, 'Summon Satan', 'Profs', '2024-10-28 10:00:00', '2024-10-28 11:00:00'),
(23, 4, 2, 'asdad123', '2114', '2024-10-28 13:00:00', '2024-10-28 14:00:00'),
(24, 4, 4, 'mnbmbn', 'zcx', '2024-10-28 12:00:00', '2024-10-28 13:00:00'),
(25, 4, 4, 'test', 'zcx', '2024-11-04 12:00:00', '2024-11-04 13:00:00'),
(27, 4, 4, 'zxzxcz', 'zcx', '2024-11-18 12:00:00', '2024-11-18 13:00:00'),
(28, 4, 4, 'zxzxcz', 'zcx', '2024-11-25 12:00:00', '2024-11-25 13:00:00'),
(29, 4, 1, 'science', 'crim 1', '2024-10-29 07:00:00', '2024-10-29 08:00:00'),
(30, 4, 1, 'science', 'crim 1', '2024-11-05 07:00:00', '2024-11-05 08:00:00'),
(31, 4, 1, 'science', 'crim 1', '2024-11-12 07:00:00', '2024-11-12 08:00:00'),
(32, 4, 1, 'science', 'crim 1', '2024-11-19 07:00:00', '2024-11-19 08:00:00'),
(33, 4, 1, 'science', 'crim 1', '2024-11-26 07:00:00', '2024-11-26 08:00:00'),
(34, 4, 3, 'math', 'zcx', '2024-10-29 09:00:00', '2024-10-29 10:00:00'),
(35, 4, 3, 'math', 'zcx', '2024-11-05 09:00:00', '2024-11-05 10:00:00'),
(36, 4, 4, 'once', 'cl', '2024-10-29 10:00:00', '2024-10-29 11:00:00'),
(37, 4, 7, 'swal', 'adwaawda', '2024-10-29 12:00:00', '2024-10-29 13:00:00'),
(38, 4, 3, 'repeat', 'week', '2024-10-29 14:00:00', '2024-10-29 15:00:00'),
(39, 4, 3, 'repeat', 'week', '2024-11-05 14:00:00', '2024-11-05 15:00:00'),
(40, 4, 3, 'repeat', 'week', '2024-11-12 14:00:00', '2024-11-12 15:00:00'),
(41, 4, 3, 'repeat', 'week', '2024-11-19 14:00:00', '2024-11-19 15:00:00'),
(42, 4, 3, 'repeat', 'week', '2024-11-26 14:00:00', '2024-11-26 15:00:00'),
(43, 4, 1, 'now', 'now', '2024-10-29 18:00:00', '2024-10-29 19:00:00'),
(44, 4, 8, 'usage', 'log', '2024-10-30 07:00:00', '2024-10-30 08:00:00'),
(45, 13, 8, 'more use', 'log', '2024-10-30 08:00:00', '2024-10-30 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('male','female','other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `first_name`, `middle_name`, `last_name`, `gender`) VALUES
(2, 'johndoe', '$2y$10$MBGE.XiA0LnOPL6yjGUaVeODEIddy1kI0KWqTYtCuFjFbxHCuv/ti', 'admin', 'John', 'D', 'Doe', 'male'),
(4, 'jakedoe', '$2y$10$ratAPPAxUiNqwwz6wKStouPF5f4W./qogbeYA3I2mRKx7EosREytu', 'teacher', 'Jake', 'D', 'Doe', 'male'),
(5, 'jeandoe', '$2y$10$l4J1CspnV9oqob4O9LKtbOQMBt2d6kHHEXW0G.7oRAlPhjY/G6SQK', 'student', 'Jean', 'D', 'Doe', 'female'),
(8, 'janedoe', '$2y$10$wm9i8K6F/UxodoI5LmbCvuOg/EyPufdYr3ONQa5LMBh7EUdBc3mRa', 'student', 'Jane', 'D', 'Doe', 'female'),
(13, 'jackdoe', '$2y$10$nrGcZT0Rrqcx9zztv3bPuOKPu5Rv4ZXRu9Jz9RxrEi3DzpLgZgcvG', 'teacher', 'Jack', 'D', 'Doe', 'male'),
(21, 'oli', '$2y$10$TFgSHHMhC/kX3562VY99JuYTrxTN5w4WUm3W5nLCtQhuutizq10IK', 'student', 'oli', 'oli', 'oli', 'male');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_log`
--

INSERT INTO `user_activity_log` (`log_id`, `user_id`, `login_time`) VALUES
(2, 4, '2024-10-02 11:10:02'),
(3, 5, '2024-10-02 11:15:11'),
(5, 2, '2024-10-02 14:16:07'),
(6, 4, '2024-10-02 14:16:33'),
(8, 4, '2024-10-03 13:45:12'),
(9, 4, '2024-10-03 13:47:14'),
(11, 2, '2024-10-03 13:48:00'),
(12, 2, '2024-10-03 13:48:11'),
(13, 4, '2024-10-03 13:49:16'),
(14, 5, '2024-10-03 13:50:36'),
(16, 4, '2024-10-03 13:52:35'),
(17, 2, '2024-10-03 13:52:55'),
(18, 2, '2024-10-03 13:53:26'),
(19, 4, '2024-10-03 14:04:46'),
(20, 8, '2024-10-10 12:20:44'),
(21, 4, '2024-10-10 12:28:24'),
(22, 5, '2024-10-10 12:28:41'),
(23, 4, '2024-10-10 12:35:48'),
(24, 2, '2024-10-10 12:36:12'),
(25, 4, '2024-10-10 12:36:44'),
(26, 8, '2024-10-11 09:08:21'),
(27, 4, '2024-10-11 09:25:44'),
(29, 4, '2024-10-11 09:46:22'),
(30, 5, '2024-10-11 09:48:16'),
(31, 4, '2024-10-11 09:49:19'),
(32, 4, '2024-10-11 11:52:57'),
(33, 4, '2024-10-11 12:03:56'),
(35, 13, '2024-10-11 12:21:55'),
(38, 4, '2024-10-11 12:47:01'),
(39, 5, '2024-10-11 12:47:42'),
(41, 4, '2024-10-14 11:14:19'),
(42, 5, '2024-10-14 11:16:23'),
(43, 4, '2024-10-14 11:17:37'),
(44, 2, '2024-10-16 15:27:24'),
(45, 4, '2024-10-16 15:28:15'),
(47, 4, '2024-10-20 13:25:03'),
(49, 4, '2024-10-20 15:20:58'),
(52, 4, '2024-10-21 10:52:40'),
(53, 2, '2024-10-21 10:55:51'),
(54, 4, '2024-10-21 11:01:58'),
(55, 4, '2024-10-21 11:26:44'),
(56, 2, '2024-10-21 11:26:52'),
(57, 4, '2024-10-21 14:49:22'),
(58, 13, '2024-10-21 14:49:41'),
(59, 2, '2024-10-21 15:11:21'),
(60, 13, '2024-10-21 15:25:56'),
(61, 2, '2024-10-21 15:32:48'),
(62, 13, '2024-10-21 15:34:35'),
(63, 2, '2024-10-21 15:49:12'),
(64, 2, '2024-10-23 03:22:10'),
(65, 2, '2024-10-23 07:39:14'),
(66, 2, '2024-10-23 07:57:11'),
(67, 4, '2024-10-23 08:42:17'),
(68, 2, '2024-10-23 09:54:15'),
(69, 5, '2024-10-23 10:16:40'),
(70, 4, '2024-10-23 10:20:09'),
(71, 2, '2024-10-23 10:21:29'),
(72, 4, '2024-10-23 10:47:01'),
(73, 2, '2024-10-23 10:48:01'),
(74, 4, '2024-10-23 11:35:20'),
(75, 2, '2024-10-23 11:40:06'),
(76, 2, '2024-10-23 11:40:51'),
(77, 2, '2024-10-23 13:44:27'),
(78, 2, '2024-10-23 13:49:28'),
(79, 2, '2024-10-26 08:36:40'),
(80, 2, '2024-10-26 09:14:08'),
(81, 4, '2024-10-26 13:07:43'),
(82, 2, '2024-10-26 13:08:05'),
(83, 4, '2024-10-26 13:30:46'),
(84, 13, '2024-10-26 13:31:24'),
(85, 2, '2024-10-26 13:31:57'),
(86, 2, '2024-10-26 13:52:30'),
(87, 2, '2024-10-26 13:54:44'),
(88, 2, '2024-10-27 11:58:31'),
(89, 4, '2024-10-27 12:56:09'),
(90, 2, '2024-10-27 15:30:58'),
(91, 8, '2024-10-27 16:19:07'),
(92, 5, '2024-10-27 16:25:36'),
(93, 4, '2024-10-27 16:44:18'),
(94, 2, '2024-10-27 16:44:41'),
(95, 8, '2024-10-27 16:46:28'),
(96, 4, '2024-10-27 17:10:28'),
(97, 13, '2024-10-27 17:37:22'),
(98, 2, '2024-10-27 17:51:08'),
(99, 4, '2024-10-27 18:02:06'),
(100, 5, '2024-10-27 18:03:05'),
(101, 2, '2024-10-27 18:16:51'),
(102, 2, '2024-10-27 19:01:48'),
(103, 4, '2024-10-27 23:44:33'),
(104, 4, '2024-10-27 23:47:53'),
(105, 2, '2024-10-28 12:44:02'),
(106, 2, '2024-10-28 14:22:37'),
(107, 4, '2024-10-28 15:01:15'),
(108, 5, '2024-10-28 15:01:30'),
(109, 2, '2024-10-28 15:01:42'),
(110, 2, '2024-10-29 08:04:09'),
(111, 4, '2024-10-29 08:52:34'),
(112, 2, '2024-10-29 10:31:40'),
(113, 4, '2024-10-29 10:33:14'),
(114, 2, '2024-10-29 10:34:14'),
(115, 4, '2024-10-29 10:36:03'),
(116, 2, '2024-10-29 10:54:03'),
(117, 4, '2024-10-29 10:54:20'),
(118, 5, '2024-10-29 10:54:55'),
(119, 2, '2024-10-29 11:09:25'),
(120, 5, '2024-10-29 12:02:48'),
(121, 2, '2024-10-29 12:03:48'),
(122, 2, '2024-10-29 20:29:42'),
(123, 2, '2024-10-29 21:42:01'),
(124, 2, '2024-10-29 21:44:52'),
(125, 4, '2024-10-29 22:31:04'),
(126, 2, '2024-10-29 22:31:40'),
(127, 13, '2024-10-29 22:32:15'),
(128, 2, '2024-10-29 22:37:39'),
(129, 5, '2024-10-29 23:25:11'),
(130, 2, '2024-10-29 23:49:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conflicts`
--
ALTER TABLE `conflicts`
  ADD PRIMARY KEY (`conflict_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_id` (`maintenance_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_code` (`room_code`);

--
-- Indexes for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  ADD PRIMARY KEY (`maintenance_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `room_usage_log`
--
ALTER TABLE `room_usage_log`
  ADD PRIMARY KEY (`usage_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_activity_log_ibfk_1` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conflicts`
--
ALTER TABLE `conflicts`
  MODIFY `conflict_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  MODIFY `maintenance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `room_usage_log`
--
ALTER TABLE `room_usage_log`
  MODIFY `usage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `conflicts`
--
ALTER TABLE `conflicts`
  ADD CONSTRAINT `conflicts_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `maintenance_history`
--
ALTER TABLE `maintenance_history`
  ADD CONSTRAINT `maintenance_history_ibfk_1` FOREIGN KEY (`maintenance_id`) REFERENCES `room_maintenance` (`maintenance_id`);

--
-- Constraints for table `room_maintenance`
--
ALTER TABLE `room_maintenance`
  ADD CONSTRAINT `room_maintenance_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `room_usage_log`
--
ALTER TABLE `room_usage_log`
  ADD CONSTRAINT `room_usage_log_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `room_usage_log_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
