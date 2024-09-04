-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2024 at 05:13 AM
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
-- Database: `leave_golac`
--

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dept_id` int(11) NOT NULL,
  `dept_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`dept_id`, `dept_name`) VALUES
(1, 'INFORMATION TECHNOLOGY'),
(2, 'HR & ADMIN'),
(3, 'OPERATION'),
(5, 'SALES'),
(6, 'MARKETING'),
(7, 'FINANCE');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employee_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `dept_id` int(10) NOT NULL,
  `position_name` varchar(50) NOT NULL,
  `role` enum('Admin','HR','Staff') NOT NULL,
  `join_date` varchar(15) NOT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employee_id`, `name`, `password`, `gender`, `dept_id`, `position_name`, `role`, `join_date`, `status`) VALUES
('glc000', 'aman', '$2y$10$GbeXtRaQB9GtjS7iKYxrLOIIlJEs12AncBc2Rg504uWA/kGWTB.T.', 'Male', 1, 'test', 'Staff', '01 August 2024', 'Online'),
('GLC032', 'AFWAN', '$2y$10$.m8vw.jJZ9AUPQBzsojFPOi84M5hDO7QaaWrIV74FuUUVRSKFE8tO', 'Male', 6, 'MANAGER', 'Staff', '02 April 2024', 'Offline'),
('GLC066', 'rahmad', '12345', 'Male', 3, 'exec', 'Staff', '15 August 2024', 'Online'),
('GLI001', 'MOHAMAD SHUKRI ', '$2y$10$GDMUFSVDikq9Ej.br8Q5g.9khPPLY00bbAddkEPjy8oVbAr/.Xmue', 'Male', 2, 'EXECUTIVE', 'HR', '09 May 2022', 'Online'),
('GLI021', 'NABIL FIKRI', '1234abc', 'Male', 1, 'GRAPHIC DESIGNER', 'Staff', '06 February 202', 'Online'),
('GLI041', 'SILMI ABSHAR BIN KAHARUDDIN', '$2y$10$GDMUFSVDikq9Ej.br8Q5g.9khPPLY00bbAddkEPjy8oVbAr/.Xmue', 'Male', 1, 'INTERN IN IT & WEB DEVELOPER', 'Staff', '22 April 2024', 'Online'),
('GLI042', 'AYUNI', '$2y$10$qfw6jwC6tr8lVeAZOPwrbeyoH2eoLEVoNsGxdUIcAoYQpY4qwmCz2', 'Female', 6, 'INTERN IN MARKETING ', 'Staff', '15 July 2024', 'Online');

-- --------------------------------------------------------

--
-- Table structure for table `leave_request`
--

CREATE TABLE `leave_request` (
  `leave_request_id` varchar(10) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `start_date` varchar(50) NOT NULL,
  `end_date` varchar(50) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `status_hr` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `posting_date` date NOT NULL,
  `applied_leave` int(11) DEFAULT NULL,
  `medical_document` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_request`
--

