-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 12:05 PM
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
-- Database: `youth_sk`
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
(17, 29, 'kirby', 2, 200.00, 'kikay');

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
  `category` enum('equipment','staff','facilities','admin','other') NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`transaction_id`, `category`, `description`, `amount`, `transaction_date`) VALUES
(1, 'staff', 'Initial transaction', 1000.00, '2025-04-28'),
(29, 'equipment', 'Equipment purchase: kirby', 400.00, '2025-04-28');

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
(1, 'Donation', 30000.00, '2025-04-10', 'sdsfnxmchjh', 'dzfbfgcbdfc'),
(2, 'supervisor', 10000.00, '2025-04-20', 'donation', '');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive' COMMENT 'active=online, inactive=offline',
  `civil_status` varchar(50) DEFAULT NULL,
  `work_status` varchar(50) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `parent_last_name` varchar(100) DEFAULT NULL,
  `parent_first_name` varchar(100) DEFAULT NULL,
  `parent_middle_name` varchar(100) DEFAULT NULL,
  `parent_relationship` varchar(50) DEFAULT NULL,
  `parent_contact` varchar(15) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `education_level` enum('Elementary','Junior High School','Senior High School','College','Vocational') DEFAULT NULL,
  `year_level` varchar(50) DEFAULT NULL,
  `emergency_name` varchar(255) DEFAULT NULL,
  `emergency_relationship` varchar(100) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `id_photo` varchar(255) DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `residence_certificate` varchar(255) DEFAULT NULL,
  `last_updated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `status1` enum('pending','approved','denied') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `first_name`, `middle_name`, `last_name`, `age`, `gender`, `contact_number`, `email`, `username`, `password`, `created_at`, `status`, `civil_status`, `work_status`, `position`, `dob`, `place_of_birth`, `social_media`, `street`, `barangay`, `city`, `province`, `parent_last_name`, `parent_first_name`, `parent_middle_name`, `parent_relationship`, `parent_contact`, `school`, `education_level`, `year_level`, `emergency_name`, `emergency_relationship`, `emergency_contact`, `id_photo`, `birth_certificate`, `residence_certificate`, `last_updated`, `status1`) VALUES
(4, 'kirby Jay', 'Servidad', 'Geldore', 21, 'Male', '9361102342', 'kirbygeldore19@gmail.com', 'Gldekirby', '1234', '2025-04-21 03:07:34', 'inactive', 'Single', 'Student', 'Sk Member', '2003-05-17', 'Purok 3 Mahayahay, Katualan, Panabo City, Davao Del Norte', 'krizthyl geldore', 'Purok 2', 'Poblacion', 'Tupi', 'South Cotabato', 'Servidad', 'krizthyl', 'Sanchez', 'auntie', '09361190423', 'Bachelor Science of Hospitality Management', 'College', '2nd Year', 'Emerald Sanchez Servidad', 'auntie', '09361190423', 'uploads/id_photos/6805b676a7f37.jfif', 'uploads/birth_certs/6805b676aa449.jfif', 'uploads/residence_certs/6805b676ac478.jfif', '2025-05-01 03:16:54', 'approved'),
(5, 'krizthyl', 'Servidad', 'Geldore', 20, 'Female', '9261423025', 'krizthyl20@gmail.com', 'krizthyl3', '1234', '2025-04-21 03:38:07', 'inactive', 'Single', 'Student', 'Sk Member', '2004-07-13', 'Purok 3 Mahayahay, Katualan, Panabo City, Davao Del Norte', 'krizthyl geldore', 'Purok 2', 'Poblacion', 'Tupi', 'South Cotabato', 'Servidad', 'krizthyl', 'Servidad', 'auntie', '09361190423', 'Bachelor Science of Hospitality Management', 'College', '2nd Year', 'Emerald Sanchez Servidad', 'auntie', '09361190423', 'uploads/id_photos/6805bd9f3a195.jfif', 'uploads/birth_certs/6805bd9f3db69.jfif', 'uploads/residence_certs/6805bd9f41594.jfif', '2025-05-05 04:26:43', 'approved'),
(6, 'krizthyl', 'Sanchez', 'Geldore', 20, 'Female', '09361190232', 'kirbygeldore10@gmail.com', 'krizthyl2', '1234', '2025-04-21 03:40:45', 'inactive', 'Single', 'Student', 'Sk Member', '2004-07-13', 'Purok 3 Mahayahay, Katualan, Panabo City, Davao Del Norte', 'krizthyl geldore', 'Purok 2', 'Poblacion', 'Tupi', 'South Cotabato', 'Servidad', 'krizthyl', 'Sanchez', 'auntie', '09361190423', 'Bachelor Science of Hospitality Management', 'College', '2nd Year', 'Emerald Sanchez Servidad', 'auntie', '09361190423', 'uploads/id_photos/6805be3da7a0f.jfif', 'uploads/birth_certs/6805be3da9f4a.jfif', 'uploads/residence_certs/6805be3dac84b.jfif', '2025-05-05 04:26:27', 'approved'),
(7, 'Marguax', 'Servidad', 'Servidad', 9, 'Female', '09361190232', 'kirbygeldore22@gmail.com', 'krizthyl1', '1234', '2025-04-21 03:59:45', 'inactive', 'Single', 'Student', 'Sk Member', '2015-07-21', 'Purok 3 Poblacion, Tupi, South Cotabato', 'Marguax geldore', 'Purok 3', 'Poblacion', 'Tupi', 'South Cotabato', 'Servidad', 'Marguax', 'Servidad', 'Auntie', '09361102342', 'Bachelor Science of Hospitality Management', 'Junior High School', 'Grade 7', 'Emerald Sanchez Servidad', 'Auntie', '09361102342', 'uploads/id_photos/6805c2b182df9.jpg', 'uploads/birth_certs/6805c2b185ebc.jpg', 'uploads/residence_certs/6805c2b188a13.jpg', '2025-05-01 09:19:16', 'approved'),
(8, 'Marguax', 'Servidad', 'Servidad', 10, 'Female', '09361190232', 'kirbygeldore23@gmail.com', 'krizthyl4', '1234', '2025-04-21 04:12:02', 'inactive', 'Single', 'Student', 'Sk Member', '2014-07-21', 'Purok 3 Poblacion, Tupi, South Cotabato', 'Marguax geldore', 'Purok 4', 'Poblacion', 'Tupi', 'South Cotabato', 'Servidad', 'Marguax', 'Servidad', 'Auntie', '09361102342', 'Bachelor Science of Hospitality Management', 'Junior High School', 'Grade 7', 'Emerald Sanchez Servidad', 'Auntie', '09361102342', 'uploads/id_photos/6805c591f1e9d.jpg', 'uploads/birth_certs/6805c59200465.jpg', 'uploads/residence_certs/6805c59208427.jpg', '2025-05-05 04:27:01', 'denied');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `member_id`, `sender_name`, `message`, `is_read`, `created_at`) VALUES
(1, 4, 'Admin', 'Hi kirby Jay! 📅 You\'re invited to the event: \"Seminar\" on 2025-05-13 to 2025-05-14 at 10:30 in Prairieland Park, Saskatoon54g5.\n\nEvent Details:\n k km ,m m, mm,\n\nSee you there!', 0, '2025-05-01 06:29:29'),
(2, 5, 'Admin', 'Hi krizthyl! 📅 You\'re invited to the event: \"Seminar\" on 2025-05-13 to 2025-05-14 at 10:30 in Prairieland Park, Saskatoon54g5.\n\nEvent Details:\n k km ,m m, mm,\n\nSee you there!', 0, '2025-05-01 06:29:30'),
(3, 6, 'Admin', 'Hi krizthyl! 📅 You\'re invited to the event: \"Seminar\" on 2025-05-13 to 2025-05-14 at 10:30 in Prairieland Park, Saskatoon54g5.\n\nEvent Details:\n k km ,m m, mm,\n\nSee you there!', 0, '2025-05-01 06:29:30'),
(4, 7, 'Admin', 'Hi Marguax! 📅 You\'re invited to the event: \"Seminar\" on 2025-05-13 to 2025-05-14 at 10:30 in Prairieland Park, Saskatoon54g5.\n\nEvent Details:\n k km ,m m, mm,\n\nSee you there!', 0, '2025-05-01 06:29:31'),
(5, 8, 'Admin', 'Hi Marguax! 📅 You\'re invited to the event: \"Seminar\" on 2025-05-13 to 2025-05-14 at 10:30 in Prairieland Park, Saskatoon54g5.\n\nEvent Details:\n k km ,m m, mm,\n\nSee you there!', 0, '2025-05-01 06:29:31'),
(6, 4, 'Admin', 'bayot kah', 0, '2025-05-01 09:19:58'),
(7, 5, 'Admin', 'kikay🥰', 0, '2025-05-01 09:41:57'),
(8, 5, 'Admin', 'kikay🥰', 0, '2025-05-01 09:42:16'),
(9, 8, 'Admin', 'sancisancsa', 0, '2025-05-01 09:54:46'),
(10, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:56:12'),
(11, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:56:13'),
(12, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:56:14'),
(13, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:56:14'),
(14, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:56:14'),
(15, 4, 'Admin', 'svbgnfggf', 0, '2025-05-01 09:59:16'),
(16, 4, 'Admin', 'vdfvdfbdf', 0, '2025-05-01 10:01:17'),
(17, 4, 'Admin', 'vdfvdfbdf', 0, '2025-05-01 10:01:18'),
(18, 4, 'Admin', 'vdfvdfbdf', 0, '2025-05-01 10:01:18'),
(19, 4, 'Admin', 'vdfvdfbdf', 0, '2025-05-01 10:01:18'),
(20, 4, 'Admin', 'vdfvdfbdf', 0, '2025-05-01 10:01:18'),
(21, 4, 'Admin', 'hello', 0, '2025-05-01 10:04:25');

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
(56, 'Understanding Drugs: Impact, Awareness, and Prevention', '2025-05-02', '2025-05-08', '10:00:00', 'Prairieland Park, Saskatoon54g5', 'bajsbkkdcjk zd'),
(57, 'Seminar', '2025-05-13', '2025-05-14', '10:30:00', 'Prairieland Park, Saskatoon54g5', ' k km ,m m, mm,');

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

--
-- Dumping data for table `staff_payments`
--

INSERT INTO `staff_payments` (`payment_id`, `transaction_id`, `staff_name`, `role`, `payment_period`) VALUES
(6, 1, 'kirby', 'ncaklsn', '2300'),
(7, 1, 'kirby', 'ncaklsn', '2300'),
(9, 1, 'kikay', 'ncaklsn', '2300');

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
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
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
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `facility_costs`
--
ALTER TABLE `facility_costs`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `funds`
--
ALTER TABLE `funds`
  MODIFY `fund_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `other_expenses`
--
ALTER TABLE `other_expenses`
  MODIFY `other_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_events`
--
ALTER TABLE `schedule_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `staff_payments`
--
ALTER TABLE `staff_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- Constraints for table `other_expenses`
--
ALTER TABLE `other_expenses`
  ADD CONSTRAINT `other_expenses_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);

--
-- Constraints for table `staff_payments`
--
ALTER TABLE `staff_payments`
  ADD CONSTRAINT `staff_payments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `financial_transactions` (`transaction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
