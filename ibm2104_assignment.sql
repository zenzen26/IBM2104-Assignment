-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2020 at 09:50 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ibm2104_assignment`
--
CREATE DATABASE IF NOT EXISTS `ibm2104_assignment` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ibm2104_assignment`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `payment_dateTime` datetime NOT NULL DEFAULT current_timestamp(),
  `transaction_no` varchar(50) NOT NULL,
  `account_card_no` varchar(50) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `payment_type` enum('OnlineBanking','CreditCard') NOT NULL,
  `remark` text DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `cust_no` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `payment_dateTime`, `transaction_no`, `account_card_no`, `amount`, `payment_type`, `remark`, `email`, `cust_no`) VALUES
(1, '2020-11-26 13:21:30', 'ACRAF23DB3C4', '576298463279', '1000', 'CreditCard', 'Honeymoon', 'alicia@test.com', 1),
(2, '2020-11-26 13:21:30', 'SG79MOJ635JG', '798135649873', '2600', 'OnlineBanking', 'Study abroad', 'adam@test.com', 3),
(3, '2020-11-26 13:23:12', 'HK07BU693KL6', '468134685184', '500', 'OnlineBanking', 'Family visit', 'sayaka@test.com', 3),
(4, '2020-11-26 13:24:48', 'JLJ879HBK356H', '133546873165', '550', 'OnlineBanking', 'travel', 'sayaka@test.com', 4),
(5, '2020-11-10 16:05:55', 'JS82K720S678', '274958475896', '1500', 'CreditCard', NULL, 'JohnDoe@gmail.com', 5),
(6, '2020-11-10 16:07:36', 'HSY2836KSY65', '726389483746', '2200', 'CreditCard', NULL, 'Jon@gmail.com', 6),
(7, '2020-11-16 16:08:11', 'Y7628J715H61', '789309476512', '1800', 'OnlineBanking', NULL, 'stuart@gmail.com', 7),
(8, '2020-11-07 16:08:42', 'U82763HY7891', '182739487283', '1400', 'CreditCard', NULL, 'Harry@gmail.com', 8),
(9, '2020-11-15 16:09:17', 'YH928374U172', '130089273847', '1500', 'CreditCard', NULL, 'AlexM@gmail.com', 9),
(10, '2020-11-16 16:10:04', 'UJ928374KW81', '273849280293', '1600', 'OnlineBanking', NULL, 'HarisC@gmail.com', 10),
(11, '2020-11-15 16:10:41', 'UY827384IW82', '183948273847', '1200', 'OnlineBanking', NULL, 'KylieDown@gmail.com\r\n', 11),
(12, '2020-11-15 16:11:16', 'IW19172364U7', '182793849127', '800', 'OnlineBanking', NULL, 'NatLoh@gmail.com', 12);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `cust_name` varchar(255) NOT NULL,
  `contact` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `cust_name`, `contact`, `email`, `address`, `username`, `password`) VALUES
(1, 'Alicia Yap', '0123456789', 'alicia@test.com', 'Jalan alicia', 'aliciayap', '123456'),
(2, 'Joseph Tan', '0123456789', 'joseph@test.com', 'Jalan Joseph', 'josephtan', '123456'),
(3, 'Adam Lee', '0123456899', 'adam@test.com', 'Jalan Adam', 'adamlee', '123456'),
(4, 'Daniel How', '7894531230', 'daniel@test.com', 'Jalan Daniel', 'daniellee', '123456'),
(5, 'John Doe', '0173456789', 'JohnDoe@gmail.com', '42, Lorong Pengkalan, Taman Canning, 33300, Ipoh, Perak ', 'JohnDoe', '1234'),
(6, 'Jonathan Lim', '0133456789', 'Jon@gmail.com', '2, Jalan Panorama, Taman Teng Chun, 31650, Ipoh, Perak', 'JohnLim', '12345'),
(7, 'Stuart Lim', '0123456789', 'stuart@gmail.com', '98, Hala Sepakat, Taman Panorama, 31350, Ipoh, Perak', 'stu0607', '123456'),
(8, 'Harry Lewis', '0176345789', 'Harry@gmail.com', '21, Jalan Pasir Puteh, Taman Pasir Puteh, 31650, Ipoh, Perak', 'HarryLewis', '1234567'),
(9, 'Alex Mahone', '0125053178', 'AlexM@gmail.com', '5, Jalan Rantau Panjang, Kampung Rantau Panjang, 42100, Klang, Selangor', 'AlexM', 'Alex1234'),
(10, 'Haris Cobb', '012987432', 'HarisC@gmail.com', 'A 11, Jln Sri Ampang, Taman, Jaya, 31350, Ipoh, Perak', 'HarisC', 'Haris1234'),
(11, 'Kylie Downling', '0165666929', 'KylieDown@gmail.com', 'Unit 5, Block 2, Puteri Apartment, 15/55A Taman Setiawangsa, 54200, Kuala Lumpur, Wilayah Persekutuan', 'KylieDown', 'Kylie1234'),
(12, 'Natasha Loh', '0165253198', 'NatLoh@gmail.com', '26, Jalan Mutiara Emas, 5/21 Taman Mount Austin, 81100, Johor Bahru, Johor', 'NatLoh', 'Nat1234');

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `registration` varchar(10) NOT NULL,
  `departure` varchar(255) NOT NULL,
  `arrival` varchar(255) NOT NULL,
  `est_duration` int(11) NOT NULL,
  `type` enum('Domestic','International') NOT NULL,
  `airplane_no` int(11) NOT NULL,
  `airplane_model` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `registration`, `departure`, `arrival`, `est_duration`, `type`, `airplane_no`, `airplane_model`, `capacity`) VALUES
