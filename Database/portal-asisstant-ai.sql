-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 12:21 PM
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
-- Database: `portal-asisstant-ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_workload`
--

CREATE TABLE `academic_workload` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) DEFAULT NULL,
  `unit_name` varchar(100) DEFAULT NULL,
  `year_level` enum('First Year','Second Year','Third Year','Fourth Year') DEFAULT NULL,
  `semester_level` enum('1st Semester','2nd Semester') DEFAULT NULL,
  `offering_time` enum('Every Semester','Once a Year') DEFAULT 'Every Semester'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_workload`
--

INSERT INTO `academic_workload` (`id`, `unit_code`, `unit_name`, `year_level`, `semester_level`, `offering_time`) VALUES
(1, 'BIT4202', 'Artificial Intelligence', 'Fourth Year', '2nd Semester', 'Once a Year'),
(6, 'BBM1101', 'Introduction To Business Studies', 'First Year', '1st Semester', 'Every Semester'),
(7, 'BUCUOO7 ', 'Communication Skills And Academic Writting', 'First Year', '1st Semester', 'Every Semester'),
(8, 'BIT1101', 'Computer Architecture', 'First Year', '1st Semester', 'Once a Year'),
(9, 'BIT1106', 'Introduction to Computer Application Packages ', 'First Year', '1st Semester', 'Every Semester'),
(10, 'BMA1106', 'Foundation mathematics ', 'First Year', '1st Semester', 'Once a Year'),
(11, 'BIT1102 ', 'Introduction to programming and algorithms ', 'Second Year', '1st Semester', 'Once a Year'),
(12, 'BBM1202', 'Principles of Marketing ', 'First Year', '1st Semester', 'Every Semester');

-- --------------------------------------------------------

--
-- Table structure for table `admin_referrals`
--

CREATE TABLE `admin_referrals` (
  `id` int(11) NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sender_name` varchar(100) DEFAULT NULL,
  `conversation_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_referrals`
--

INSERT INTO `admin_referrals` (`id`, `admin_reply`, `status`, `created_at`, `sender_name`, `conversation_id`) VALUES
(24, NULL, 'pending', '2026-03-18 05:59:52', 'Noah  Chepkonga', NULL),
(25, NULL, 'pending', '2026-03-18 06:00:51', 'System Admin', NULL),
(26, NULL, 'pending', '2026-03-18 06:02:33', 'Noah  Chepkonga', NULL),
(27, NULL, 'pending', '2026-03-18 06:28:31', 'Noah  Chepkonga', 'hu6idb525bbc5i2ke919s52qgf'),
(28, NULL, 'pending', '2026-03-18 06:53:32', 'System Admin', '2c918aq9l2aqf2ilhhdn151dgg'),
(29, NULL, 'pending', '2026-03-18 06:54:58', 'Noah  Chepkonga', '3br9bejjka0m440iad0b4gidhb'),
(30, NULL, 'pending', '2026-03-18 15:10:44', 'Noah  Chepkonga', 'm2bt8q3vmib70ucbbcp7jlddmm'),
(31, NULL, 'pending', '2026-03-24 09:23:15', 'Vera Michael', 'b6krnmh6tdfjionsp62h55sqjv');

-- --------------------------------------------------------

--
-- Table structure for table `ai_knowledge_base`
--

CREATE TABLE `ai_knowledge_base` (
  `id` int(11) NOT NULL,
  `student_query` text NOT NULL,
  `verified_answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_knowledge_base`
--

INSERT INTO `ai_knowledge_base` (`id`, `student_query`, `verified_answer`, `created_at`) VALUES
(19, 'Admin Escalation Reply', 'Hello How may I help you today', '2026-03-18 06:00:37'),
(20, 'Admin Escalation Reply', 'hello', '2026-03-18 06:02:57'),
(21, 'hello', 'hello', '2026-03-18 06:12:56'),
(22, 'when is the exams begining>', '13th april', '2026-03-18 06:29:10'),
(23, 'thanks', 'You are welcome', '2026-03-18 06:54:01'),
(24, 'when is the exams begining', 'hello', '2026-03-24 06:36:46');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` varchar(50) DEFAULT NULL,
  `sender_type` varchar(10) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_type`, `message`, `created_at`) VALUES