INSERT INTO `leave_request` (`leave_request_id`, `employee_id`, `start_date`, `end_date`, `leave_type_id`, `status_hr`, `reason`, `posting_date`, `applied_leave`, `medical_document`) VALUES
('GLC007', 'glc000', '31 August 2024', '04 September 2024', 1, 2, 'test', '2024-08-28', 5, ''),
('GLC008', 'glc000', '04 September 2024', '07 September 2024', 5, 1, 'beranak', '2024-08-28', 4, ''),
('GLC009', 'glc000', '31 August 2024', '30 September 2024', 2, 2, 'test', '2024-08-28', 31, ''),
('GLC010', 'glc000', '28 August 2024', '28 August 2024', 3, 1, 'test', '2024-08-28', 1, 'G.png'),
('GLC011', 'GLC066', '25 September 2024', '25 September 2024', 1, 2, 'HOLIDAY', '2024-08-28', 4, ''),
('LR012', 'GLI041', '09 September 2024', '12 September 2024', 2, 1, 'Balik Rumah ', '2024-08-29', 4, ''),
('LR013', 'GLI041', '12 September 2024', '14 September 2024', 1, 1, 'Menghadiri program temubual bersama MBOT', '2024-08-30', 3, 'KOLEJ VOKASIONAL GERIK.pdf'),
('LR014', 'GLI041', '17 September 2024', '17 September 2024', 12, 0, 'Appoinment doctor', '2024-09-01', 1, ''),
('LR015', 'GLI001', '17 September 2024', '20 September 2024', 1, 0, 'Healing', '2024-09-01', 4, '1.0 Leave Application Form. LATEST.docx_page-0001.jpg'),
('LR016', 'GLI001', '17 September 2024', '20 September 2024', 1, 1, 'Healing', '2024-09-01', 4, ''),
('LR017', 'GLI001', '17 September 2024', '20 September 2024', 3, 1, 'rehat sat', '2024-09-01', 4, 'E-leave.png'),
('LR018', 'GLI041', '12 September 2024', '14 September 2024', 3, 0, 'Healing wxy', '2024-09-01', 3, 'GOLAC COMMERCE ORGANIZATION CHART.docx_page-0001.jpg'),
('LR019', 'GLI041', '10 September 2024', '12 September 2024', 1, 0, 'healing', '2024-09-01', 3, ''),
('LR020', 'GLI001', '12 September 2024', '14 September 2024', 1, 1, 'g5tguvuu', '2024-09-01', 3, 'WhatsApp Image 2024-08-06 at 13.48.50_1f00c117.jpg'),
('LR021', 'GLI001', '05 September 2024', '07 September 2024', 1, 2, 'Bini bersalin', '2024-09-01', 3, '1.0 Leave Application Form. LATEST.docx_page-0001.jpg'),
('LR022', 'GLI041', '09 September 2024', '13 September 2024', 5, 0, 'bersalin', '2024-09-02', 5, ''),
('LR023', 'GLI001', '06 September 2024', '13 September 2024', 3, 1, 'Tamat OJT', '2024-09-03', 8, '../uploads/SILMI_ABSHAR_BIN_KAHARUDDIN_leave_application.pdf'),
('LR024', 'GLI001', '06 September 2024', '07 September 2024', 11, 0, ' n  tbtbrg', '2024-09-03', 2, '../uploads/E-leave-White.png');

-- --------------------------------------------------------

--
-- Table structure for table `leave_type`
--

CREATE TABLE `leave_type` (
  `leave_type_id` int(11) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `total_leave` decimal(11,0) NOT NULL,
  `new_balance` decimal(11,0) NOT NULL,
  `total_apply` decimal(11,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_type`
--

INSERT INTO `leave_type` (`leave_type_id`, `type_name`, `total_leave`, `new_balance`, `total_apply`) VALUES
(1, 'Annual Leave', 8, 8, 0),
(2, 'Unpaid Leave', 0, 0, 5),
(3, 'Medical Leave', 14, 14, 0),
(4, 'Maternity Leave', 98, 98, 0),
(5, 'Paternity Leave', 7, 7, 0),
(11, 'Other', 0, 0, 0),
(12, 'Halfday Leave', 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `position_id` (`position_name`);

--
-- Indexes for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD PRIMARY KEY (`leave_request_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `leave_type_id` (`leave_type_id`),
  ADD KEY `total_leave` (`applied_leave`);

--
-- Indexes for table `leave_type`
--
ALTER TABLE `leave_type`
  ADD PRIMARY KEY (`leave_type_id`),
  ADD KEY `leave_type_id` (`leave_type_id`,`type_name`,`total_leave`,`new_balance`,`total_apply`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `leave_type`
--
ALTER TABLE `leave_type`
  MODIFY `leave_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`);

--
-- Constraints for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD CONSTRAINT `leave_request_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_type` (`leave_type_id`),
  ADD CONSTRAINT `leave_request_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employee` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