(1, '9M-AQG	', 'Kuala Lumpur International Airport (WMKK)', 'Adelaide Airport (YPAD)', 120, 'International', 1, 'Airbus A321neo', 200),
(2, '9M-AQG	', 'Kuala Lumpur International Airport (WMKK)', 'Beijing Capital International Airport (ZBAA)', 320, 'International', 2, 'Airbus A321neo', 200),
(3, '9M-AGI', 'Hong Kong  International Airport (VHHH)', 'Fukuoka Airport (RJFF)', 180, 'International', 3, 'Airbus A320neo', 300),
(4, '9M-AFD	', 'London Gatwick Airport (EGKK)', 'Kuala Lumpur International Airport (WMKK)', 540, 'International', 4, 'Airbus A321neo', 200),
(5, '9M-AQG	', 'Incheon International Airport (RKSI)', 'Kuala Lumpur International Airport (WMKK)', 300, 'International', 1, 'Airbus A320-200	', 200),
(6, '9M-AQG	', 'Kuala Lumpur International Airport (WMKK)', 'Incheon International Airport (RKSI)', 300, 'International', 2, 'Airbus A320-200	', 200),
(7, '9M-VAB', 'Kuala Lumpur International Airport (WMKK)', 'Bintulu Airport (WBGB)', 60, 'Domestic', 2, 'Airbus A321neo', 100),
(8, '9M-VAB', 'Bintulu Airport (WBGB)', 'Kuala Lumpur International Airport (WMKK)', 60, 'Domestic', 7, 'Airbus A321neo', 100);

-- --------------------------------------------------------

--
-- Table structure for table `flight_attendant_team`
--

