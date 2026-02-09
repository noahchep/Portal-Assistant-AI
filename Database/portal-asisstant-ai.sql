-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 11:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(15, 'ADMIN/001', 'BIT2026', 'Regular', 'Day', 'Jan/Apr', '2026', 'Confirmed', '2026-02-03 13:08:23');

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
(10, 'BAF1101', 'Financial Accounting I ', '07:00', '10:00', 'CC1', 'CLASS 1', 'Mrs. MATHENGE  ', '0000-00-00', '', '2026', '2026-02-02 06:47:46');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `reg_number`, `email`, `password`, `role`, `department`, `created_at`) VALUES
(1, 'System Admin', 'ADMIN/001', 'admin@mku.ac.ke', '$2y$10$P.VKg4sPX1yHxleIOEwf1OKlHbYUWXlERdv.GC4clNTvCJWjwS5uG', 'admin', '', '2026-01-18 19:01:19'),
(21, 'Noah  Chepkonga', 'BIT/2026/0001', 'novrah4g@gmail.com', '$2y$10$NvoWE/x6V5/MQ2kbnHknWebAS872q/gcj7L0gukzBvXT9gGsRmycq', 'student', 'Information Technology', '2026-02-02 08:31:07'),
(23, 'Noah  Chepkonga', 'BIT/2026/0002', 'noahchep1@gmail.com', '$2y$10$lhqDf8T0GoKaxX8sJZxx6.QwGEOBdcnw9ywNgbA8BuW0dEFvVqi3m', 'student', 'Information Technology', '2026-02-02 08:42:12'),
(24, 'My Love  Vera', 'BIT/2026/0003', 'veramichael678@gmail.com', '$2y$10$vZfLBvVjwDg5aQsDIemsLuOD9Qrn2IueA72JV4ls0PRPYPH5ezbI.', 'student', 'Information Technology', '2026-02-02 08:46:29');

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
-- Indexes for table `registered_courses`
--
ALTER TABLE `registered_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_student_unit` (`student_reg_no`,`unit_code`),
  ADD KEY `fk_registered_unit` (`unit_code`);

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
-- AUTO_INCREMENT for table `registered_courses`
--
ALTER TABLE `registered_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registered_courses`
--
ALTER TABLE `registered_courses`
  ADD CONSTRAINT `fk_registered_unit` FOREIGN KEY (`unit_code`) REFERENCES `timetable` (`unit_code`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
