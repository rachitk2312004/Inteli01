-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 27, 2025 at 04:18 PM
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
-- Database: `QUIZ`
--

-- --------------------------------------------------------

--
-- Table structure for table `prize_distribution`
--

CREATE TABLE `prize_distribution` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `rank_start` int(11) NOT NULL,
  `rank_end` int(11) NOT NULL,
  `prize_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prize_distribution`
--

INSERT INTO `prize_distribution` (`id`, `quiz_id`, `rank_start`, `rank_end`, `prize_amount`) VALUES
(1, 1, 1, 1, 147.00),
(2, 1, 2, 2, 63.00),
(5, 2, 1, 1, 147.00),
(6, 2, 2, 2, 63.00),
(7, 3, 1, 1, 2539.00),
(8, 3, 2, 2, 1105.00),
(9, 3, 3, 3, 680.00),
(10, 3, 4, 4, 481.00),
(11, 3, 5, 5, 368.00),
(12, 3, 6, 6, 296.00),
(13, 3, 7, 7, 246.00),
(14, 3, 8, 8, 210.00),
(15, 3, 9, 9, 182.00),
(16, 3, 10, 10, 161.00),
(17, 3, 11, 11, 143.00),
(18, 3, 12, 12, 129.00),
(19, 3, 13, 13, 117.00),
(20, 3, 14, 14, 107.00),
(21, 3, 15, 15, 99.00),
(22, 3, 16, 16, 92.00),
(23, 3, 17, 17, 85.00),
(24, 3, 18, 18, 80.00),
(25, 3, 19, 19, 75.00),
(26, 3, 20, 20, 70.00),
(27, 3, 21, 21, 66.00),
(28, 3, 22, 22, 63.00),
(29, 3, 23, 23, 59.00),
(30, 3, 24, 24, 57.00),
(31, 3, 25, 25, 54.00),
(32, 3, 26, 26, 50.00),
(33, 3, 27, 27, 48.00),
(34, 3, 28, 28, 46.00),
(35, 3, 29, 29, 44.00),
(36, 3, 30, 30, 42.00),
(37, 3, 31, 31, 41.00),
(38, 3, 32, 32, 39.00),
(39, 3, 33, 33, 38.00),
(40, 3, 34, 34, 36.00),
(41, 3, 35, 35, 35.00),
(42, 3, 36, 36, 34.00),
(43, 3, 37, 37, 33.00),
(44, 3, 38, 38, 32.00),
(45, 3, 39, 39, 31.00),
(46, 3, 40, 40, 30.00),
(47, 3, 41, 41, 29.00),
(48, 3, 42, 42, 28.00),
(49, 3, 43, 44, 27.00),
(50, 3, 45, 45, 26.00),
(51, 3, 46, 47, 25.00),
(52, 3, 48, 48, 24.00),
(53, 3, 49, 50, 23.00),
(54, 4, 1, 1, 165.00),
(55, 4, 2, 2, 72.00),
(56, 4, 3, 3, 45.00),
(57, 4, 4, 4, 31.00),
(58, 4, 5, 5, 23.00),
(59, 5, 1, 1, 391.00),
(60, 5, 2, 2, 170.00),
(61, 5, 3, 3, 105.00),
(62, 5, 4, 4, 73.00),
(63, 5, 5, 5, 56.00),
(64, 5, 6, 6, 45.00),
(65, 6, 1, 1, 293.00),
(66, 6, 2, 2, 127.00),
(67, 7, 1, 1, 126.00),
(68, 8, 1, 1, 439.00),
(69, 8, 2, 2, 191.00);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `entry_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_spots` int(11) NOT NULL DEFAULT 0,
  `prize_pool` decimal(10,2) DEFAULT 0.00,
  `commission_percent` decimal(5,2) NOT NULL DEFAULT 16.00,
  `is_active` tinyint(1) DEFAULT 1,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `winners` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `entry_fee`, `max_spots`, `prize_pool`, `commission_percent`, `is_active`, `start_time`, `end_time`, `created_at`, `winners`) VALUES