CREATE TABLE `flight_attendant_team` (
  `flight_team` int(11) NOT NULL,
  `flight_attendant` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `flight_attendant_team`
--

INSERT INTO `flight_attendant_team` (`flight_team`, `flight_attendant`) VALUES
(1, 7),
(1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `flight_schedules`
--

CREATE TABLE `flight_schedules` (
  `id` int(11) NOT NULL,
  `flight_no` int(11) NOT NULL,
  `depart_dateTime` datetime NOT NULL,
  `arrive_dateTime` datetime NOT NULL,
  `status` enum('Scheduled','Delayed','Departed','In Air','Expected','Diverted','Recovery','Landed','Arrived','Cancelled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `flight_schedules`
--

INSERT INTO `flight_schedules` (`id`, `flight_no`, `depart_dateTime`, `arrive_dateTime`, `status`) VALUES
(1, 1, '2021-02-23 14:32:27', '2021-02-24 03:34:49', 'Scheduled'),
(2, 3, '2020-12-12 11:00:00', '2020-12-12 13:00:00', 'Delayed'),
(3, 3, '2020-11-27 07:00:00', '2020-11-27 10:00:00', 'Scheduled'),
(4, 1, '2020-12-11 15:30:00', '2020-12-11 17:30:00', 'Scheduled'),
(5, 6, '2020-11-28 10:00:00', '2020-11-28 15:00:00', 'In Air'),
(6, 6, '2020-12-20 16:45:00', '2020-12-20 21:45:00', 'Expected'),
(7, 7, '2020-11-28 11:40:40', '2020-11-28 12:40:40', 'Scheduled'),
(8, 4, '2020-11-30 14:00:00', '2020-11-30 23:00:00', 'Expected'),
(9, 5, '2020-12-02 04:35:35', '2020-12-02 09:35:35', 'Scheduled'),
(10, 6, '2020-11-27 14:17:43', '2020-11-27 19:17:43', 'Departed'),
(11, 8, '2020-11-24 14:18:35', '2020-11-24 15:18:35', 'Arrived'),
(12, 1, '2020-12-09 00:19:00', '2020-12-09 02:19:00', 'Scheduled'),
(13, 2, '2020-12-02 23:00:00', '2020-11-03 03:20:00', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `flight_teams`
--

CREATE TABLE `flight_teams` (
  `id` int(11) NOT NULL,
  `schedule_no` int(11) NOT NULL,
  `pilot` int(11) NOT NULL,
  `co_pilot` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `flight_teams`
--

INSERT INTO `flight_teams` (`id`, `schedule_no`, `pilot`, `co_pilot`) VALUES
(1, 1, 1, 2),
(2, 5, 3, 1),
(3, 4, 5, 5),
(4, 7, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `flight_tickets`
--

CREATE TABLE `flight_tickets` (
  `id` int(11) NOT NULL,
  `booking_no` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `schedule_no` int(11) NOT NULL,
  `class` int(11) NOT NULL,
  `seat_no` varchar(11) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  `passenger_name` varchar(10000) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date NOT NULL,
  `ic_passport` varchar(50) DEFAULT NULL,
  `passport_country` varchar(100) DEFAULT NULL,
  `residence_country` varchar(100) DEFAULT NULL,
  `baggage_limit` int(11) NOT NULL,
  `status` enum('Pending','CheckedIn','Boarded') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `flight_tickets`
--

INSERT INTO `flight_tickets` (`id`, `booking_no`, `purchase_date`, `schedule_no`, `class`, `seat_no`, `price`, `passenger_name`, `gender`, `date_of_birth`, `ic_passport`, `passport_country`, `residence_country`, `baggage_limit`, `status`) VALUES
(1, 1, '2020-10-16', 3, 2, '5E', '500', 'Alicia Yap', 'Female', '1990-11-01', 'AX7982', 'Malaysia', 'Singapore', 1, 'Pending'),
(2, 1, '2020-10-10', 3, 2, '16F', '500', 'Henry Koh', 'Male', '1990-11-23', 'AX7951', 'Malaysia', 'Malaysia', 1, 'Pending'),
(3, 2, '2020-08-17', 2, 1, '12A', '1500', 'Adam Lee', 'Male', '2000-09-07', 'AX4687', 'Malaysia', 'Malaysia', 3, 'Pending'),
(4, 3, '2020-09-02', 1, 3, '10C', '500', 'Sayaka Kanae', 'Female', '1997-02-01', 'JP1369', 'Japan', 'Malaysia', 2, 'Pending'),
(5, 3, '2020-09-02', 1, 3, '18A', '500', 'Aoi Kanabe', 'Male', '1991-12-26', 'JP1987', 'Japan', 'Malaysia', 2, 'Pending'),
(6, 1, '2020-09-01', 7, 2, '1C', '1000', 'Sayaka Kanae', 'Female', '1997-02-01', 'JP1369', 'Japan', 'Malaysia', 1, 'Pending'),
(7, 7, '2020-10-03', 4, 1, '1F', '800', 'Stuart Lim', 'Male', '1998-05-21', 'SG4231', 'Singapore', 'Singapore', 2, 'Pending'),
(8, 8, '2020-10-19', 10, 3, '6B', '500', 'Harry Lewis', 'Male', '1981-11-14', 'SG3425', 'Singapore', 'Singapore', 1, 'Boarded'),
(9, 9, '2020-10-10', 3, 2, '7D', '900', 'Alex Mahone', 'Female', '1990-11-01', 'SK9245', 'South Korea', 'South Korea', 3, 'CheckedIn'),
(10, 10, '2020-09-15', 9, 3, '9E', '800', 'Haris Cobb', 'Male', '1995-08-30', 'SK9283', 'South Korea', 'South Korea', 4, 'Pending'),
(11, 11, '2020-09-05', 7, 3, '30F', '300', 'Kylie Downling', 'Female', '2001-11-03', 'SG2452', 'Singapore', 'Singapore', 1, 'Pending'),
(12, 12, '2020-09-27', 10, 2, '18A', '600', 'Natasha Loh', 'Female', '1997-01-22', 'US5241', 'United States', 'Malaysia', 1, 'Boarded');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `staff_name` varchar(10000) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('Pilot','FlightAttendant','NormalManagement','Superior','Manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_name`, `email`, `password`, `role`) VALUES
(1, 'Shao Yan Feng', 'shao@test.com', '123456', 'Pilot'),
(2, 'Tang Xin Mae', 'tang@test.com', '123456', 'Pilot'),
(3, 'Mei Liew Ho', 'mei@test.com', '123456', 'Pilot'),
(4, 'Jason Lee Seung Jun', 'jason@test.com', '123456', 'Pilot'),
(5, 'Sophia Abigail', 'sophia@test.com', '123456', 'Pilot'),
(6, 'Vinita Parameswaran', 'vinita@test.com', '123456', 'FlightAttendant'),
(7, 'Hannah Tan Yi Ahn', 'hannah@test.com', '123456', 'FlightAttendant'),
(8, 'Kaitlyn Janett', 'kaitlyn@test.com', '123456', 'FlightAttendant'),
(9, 'Maya Himawari', 'maya@test.com', '123456', 'FlightAttendant'),
(10, 'Farah Izz ', 'teoh@test.com', '123456', 'Manager'),
(11, 'Eugene Teoh Zhen Hao', 'eugene@test.com', '123456', 'NormalManagement'),
(12, 'Jenna Lim Xiao Yu', 'jenna@test.com', '123456', 'Superior'),
(13, 'Irfan Yahayan', 'irhan@test.com', '123456', 'Superior'),
(14, 'Diana How Foo Xin', 'diana@test.com', '123456', 'Manager'),
(15, 'Ameena Nur Bakar', 'ameena@test.com', '123456', 'Manager'),
(16, 'Fatimah Hashan', 'fatimah@test.com', '123456', 'FlightAttendant');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_class`
--

CREATE TABLE `ticket_class` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ticket_class`
--

INSERT INTO `ticket_class` (`id`, `name`, `price`) VALUES
(1, 'First', '500'),
(2, 'Business', '300'),
(3, 'Economy', '150');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cust_no` (`cust_no`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flight_attendant_team`
--
ALTER TABLE `flight_attendant_team`
  ADD PRIMARY KEY (`flight_team`,`flight_attendant`),
  ADD KEY `flight_attendant` (`flight_attendant`);

--
-- Indexes for table `flight_schedules`
--
ALTER TABLE `flight_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flight_no` (`flight_no`);

--
-- Indexes for table `flight_teams`
--
ALTER TABLE `flight_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_no` (`schedule_no`),
  ADD KEY `pilot` (`pilot`),
  ADD KEY `co_pilot` (`co_pilot`);

--
-- Indexes for table `flight_tickets`
--
ALTER TABLE `flight_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_no` (`schedule_no`),
  ADD KEY `class` (`class`),
  ADD KEY `booking_no` (`booking_no`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_class`
--
ALTER TABLE `ticket_class`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `flight_schedules`
--
ALTER TABLE `flight_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `flight_teams`
--
ALTER TABLE `flight_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `flight_tickets`
--
ALTER TABLE `flight_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ticket_class`
--
ALTER TABLE `ticket_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`cust_no`) REFERENCES `customers` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `flight_attendant_team`
--
ALTER TABLE `flight_attendant_team`
  ADD CONSTRAINT `flight_attendant_team_ibfk_1` FOREIGN KEY (`flight_team`) REFERENCES `flight_teams` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_attendant_team_ibfk_2` FOREIGN KEY (`flight_attendant`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `flight_schedules`
--
ALTER TABLE `flight_schedules`
  ADD CONSTRAINT `flight_schedules_ibfk_1` FOREIGN KEY (`flight_no`) REFERENCES `flights` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `flight_teams`
--
ALTER TABLE `flight_teams`
  ADD CONSTRAINT `flight_teams_ibfk_1` FOREIGN KEY (`schedule_no`) REFERENCES `flight_schedules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_teams_ibfk_2` FOREIGN KEY (`pilot`) REFERENCES `staff` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_teams_ibfk_3` FOREIGN KEY (`co_pilot`) REFERENCES `staff` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `flight_tickets`
--
ALTER TABLE `flight_tickets`
  ADD CONSTRAINT `flight_tickets_ibfk_1` FOREIGN KEY (`schedule_no`) REFERENCES `flight_schedules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_tickets_ibfk_2` FOREIGN KEY (`class`) REFERENCES `ticket_class` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_tickets_ibfk_3` FOREIGN KEY (`booking_no`) REFERENCES `bookings` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
