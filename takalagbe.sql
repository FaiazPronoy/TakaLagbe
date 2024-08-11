-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 23, 2024 at 07:25 AM
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
-- Database: `takalagbe`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `phone` varchar(11) NOT NULL,
  `password` varchar(32) NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education_fee`
--

CREATE TABLE `education_fee` (
  `id` int(11) NOT NULL,
  `institute_id` int(11) NOT NULL,
  `institute_name` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electricity_bills`
--

CREATE TABLE `electricity_bills` (
  `id` int(11) NOT NULL,
  `organization_id` varchar(255) NOT NULL,
  `organization_name` varchar(30) NOT NULL,
  `meter_no` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `month` varchar(255) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gas_bills`
--

CREATE TABLE `gas_bills` (
  `id` int(11) NOT NULL,
  `organization_id` varchar(255) NOT NULL,
  `organization_name` varchar(30) NOT NULL,
  `customer_no` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `month` varchar(20) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `historyid` int(11) NOT NULL,
  `transaction_from` varchar(30) NOT NULL,
  `transaction_to` varchar(30) NOT NULL,
  `transaction_type` varchar(20) NOT NULL,
  `transaction_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institutes`
--

CREATE TABLE `institutes` (
  `id` int(11) NOT NULL,
  `institute_id` varchar(255) NOT NULL,
  `institute_name` varchar(255) NOT NULL,
  `total_received_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institutes`
--

INSERT INTO `institutes` (`id`, `institute_id`, `institute_name`, `total_received_amount`) VALUES
(1, '555', 'BRAC University', 2223.00),
(2, '556', 'BUET', 0.00),
(3, '557', 'NotreDame College', 0.00),
(4, '558', 'National Ideal College', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `internet_bills`
--

CREATE TABLE `internet_bills` (
  `id` int(11) NOT NULL,
  `organization_id` varchar(255) NOT NULL,
  `organization_name` varchar(30) NOT NULL,
  `customer_no` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `month` varchar(255) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `organization_id` varchar(255) NOT NULL,
  `organization_name` varchar(255) NOT NULL,
  `total_received_amount` decimal(10,2) NOT NULL,
  `billtype` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `organization_id`, `organization_name`, `total_received_amount`, `billtype`) VALUES
(1, '111', 'Titas Gas Limited', 1125.00, 'gas_bills'),
(2, '112', 'Karnaphuli Gas Limited', 0.00, 'gas_bills'),
(3, '113', 'Petrobangla Gas Limited', 0.00, 'gas_bills'),
(4, '114', 'Bakhrabad Gas Limited', 0.00, 'gas_bills'),
(5, '222', 'Dhaka Wasa', 100.00, 'water_bills'),
(6, '223', 'Chattogram Wasa', 0.00, 'water_bills'),
(7, '224', 'Khulna Wasa', 0.00, 'water_bills'),
(8, '225', 'Rajshahi Wasa', 0.00, 'water_bills'),
(9, '333', 'Bnet', 1000.00, 'internet_bills'),
(10, '334', 'X-press Technologies', 0.00, 'internet_bills'),
(11, '335', 'Sam Online', 0.00, 'internet_bills'),
(12, '336', 'Exord Online', 0.00, 'internet_bills'),
(13, '444', 'DPDC', 1000.00, 'electricity_bills'),
(14, '445', 'DESCO', 0.00, 'electricity_bills'),
(15, '446', 'Palli Bidyut', 0.00, 'electricity_bills'),
(16, '447', 'BPDB', 0.00, 'electricity_bills');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int(11) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `nid` bigint(18) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` bigint(6) NOT NULL,
  `dob` date NOT NULL,
  `male` tinyint(1) NOT NULL,
  `female` tinyint(1) NOT NULL,
  `others` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `water_bills`
--

CREATE TABLE `water_bills` (
  `id` int(11) NOT NULL,
  `organization_id` varchar(255) NOT NULL,
  `organization_name` varchar(30) NOT NULL,
  `customer_no` bigint(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `month` varchar(20) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------


--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_phone` varchar(255) NOT NULL,
  `receiver_phone` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_phone` varchar(20) DEFAULT NULL,
  `issue_solved` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------


CREATE TABLE agent_details (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id VARCHAR(255) NOT NULL,
    phone_number VARCHAR(255) NOT NULL,
    cash_in_amount DECIMAL(10, 2) NOT NULL
);


--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`phone`);

--
-- Indexes for table `education_fee`
--
ALTER TABLE `education_fee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `electricity_bills`
--
ALTER TABLE `electricity_bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gas_bills`
--
ALTER TABLE `gas_bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`historyid`);

--
-- Indexes for table `institutes`
--
ALTER TABLE `institutes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `internet_bills`
--
ALTER TABLE `internet_bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`phone`),
  ADD UNIQUE KEY `userid` (`userid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nid` (`nid`);

--
-- Indexes for table `water_bills`
--
ALTER TABLE `water_bills`
  ADD PRIMARY KEY (`id`);


--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);


--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `education_fee`
--
ALTER TABLE `education_fee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `electricity_bills`
--
ALTER TABLE `electricity_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gas_bills`
--
ALTER TABLE `gas_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `historyid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `institutes`
--
ALTER TABLE `institutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `internet_bills`
--
ALTER TABLE `internet_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=337;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `water_bills`
--
ALTER TABLE `water_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;




/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