(1, 'Quiz-1', 50.00, 5, 210.00, 16.00, 0, '2025-06-26 20:30:00', '2025-06-26 20:35:00', '2025-06-25 20:15:26', 2),
(2, 'Quiz-1', 50.00, 5, 210.00, 16.00, 0, '2025-06-26 20:30:00', '2025-06-26 20:35:00', '2025-06-26 14:56:59', 2),
(3, 'Quiz-2', 100.00, 100, 8400.00, 16.00, 0, '2025-06-27 16:50:00', '2025-06-27 16:55:00', '2025-06-27 11:17:23', 50),
(4, 'Quiz-3', 40.00, 10, 336.00, 16.00, 0, '2025-06-27 17:15:00', '2025-06-27 17:20:00', '2025-06-27 11:41:05', 5),
(5, 'Quiz-4', 50.00, 20, 840.00, 16.00, 0, '2025-06-27 17:30:00', '2025-06-27 17:35:00', '2025-06-27 11:55:41', 6),
(6, 'Quiz-5', 100.00, 5, 420.00, 16.00, 1, '2025-06-27 17:45:00', '2025-06-27 17:50:00', '2025-06-27 12:12:32', 2),
(7, 'Quiz-6', 50.00, 3, 126.00, 16.00, 1, '2025-06-27 18:00:00', '2025-06-27 18:05:00', '2025-06-27 12:24:41', 1),
(8, 'Quiz Main', 150.00, 5, 630.00, 16.00, 1, '2025-06-27 18:25:00', '2025-06-27 18:30:00', '2025-06-27 12:45:57', 2);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `id` int(11) NOT NULL,
  `entry_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `selected_option` char(1) DEFAULT NULL,
  `time_taken_seconds` int(11) DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`id`, `entry_id`, `question_id`, `selected_option`, `time_taken_seconds`, `is_correct`) VALUES
(1, 5, 5, 'a', NULL, 1),
(2, 6, 6, 'a', NULL, 1),
(3, 6, 7, 'b', NULL, 1),
(4, 7, 8, 'a', NULL, 1),
(5, 7, 9, 'a', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_entries`
--

CREATE TABLE `quiz_entries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled','submitted') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_entries`
--

INSERT INTO `quiz_entries` (`id`, `user_id`, `quiz_id`, `joined_at`, `status`) VALUES
(1, 1, 1, '2025-06-26 14:58:08', 'active'),
(2, 1, 3, '2025-06-27 11:18:17', 'active'),
(3, 1, 4, '2025-06-27 11:42:50', 'active'),
(4, 1, 5, '2025-06-27 11:56:17', 'submitted'),
(5, 1, 6, '2025-06-27 12:13:09', 'submitted'),
(6, 1, 7, '2025-06-27 12:26:20', 'submitted'),
(7, 1, 8, '2025-06-27 12:53:53', 'submitted');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_option` char(1) DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `difficulty`) VALUES
(1, 1, 'kya apke toothpaste mai namak hai?', 'ha', 'nhi', '', '', 'a', 'hard'),
(2, 3, 'kya apke tooth paste m namak h?', 'ha', 'na', '', '', 'a', 'hard'),
(3, 4, 'is your name rachit..?', 'ha', 'na', '', '', 'a', 'hard'),
(4, 5, 'ice is solid.', 'true', 'false', '', '', 'a', 'easy'),
(5, 6, 'Answer in true or false:\\r\\nIce is solid?', 'True', 'False', '', '', 'a', 'easy'),
(6, 7, 'Is ice solid?', 'True', 'False', '', '', 'a', 'easy'),
(7, 7, 'Is water solid?', 'True', 'False', '', '', 'b', 'easy'),
(8, 8, 'Is Ice solid?', 'True', 'False', '', '', 'a', 'easy'),
(9, 8, 'Is water Liquid?', 'True', 'False', '', '', 'a', 'easy');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_score` int(11) DEFAULT NULL,
  `total_time` int(11) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `prize_earned` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `quiz_id`, `user_id`, `total_score`, `total_time`, `rank`, `prize_earned`, `created_at`) VALUES
(1, 6, 1, 1, NULL, NULL, 0.00, '2025-06-27 12:15:24'),
(2, 7, 1, 2, NULL, NULL, 0.00, '2025-06-27 12:30:18'),
(3, 8, 1, 2, NULL, NULL, 0.00, '2025-06-27 12:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `wallet_balance` decimal(10,2) DEFAULT 0.00,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `wallet_balance`, `is_admin`, `created_at`) VALUES
(1, 'rachit001', 'rachitkumar2312004@gmail.com', '$2y$10$inLcrHuLI9PK8xWGdm.v4OwW1xHZwiZZY0/DaXXRhV29t5ZO06JfO', 0.00, 0, '2025-06-25 14:16:27'),
(2, 'Naman01', 'nnn1422004@gmail.com', '$2y$10$/x0U/p6Vh4tQATshUzXpkOndOKgplDI.nOebKLhjjJGgDscHVtm3m', 0.00, 1, '2025-06-25 14:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_txns`
--

CREATE TABLE `wallet_txns` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('add','withdraw','entry_fee','deposit','refund','earning','prize') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `prize_distribution`
--
ALTER TABLE `prize_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_quiz_id` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entry_id` (`entry_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_entries`
--
ALTER TABLE `quiz_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallet_txns`
--
ALTER TABLE `wallet_txns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prize_distribution`
--
ALTER TABLE `prize_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quiz_entries`
--
ALTER TABLE `quiz_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wallet_txns`
--
ALTER TABLE `wallet_txns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `prize_distribution`
--
ALTER TABLE `prize_distribution`
  ADD CONSTRAINT `fk_quiz_id` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `quiz_entries` (`id`),
  ADD CONSTRAINT `quiz_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`);

--
-- Constraints for table `quiz_entries`
--
ALTER TABLE `quiz_entries`
  ADD CONSTRAINT `quiz_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quiz_entries_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`),
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wallet_txns`
--
ALTER TABLE `wallet_txns`
  ADD CONSTRAINT `wallet_txns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
