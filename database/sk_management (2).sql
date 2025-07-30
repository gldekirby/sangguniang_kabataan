-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 03:57 AM
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
-- Database: `sk_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_expenses`
--

CREATE TABLE `admin_expenses` (
  `admin_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `expense_type` varchar(50) NOT NULL,
  `vendor` varchar(100) DEFAULT NULL,
  `service_period` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_expenses`
--

INSERT INTO `admin_expenses` (`admin_id`, `transaction_id`, `expense_type`, `vendor`, `service_period`) VALUES
(1, 5, '', 'dsxcvdfc', 'dsxcvbzf');

-- --------------------------------------------------------

--
-- Table structure for table `budget_allocations`
--

CREATE TABLE `budget_allocations` (
  `budget_id` int(11) NOT NULL,
  `category` enum('equipment','travel','staff','facilities','admin','other') NOT NULL,
  `fiscal_year` varchar(10) NOT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_allocations`
--

INSERT INTO `budget_allocations` (`budget_id`, `category`, `fiscal_year`, `allocated_amount`, `notes`) VALUES
(1, 'equipment', '2024-2025', 4500.00, 'Team gear and training equipment');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `transaction_id`, `item_name`, `quantity`, `unit_price`, `supplier`) VALUES
(1, 1, 'Soccer Balls', 10, 50.00, 'Sports World'),
(3, 4, 'Casket - Premium', 20, 30.00, 'asbgfndf');

-- --------------------------------------------------------

--
-- Table structure for table `facility_costs`
--

CREATE TABLE `facility_costs` (
  `facility_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `venue_name` varchar(100) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `duration_hours` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `transaction_id` int(11) NOT NULL,
  `category` enum('equipment','travel','staff','facilities','admin','other') NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`transaction_id`, `category`, `description`, `amount`, `transaction_date`, `created_at`) VALUES
(1, 'equipment', 'Soccer balls and cones', 750.00, '2024-03-15', '2025-04-09 15:47:59'),
(3, 'travel', 'zxvczdfbdfb', 3000.00, '2025-04-10', '2025-04-09 16:38:51'),
(4, 'equipment', 'acvzdbvc', 243.00, '2025-04-10', '2025-04-09 16:40:07'),
(5, 'admin', 'sfdzs', 3545.00, '2025-04-10', '2025-04-09 16:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `funds`
--

CREATE TABLE `funds` (
  `fund_id` int(11) NOT NULL,
  `source` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `received_date` date NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `funds`
--

INSERT INTO `funds` (`fund_id`, `source`, `amount`, `received_date`, `purpose`, `notes`) VALUES
(1, 'Donation', 30000.00, '2025-04-10', 'sdsfnxmchjh', 'dzfbfgcbdfc');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `firstname`, `middlename`, `lastname`, `address`, `age`, `gender`, `contact_number`, `email`, `username`, `password`, `photo`, `created_at`, `status`) VALUES
(20, 'KIRBY JAY', 'SERVIDAD', 'GELDORE', 'Prk. 2 Poblacion', 21, 'male', '09361102342', 'krb1@gmail.com', 'kirby', '1234', 'uploads_members/01 seven falls lake sebu.jpg', '2025-04-08 14:44:04', 'active'),
(21, 'Gwapo', 'Sanchez', 'Servidad', 'Prk. 2 Poblacion', 68, 'female', '09361102342', 'marg2@gmail.com', 'marguaxxx', '1234', 'uploads_members/3-Login-Page-Screen.jpg', '2025-04-08 15:47:30', 'inactive'),
(22, 'Krystal', 'S.', 'Ventura', 'Prk. 3 Poblacion', 21, 'female', '09261423022', 'sarahjean@gmail.com', 'krystal', '1234', 'uploads_members/7falls.jpg', '2025-04-09 23:14:19', 'inactive'),
(23, 'Ramser', 'K', 'Kudtugan', 'Prk. 13 poblacion', 19, 'male', '09518021806', 'kudtugan@gmail.com', 'ramser19', '1234', 'uploads_members/01 seven falls lake sebu.jpg', '2025-04-10 01:04:20', 'inactive'),
(24, 'Justine', 'C', 'Bayate', 'Prk 3 Poblacion', 20, 'male', '09922343075', 'Bayate@gmail.com', 'BayJust', '1234', 'uploads_members/7falls.jpg', '2025-04-10 01:14:19', 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sender_name` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `other_expenses`
--

CREATE TABLE `other_expenses` (
  `other_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `expense_type` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_events`
--

CREATE TABLE `schedule_events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_events`
--

INSERT INTO `schedule_events` (`event_id`, `event_name`, `start_date`, `end_date`, `event_time`, `location`, `description`) VALUES
(30, 'Program', '2025-04-10', '2025-04-11', '11:15:00', 'Poblacion', 'samkldsnvdsivn'),
(31, 'Program', '2025-04-16', '2025-04-20', '10:26:00', 'poblacion gym', 'sports larung pinoy');

-- --------------------------------------------------------

--
-- Table structure for table `staff_payments`
--

CREATE TABLE `staff_payments` (
  `payment_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `staff_name` varchar(100) NOT NULL,
  `role` varchar(50) NOT NULL,
  `payment_period` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `travel_expenses`
--

CREATE TABLE `travel_expenses` (
  `travel_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `trip_purpose` varchar(100) NOT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `participants` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_expenses`
--

INSERT INTO `travel_expenses` (`travel_id`, `transaction_id`, `trip_purpose`, `destination`, `start_date`, `end_date`, `participants`) VALUES
(1, 3, 'esdgdf', 'sdgdfhdf', '2025-04-11', '2025-04-23', 23);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_expenses`
--
ALTER TABLE `admin_expenses`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `facility_costs`
--
ALTER TABLE `facility_costs`
  ADD PRIMARY KEY (`facility_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `funds`
--
ALTER TABLE `funds`
  ADD PRIMARY KEY (`fund_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `other_expenses`
--
ALTER TABLE `other_expenses`
  ADD PRIMARY KEY (`other_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `schedule_events`
--
ALTER TABLE `schedule_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `travel_expenses`
--
ALTER TABLE `travel_expenses`
  ADD PRIMARY KEY (`travel_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_expenses`
--
ALTER TABLE `admin_expenses`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `budget_allocations`
--
ALTER TABLE `budget_allocations`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `facility_costs`
--
ALTER TABLE `facility_costs`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `funds`
--
ALTER TABLE `funds`
  MODIFY `fund_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `other_expenses`
--
ALTER TABLE `other_expenses`
  MODIFY `other_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_events`
--
ALTER TABLE `schedule_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `staff_payments`
--
ALTER TABLE `staff_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel_expenses`
--
ALTER TABLE `travel_expenses`
  MODIFY `travel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_expenses`
--
ALTER TABLE `admin_expenses`
  ADD CONSTRAINT `admin_expenses_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `facility_costs`
--
ALTER TABLE `facility_costs`
  ADD CONSTRAINT `facility_costs_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `other_expenses`
--
ALTER TABLE `other_expenses`
  ADD CONSTRAINT `other_expenses_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD CONSTRAINT `staff_payments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `travel_expenses`
--
ALTER TABLE `travel_expenses`
  ADD CONSTRAINT `travel_expenses_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
