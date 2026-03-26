-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 01:02 PM
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
(31, NULL, 'pending', '2026-03-24 09:23:15', 'Vera Michael', 'b6krnmh6tdfjionsp62h55sqjv'),
(32, NULL, 'pending', '2026-03-25 08:12:52', 'Vera Michael', '3mp68k686q1ip0jsl3vg6atl6j'),
(33, NULL, 'pending', '2026-03-25 09:02:26', 'Vera Michael', 'o5ud24l4b0kboiremqulpl0eub'),
(34, NULL, 'pending', '2026-03-25 11:14:15', 'Vera Michael', '3hqo9m6g4e5d15uu6apbfl7e0f');

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
(24, 'when is the exams begining', 'hello', '2026-03-24 06:36:46'),
(25, 'first year', 'yes', '2026-03-25 08:39:48');

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
(95, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'You are welcome', '2026-03-24 09:23:56'),
(96, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'WORKLOARD', '2026-03-25 08:12:51'),
(97, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:12:52'),
(98, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'second year', '2026-03-25 08:29:28'),
(99, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:29:28'),
(100, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'eexams', '2026-03-25 08:29:56'),
(101, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:29:57'),
(102, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'exams', '2026-03-25 08:29:59'),
(103, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:29:59'),
(104, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'start', '2026-03-25 08:30:04'),
(105, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:30:05'),
(106, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'when', '2026-03-25 08:30:08'),
(107, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:30:08'),
(108, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'unit', '2026-03-25 08:30:16'),
(109, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:30:17'),
(110, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'exam', '2026-03-25 08:38:19'),
(111, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:38:19'),
(112, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'search', '2026-03-25 08:38:30'),
(113, '3mp68k686q1ip0jsl3vg6atl6j', 'student', '1', '2026-03-25 08:38:59'),
(114, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'first year', '2026-03-25 08:39:05'),
(115, '3mp68k686q1ip0jsl3vg6atl6j', 'admin', 'yes', '2026-03-25 08:39:48'),
(116, 'c99b854dgjiuucsa8rd9m1dch8', 'student', 'first year', '2026-03-25 08:39:57'),
(117, 'c99b854dgjiuucsa8rd9m1dch8', 'bot', 'yes', '2026-03-25 08:39:57'),
(118, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'what are some study tips for it students?', '2026-03-25 09:02:26'),
(119, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:02:26'),
(120, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'vera, give me three tips for succeeding in an it degree at mku', '2026-03-25 09:12:50'),
(121, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:12:50'),
(122, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'vera, give me three tips for succeeding in an it degree at mku.', '2026-03-25 09:15:16'),
(123, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Connection Error: error setting certificate file: C:\nmpp\\phpxtras\\ssl\\cacert.pem', '2026-03-25 09:15:16'),
(124, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heelo', '2026-03-25 09:18:05'),
(125, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:18:06'),
(126, 'o5ud24l4b0kboiremqulpl0eub', 'student', '3 it tips again! does it work now', '2026-03-25 09:18:12'),
(127, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:18:15'),
(128, 'o5ud24l4b0kboiremqulpl0eub', 'student', '3 it tips again. what does vera say now', '2026-03-25 09:20:06'),
(129, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash-latest is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:20:07'),
(130, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heello', '2026-03-25 09:20:14'),
(131, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash-latest is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:20:17'),
(132, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heelo', '2026-03-25 09:21:22'),
(133, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:21:24'),
(134, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:21:54'),
(135, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:21:55'),
(136, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helloll', '2026-03-25 09:37:01'),
(137, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:37:03'),
(138, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:39:02'),
(139, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-pro is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:39:03'),
(140, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'like?', '2026-03-25 09:41:40'),
(141, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:41:41'),
(142, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:41:46'),
(143, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:41:46'),
(144, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'hellooo', '2026-03-25 09:42:10'),
(145, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:11'),
(146, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:42:28'),
(147, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:29'),
(148, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helo', '2026-03-25 09:42:45'),
(149, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:47'),
(150, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:43:12'),
(151, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:43:14'),
(152, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:48:39'),
(153, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: API key not valid. Please pass a valid API key. (Code: 400)', '2026-03-25 09:48:41'),
(154, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'hello', '2026-03-25 09:49:51'),
(155, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'hello', '2026-03-25 09:49:51'),
(156, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:49:55'),
(157, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: API key not valid. Please pass a valid API key. (Code: 400)', '2026-03-25 09:49:57'),
(158, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'coool', '2026-03-25 09:52:47'),
(159, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-pro is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:52:48'),
(160, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:00:31'),
(161, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:00:33'),
(162, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:02:55'),
(163, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:02:58'),
(164, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:04:14'),
(165, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.0-pro is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:04:15'),
(166, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:06:32'),
(167, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\nPlease retry in 18.647625673s.', '2026-03-25 10:06:33'),
(168, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:08:11'),
(169, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 38.618036161s.', '2026-03-25 10:08:13'),
(170, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:10:46'),
(171, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash-lite\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash-lite\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash-lite\nPlease retry in 4.036523893s.', '2026-03-25 10:10:47'),
(172, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hello', '2026-03-25 10:33:47'),
(173, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'hello', '2026-03-25 10:33:47'),
(174, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hh', '2026-03-25 10:33:53'),
(175, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'Connection Error: Failed to connect to generativelanguage.googleapis.com port 443 after 21056 ms: Couldn\'t connect to server', '2026-03-25 10:34:14'),
(176, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hii', '2026-03-25 10:34:34'),
(177, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'Connection Error: Failed to connect to generativelanguage.googleapis.com port 443 after 21067 ms: Couldn\'t connect to server', '2026-03-25 10:34:56'),
(178, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:43:13'),
(179, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'Google API Error: models/gemini-1.5-flash-lite is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:43:14'),
(180, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:49:58'),
(181, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: API key expired. Please renew the API key.', '2026-03-25 10:49:59'),
(182, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:52:28'),
(183, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:52:30'),
(184, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hiii', '2026-03-25 10:55:33'),
(185, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:55:34'),
(186, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:01:05'),
(187, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: API key expired. Please renew the API key.', '2026-03-25 11:01:06'),
(188, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:01:41'),
(189, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 7.808758193s.', '2026-03-25 11:01:43'),
(190, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:04:55'),
(191, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 53.965040662s.', '2026-03-25 11:04:56'),
(192, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'habari', '2026-03-25 11:07:25'),
(193, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Hello! I am Vera. How can I help you with your MKU units today?', '2026-03-25 11:07:25'),
(194, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units', '2026-03-25 11:07:29'),
(195, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:29'),
(196, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', '1.1', '2026-03-25 11:07:38'),
(197, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:38'),
(198, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units for 1.1', '2026-03-25 11:07:48'),
(199, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:48'),
(200, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'unit', '2026-03-25 11:07:57'),
(201, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:57'),
(202, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units', '2026-03-25 11:08:11'),
(203, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:08:11'),
(204, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units for 1.1', '2026-03-25 11:08:19'),
(205, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:08:19'),
(206, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semester', '2026-03-25 11:11:04'),
(207, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 41.003766428s.', '2026-03-25 11:11:10'),
(208, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semester', '2026-03-25 11:12:19'),
(209, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semeter', '2026-03-25 11:14:15'),
(210, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 11:14:15'),
(211, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hello', '2026-03-25 11:25:38'),
(212, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'hello', '2026-03-25 11:25:38'),
(213, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'year', '2026-03-25 11:27:35'),
(214, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'yes', '2026-03-25 11:27:35'),
(215, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'yeah', '2026-03-25 11:27:42'),
(216, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'ve already forwarded your previous query to the admin. They will respond soon.', '2026-03-25 11:27:43'),
(217, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'show me second year units', '2026-03-25 11:31:11'),
(218, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'ve already forwarded your previous query to the admin. They will respond soon.', '2026-03-25 11:31:11'),
(219, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hello', '2026-03-25 11:58:01'),
(220, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'hello', '2026-03-25 11:58:01'),
(221, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', '<b>📚 Definition of \"faculty\":</b><br><br><b>noun:</b><br>  1. The academic staff at schools, colleges, universities or not-for-profit research institutes, as opposed to the students or support staff.<br>  2. A division of a university.<br>     <i>Example: \"She transferred from the Faculty of Science to the Faculty of Medicine.\"</i><br><br>', '2026-03-25 11:59:04'),
(222, 'a4mj3slja5dm9shqbj5toao5ln', 'student', 'hello', '2026-03-25 13:07:11'),
(223, 'a4mj3slja5dm9shqbj5toao5ln', 'bot', 'hello', '2026-03-25 13:07:11'),
(224, 'rssmginhuvq7t7k27vakdi0ucg', 'student', 'hello', '2026-03-25 18:36:44'),
(225, 'rssmginhuvq7t7k27vakdi0ucg', 'bot', 'hello', '2026-03-25 18:36:44'),
(226, 'rssmginhuvq7t7k27vakdi0ucg', 'student', 'first year', '2026-03-25 18:37:12'),
(227, 'rssmginhuvq7t7k27vakdi0ucg', 'bot', 'yes', '2026-03-25 18:37:12');

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
(16, 'BIS/2026/00001', 'BUCUOO7', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:25:49'),
(17, 'BIS/2026/00001', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:10'),
(18, 'BIS/2026/00001', 'BIT2026', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:34'),
(19, 'BIS/2026/00001', 'BBM1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 08:26:58'),
(20, 'BIT/2026/00002', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-24 11:16:40'),
(22, 'BIT/2026/00002', 'BBM1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 04:50:58'),
(29, 'BIS/2026/00001', 'BIT1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 05:37:50'),
(32, 'BEC/2026/00003', 'BAF1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 06:05:43'),
(33, 'BEC/2026/00003', 'BMA1106', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 06:15:54'),
(34, 'BEC/2026/00003', 'BIT1101', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 07:08:23'),
(35, 'BEC/2026/00003', 'BUCUOO7', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 07:13:09'),
(36, 'BIT/2026/00002', 'BMA1106', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 12:41:48'),
(37, 'BIT/2026/00002', 'BUCUOO7', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-25 12:42:19');

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE `survey_responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_type` varchar(255) DEFAULT NULL,
  `guidance_need` varchar(255) DEFAULT NULL,
  `chatbot_help` varchar(255) DEFAULT NULL,
  `ui_experience` varchar(255) DEFAULT NULL,
  `academic_value` varchar(255) DEFAULT NULL,
  `ease_rating` int(11) DEFAULT NULL,
  `student_comments` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(15, 'BUCUOO7 ', 'Communication Skills And Academic Writting', '07:01', '10:00', 'CT HALL', 'Jan24', 'Md Helena', NULL, '1', '2026', '2026-03-24 08:23:54'),
(16, 'BIT1101', 'Computer Architecture', '10:10', '13:00', 'CC1', 'Jan24', 'Muchiri', NULL, '1', '2026', '2026-03-25 05:21:37'),
(17, 'BMA1106', 'Foundation mathematics ', '07:00', '10:00', 'MLT Hall B', 'Jan24', 'Mrs Ochieng', NULL, '1', '2026', '2026-03-25 05:43:32');

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
(1, 'System Admin', 'ADMIN/001', 'admin@mku.ac.ke', '$2y$10$P.VKg4sPX1yHxleIOEwf1OKlHbYUWXlERdv.GC4clNTvCJWjwS5uG', 'admin', '', '2026-01-18 19:01:19', 1),
(27, 'Vera Michael', 'BIS/2026/00001', 'veramichael678@gmail.com', '$2y$10$Dth685QYpVp9Vh2PJxp9AOnfxgTTg2TMI5NUl4zpJ77HVwW5T3yHK', 'student', 'Information Science', '2026-03-24 06:37:30', 1),
(28, 'Noah Chepkonga', 'BIT/2026/00002', 'noahchep1@gmail.com', '$2y$10$Vd5OPSVMIbT8Dchwo.4LpOm6Zi7Y.9OcSJyo1/HVBG3043uOznE96', 'student', 'Information Technology', '2026-03-24 07:32:49', 1),
(30, 'Chepkonga', 'BEC/2026/00003', 'novrah4g@gmail.com', '$2y$10$OVp3ZSt4QeD0m1diYkBmdeZJ4QuK8jN9Fh5n3hYBnl2ysoACZYFUS', 'student', 'Enterprise Computing', '2026-03-25 06:02:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `vocabulary`
--

CREATE TABLE `vocabulary` (
  `id` int(11) NOT NULL,
  `word` varchar(100) NOT NULL,
  `part_of_speech` varchar(50) DEFAULT NULL,
  `definition` text NOT NULL,
  `example` text DEFAULT NULL,
  `synonyms` text DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vocabulary`
--

INSERT INTO `vocabulary` (`id`, `word`, `part_of_speech`, `definition`, `example`, `synonyms`, `category`, `created_at`) VALUES
(1, 'algorithm', 'noun', 'A step-by-step procedure for solving a problem or accomplishing a task, especially by a computer.', 'The sorting algorithm efficiently organized the data in ascending order.', NULL, 'computer science', '2026-03-25 11:56:41'),
(2, 'database', 'noun', 'An organized collection of structured information or data stored electronically.', 'The university database stores all student records securely.', NULL, 'computer science', '2026-03-25 11:56:41'),
(3, 'artificial intelligence', 'noun', 'The simulation of human intelligence in machines programmed to think and learn.', 'AI helps chatbots understand and respond to user queries naturally.', NULL, 'technology', '2026-03-25 11:56:41'),
(4, 'semester', 'noun', 'A half-year term in a school or university.', 'The fall semester runs from September to December.', NULL, 'academic', '2026-03-25 11:56:41'),
(5, 'curriculum', 'noun', 'The subjects comprising a course of study in a school or college.', 'The computer science curriculum includes programming and algorithms.', NULL, 'academic', '2026-03-25 11:56:41'),
(6, 'syllabus', 'noun', 'An outline of the subjects in a course of study.', 'The professor distributed the syllabus on the first day of class.', NULL, 'academic', '2026-03-25 11:56:41'),
(7, 'prerequisite', 'noun', 'A course that must be completed before enrolling in a more advanced one.', 'Programming 101 is a prerequisite for Advanced Programming.', NULL, 'academic', '2026-03-25 11:56:41'),
(8, 'thesis', 'noun', 'A long essay or dissertation involving personal research.', 'She is working on her master\'s thesis about machine learning.', NULL, 'academic', '2026-03-25 11:56:41'),
(9, 'internship', 'noun', 'A period of work experience offered by an organization.', 'He completed an internship at a software company.', NULL, 'career', '2026-03-25 11:56:41'),
(10, 'scholarship', 'noun', 'Financial aid awarded to a student for academic achievement.', 'She received a full scholarship to study computer science.', NULL, 'academic', '2026-03-25 11:56:41'),
(11, 'diligent', 'adjective', 'Having or showing care and conscientiousness in one\'s work or duties.', 'She was a diligent student who always completed her homework.', NULL, 'english', '2026-03-25 11:56:41'),
(12, 'perseverance', 'noun', 'Continued effort to do or achieve something despite difficulties.', 'His perseverance paid off when he finally graduated.', NULL, 'english', '2026-03-25 11:56:41'),
(13, 'innovative', 'adjective', 'Featuring new methods; advanced and original.', 'The innovative teaching methods engaged all students.', NULL, 'english', '2026-03-25 11:56:41'),
(14, 'comprehensive', 'adjective', 'Complete; including all or nearly all elements.', 'The textbook provides a comprehensive overview of the subject.', NULL, 'english', '2026-03-25 11:56:41'),
(15, 'analytical', 'adjective', 'Relating to or using analysis or logical reasoning.', 'The course develops analytical thinking skills.', NULL, 'english', '2026-03-25 11:56:41'),
(16, 'curious', 'adjective', 'Eager to know or learn something.', 'She was curious about how the AI chatbot worked.', NULL, 'english', '2026-03-25 11:56:41'),
(17, 'resilient', 'adjective', 'Able to recover quickly from difficulties.', 'Resilient students overcome academic challenges.', NULL, 'english', '2026-03-25 11:56:41'),
(18, 'mentor', 'noun', 'An experienced person who advises and guides a less experienced person.', 'My professor acted as my mentor throughout the research project.', NULL, 'academic', '2026-03-25 11:56:41'),
(19, 'campus', 'noun', 'The grounds and buildings of a university or college.', 'The campus is beautiful during spring.', NULL, 'academic', '2026-03-25 11:56:41'),
(20, 'lecture', 'noun', 'An educational talk to an audience, especially to students.', 'The lecture on AI was fascinating.', NULL, 'academic', '2026-03-25 11:56:41'),
(21, 'faculty', 'noun', '📚 Definition of \"faculty\":noun: 1. The academic staff at schools, colleges, universities or not-for-profit research institutes, as opposed to the students or support staff. 2. A division of a university.', 'She transferred from the Faculty of Science to the Faculty of Medicine.', '', 'general', '2026-03-25 11:59:04');

-- --------------------------------------------------------

--
-- Table structure for table `vocabulary_requests`
--

CREATE TABLE `vocabulary_requests` (
  `id` int(11) NOT NULL,
  `word` varchar(100) NOT NULL,
  `requested_by` varchar(100) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `added_to_db` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vocabulary_requests`
--

INSERT INTO `vocabulary_requests` (`id`, `word`, `requested_by`, `requested_at`, `added_to_db`) VALUES
(1, '?', 'Vera Michael', '2026-03-25 11:57:02', 0);

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
-- Indexes for table `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `word` (`word`),
  ADD KEY `idx_word` (`word`);

--
-- Indexes for table `vocabulary_requests`
--
ALTER TABLE `vocabulary_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_word` (`word`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `ai_knowledge_base`
--
ALTER TABLE `ai_knowledge_base`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT for table `registered_courses`
--
ALTER TABLE `registered_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `vocabulary`
--
ALTER TABLE `vocabulary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `vocabulary_requests`
--
ALTER TABLE `vocabulary_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