(7, '16', 'student', 'when will exams start?', '2026-03-17 05:57:39'),
(8, '16', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-17 05:57:41'),
(9, '17', 'student', 'cool', '2026-03-18 03:53:12'),
(10, '17', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 03:53:12'),
(11, '18', 'student', 'you are good?', '2026-03-18 04:13:57'),
(12, '18', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:13:57'),
(13, '19', 'student', 'what is my name?', '2026-03-18 04:27:56'),
(14, '19', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:27:56'),
(15, '20', 'student', 'hello', '2026-03-18 04:45:11'),
(16, '20', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:45:11'),
(17, '21', 'student', 'helo', '2026-03-18 04:47:02'),
(18, '21', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:47:02'),
(19, '21', 'admin', 'hi', '2026-03-18 05:00:04'),
(20, '22', 'student', 'when does the exams start?', '2026-03-18 05:00:44'),
(21, '22', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:00:44'),
(22, '22', 'admin', 'next month 13th', '2026-03-18 05:28:21'),
(23, '22', 'admin', 'hi', '2026-03-18 05:29:05'),
(24, '23', 'student', 'when does the exams start?', '2026-03-18 05:32:41'),
(25, '23', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:32:41'),
(26, '23', 'admin', 'hi', '2026-03-18 05:36:59'),
(27, '23', 'admin', 'helllow whatsup', '2026-03-18 05:40:31'),
(28, '23', 'admin', 'helllow whatsup', '2026-03-18 05:41:29'),
(29, '23', 'admin', 'hehehehe', '2026-03-18 05:41:55'),
(30, '23', 'admin', 'yoh', '2026-03-18 05:42:07'),
(31, '23', 'admin', 'ni mbayaa!!!', '2026-03-18 05:43:22'),
(32, '23', 'admin', 'ni mbayaa!!!', '2026-03-18 05:53:08'),
(33, '23', 'admin', 'n', '2026-03-18 05:53:26'),
(34, '23', 'admin', 'hhh', '2026-03-18 05:54:05'),
(35, '24', 'student', 'hello', '2026-03-18 05:59:52'),
(36, '24', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:59:52'),
(37, '24', 'admin', 'Hello How may I help you today', '2026-03-18 06:00:37'),
(38, '25', 'student', 'hello', '2026-03-18 06:00:51'),
(39, '25', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:00:52'),
(40, '26', 'student', 'hello', '2026-03-18 06:02:33'),
(41, '26', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:02:33'),
(42, '26', 'admin', 'hello', '2026-03-18 06:02:57'),
(43, '26', 'admin', 'hello', '2026-03-18 06:12:56'),
(44, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'hello', '2026-03-18 06:25:14'),
(45, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'hello', '2026-03-18 06:25:14'),
(46, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'when is the exams begining', '2026-03-18 06:25:37'),
(47, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'hello', '2026-03-18 06:25:45'),
(48, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'hello', '2026-03-18 06:25:45'),
(49, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'when is the exams begining>', '2026-03-18 06:28:31'),
(50, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin!', '2026-03-18 06:28:31'),
(51, 'hu6idb525bbc5i2ke919s52qgf', 'admin', '13th april', '2026-03-18 06:29:10'),
(52, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'when is the exams begining>', '2026-03-18 06:29:26'),
(53, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', '13th april', '2026-03-18 06:29:26'),
(54, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'when is the exams begining>', '2026-03-18 06:51:44'),
(55, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', '13th april', '2026-03-18 06:51:44'),
(56, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'thanks', '2026-03-18 06:53:31'),
(57, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:53:32'),
(58, '2c918aq9l2aqf2ilhhdn151dgg', 'admin', 'You are welcome', '2026-03-18 06:54:01'),
(59, '3br9bejjka0m440iad0b4gidhb', 'student', 'when is the exam begining?', '2026-03-18 06:54:58'),
(60, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:54:58'),
(61, '3br9bejjka0m440iad0b4gidhb', 'student', 'thanks', '2026-03-18 06:55:03'),
(62, '3br9bejjka0m440iad0b4gidhb', 'bot', 'You are welcome', '2026-03-18 06:55:03'),
(63, '3br9bejjka0m440iad0b4gidhb', 'student', 'exam', '2026-03-18 07:27:20'),
(64, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:20'),
(65, '3br9bejjka0m440iad0b4gidhb', 'student', 'when', '2026-03-18 07:27:26'),
(66, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:26'),
(67, '3br9bejjka0m440iad0b4gidhb', 'student', 'begin', '2026-03-18 07:27:31'),
(68, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:33'),
(69, '3br9bejjka0m440iad0b4gidhb', 'student', 'begin exam when', '2026-03-18 07:27:45'),
(70, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:27:46'),
(71, '3br9bejjka0m440iad0b4gidhb', 'student', 'name', '2026-03-18 07:29:09'),
(72, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:10'),
(73, '3br9bejjka0m440iad0b4gidhb', 'student', 'what is my name', '2026-03-18 07:29:16'),
(74, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:16'),
(75, '3br9bejjka0m440iad0b4gidhb', 'student', 'semester', '2026-03-18 07:29:33'),
(76, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:33'),
(77, '3br9bejjka0m440iad0b4gidhb', 'student', 'hjhj', '2026-03-18 08:20:24'),
(78, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 08:20:26'),
(79, 'm2bt8q3vmib70ucbbcp7jlddmm', 'student', 'vision', '2026-03-18 15:10:44'),
(80, 'm2bt8q3vmib70ucbbcp7jlddmm', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 15:10:44'),
(81, 'm2bt8q3vmib70ucbbcp7jlddmm', 'student', 'when is the exams begining', '2026-03-18 15:11:27'),
(82, 'm2bt8q3vmib70ucbbcp7jlddmm', 'bot', '13th april', '2026-03-18 15:11:27'),
(83, 'm2bt8q3vmib70ucbbcp7jlddmm', 'admin', 'hello', '2026-03-24 06:36:46'),
(84, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'when is exams', '2026-03-24 09:23:14'),
(85, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:15'),
(86, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'when is the exams begining', '2026-03-24 09:23:38'),
(87, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', '13th april', '2026-03-24 09:23:38'),
(88, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'cook', '2026-03-24 09:23:44'),
(89, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:45'),
(90, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'okey', '2026-03-24 09:23:47'),
(91, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:48'),
(92, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'cool', '2026-03-24 09:23:50'),
(93, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:51'),
(94, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'thanks', '2026-03-24 09:23:56'),
(95, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'You are welcome', '2026-03-24 09:23:56');

-- --------------------------------------------------------

--
-- Table structure for table `registered_courses`
--

CREATE TABLE `registered_courses` (
  `id` int(11) NOT NULL,
  `student_reg_no` varchar(30) NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `exam_type` varchar(30) NOT NULL,
  `class_group` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `academic_year` varchar(10) NOT NULL,
  `status` enum('Provisional','Confirmed') DEFAULT 'Provisional',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_courses`
--

INSERT INTO `registered_courses` (`id`, `student_reg_no`, `unit_code`, `exam_type`, `class_group`, `semester`, `academic_year`, `status`, `registered_at`) VALUES
(13, 'BIT/2026/0001', 'BIT2026', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-02-02 09:29:33'),
(14, 'BIT/2026/0003', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-02-02 09:30:29'),
(15, 'ADMIN/001', 'BIT2026', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-02-03 13:08:23'),
(16, 'BIS/2026/00001', 'BUCUOO7', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:25:49'),
(17, 'BIS/2026/00001', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:10'),
(18, 'BIS/2026/00001', 'BIT2026', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:34'),
(19, 'BIS/2026/00001', 'BBM1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:58'),
(20, 'BIT/2026/00002', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 11:16:40');

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_type` varchar(255) DEFAULT NULL,
  `ease_rating` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey_responses`
--

INSERT INTO `survey_responses` (`id`, `user_id`, `challenge_type`, `ease_rating`, `submitted_at`) VALUES
(1, 28, 'Finding Codes', 3, '2026-03-24 11:17:07'),
(2, 28, 'Finding Codes', 3, '2026-03-24 11:18:05');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `time_from` varchar(10) DEFAULT NULL,
  `time_to` varchar(10) DEFAULT NULL,
  `venue` varchar(50) DEFAULT NULL,
  `unit_group` varchar(20) DEFAULT NULL,
  `lecturer` varchar(50) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `semester` enum('1','2','3') NOT NULL,
  `academic_year` year(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `unit_code`, `course_title`, `time_from`, `time_to`, `venue`, `unit_group`, `lecturer`, `exam_date`, `semester`, `academic_year`, `created_at`) VALUES
(4, 'BIT2026', 'Artificial Inteligence', '10:00', '13:00', 'MLT Hall B', 'Jan24', 'Muchiri', '0000-00-00', '', '2026', '2026-02-02 06:18:58'),
(10, 'BAF1101', 'Financial Accounting I ', '07:00', '10:00', 'CC1', 'CLASS 1', 'Mrs. MATHENGE  ', '0000-00-00', '', '2026', '2026-02-02 06:47:46'),
(14, 'BBM1101', 'Introduction To Business Studies', '13:00', '16:00', 'MLT Hall B', NULL, 'Mr Margaret ', NULL, '1', '2026', '2026-03-24 08:21:03'),
(15, 'BUCUOO7 ', 'Communication Skills And Academic Writting', '07:01', '10:00', 'CT HALL', 'Jan24', 'Md Helena', NULL, '1', '2026', '2026-03-24 08:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `reg_number` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `survey_done` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `reg_number`, `email`, `password`, `role`, `department`, `created_at`, `survey_done`) VALUES
(1, 'System Admin', 'ADMIN/001', 'admin@mku.ac.ke', '$2y$10$P.VKg4sPX1yHxleIOEwf1OKlHbYUWXlERdv.GC4clNTvCJWjwS5uG', 'admin', '', '2026-01-18 19:01:19', 0),
(21, 'Noah  Chepkonga', 'BIT/2026/0001', 'novrah4g@gmail.com', '$2y$10$NvoWE/x6V5/MQ2kbnHknWebAS872q/gcj7L0gukzBvXT9gGsRmycq', 'student', 'Information Technology', '2026-02-02 08:31:07', 0),
(27, 'Vera Michael', 'BIS/2026/00001', 'veramichael678@gmail.com', '$2y$10$Dth685QYpVp9Vh2PJxp9AOnfxgTTg2TMI5NUl4zpJ77HVwW5T3yHK', 'student', 'Information Science', '2026-03-24 06:37:30', 0),
(28, 'Noah Chepkonga', 'BIT/2026/00002', 'noahchep1@gmail.com', '$2y$10$Vd5OPSVMIbT8Dchwo.4LpOm6Zi7Y.9OcSJyo1/HVBG3043uOznE96', 'student', 'Information Technology', '2026-03-24 07:32:49', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_workload`
--
ALTER TABLE `academic_workload`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_code` (`unit_code`);

--
-- Indexes for table `admin_referrals`
--
ALTER TABLE `admin_referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ai_knowledge_base`
--
ALTER TABLE `ai_knowledge_base`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registered_courses`
--
ALTER TABLE `registered_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_unit` (`student_reg_no`,`unit_code`),
  ADD KEY `fk_registered_unit` (`unit_code`);

--
-- Indexes for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_unit_offering` (`unit_code`,`semester`,`academic_year`),
  ADD KEY `idx_unit_code` (`unit_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reg_number` (`reg_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_workload`
--
ALTER TABLE `academic_workload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `admin_referrals`
--
ALTER TABLE `admin_referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `ai_knowledge_base`
--
ALTER TABLE `ai_knowledge_base`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `registered_courses`
--
ALTER TABLE `registered_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registered_courses`
--
ALTER TABLE `registered_courses`
  ADD CONSTRAINT `fk_registered_unit` FOREIGN KEY (`unit_code`) REFERENCES `timetable` (`unit_code`) ON UPDATE CASCADE;

--
-- Constraints for table `survey_responses`
--
ALTER TABLE `survey_responses`
  ADD CONSTRAINT `survey_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
