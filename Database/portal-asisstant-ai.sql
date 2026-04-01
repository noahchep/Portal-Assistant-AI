-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 01, 2026 at 01:49 PM
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
  `department` varchar(50) DEFAULT NULL,
  `year_level` enum('First Year','Second Year','Third Year','Fourth Year') DEFAULT NULL,
  `semester_level` enum('1st Semester','2nd Semester') DEFAULT NULL,
  `offering_time` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_workload`
--

INSERT INTO `academic_workload` (`id`, `unit_code`, `unit_name`, `department`, `year_level`, `semester_level`, `offering_time`) VALUES
(69, 'ABCU001', 'Research Methodology', NULL, 'First Year', '1st Semester', 'Every Semester'),
(70, 'BAF1101', 'Financial Accounting I', NULL, 'First Year', '1st Semester', 'Every Semester'),
(71, 'BBM1101', 'Introduction to Business Studies', NULL, 'First Year', '1st Semester', 'Every Semester'),
(72, 'BIT1101', 'Computer Architecture', NULL, 'First Year', '1st Semester', 'Once in 3 Semesters'),
(73, 'BIT1102', 'Introduction to Programming and Algorithms', NULL, 'First Year', '1st Semester', 'Every Semester'),
(74, 'BIT1106', 'Introduction to Computer Application Packages', NULL, 'First Year', '1st Semester', 'Every Semester'),
(75, 'BMA1104', 'Probability and Statistics I', NULL, 'First Year', '1st Semester', 'Every Semester'),
(76, 'BMA1106', 'Foundation Mathematics', NULL, 'First Year', '1st Semester', 'Every Semester'),
(77, 'BUCU007', 'Communication Skills and Academic Writing', NULL, 'First Year', '1st Semester', 'Every Semester'),
(78, 'BUCU011', 'Health Literacy', NULL, 'First Year', '1st Semester', 'Every Semester'),
(79, 'BBM1201', 'Principles of Management', NULL, 'First Year', '2nd Semester', 'Every Semester'),
(80, 'BBM1202', 'Principles of Marketing', NULL, 'First Year', '2nd Semester', 'Every Semester'),
(81, 'BIT1208', 'Structured Programming', NULL, 'First Year', '2nd Semester', 'Twice in 3 Semesters'),
(82, 'BMA1202', 'Discrete Mathematics', NULL, 'First Year', '2nd Semester', 'Every Semester'),
(83, 'BPY1101', 'Basic Electricity and Optics', NULL, 'First Year', '2nd Semester', 'Once in 3 Semesters'),
(84, 'BBM2103', 'Organization Behavior', NULL, 'Second Year', '1st Semester', 'Every Semester'),
(85, 'BEG2112', 'Digital Electronics and Devices', NULL, 'Second Year', '1st Semester', 'Once in 3 Semesters'),
(86, 'BIT1201', 'Database Systems', NULL, 'Second Year', '1st Semester', 'Twice in 3 Semesters'),
(87, 'BIT1202', 'Introduction to Web Design', NULL, 'Second Year', '1st Semester', 'Twice in 3 Semesters'),
(88, 'BIT2102', 'Fundamentals of Internet', NULL, 'Second Year', '1st Semester', 'Every Semester'),
(89, 'BIT2103', 'Hardware and Software Installation and Support', NULL, 'Second Year', '1st Semester', 'Once in 3 Semesters'),
(90, 'BIT2104', 'Operating Systems', NULL, 'Second Year', '1st Semester', 'Twice in 3 Semesters'),
(91, 'BMA2102', 'Probability and Statistics II', NULL, 'Second Year', '1st Semester', 'Every Semester'),
(92, 'BIT2203', 'Data Structure and Algorithms', NULL, 'Second Year', '2nd Semester', 'Twice in 3 Semesters'),
(93, 'BIT2204', 'Data Communication and Networks', NULL, 'Second Year', '2nd Semester', 'Twice in 3 Semesters'),
(94, 'BIT2205', 'Object Oriented Programming II', NULL, 'Second Year', '2nd Semester', 'Once in 3 Semesters'),
(95, 'BIT2206', 'Systems Analysis and Design', NULL, 'Second Year', '2nd Semester', 'Twice in 3 Semesters'),
(96, 'BIT3101', 'Software Engineering', NULL, 'Third Year', '1st Semester', 'Twice in 3 Semesters'),
(97, 'BIT3102', 'Event Driven Programming', NULL, 'Third Year', '1st Semester', 'Once in 3 Semesters'),
(98, 'BIT3105', 'Management Information Systems', NULL, 'Third Year', '1st Semester', 'Every Semester'),
(99, 'BIT3106', 'Object Oriented Programming', NULL, 'Third Year', '1st Semester', 'Twice in 3 Semesters'),
(100, 'BIT3107', 'Database Systems II', NULL, 'Third Year', '1st Semester', 'Every Semester'),
(101, 'BIT3201', 'Object Oriented Analysis and Design', NULL, 'Third Year', '2nd Semester', 'Twice in 3 Semesters'),
(102, 'BIT3202', 'Internet Programming', NULL, 'Third Year', '2nd Semester', 'Every Semester'),
(103, 'BIT3204', 'Network Management', NULL, 'Third Year', '2nd Semester', 'Once in 3 Semesters'),
(104, 'BIT3206', 'ICT Project Management', NULL, 'Third Year', '2nd Semester', 'Every Semester'),
(105, 'BIT3209', 'Design and Analysis of Algorithm', NULL, 'Third Year', '2nd Semester', 'Twice in 3 Semesters'),
(106, 'BIT3221', 'Network Operating Systems', NULL, 'Third Year', '2nd Semester', 'Once in 3 Semesters'),
(107, 'BMA3201', 'Operation Research I', NULL, 'Third Year', '2nd Semester', 'Every Semester'),
(108, 'BUCU010', 'Entrepreneurial Mindset and Financial Literacy', NULL, 'Third Year', '2nd Semester', 'Every Semester'),
(109, 'BIT3224', 'Computing Projects Development Approaches', NULL, 'Fourth Year', '1st Semester', 'Once in 3 Semesters'),
(110, 'BIT4102', 'Computer Graphics', NULL, 'Fourth Year', '1st Semester', 'Once in 3 Semesters'),
(111, 'BIT4103', 'Human Computer Interaction', NULL, 'Fourth Year', '1st Semester', 'Every Semester'),
(112, 'BIT4104', 'Security and Cryptography', NULL, 'Fourth Year', '1st Semester', 'Once in 3 Semesters'),
(113, 'BIT4105', 'Advanced Data Structures and Computer Algorithms', NULL, 'Fourth Year', '1st Semester', 'Once in 3 Semesters'),
(114, 'BIT4107', 'Mobile Applications Development', NULL, 'Fourth Year', '1st Semester', 'Every Semester'),
(115, 'BIT4108', 'Information Systems Audit', NULL, 'Fourth Year', '1st Semester', 'Once in 3 Semesters'),
(116, 'BUCU009', 'Climate Change and Development', NULL, 'Fourth Year', '1st Semester', 'Every Semester'),
(117, 'BIT4201', 'Mobile Computing I', NULL, 'Fourth Year', '2nd Semester', 'Once in 3 Semesters'),
(118, 'BIT4202', 'Artificial Intelligence', NULL, 'Fourth Year', '2nd Semester', 'Once in 3 Semesters'),
(119, 'BIT4203', 'Distributed Multimedia Systems', NULL, 'Fourth Year', '2nd Semester', 'Every Semester'),
(120, 'BIT4204', 'E - Commerce', NULL, 'Fourth Year', '2nd Semester', 'Every Semester'),
(121, 'BIT4205', 'Network Programming', NULL, 'Fourth Year', '2nd Semester', 'Once in 3 Semesters'),
(122, 'BIT4206', 'ICT in Business and Society', NULL, 'Fourth Year', '2nd Semester', 'Every Semester'),
(123, 'BIT4209', 'Distributed Systems', NULL, 'Fourth Year', '2nd Semester', 'Once in 3 Semesters'),
(124, 'BIT4217', 'Total Quality Management For IT', NULL, 'Fourth Year', '2nd Semester', 'Every Semester'),
(125, 'BBM 1101', 'Introduction to Business Management', 'Management', 'First Year', '1st Semester', 'Every Semester'),
(126, 'BEC 1102', 'Principles of Microeconomics', 'Economics', 'First Year', '1st Semester', 'Every Semester'),
(127, 'BAF 1103', 'Business Mathematics', 'Accounting and Finance', 'First Year', '1st Semester', 'Once a Year'),
(128, 'BBM 1104', 'Communication Skills for Business', 'Management', 'First Year', '1st Semester', 'Every Semester'),
(129, 'BAF 1106', 'Financial Accounting I', 'Accounting and Finance', 'First Year', '1st Semester', 'Every Semester'),
(130, 'BEC 1201', 'Principles of Macroeconomics', 'Economics', 'First Year', '2nd Semester', 'Once a Year'),
(131, 'BBM 1202', 'Business Law I', 'Management', 'First Year', '2nd Semester', 'Every Semester'),
(132, 'BAF 1203', 'Business Statistics', 'Accounting and Finance', 'First Year', '2nd Semester', 'Sep-Dec 2026'),
(133, 'BBM 2101', 'Organizational Behavior', 'Management', 'Second Year', '1st Semester', 'Every Semester'),
(134, 'BAF 2102', 'Intermediate Accounting I', 'Accounting and Finance', 'Second Year', '1st Semester', 'Once a Year'),
(135, 'BEC 2103', 'Intermediate Microeconomics', 'Economics', 'Second Year', '1st Semester', 'May-Aug 2026'),
(136, 'BBM 2104', 'Human Resource Management', 'Management', 'Second Year', '1st Semester', 'Every Semester'),
(137, 'BAF 2105', 'Cost Accounting', 'Accounting and Finance', 'Second Year', '1st Semester', 'Once a Year'),
(138, 'BBM 2106', 'Purchasing and Supplies Management', 'Management', 'Second Year', '1st Semester', 'Sep-Dec 2026'),
(139, 'BAF 2201', 'Intermediate Accounting II', 'Accounting and Finance', 'Second Year', '2nd Semester', 'Once a Year'),
(140, 'BEC 2202', 'Intermediate Macroeconomics', 'Economics', 'Second Year', '2nd Semester', 'May-Aug 2026'),
(141, 'BBM 2203', 'Business Law II', 'Management', 'Second Year', '2nd Semester', 'Every Semester'),
(142, 'BAF 2204', 'Financial Management I', 'Accounting and Finance', 'Second Year', '2nd Semester', 'Sep-Dec 2026'),
(143, 'BAF 3101', 'Financial Management II', 'Accounting and Finance', 'Third Year', '1st Semester', 'Once a Year'),
(144, 'BBM 3102', 'Operations Management', 'Management', 'Third Year', '1st Semester', 'May-Aug 2026'),
(145, 'BEC 3103', 'Econometrics I', 'Economics', 'Third Year', '1st Semester', 'Once a Year'),
(146, 'BAF 3104', 'Management Accounting', 'Accounting and Finance', 'Third Year', '1st Semester', 'Sep-Dec 2026'),
(147, 'BBM 3105', 'Research Methods in Business', 'Management', 'Third Year', '1st Semester', 'Every Semester'),
(148, 'BAF 3106', 'Auditing and Assurance I', 'Accounting and Finance', 'Third Year', '1st Semester', 'Once a Year'),
(149, 'BBM 3201', 'Strategic Management', 'Management', 'Third Year', '2nd Semester', 'Every Semester'),
(150, 'BEC 3202', 'Public Finance', 'Economics', 'Third Year', '2nd Semester', 'May-Aug 2026'),
(151, 'BAF 3203', 'Taxation I', 'Accounting and Finance', 'Third Year', '2nd Semester', 'Once a Year'),
(152, 'BBM 3204', 'Innovation and Change Management', 'Management', 'Third Year', '2nd Semester', 'Sep-Dec 2026'),
(153, 'BBM 3206', 'Industrial Attachment', 'Management', 'Third Year', '2nd Semester', 'May-Aug 2026'),
(154, 'BBM 4101', 'Entrepreneurship', 'Management', 'Fourth Year', '1st Semester', 'Every Semester'),
(155, 'BAF 4102', 'International Finance', 'Accounting and Finance', 'Fourth Year', '1st Semester', 'Once a Year'),
(156, 'BEC 4103', 'History of Economic Thought', 'Economics', 'Fourth Year', '1st Semester', 'Sep-Dec 2026'),
(157, 'BBM 4104', 'Project Management', 'Management', 'Fourth Year', '1st Semester', 'May-Aug 2026'),
(158, 'BAF 4105', 'Corporate Governance', 'Accounting and Finance', 'Fourth Year', '1st Semester', 'Once a Year'),
(159, 'BBM 4201', 'Business Ethics', 'Management', 'Fourth Year', '2nd Semester', 'Every Semester'),
(160, 'BAF 4202', 'Taxation II', 'Accounting and Finance', 'Fourth Year', '2nd Semester', 'Once a Year'),
(161, 'BEC 4203', 'Development Economics', 'Economics', 'Fourth Year', '2nd Semester', 'May-Aug 2026'),
(162, 'BBM 4204', 'Total Quality Management', 'Management', 'Fourth Year', '2nd Semester', 'Sep-Dec 2026'),
(163, 'BBM 4205', 'Research Project', 'Management', 'Fourth Year', '2nd Semester', 'Every Semester');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `intent` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_type`, `message`, `created_at`, `intent`) VALUES
(7, '16', 'student', 'when will exams start?', '2026-03-17 05:57:39', NULL),
(8, '16', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-17 05:57:41', NULL),
(9, '17', 'student', 'cool', '2026-03-18 03:53:12', NULL),
(10, '17', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 03:53:12', NULL),
(11, '18', 'student', 'you are good?', '2026-03-18 04:13:57', NULL),
(12, '18', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:13:57', NULL),
(13, '19', 'student', 'what is my name?', '2026-03-18 04:27:56', NULL),
(14, '19', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:27:56', NULL),
(15, '20', 'student', 'hello', '2026-03-18 04:45:11', NULL),
(16, '20', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:45:11', NULL),
(17, '21', 'student', 'helo', '2026-03-18 04:47:02', NULL),
(18, '21', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 04:47:02', NULL),
(19, '21', 'admin', 'hi', '2026-03-18 05:00:04', NULL),
(20, '22', 'student', 'when does the exams start?', '2026-03-18 05:00:44', NULL),
(21, '22', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:00:44', NULL),
(22, '22', 'admin', 'next month 13th', '2026-03-18 05:28:21', NULL),
(23, '22', 'admin', 'hi', '2026-03-18 05:29:05', NULL),
(24, '23', 'student', 'when does the exams start?', '2026-03-18 05:32:41', NULL),
(25, '23', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:32:41', NULL),
(26, '23', 'admin', 'hi', '2026-03-18 05:36:59', NULL),
(27, '23', 'admin', 'helllow whatsup', '2026-03-18 05:40:31', NULL),
(28, '23', 'admin', 'helllow whatsup', '2026-03-18 05:41:29', NULL),
(29, '23', 'admin', 'hehehehe', '2026-03-18 05:41:55', NULL),
(30, '23', 'admin', 'yoh', '2026-03-18 05:42:07', NULL),
(31, '23', 'admin', 'ni mbayaa!!!', '2026-03-18 05:43:22', NULL),
(32, '23', 'admin', 'ni mbayaa!!!', '2026-03-18 05:53:08', NULL),
(33, '23', 'admin', 'n', '2026-03-18 05:53:26', NULL),
(34, '23', 'admin', 'hhh', '2026-03-18 05:54:05', NULL),
(35, '24', 'student', 'hello', '2026-03-18 05:59:52', NULL),
(36, '24', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 05:59:52', NULL),
(37, '24', 'admin', 'Hello How may I help you today', '2026-03-18 06:00:37', NULL),
(38, '25', 'student', 'hello', '2026-03-18 06:00:51', NULL),
(39, '25', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:00:52', NULL),
(40, '26', 'student', 'hello', '2026-03-18 06:02:33', NULL),
(41, '26', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:02:33', NULL),
(42, '26', 'admin', 'hello', '2026-03-18 06:02:57', NULL),
(43, '26', 'admin', 'hello', '2026-03-18 06:12:56', NULL),
(44, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'hello', '2026-03-18 06:25:14', NULL),
(45, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'hello', '2026-03-18 06:25:14', NULL),
(46, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'when is the exams begining', '2026-03-18 06:25:37', NULL),
(47, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'hello', '2026-03-18 06:25:45', NULL),
(48, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'hello', '2026-03-18 06:25:45', NULL),
(49, 'hu6idb525bbc5i2ke919s52qgf', 'student', 'when is the exams begining>', '2026-03-18 06:28:31', NULL),
(50, 'hu6idb525bbc5i2ke919s52qgf', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin!', '2026-03-18 06:28:31', NULL),
(51, 'hu6idb525bbc5i2ke919s52qgf', 'admin', '13th april', '2026-03-18 06:29:10', NULL),
(52, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'when is the exams begining>', '2026-03-18 06:29:26', NULL),
(53, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', '13th april', '2026-03-18 06:29:26', NULL),
(54, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'when is the exams begining>', '2026-03-18 06:51:44', NULL),
(55, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', '13th april', '2026-03-18 06:51:44', NULL),
(56, '2c918aq9l2aqf2ilhhdn151dgg', 'student', 'thanks', '2026-03-18 06:53:31', NULL),
(57, '2c918aq9l2aqf2ilhhdn151dgg', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:53:32', NULL),
(58, '2c918aq9l2aqf2ilhhdn151dgg', 'admin', 'You are welcome', '2026-03-18 06:54:01', NULL),
(59, '3br9bejjka0m440iad0b4gidhb', 'student', 'when is the exam begining?', '2026-03-18 06:54:58', NULL),
(60, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 06:54:58', NULL),
(61, '3br9bejjka0m440iad0b4gidhb', 'student', 'thanks', '2026-03-18 06:55:03', NULL),
(62, '3br9bejjka0m440iad0b4gidhb', 'bot', 'You are welcome', '2026-03-18 06:55:03', NULL),
(63, '3br9bejjka0m440iad0b4gidhb', 'student', 'exam', '2026-03-18 07:27:20', NULL),
(64, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:20', NULL),
(65, '3br9bejjka0m440iad0b4gidhb', 'student', 'when', '2026-03-18 07:27:26', NULL),
(66, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:26', NULL),
(67, '3br9bejjka0m440iad0b4gidhb', 'student', 'begin', '2026-03-18 07:27:31', NULL),
(68, '3br9bejjka0m440iad0b4gidhb', 'bot', '13th april', '2026-03-18 07:27:33', NULL),
(69, '3br9bejjka0m440iad0b4gidhb', 'student', 'begin exam when', '2026-03-18 07:27:45', NULL),
(70, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:27:46', NULL),
(71, '3br9bejjka0m440iad0b4gidhb', 'student', 'name', '2026-03-18 07:29:09', NULL),
(72, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:10', NULL),
(73, '3br9bejjka0m440iad0b4gidhb', 'student', 'what is my name', '2026-03-18 07:29:16', NULL),
(74, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:16', NULL),
(75, '3br9bejjka0m440iad0b4gidhb', 'student', 'semester', '2026-03-18 07:29:33', NULL),
(76, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 07:29:33', NULL),
(77, '3br9bejjka0m440iad0b4gidhb', 'student', 'hjhj', '2026-03-18 08:20:24', NULL),
(78, '3br9bejjka0m440iad0b4gidhb', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 08:20:26', NULL),
(79, 'm2bt8q3vmib70ucbbcp7jlddmm', 'student', 'vision', '2026-03-18 15:10:44', NULL),
(80, 'm2bt8q3vmib70ucbbcp7jlddmm', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-18 15:10:44', NULL),
(81, 'm2bt8q3vmib70ucbbcp7jlddmm', 'student', 'when is the exams begining', '2026-03-18 15:11:27', NULL),
(82, 'm2bt8q3vmib70ucbbcp7jlddmm', 'bot', '13th april', '2026-03-18 15:11:27', NULL),
(83, 'm2bt8q3vmib70ucbbcp7jlddmm', 'admin', 'hello', '2026-03-24 06:36:46', NULL),
(84, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'when is exams', '2026-03-24 09:23:14', NULL),
(85, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:15', NULL),
(86, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'when is the exams begining', '2026-03-24 09:23:38', NULL),
(87, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', '13th april', '2026-03-24 09:23:38', NULL),
(88, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'cook', '2026-03-24 09:23:44', NULL),
(89, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:45', NULL),
(90, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'okey', '2026-03-24 09:23:47', NULL),
(91, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:48', NULL),
(92, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'cool', '2026-03-24 09:23:50', NULL),
(93, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-24 09:23:51', NULL),
(94, 'b6krnmh6tdfjionsp62h55sqjv', 'student', 'thanks', '2026-03-24 09:23:56', NULL),
(95, 'b6krnmh6tdfjionsp62h55sqjv', 'bot', 'You are welcome', '2026-03-24 09:23:56', NULL),
(96, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'WORKLOARD', '2026-03-25 08:12:51', NULL),
(97, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:12:52', NULL),
(98, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'second year', '2026-03-25 08:29:28', NULL),
(99, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:29:28', NULL),
(100, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'eexams', '2026-03-25 08:29:56', NULL),
(101, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:29:57', NULL),
(102, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'exams', '2026-03-25 08:29:59', NULL),
(103, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:29:59', NULL),
(104, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'start', '2026-03-25 08:30:04', NULL),
(105, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:30:05', NULL),
(106, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'when', '2026-03-25 08:30:08', NULL),
(107, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:30:08', NULL),
(108, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'unit', '2026-03-25 08:30:16', NULL),
(109, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 08:30:17', NULL),
(110, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'exam', '2026-03-25 08:38:19', NULL),
(111, '3mp68k686q1ip0jsl3vg6atl6j', 'bot', '13th april', '2026-03-25 08:38:19', NULL),
(112, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'search', '2026-03-25 08:38:30', NULL),
(113, '3mp68k686q1ip0jsl3vg6atl6j', 'student', '1', '2026-03-25 08:38:59', NULL),
(114, '3mp68k686q1ip0jsl3vg6atl6j', 'student', 'first year', '2026-03-25 08:39:05', NULL),
(115, '3mp68k686q1ip0jsl3vg6atl6j', 'admin', 'yes', '2026-03-25 08:39:48', NULL),
(116, 'c99b854dgjiuucsa8rd9m1dch8', 'student', 'first year', '2026-03-25 08:39:57', NULL),
(117, 'c99b854dgjiuucsa8rd9m1dch8', 'bot', 'yes', '2026-03-25 08:39:57', NULL),
(118, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'what are some study tips for it students?', '2026-03-25 09:02:26', NULL),
(119, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:02:26', NULL),
(120, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'vera, give me three tips for succeeding in an it degree at mku', '2026-03-25 09:12:50', NULL),
(121, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:12:50', NULL),
(122, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'vera, give me three tips for succeeding in an it degree at mku.', '2026-03-25 09:15:16', NULL),
(123, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Connection Error: error setting certificate file: C:\nmpp\\phpxtras\\ssl\\cacert.pem', '2026-03-25 09:15:16', NULL),
(124, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heelo', '2026-03-25 09:18:05', NULL),
(125, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:18:06', NULL),
(126, 'o5ud24l4b0kboiremqulpl0eub', 'student', '3 it tips again! does it work now', '2026-03-25 09:18:12', NULL),
(127, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:18:15', NULL),
(128, 'o5ud24l4b0kboiremqulpl0eub', 'student', '3 it tips again. what does vera say now', '2026-03-25 09:20:06', NULL),
(129, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash-latest is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:20:07', NULL),
(130, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heello', '2026-03-25 09:20:14', NULL),
(131, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash-latest is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 09:20:17', NULL),
(132, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'heelo', '2026-03-25 09:21:22', NULL),
(133, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:21:24', NULL),
(134, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:21:54', NULL),
(135, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:21:55', NULL),
(136, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helloll', '2026-03-25 09:37:01', NULL),
(137, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:37:03', NULL),
(138, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:39:02', NULL),
(139, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-pro is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:39:03', NULL),
(140, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'like?', '2026-03-25 09:41:40', NULL),
(141, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:41:41', NULL),
(142, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:41:46', NULL),
(143, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 09:41:46', NULL),
(144, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'hellooo', '2026-03-25 09:42:10', NULL),
(145, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:11', NULL),
(146, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:42:28', NULL),
(147, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:29', NULL),
(148, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helo', '2026-03-25 09:42:45', NULL),
(149, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:42:47', NULL),
(150, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:43:12', NULL),
(151, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:43:14', NULL),
(152, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:48:39', NULL),
(153, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: API key not valid. Please pass a valid API key. (Code: 400)', '2026-03-25 09:48:41', NULL),
(154, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'hello', '2026-03-25 09:49:51', NULL),
(155, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'hello', '2026-03-25 09:49:51', NULL),
(156, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'helllo', '2026-03-25 09:49:55', NULL),
(157, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: API key not valid. Please pass a valid API key. (Code: 400)', '2026-03-25 09:49:57', NULL),
(158, 'o5ud24l4b0kboiremqulpl0eub', 'student', 'coool', '2026-03-25 09:52:47', NULL),
(159, 'o5ud24l4b0kboiremqulpl0eub', 'bot', 'Google API Error: models/gemini-1.5-pro is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods. (Code: 404)', '2026-03-25 09:52:48', NULL),
(160, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:00:31', NULL),
(161, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:00:33', NULL),
(162, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:02:55', NULL),
(163, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:02:58', NULL),
(164, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:04:14', NULL),
(165, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: models/gemini-1.0-pro is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:04:15', NULL),
(166, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:06:32', NULL),
(167, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\nPlease retry in 18.647625673s.', '2026-03-25 10:06:33', NULL),
(168, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hiii', '2026-03-25 10:08:11', NULL),
(169, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 38.618036161s.', '2026-03-25 10:08:13', NULL),
(170, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'student', 'hii', '2026-03-25 10:10:46', NULL),
(171, 'g7v0jgv2crv6q5gtavlmnnjqkk', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash-lite\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash-lite\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash-lite\nPlease retry in 4.036523893s.', '2026-03-25 10:10:47', NULL),
(172, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hello', '2026-03-25 10:33:47', NULL),
(173, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'hello', '2026-03-25 10:33:47', NULL),
(174, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hh', '2026-03-25 10:33:53', NULL),
(175, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'Connection Error: Failed to connect to generativelanguage.googleapis.com port 443 after 21056 ms: Couldn\'t connect to server', '2026-03-25 10:34:14', NULL),
(176, 'ahe0hvffeffr0rufdiin0p5147', 'student', 'hii', '2026-03-25 10:34:34', NULL),
(177, 'ahe0hvffeffr0rufdiin0p5147', 'bot', 'Connection Error: Failed to connect to generativelanguage.googleapis.com port 443 after 21067 ms: Couldn\'t connect to server', '2026-03-25 10:34:56', NULL),
(178, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:43:13', NULL),
(179, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'Google API Error: models/gemini-1.5-flash-lite is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:43:14', NULL),
(180, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:49:58', NULL),
(181, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: API key expired. Please renew the API key.', '2026-03-25 10:49:59', NULL),
(182, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hii', '2026-03-25 10:52:28', NULL),
(183, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: models/gemini-1.5-flash is not found for API version v1, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:52:30', NULL),
(184, 'mpvvoqdbr2h538btnjoot00ob5', 'student', 'hiii', '2026-03-25 10:55:33', NULL),
(185, 'mpvvoqdbr2h538btnjoot00ob5', 'bot', 'System Busy: models/gemini-1.5-flash is not found for API version v1beta, or is not supported for generateContent. Call ListModels to see the list of available models and their supported methods.', '2026-03-25 10:55:34', NULL),
(186, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:01:05', NULL),
(187, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: API key expired. Please renew the API key.', '2026-03-25 11:01:06', NULL),
(188, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:01:41', NULL),
(189, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 7.808758193s.', '2026-03-25 11:01:43', NULL),
(190, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hii', '2026-03-25 11:04:55', NULL),
(191, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 53.965040662s.', '2026-03-25 11:04:56', NULL),
(192, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'habari', '2026-03-25 11:07:25', NULL),
(193, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Hello! I am Vera. How can I help you with your MKU units today?', '2026-03-25 11:07:25', NULL),
(194, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units', '2026-03-25 11:07:29', NULL),
(195, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:29', NULL),
(196, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', '1.1', '2026-03-25 11:07:38', NULL),
(197, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:38', NULL),
(198, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units for 1.1', '2026-03-25 11:07:48', NULL),
(199, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:48', NULL),
(200, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'unit', '2026-03-25 11:07:57', NULL),
(201, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:07:57', NULL),
(202, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units', '2026-03-25 11:08:11', NULL),
(203, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:08:11', NULL),
(204, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'units for 1.1', '2026-03-25 11:08:19', NULL),
(205, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Please specify the year and semester (e.g., \'units for 1.1\').', '2026-03-25 11:08:19', NULL),
(206, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semester', '2026-03-25 11:11:04', NULL),
(207, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'Google API Error: You exceeded your current quota, please check your plan and billing details. For more information on this error, head to: https://ai.google.dev/gemini-api/docs/rate-limits. To monitor your current usage, head to: https://ai.dev/rate-limit. \n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_input_token_count, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\n* Quota exceeded for metric: generativelanguage.googleapis.com/generate_content_free_tier_requests, limit: 0, model: gemini-2.0-flash\nPlease retry in 41.003766428s.', '2026-03-25 11:11:10', NULL),
(208, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semester', '2026-03-25 11:12:19', NULL),
(209, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'next semeter', '2026-03-25 11:14:15', NULL),
(210, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'m not sure about that. I\'ve forwarded your query to the Admin inbox for you!', '2026-03-25 11:14:15', NULL),
(211, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hello', '2026-03-25 11:25:38', NULL),
(212, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'hello', '2026-03-25 11:25:38', NULL),
(213, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'year', '2026-03-25 11:27:35', NULL),
(214, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'yes', '2026-03-25 11:27:35', NULL),
(215, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'yeah', '2026-03-25 11:27:42', NULL),
(216, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'ve already forwarded your previous query to the admin. They will respond soon.', '2026-03-25 11:27:43', NULL),
(217, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'show me second year units', '2026-03-25 11:31:11', NULL),
(218, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'I\'ve already forwarded your previous query to the admin. They will respond soon.', '2026-03-25 11:31:11', NULL),
(219, '3hqo9m6g4e5d15uu6apbfl7e0f', 'student', 'hello', '2026-03-25 11:58:01', NULL),
(220, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', 'hello', '2026-03-25 11:58:01', NULL),
(221, '3hqo9m6g4e5d15uu6apbfl7e0f', 'bot', '<b>📚 Definition of \"faculty\":</b><br><br><b>noun:</b><br>  1. The academic staff at schools, colleges, universities or not-for-profit research institutes, as opposed to the students or support staff.<br>  2. A division of a university.<br>     <i>Example: \"She transferred from the Faculty of Science to the Faculty of Medicine.\"</i><br><br>', '2026-03-25 11:59:04', NULL),
(222, 'a4mj3slja5dm9shqbj5toao5ln', 'student', 'hello', '2026-03-25 13:07:11', NULL),
(223, 'a4mj3slja5dm9shqbj5toao5ln', 'bot', 'hello', '2026-03-25 13:07:11', NULL),
(224, 'rssmginhuvq7t7k27vakdi0ucg', 'student', 'hello', '2026-03-25 18:36:44', NULL),
(225, 'rssmginhuvq7t7k27vakdi0ucg', 'bot', 'hello', '2026-03-25 18:36:44', NULL),
(226, 'rssmginhuvq7t7k27vakdi0ucg', 'student', 'first year', '2026-03-25 18:37:12', NULL),
(227, 'rssmginhuvq7t7k27vakdi0ucg', 'bot', 'yes', '2026-03-25 18:37:12', NULL),
(228, 's47hpo3anjao8jgbok8hqi55d2', 'student', 'hello', '2026-03-26 17:36:16', NULL),
(229, 's47hpo3anjao8jgbok8hqi55d2', 'bot', 'hello', '2026-03-26 17:36:16', NULL),
(230, 'urdavpuq9g7lu8530ijq790ddm', 'student', 'hello', '2026-03-28 06:50:01', NULL),
(231, 'urdavpuq9g7lu8530ijq790ddm', 'bot', 'hello', '2026-03-28 06:50:01', NULL),
(232, 'urdavpuq9g7lu8530ijq790ddm', 'student', 'exams', '2026-03-28 07:19:35', NULL),
(233, 'urdavpuq9g7lu8530ijq790ddm', 'bot', '13th april', '2026-03-28 07:19:35', NULL),
(234, 'vufk6ubh9vervht2u14vbmkesp', 'student', 'hello', '2026-04-01 07:11:47', NULL),
(235, 'vufk6ubh9vervht2u14vbmkesp', 'bot', 'hello', '2026-04-01 07:11:47', NULL),
(236, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'hi', '2026-04-01 10:41:09', 'greet'),
(237, '5uvfjoi60sv9s7douepnhtrfas', 'bot', 'Good afternoon Chepkonga! 🌤️ How can I make your day better today? 😊<br><br>💡 <i>Anything else on your academic journey I can assist with? 🌟</i>', '2026-04-01 10:41:09', 'greet'),
(238, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'units', '2026-04-01 10:41:24', 'my_courses'),
(239, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>Would you like to see the timetable for these courses? 📅</i>', '2026-04-01 10:41:24', 'my_courses'),
(240, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'yes', '2026-04-01 10:41:34', 'view_all'),
(241, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>Want to know which units you should take next semester? 🎯</i><br><br>🎯 <i>The exam period is coming up! 📅 Need help planning your study schedule?</i>', '2026-04-01 10:41:34', 'view_all'),
(242, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'yes', '2026-04-01 10:41:42', 'view_all'),
(243, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>I can show you unit prerequisites if you\'d like! 📚</i>', '2026-04-01 10:41:42', 'view_all'),
(244, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'yes please', '2026-04-01 10:41:51', 'view_all'),
(245, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>Need details about any specific unit? 🔍 I can tell you all about it!</i>', '2026-04-01 10:41:51', 'view_all'),
(246, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'just show me all the units', '2026-04-01 10:42:03', 'view_all'),
(247, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>Need details about any specific unit? 🔍 I can tell you all about it!</i>', '2026-04-01 10:42:03', 'view_all'),
(248, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'baf1101', '2026-04-01 10:42:12', 'view_all'),
(249, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>What else would you like to know about? 🤔 Don\'t be shy!</i>', '2026-04-01 10:42:12', 'view_all'),
(250, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'show me my units', '2026-04-01 10:43:37', 'my_courses'),
(251, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>I can show you unit prerequisites if you\'d like! 📚</i><br><br>🎯 <i>The exam period is coming up! 📅 Need help planning your study schedule?</i>', '2026-04-01 10:43:37', 'my_courses'),
(252, '5uvfjoi60sv9s7douepnhtrfas', 'student', 'yes please do so now', '2026-04-01 10:43:52', 'view_all'),
(253, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<br><br>💡 <i>Need details about any specific unit? 🔍 I can tell you all about it!</i>', '2026-04-01 10:43:52', 'view_all'),
(254, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<b>📚 Definition of \"systems\":</b><br><br><b>noun:</b><br>  1. A collection of organized things; a whole composed of relationships among its members.<br>     <i>Example: \"There are eight planets in the solar system.\"</i><br>  2. A method or way of organizing or planning.<br>     <i>Example: \"Followers should have a system to follow that works in their interests, not against them.\"</i><br><br><b>Synonyms:</b> arrangement, complex, composition, organization, set up<br>', '2026-04-01 10:58:34', NULL),
(255, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<b>📚 Definition of \"algorithm\":</b><br><br><b>noun:</b> A step-by-step procedure for solving a problem or accomplishing a task, especially by a computer.<br><i>Example: \"The sorting algorithm efficiently organized the data in ascending order.\"</i><br>', '2026-04-01 10:58:54', NULL),
(256, '5uvfjoi60sv9s7douepnhtrfas', 'bot', '<b>📚 Definition of \"school\":</b><br><br><b>noun:</b><br>  1. (collective) A group of fish or a group of marine mammals such as porpoises, dolphins, or whales.<br>     <i>Example: \"The divers encountered a huge school of mackerel.\"</i><br>  2. A multitude.<br><br><b>verb:</b><br>  1. (of fish) To form into, or travel in a school.<br><br><b>Synonyms:</b> shoal<br>', '2026-04-01 10:59:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registered_courses`
--

CREATE TABLE `registered_courses` (
  `id` int(11) NOT NULL,
  `student_reg_no` varchar(30) NOT NULL,
  `unit_code` varchar(20) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
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

INSERT INTO `registered_courses` (`id`, `student_reg_no`, `unit_code`, `department`, `exam_type`, `class_group`, `semester`, `academic_year`, `status`, `registered_at`) VALUES
(1, 'BEC/2026/00003', 'BAF1101', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(2, 'BEC/2026/00003', 'BBM1101', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(3, 'BEC/2026/00003', 'BIT1201', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(4, 'BEC/2026/00003', 'BIT3208', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(5, 'BEC/2026/00003', 'BIT4203', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(6, 'BEC/2026/00003', 'BIT3105', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-03-31 20:12:17'),
(8, 'BEC/2026/00003', 'BIT3201', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-04-01 06:38:46'),
(10, 'BIS/2026/00001', 'BAF1101', 'Information Science', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-04-01 06:43:52'),
(11, 'BIT/2026/00005', 'BBM2103', 'Information Technology', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-04-01 07:21:10'),
(12, 'BEC/2026/00003', 'BMA3102', 'Enterprise Computing', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-04-01 10:09:46');

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

--
-- Dumping data for table `survey_responses`
--

INSERT INTO `survey_responses` (`id`, `user_id`, `challenge_type`, `guidance_need`, `chatbot_help`, `ui_experience`, `academic_value`, `ease_rating`, `student_comments`, `submitted_at`) VALUES
(11, 33, 'Prerequisite Info', NULL, 'Mostly Accurate', 'User Friendly', 'Significant', 5, 'The system has helped me to register for my 1st semester units', '2026-04-01 07:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `time_from` varchar(10) DEFAULT NULL,
  `time_to` varchar(10) DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') DEFAULT NULL,
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

INSERT INTO `timetable` (`id`, `unit_code`, `course_title`, `department`, `time_from`, `time_to`, `day_of_week`, `venue`, `unit_group`, `lecturer`, `exam_date`, `semester`, `academic_year`, `created_at`) VALUES
(92, 'BAF1101', 'Financial Accounting I', NULL, '07:00', '10:00', 'Monday', 'CT HALL', 'Class I', 'Mrs. MATHENGE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(93, 'BBM1101', 'Introduction to business studies', NULL, '10:00', '13:00', 'Monday', 'CC1', 'Class I', 'Ms. KHAYALI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(94, 'BBM1201', 'Principles of Management', NULL, '13:00', '16:00', 'Monday', 'FLT HALL A', 'Class I', 'Mrs. MWANGI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(95, 'BBM1202', 'Principles of Marketing', NULL, '16:00', '22:00', 'Monday', 'BL 5', 'Class I', 'Mrs. MWAKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(96, 'BBM2103', 'Organization Behavior', NULL, '07:00', '10:00', 'Monday', 'CC5', 'Class I', 'Ms. KHAYALI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(97, 'BBM3107', 'Human Resource Management', NULL, '10:00', '13:00', 'Monday', 'CT 8.3', 'Class I', 'Mrs. MWANGI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(98, 'BEG2112', 'Digital Electronics and Devices', NULL, '13:00', '16:00', 'Monday', 'COMP LAB 1', 'Class I', 'Mr. KAMAU', NULL, '1', '2026', '2026-03-31 20:08:29'),
(99, 'BIT1101', 'Computer Architecture', NULL, '16:00', '22:00', 'Monday', 'CTA HALL', 'Class I', 'Mr. NYAGA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(100, 'BIT1102', 'Introduction to programming and algorithms', NULL, '07:00', '10:00', 'Monday', 'COMP LAB 2', 'Class I', 'Mr. MURIUKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(101, 'BIT1106', 'Intro to Computer Application Packages', NULL, '10:00', '13:00', 'Monday', 'COMP LAB 3', 'Class I', 'Mr. MURIUKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(102, 'BIT1201', 'Database systems', NULL, '07:00', '10:00', 'Tuesday', 'CC2', 'Class I', 'Mr. OWINO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(103, 'BIT2102', 'Fundamentals of Internet', NULL, '10:00', '13:00', 'Tuesday', 'CC8', 'Class I', 'Mr. MAGATI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(104, 'BIT2205', 'Object oriented programming II', NULL, '13:00', '16:00', 'Tuesday', 'COMP LAB 4', 'Class I', 'Mr. MURIUKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(105, 'BIT2206', 'Systems analysis and design', NULL, '16:00', '22:00', 'Tuesday', 'CTA 6', 'Class I', 'Mr. MURIUKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(106, 'BIT3101', 'Software Engineering', NULL, '07:00', '10:00', 'Tuesday', 'CC3', 'Class I', 'Mr. MASITA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(107, 'BIT3102', 'Event Driven Programming', NULL, '10:00', '13:00', 'Tuesday', 'COMP LAB 5', 'Class I', 'Mr. OWINO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(108, 'BIT3201', 'Object Oriented Analysis and Design', NULL, '13:00', '16:00', 'Tuesday', 'CTA 7', 'Class I', 'Mr. MAGATI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(109, 'BIT3204', 'Network Management', NULL, '16:00', '22:00', 'Tuesday', 'CC4', 'Class I', 'Ms. KIARIE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(110, 'BIT3205', 'Business systems simulation and modeling', NULL, '07:00', '10:00', 'Tuesday', 'CC6', 'Class I', 'Mr. MASITA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(111, 'BIT3206', 'ICT project management', NULL, '10:00', '13:00', 'Tuesday', 'FLT HALL B', 'Class I', 'Ms. KIARIE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(112, 'BIT3208', 'Advanced Web Design, Dev and Mgmt', NULL, '07:00', '10:00', 'Wednesday', 'COMP LAB 1', 'Class I', 'Mrs. NYANSIABOKA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(113, 'BIT4101', 'Business Data Mining and Warehousing', NULL, '10:00', '13:00', 'Wednesday', 'CC1', 'Class I', 'Mrs. MWINJI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(114, 'BIT4102', 'Computer Graphics', NULL, '13:00', '16:00', 'Wednesday', 'COMP LAB 2', 'Class I', 'Mr. OWINO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(115, 'BIT4103', 'Human Computer Interaction', NULL, '16:00', '22:00', 'Wednesday', 'CT 8.3', 'Class I', 'Mrs. MWINJI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(116, 'BIT4104', 'Security and Cryptography', NULL, '07:00', '10:00', 'Wednesday', 'CTA 5', 'Class I', 'Mrs. MWINJI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(117, 'BIT4105', 'Advanced Data Structures and Algorithms', NULL, '10:00', '13:00', 'Wednesday', 'COMP LAB 3', 'Class I', 'Mr. MAGATI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(118, 'BIT4107', 'Mobile Applications Development', NULL, '13:00', '16:00', 'Wednesday', 'COMP LAB 4', 'Class I', 'Mr. OWINO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(119, 'BIT4108', 'Information Systems Audit', NULL, '16:00', '22:00', 'Wednesday', 'CC7', 'Class I', 'Ms. MWAI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(120, 'BIT4201', 'Mobile Computing I', NULL, '07:00', '10:00', 'Wednesday', 'CC8', 'Class I', 'Mr. OWINO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(121, 'BIT4202', 'Artificial Intelligence', NULL, '10:00', '13:00', 'Wednesday', 'FLT HALL A', 'Class I', 'Mr. OKELLO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(122, 'BIT4203', 'Distributed Multimedia Systems', NULL, '07:00', '10:00', 'Thursday', 'CTA HALL', 'Class I', 'Ms. MWAI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(123, 'BIT4204', 'E - Commerce', NULL, '10:00', '13:00', 'Thursday', 'BL 5', 'Class I', 'Mr. WAMBUI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(124, 'BIT4205', 'Network Programming', NULL, '13:00', '16:00', 'Thursday', 'COMP LAB 5', 'Class I', 'Mr. KODHEK', NULL, '1', '2026', '2026-03-31 20:08:29'),
(125, 'BIT4206', 'ICT In Business and Society', NULL, '16:00', '22:00', 'Thursday', 'CT HALL', 'Class I', 'Ms. MWAI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(126, 'BIT4209', 'Distributed Systems', NULL, '07:00', '10:00', 'Thursday', 'CC5', 'Class I', 'Mr. NYAGA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(127, 'BIT4217', 'Total Quality Management for IT', NULL, '10:00', '13:00', 'Thursday', 'CC2', 'Class I', 'Ms. MWAI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(128, 'BMA1104', 'Probability and Statistics I', NULL, '13:00', '16:00', 'Thursday', 'CTA 6', 'Class I', 'Mr. KABUE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(129, 'BMA1106', 'Foundation mathematics', NULL, '16:00', '22:00', 'Thursday', 'CC1', 'Class I', 'Ms. CHEROTICH', NULL, '1', '2026', '2026-03-31 20:08:29'),
(130, 'BMA1202', 'Discrete Mathematics', NULL, '07:00', '10:00', 'Thursday', 'CC3', 'Class I', 'Mr. KEITANY', NULL, '1', '2026', '2026-03-31 20:08:29'),
(131, 'BMA2102', 'Probability and statistics II', NULL, '10:00', '13:00', 'Thursday', 'CC4', 'Class I', 'Ms. CHEROTICH', NULL, '1', '2026', '2026-03-31 20:08:29'),
(132, 'BMA3102', 'Business statistics II', NULL, '07:00', '10:00', 'Friday', 'CC6', 'Class I', 'Mr. CHEGE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(133, 'BMA3201', 'Operation research I', NULL, '10:00', '13:00', 'Friday', 'CC7', 'Class I', 'Mr. KEITANY', NULL, '1', '2026', '2026-03-31 20:08:29'),
(134, 'BUCU007', 'Communication Skills and Academic Writing', NULL, '13:00', '16:00', 'Friday', 'CTA HALL', 'Class I', 'Mrs. AREGE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(135, 'BUCU009', 'Climate Change and Development', NULL, '16:00', '22:00', 'Friday', 'FLT HALL B', 'Class I', 'Mr. MUINDE', NULL, '1', '2026', '2026-03-31 20:08:29'),
(136, 'BUCU011', 'Health Literacy', NULL, '07:00', '10:00', 'Friday', 'CT HALL', 'Class I', 'Mr. OTIENO', NULL, '1', '2026', '2026-03-31 20:08:29'),
(137, 'BUCU008', 'Digital and Information Literacy Skills', NULL, '10:00', '13:00', 'Friday', 'COMP LAB 1', 'Class I', 'Mr. NYAGA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(138, 'BIT3105', 'Management Information Systems', NULL, '13:00', '16:00', 'Friday', 'CC2', 'Class I', 'Mr. MITAKI', NULL, '1', '2026', '2026-03-31 20:08:29'),
(139, 'BIT3224', 'Computing Projects Dev Approaches', NULL, '16:00', '22:00', 'Friday', 'COMP LAB 2', 'Class I', 'Mr. NYAGA', NULL, '1', '2026', '2026-03-31 20:08:29'),
(140, 'BUCU010', 'Entrepreneurial Mindset and Finance', NULL, '07:00', '10:00', 'Friday', 'BL 5', 'Class I', 'Ms. NDEGE', NULL, '1', '2026', '2026-03-31 20:08:29');

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
(30, 'Chepkonga', 'BEC/2026/00003', 'novrah4g@gmail.com', '$2y$10$OVp3ZSt4QeD0m1diYkBmdeZJ4QuK8jN9Fh5n3hYBnl2ysoACZYFUS', 'student', 'Enterprise Computing', '2026-03-25 06:02:57', 1),
(33, 'Chepchieng Noah', 'BIT/2026/00005', 'veramichael678@gmail.com', '$2y$10$HDIZVtQDe3GxqEoAoNRiIOiAH95HbECQgCB2JEPBNnnLaKbpCwjgq', 'student', 'Information Technology', '2026-04-01 07:10:08', 1),
(35, 'Noah chep', 'BBM/2026/00006', 'noahchep1@gmail.com', '$2y$10$ApEYH3mh0fcxImyA0ZdttO/KfIIakBPchD99T5Sl/zzuxtzZS5sTq', 'student', 'Management', '2026-04-01 11:42:43', 0);

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
(21, 'faculty', 'noun', '📚 Definition of \"faculty\":noun: 1. The academic staff at schools, colleges, universities or not-for-profit research institutes, as opposed to the students or support staff. 2. A division of a university.', 'She transferred from the Faculty of Science to the Faculty of Medicine.', '', 'general', '2026-03-25 11:59:04'),
(22, 'systems', 'noun', '📚 Definition of \"systems\":noun: 1. A collection of organized things; a whole composed of relationships among its members. 2. A method or way of organizing or planning.', 'There are eight planets in the solar system.', 'arrangement, complex, composition, organization, set up', 'general', '2026-04-01 10:58:34'),
(23, 'school', 'noun', '📚 Definition of \"school\":noun: 1. (collective) A group of fish or a group of marine mammals such as porpoises, dolphins, or whales. 2. A multitude.verb: 1. (of fish) To form into, or travel in a school.', 'The divers encountered a huge school of mackerel.', 'shoal', 'general', '2026-04-01 10:59:17');

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
(1, '?', 'Vera Michael', '2026-03-25 11:57:02', 0),
(2, '?', 'Chepkonga', '2026-03-31 20:41:41', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=257;

--
-- AUTO_INCREMENT for table `registered_courses`
--
ALTER TABLE `registered_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `survey_responses`
--
ALTER TABLE `survey_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `vocabulary`
--
ALTER TABLE `vocabulary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `vocabulary_requests`
--
ALTER TABLE `vocabulary_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
