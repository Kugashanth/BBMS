-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 18, 2025 at 02:34 PM
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
-- Database: `blood_bank`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `nic` varchar(20) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `img` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `created_at`, `firstName`, `lastName`, `nic`, `phone`, `img`) VALUES
(5, 'admin', 'admin@gmail.com', '$2y$10$r8UpEQE56//6PG2GgMpwVO6woAMektbbYDKpdwVLuSvUKu.xp3h06', '2025-02-12 10:14:57', 'Admin', 'User', '123456789V', '0123456789', 'img/profile/profile.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `blood_camps`
--

CREATE TABLE `blood_camps` (
  `id` int(11) NOT NULL,
  `camp_name` varchar(255) NOT NULL,
  `camp_date` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blood_camps`
--

INSERT INTO `blood_camps` (`id`, `camp_name`, `camp_date`, `location`, `hospital_id`, `created_at`) VALUES
(1, 'Blood Donation Camp 1', '2025-05-18', 'Location A', 116, '2025-02-17 06:30:00'),
(2, 'Blood Donation Camp 2', '2025-02-19', 'Location B', 117, '2025-02-17 07:00:00'),
(3, 'Blood Donation Camp 3', '2025-02-19', 'Location C', 118, '2025-02-17 07:30:00'),
(4, 'Blood Donation Camp 4', '2025-02-17', 'Location D', 119, '2025-02-17 08:00:00'),
(5, 'Blood Donation Camp 5', '2025-02-18', 'Location E', 120, '2025-02-17 08:30:00'),
(6, 'Blood Donation Camp 6', '2025-02-25', 'Location F', 121, '2025-02-17 09:00:00'),
(7, 'Blood Donation Camp 7', '2025-02-26', 'Location G', 122, '2025-02-17 09:30:00'),
(8, 'Blood Donation Camp 8', '2025-02-27', 'Location H', 123, '2025-02-17 10:00:00'),
(9, 'Blood Donation Camp 9', '2025-02-28', 'Location I', 124, '2025-02-17 10:30:00'),
(10, 'Blood Donation Camp 10', '2025-03-01', 'Location J', 125, '2025-02-17 11:00:00'),
(11, 'Blood Donation Camp 11', '2025-03-02', 'Location K', 126, '2025-02-17 11:30:00'),
(12, 'Blood Donation Camp 12', '2025-03-03', 'Location L', 127, '2025-02-17 12:00:00'),
(13, 'Blood Donation Camp 13', '2025-03-04', 'Location M', 128, '2025-02-17 12:30:00'),
(14, 'Blood Donation Camp 14', '2025-03-05', 'Location N', 129, '2025-02-17 13:00:00'),
(15, 'Blood Donation Camp 15', '2025-03-06', 'Location O', 130, '2025-02-17 13:30:00'),
(16, 'Blood Donation Camp 16', '2025-03-07', 'Location P', 131, '2025-02-17 14:00:00'),
(17, 'Blood Donation Camp 17', '2025-03-08', 'Location Q', 132, '2025-02-17 14:30:00'),
(18, 'Blood Donation Camp 18', '2025-03-09', 'Location R', 133, '2025-02-17 15:00:00'),
(19, 'Blood Donation Camp 19', '2025-03-10', 'Location S', 134, '2025-02-17 15:30:00'),
(20, 'Blood Donation Camp 20', '2025-03-11', 'Location T', 135, '2025-02-17 16:00:00'),
(21, 'Blood Donation Camp 21', '2025-03-12', 'Location U', 136, '2025-02-17 16:30:00'),
(22, 'Blood Donation Camp 22', '2025-03-13', 'Location V', 137, '2025-02-17 17:00:00'),
(23, 'Blood Donation Camp 23', '2025-03-14', 'Location W', 138, '2025-02-17 17:30:00'),
(24, 'Blood Donation Camp 24', '2025-05-18', 'Location Y', 116, '2025-02-17 18:00:00'),
(27, 'Blood Donation Camp 27', '2025-03-18', 'Location AA', 119, '2025-02-17 19:30:00'),
(28, 'Blood Donation Camp 28', '2025-03-19', 'Location AB', 120, '2025-02-17 20:00:00'),
(29, 'Blood Donation Camp 29', '2025-03-20', 'Location AC', 121, '2025-02-17 20:30:00'),
(30, 'Blood Donation Camp 30', '2025-03-21', 'Location AD', 122, '2025-02-17 21:00:00'),
(31, 'Blood Donation Camp 31', '2025-03-22', 'Location AE', 123, '2025-02-17 21:30:00'),
(32, 'Blood Donation Camp 32', '2025-03-23', 'Location AF', 124, '2025-02-17 22:00:00'),
(33, 'Blood Donation Camp 33', '2025-03-24', 'Location AG', 125, '2025-02-17 22:30:00'),
(34, 'Blood Donation Camp 34', '2025-03-25', 'Location AH', 126, '2025-02-17 23:00:00'),
(35, 'Blood Donation Camp 35', '2025-03-26', 'Location AI', 127, '2025-02-17 23:30:00'),
(36, 'Blood Donation Camp 36', '2025-03-27', 'Location AJ', 128, '2025-02-18 00:00:00'),
(37, 'Blood Donation Camp 37', '2025-03-28', 'Location AK', 129, '2025-02-18 00:30:00'),
(38, 'Blood Donation Camp 38', '2025-03-29', 'Location AL', 130, '2025-02-18 01:00:00'),
(39, 'Blood Donation Camp 39', '2025-03-30', 'Location AM', 131, '2025-02-18 01:30:00'),
(40, 'Blood Donation Camp 40', '2025-03-31', 'Location AN', 132, '2025-02-18 02:00:00'),
(41, 'Blood Donation Camp 41', '2025-04-01', 'Location AO', 133, '2025-02-18 02:30:00'),
(42, 'Blood Donation Camp 42', '2025-04-02', 'Location AP', 134, '2025-02-18 03:00:00'),
(43, 'Blood Donation Camp 43', '2025-04-03', 'Location AQ', 135, '2025-02-18 03:30:00'),
(44, 'Blood Donation Camp 44', '2025-04-04', 'Location AR', 136, '2025-02-18 04:00:00'),
(45, 'Blood Donation Camp 45', '2025-04-05', 'Location AS', 137, '2025-02-18 04:30:00'),
(46, 'Blood Donation Camp 46', '2025-04-06', 'Location AT', 138, '2025-02-18 05:00:00'),
(47, 'Blood Donation Camp 47', '2025-05-18', 'Location AU', 116, '2025-02-18 05:30:00'),
(48, 'Blood Donation Camp 48', '2025-04-08', 'Location AV', 117, '2025-02-18 06:00:00'),
(49, 'Blood Donation Camp 49', '2025-04-09', 'Location AW', 118, '2025-02-18 06:30:00'),
(50, 'Blood Donation Camp 50', '2025-04-10', 'Location AX', 119, '2025-02-18 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `units` int(11) NOT NULL CHECK (`units` >= 0),
  `hospital_id` int(11) NOT NULL,
  `camp_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `donor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`id`, `blood_group`, `units`, `hospital_id`, `camp_id`, `updated_at`, `donor_id`) VALUES
(13, 'A-', 29, 116, 1, '2025-05-16 13:20:28', NULL),
(18, 'AB-', 14, 116, 1, '2025-05-16 13:20:28', NULL),
(19, 'B+', 27, 116, 1, '2025-05-18 09:24:12', NULL),
(35, 'A+', 0, 116, NULL, '2025-05-18 01:19:17', NULL),
(36, 'A+', 5, 117, NULL, '2025-05-18 01:19:17', NULL),
(37, 'A-', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(38, 'B+', 15, 117, NULL, '2025-05-18 09:24:12', NULL),
(39, 'B-', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(40, 'O+', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(41, 'O-', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(42, 'AB+', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(43, 'AB-', 0, 117, NULL, '2025-05-18 03:08:12', NULL),
(44, 'A+', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(45, 'A-', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(46, 'B+', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(47, 'B-', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(48, 'O+', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(49, 'O-', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(50, 'AB+', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(51, 'AB-', 0, 126, NULL, '2025-05-18 03:13:58', NULL),
(52, 'A+', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(53, 'A-', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(54, 'B+', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(55, 'B-', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(56, 'O+', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(57, 'O-', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(58, 'AB+', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(59, 'AB-', 0, 139, NULL, '2025-05-18 03:13:58', NULL),
(60, 'A+', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(61, 'A-', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(62, 'B+', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(63, 'B-', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(64, 'O+', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(65, 'O-', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(66, 'AB+', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(67, 'AB-', 0, 133, NULL, '2025-05-18 03:13:58', NULL),
(68, 'A+', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(69, 'A-', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(70, 'B+', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(71, 'B-', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(72, 'O+', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(73, 'O-', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(74, 'AB+', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(75, 'AB-', 0, 135, NULL, '2025-05-18 03:13:58', NULL),
(76, 'A+', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(77, 'A-', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(78, 'B+', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(79, 'B-', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(80, 'O+', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(81, 'O-', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(82, 'AB+', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(83, 'AB-', 0, 125, NULL, '2025-05-18 03:13:58', NULL),
(84, 'A+', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(85, 'A-', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(86, 'B+', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(87, 'B-', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(88, 'O+', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(89, 'O-', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(90, 'AB+', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(91, 'AB-', 0, 124, NULL, '2025-05-18 03:13:58', NULL),
(92, 'A+', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(93, 'A-', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(94, 'B+', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(95, 'B-', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(96, 'O+', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(97, 'O-', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(98, 'AB+', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(99, 'AB-', 0, 128, NULL, '2025-05-18 03:13:58', NULL),
(100, 'A+', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(101, 'A-', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(102, 'B+', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(103, 'B-', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(104, 'O+', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(105, 'O-', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(106, 'AB+', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(107, 'AB-', 0, 138, NULL, '2025-05-18 03:13:58', NULL),
(108, 'A+', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(109, 'A-', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(110, 'B+', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(111, 'B-', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(112, 'O+', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(113, 'O-', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(114, 'AB+', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(115, 'AB-', 0, 118, NULL, '2025-05-18 03:13:58', NULL),
(116, 'A+', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(117, 'A-', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(118, 'B+', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(119, 'B-', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(120, 'O+', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(121, 'O-', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(122, 'AB+', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(123, 'AB-', 0, 119, NULL, '2025-05-18 03:13:58', NULL),
(124, 'A+', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(125, 'A-', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(126, 'B+', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(127, 'B-', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(128, 'O+', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(129, 'O-', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(130, 'AB+', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(131, 'AB-', 0, 131, NULL, '2025-05-18 03:13:58', NULL),
(132, 'A+', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(133, 'A-', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(134, 'B+', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(135, 'B-', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(136, 'O+', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(137, 'O-', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(138, 'AB+', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(139, 'AB-', 0, 121, NULL, '2025-05-18 03:13:58', NULL),
(140, 'A+', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(141, 'A-', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(142, 'B+', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(143, 'B-', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(144, 'O+', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(145, 'O-', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(146, 'AB+', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(147, 'AB-', 0, 130, NULL, '2025-05-18 03:13:58', NULL),
(148, 'A+', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(149, 'A-', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(150, 'B+', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(151, 'B-', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(152, 'O+', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(153, 'O-', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(154, 'AB+', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(155, 'AB-', 0, 136, NULL, '2025-05-18 03:13:58', NULL),
(156, 'A+', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(157, 'A-', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(158, 'B+', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(159, 'B-', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(160, 'O+', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(161, 'O-', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(162, 'AB+', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(163, 'AB-', 0, 123, NULL, '2025-05-18 03:13:58', NULL),
(164, 'A+', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(165, 'A-', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(166, 'B+', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(167, 'B-', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(168, 'O+', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(169, 'O-', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(170, 'AB+', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(171, 'AB-', 0, 120, NULL, '2025-05-18 03:13:58', NULL),
(172, 'A+', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(173, 'A-', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(174, 'B+', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(175, 'B-', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(176, 'O+', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(177, 'O-', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(178, 'AB+', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(179, 'AB-', 0, 132, NULL, '2025-05-18 03:13:58', NULL),
(180, 'A+', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(181, 'A-', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(182, 'B+', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(183, 'B-', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(184, 'O+', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(185, 'O-', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(186, 'AB+', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(187, 'AB-', 0, 134, NULL, '2025-05-18 03:13:58', NULL),
(188, 'A+', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(189, 'A-', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(190, 'B+', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(191, 'B-', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(192, 'O+', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(193, 'O-', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(194, 'AB+', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(195, 'AB-', 0, 137, NULL, '2025-05-18 03:13:58', NULL),
(196, 'A+', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(197, 'A-', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(198, 'B+', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(199, 'B-', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(200, 'O+', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(201, 'O-', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(202, 'AB+', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(203, 'AB-', 0, 127, NULL, '2025-05-18 03:13:58', NULL),
(204, 'A+', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(205, 'A-', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(206, 'B+', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(207, 'B-', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(208, 'O+', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(209, 'O-', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(210, 'AB+', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(211, 'AB-', 0, 122, NULL, '2025-05-18 03:13:58', NULL),
(212, 'A+', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(213, 'A-', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(214, 'B+', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(215, 'B-', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(216, 'O+', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(217, 'O-', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(218, 'AB+', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(219, 'AB-', 0, 129, NULL, '2025-05-18 03:13:58', NULL),
(299, 'O+', 1, 116, 1, '2025-05-18 07:23:46', 10),
(300, 'O-', 1, 116, 1, '2025-05-18 08:50:06', 11),
(301, 'B-', 0, 116, NULL, '2025-05-18 12:27:09', NULL),
(302, 'AB+', 0, 116, NULL, '2025-05-18 12:27:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `requesting_hospital_id` int(11) NOT NULL,
  `requested_hospital_id` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unit` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `blood_group`, `requesting_hospital_id`, `requested_hospital_id`, `status`, `created_at`, `unit`) VALUES
(17, 'B+', 117, 116, 'Accepted', '2025-05-18 04:47:31', 10),
(18, 'B+', 116, 117, 'Accepted', '2025-05-18 04:54:46', 10),
(19, 'B+', 116, 117, 'Accepted', '2025-05-18 05:00:11', 5),
(20, 'B+', 117, 116, 'Accepted', '2025-05-18 05:02:05', 5),
(21, 'B+', 117, 116, 'Accepted', '2025-05-18 05:07:08', 5),
(22, 'B+', 117, 116, 'Accepted', '2025-05-18 05:16:41', 5),
(23, 'B+', 116, 117, 'Accepted', '2025-05-18 06:47:35', 10),
(24, 'B+', 116, 117, 'Accepted', '2025-05-18 09:08:56', 5),
(25, 'B+', 117, 116, 'Accepted', '2025-05-18 09:23:50', 10),
(26, 'A+', 116, 117, 'Pending', '2025-05-18 09:24:48', 4),
(27, 'A-', 117, 116, 'Pending', '2025-05-18 12:31:25', 10);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `inquiry_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `phone`, `inquiry_type`, `message`, `submitted_at`) VALUES
(1, 'keerthanan', 'keerthanan1114@gmail.com', '', 'Blood Donation', 'hi hello', '2025-02-17 03:50:48'),
(3, 'John Doe', 'john.doe@example.com', '123-456-7890', 'General Inquiry', 'I have a question regarding your services.', '2025-02-17 04:30:00'),
(4, 'Jane Smith', 'jane.smith@example.com', '987-654-3210', 'Complaint', 'I am not happy with the product quality.', '2025-02-17 04:35:00'),
(5, 'Robert Brown', 'robert.brown@example.com', '555-123-4567', 'Feedback', 'Great experience! I loved the customer support.', '2025-02-17 04:40:00'),
(6, 'Alice Johnson', 'alice.johnson@example.com', '555-234-5678', 'General Inquiry', 'Can you provide more details on your offers?', '2025-02-17 04:45:00'),
(7, 'Michael Lee', 'michael.lee@example.com', '555-345-6789', 'Complaint', 'The delivery was delayed, and I did not receive a tracking number.', '2025-02-17 04:50:00'),
(8, 'Emma Wilson', 'emma.wilson@example.com', '555-456-7890', 'Feedback', 'I am satisfied with the service but think there is room for improvement.', '2025-02-17 04:55:00'),
(9, 'James Harris', 'james.harris@example.com', '555-567-8901', 'General Inquiry', 'What are your working hours?', '2025-02-17 05:00:00'),
(10, 'Sophia Clark', 'sophia.clark@example.com', '555-678-9012', 'Complaint', 'I received the wrong item in my order.', '2025-02-17 05:05:00'),
(11, 'Daniel Lewis', 'daniel.lewis@example.com', '555-789-0123', 'Feedback', 'Loved the product! Will definitely purchase again.', '2025-02-17 05:10:00'),
(12, 'Olivia Young', 'olivia.young@example.com', '555-890-1234', 'General Inquiry', 'Do you offer international shipping?', '2025-02-17 05:15:00'),
(13, 'William Walker', 'william.walker@example.com', '555-901-2345', 'Complaint', 'I had an issue with my payment, it was not processed correctly.', '2025-02-17 05:20:00'),
(14, 'Mia Hall', 'mia.hall@example.com', '555-012-3456', 'Feedback', 'The website is easy to navigate and the checkout process was smooth.', '2025-02-17 05:25:00'),
(15, 'Benjamin Allen', 'benjamin.allen@example.com', '555-123-4567', 'General Inquiry', 'Is there a discount for first-time buyers?', '2025-02-17 05:30:00'),
(16, 'Charlotte King', 'charlotte.king@example.com', '555-234-5678', 'Complaint', 'My package arrived damaged and I need a refund.', '2025-02-17 05:35:00'),
(17, 'Lucas Wright', 'lucas.wright@example.com', '555-345-6789', 'Feedback', 'Customer service responded quickly, but the issue took too long to resolve.', '2025-02-17 05:40:00'),
(18, 'Harper Scott', 'harper.scott@example.com', '555-456-7890', 'General Inquiry', 'Can I return an item purchased online?', '2025-02-17 05:45:00'),
(19, 'Ethan Green', 'ethan.green@example.com', '555-567-8901', 'Complaint', 'I received a faulty item and am waiting for a replacement.', '2025-02-17 05:50:00'),
(20, 'Amelia Adams', 'amelia.adams@example.com', '555-678-9012', 'Feedback', 'The product exceeded my expectations!', '2025-02-17 05:55:00'),
(21, 'Aiden Nelson', 'aiden.nelson@example.com', '555-789-0123', 'General Inquiry', 'Do you offer gift wrapping services?', '2025-02-17 06:00:00'),
(22, 'Zoe Carter', 'zoe.carter@example.com', '555-890-1234', 'Complaint', 'The product was not as described on the website.', '2025-02-17 06:05:00'),
(23, 'Jack Perez', 'jack.perez@example.com', '555-901-2345', 'Feedback', 'I would love to see more variety in the product range.', '2025-02-17 06:10:00'),
(24, 'Lily Ramirez', 'lily.ramirez@example.com', '555-012-3456', 'General Inquiry', 'How long does shipping take for orders within the city?', '2025-02-17 06:15:00'),
(25, 'Sebastian Evans', 'sebastian.evans@example.com', '555-123-4567', 'Complaint', 'The product is not functioning as expected.', '2025-02-17 06:20:00'),
(26, 'Ella Murphy', 'ella.murphy@example.com', '555-234-5678', 'Feedback', 'I appreciated the fast delivery!', '2025-02-17 06:25:00'),
(27, 'Alexander Torres', 'alexander.torres@example.com', '555-345-6789', 'General Inquiry', 'Do you offer any warranties on your products?', '2025-02-17 06:30:00'),
(28, 'Scarlett Jackson', 'scarlett.jackson@example.com', '555-456-7890', 'Complaint', 'I was charged incorrectly and I need a refund.', '2025-02-17 06:35:00'),
(29, 'Henry Martin', 'henry.martin@example.com', '555-567-8901', 'Feedback', 'The customer support was very helpful in resolving my issue.', '2025-02-17 06:40:00'),
(30, 'Mason White', 'mason.white@example.com', '555-678-9012', 'General Inquiry', 'Do you have a physical store location?', '2025-02-17 06:45:00'),
(31, 'Victoria Hall', 'victoria.hall@example.com', '555-789-0123', 'Complaint', 'My order took longer than expected to arrive.', '2025-02-17 06:50:00'),
(32, 'Isaac Carter', 'isaac.carter@example.com', '555-890-1234', 'Feedback', 'The product arrived in perfect condition, thank you!', '2025-02-17 06:55:00'),
(33, 'Liam Rodriguez', 'liam.rodriguez@example.com', '555-901-2345', 'General Inquiry', 'Can I change my shipping address after placing an order?', '2025-02-17 07:00:00'),
(34, 'Evelyn Lee', 'evelyn.lee@example.com', '555-012-3456', 'Complaint', 'My order was incomplete, and I need the missing item.', '2025-02-17 07:05:00'),
(35, 'Matthew Walker', 'matthew.walker@example.com', '555-123-4567', 'Feedback', 'I’m happy with my purchase but would appreciate faster shipping.', '2025-02-17 07:10:00'),
(36, 'Ava Perez', 'ava.perez@example.com', '555-234-5678', 'General Inquiry', 'Do you offer a subscription plan?', '2025-02-17 07:15:00'),
(37, 'Sophia Martinez', 'sophia.martinez@example.com', '555-345-6789', 'Complaint', 'The product packaging was damaged upon arrival.', '2025-02-17 07:20:00'),
(38, 'Jackson White', 'jackson.white@example.com', '555-456-7890', 'Feedback', 'I found the product exactly as described online.', '2025-02-17 07:25:00'),
(39, 'Daniel Scott', 'daniel.scott@example.com', '555-567-8901', 'General Inquiry', 'What payment methods do you accept?', '2025-02-17 07:30:00'),
(40, 'Charlotte Thomas', 'charlotte.thomas@example.com', '555-678-9012', 'Complaint', 'The website was difficult to navigate on mobile.', '2025-02-17 07:35:00'),
(41, 'Benjamin Garcia', 'benjamin.garcia@example.com', '555-789-0123', 'Feedback', 'I am impressed with the quality of the product.', '2025-02-17 07:40:00'),
(42, 'Oliver King', 'oliver.king@example.com', '555-890-1234', 'General Inquiry', 'How do I track my order?', '2025-02-17 07:45:00'),
(43, 'Megan Lewis', 'megan.lewis@example.com', '555-901-2345', 'Complaint', 'The item I received was broken.', '2025-02-17 07:50:00'),
(44, 'Zachary Miller', 'zachary.miller@example.com', '555-012-3456', 'Feedback', 'I love the loyalty program and discounts!', '2025-02-17 07:55:00'),
(45, 'Chloe Robinson', 'chloe.robinson@example.com', '555-123-4567', 'General Inquiry', 'Are there any ongoing sales or promotions?', '2025-02-17 08:00:00'),
(46, 'Liam Brown', 'liam.brown@example.com', '555-234-5678', 'Complaint', 'I am unhappy with the quality of the customer service.', '2025-02-17 08:05:00'),
(47, 'Ella Williams', 'ella.williams@example.com', '555-345-6789', 'Feedback', 'Very happy with my purchase. Will recommend it to others.', '2025-02-17 08:10:00'),
(48, 'David Evans', 'david.evans@example.com', '555-456-7890', 'General Inquiry', 'How can I get a refund for a returned item?', '2025-02-17 08:15:00'),
(49, 'Aidan Johnson', 'aidan.johnson@example.com', '555-567-8901', 'Complaint', 'The product was not as advertised on the website.', '2025-02-17 08:20:00'),
(50, 'Addison Martin', 'addison.martin@example.com', '555-678-9012', 'Feedback', 'Fast shipping and great customer support. Keep it up!', '2025-02-17 08:25:00'),
(51, 'Victoria Brown', 'victoria.brown@example.com', '555-789-0123', 'General Inquiry', 'Can I modify my order after it has been placed?', '2025-02-17 08:30:00'),
(52, 'Matthew Garcia', 'matthew.garcia@example.com', '555-890-1234', 'Complaint', 'The product was delayed, and I was not notified.', '2025-02-17 08:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') NOT NULL,
  `nic` varchar(12) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`id`, `donor_name`, `blood_group`, `nic`, `hospital_id`, `created_at`) VALUES
(1, 'Kajan', 'O+', '200218100407', 116, '2025-05-16 21:46:44'),
(2, 'keerthi', 'B-', '123456789102', 116, '2025-05-16 21:56:54'),
(3, 'Ahsan', 'AB+', '123456789103', 116, '2025-05-16 22:15:10'),
(4, 'abiel', 'O-', '123456789104', 116, '2025-05-17 01:28:03'),
(5, 'jeyanash', 'AB+', '123456789105', 116, '2025-05-17 02:36:30'),
(7, 'sudalai', 'O+', '200300000000', 116, '2025-05-17 09:58:38'),
(10, 'kajan', 'O+', '200218100406', 117, '2025-05-18 06:45:09'),
(11, 'venu', 'O-', '200211111111', 116, '2025-05-18 08:50:01');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `hospital_id`, `feedback`, `date`) VALUES
(1, 116, 'Excellent service and quick response.', '2024-01-15 04:45:00'),
(2, 117, 'Doctors are very professional and caring.', '2024-02-10 09:00:00'),
(3, 118, 'The hospital environment is clean and well-maintained.', '2024-03-05 04:15:00'),
(4, 119, 'Waiting time needs improvement, but service is good.', '2024-04-12 05:50:00'),
(5, 120, 'Very satisfied with the treatment received.', '2024-05-20 10:40:00'),
(6, 121, 'Staff are friendly and helpful.', '2024-06-25 03:00:00'),
(7, 122, 'Parking is limited, needs better management.', '2024-07-14 08:20:00'),
(8, 123, 'Emergency response was fast and efficient.', '2024-08-09 12:55:00'),
(9, 124, 'Doctors explained the treatment plan well.', '2024-09-18 06:40:00'),
(10, 125, 'Pharmacy service was quick and helpful.', '2024-10-22 10:10:00'),
(11, 126, 'The hospital staff was very accommodating.', '2024-11-30 04:35:00'),
(12, 127, 'Long waiting time in the outpatient department.', '2024-12-08 08:50:00'),
(13, 128, 'Nurses were very attentive and kind.', '2024-12-15 12:05:00'),
(14, 129, 'Facilities are modern and well-equipped.', '2025-01-05 03:50:00'),
(15, 130, 'Registration process was quick and smooth.', '2025-01-14 06:25:00'),
(16, 131, 'Surgery was successful, thanks to the great team.', '2025-01-20 11:15:00'),
(17, 132, 'Ambulance service arrived on time and was very helpful.', '2025-01-28 02:40:00'),
(18, 133, 'The cafeteria food needs improvement.', '2025-02-02 08:30:00'),
(21, 136, 'Lab reports were delivered quickly.', '2025-02-12 04:20:00'),
(22, 137, 'A well-managed hospital with dedicated staff.', '2025-02-13 05:00:00'),
(23, 138, 'Overall a great experience with professional service.', '2025-02-13 07:15:00'),
(25, 116, 'Doctors are very professional and caring.', '2024-02-10 09:00:00'),
(26, 116, 'Great medical facilities and hygiene.', '2024-03-22 04:15:00'),
(27, 117, 'The hospital environment is clean and well-maintained.', '2024-04-05 06:50:00'),
(28, 117, 'Waiting time needs improvement, but service is good.', '2024-05-08 10:40:00'),
(29, 118, 'Very satisfied with the treatment received.', '2024-06-12 03:00:00'),
(30, 118, 'Staff are friendly and helpful.', '2024-07-25 09:15:00'),
(31, 118, 'Emergency response was fast and efficient.', '2024-08-19 12:55:00'),
(32, 119, 'Doctors explained the treatment plan well.', '2024-09-10 05:40:00'),
(33, 119, 'Pharmacy service was quick and helpful.', '2024-10-05 10:10:00'),
(34, 120, 'The hospital staff was very accommodating.', '2024-11-30 04:35:00'),
(35, 120, 'Nurses were very attentive and kind.', '2024-12-12 08:50:00'),
(36, 120, 'Facilities are modern and well-equipped.', '2025-01-02 03:50:00'),
(37, 121, 'Registration process was quick and smooth.', '2025-01-10 06:25:00'),
(38, 121, 'Surgery was successful, thanks to the great team.', '2025-01-15 11:15:00'),
(39, 122, 'Ambulance service arrived on time and was very helpful.', '2025-01-25 02:40:00'),
(40, 122, 'The cafeteria food needs improvement.', '2025-02-01 08:30:00'),
(43, 123, 'Lab reports were delivered quickly.', '2025-02-12 04:20:00'),
(44, 123, 'A well-managed hospital with dedicated staff.', '2025-02-13 05:00:00'),
(45, 124, 'Overall a great experience with professional service.', '2025-02-13 07:15:00'),
(46, 124, 'Doctors are very knowledgeable and caring.', '2025-02-14 03:30:00'),
(47, 125, 'Great support from nurses and hospital staff.', '2024-02-16 07:55:00'),
(48, 125, 'Hospital needs to improve cleanliness in waiting areas.', '2024-03-12 06:10:00'),
(49, 126, 'Great experience, excellent doctors.', '2024-04-05 11:40:00'),
(50, 126, 'Quick service at the emergency department.', '2024-05-20 04:00:00'),
(51, 127, 'The management is very efficient.', '2024-06-14 07:20:00'),
(52, 127, 'Had a smooth experience with no delays.', '2024-07-10 09:05:00'),
(53, 128, 'Good medical staff but needs better facilities.', '2024-08-22 04:40:00'),
(54, 128, 'The hospital is well-organized.', '2024-09-15 09:55:00'),
(55, 129, 'Good experience with the maternity department.', '2024-10-30 04:20:00'),
(56, 129, 'I had a comfortable stay post-surgery.', '2024-11-20 10:50:00'),
(57, 130, 'Nice service, but expensive treatment.', '2024-12-08 05:25:00'),
(58, 130, 'Doctors and staff were cooperative.', '2025-01-18 08:40:00'),
(59, 131, 'Great diagnosis and treatment facilities.', '2025-02-02 04:05:00'),
(61, 132, 'The emergency response team was amazing.', '2024-02-05 07:15:00'),
(62, 132, 'Clean and well-maintained hospital.', '2024-03-15 12:20:00'),
(63, 133, 'Had a minor surgery, everything went well.', '2024-04-20 05:40:00'),
(64, 133, 'The appointment system should be improved.', '2024-05-12 08:55:00'),
(65, 134, 'Friendly staff and good service.', '2024-06-28 04:00:00'),
(66, 134, 'I appreciate the patience of the doctors.', '2024-07-15 11:10:00'),
(67, 135, 'Smooth admission and discharge process.', '2024-08-05 04:45:00'),
(68, 135, 'They have a well-equipped ICU.', '2024-09-18 07:00:00'),
(69, 136, 'Affordable healthcare with great service.', '2024-10-09 08:50:00'),
(70, 136, 'Had a great experience at the cardiology department.', '2024-11-05 11:25:00'),
(71, 137, 'Hospital services are top-notch.', '2024-12-10 06:15:00'),
(72, 137, 'Waiting time needs to be reduced.', '2025-01-05 09:40:00'),
(73, 138, 'Highly recommend this hospital.', '2025-01-20 03:10:00'),
(74, 138, 'Overall, a pleasant experience.', '2025-02-05 09:25:00'),
(75, 138, 'Doctors and nurses were very professional.', '2025-02-12 06:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `name`, `location`, `contact`, `created_at`, `province`, `district`, `img`, `phone`, `website`, `username`, `email`, `password`) VALUES
(116, 'National Hospital of Sri Lanka', 'Colombo 10', '0115825655', '2025-02-12 10:32:08', 'Western', 'Colombo', 'uploads/hospitals/nhsl.jpg', '0112691111', 'https://www.nhsl.gov.lk', 'nhsl_admin', 'nhsl@gmail.com', '$2y$10$r8UpEQE56//6PG2GgMpwVO6woAMektbbYDKpdwVLuSvUKu.xp3h06'),
(117, 'Gampaha District General Hospital', 'Gampaha', '0115825655', '2025-02-12 10:32:08', 'Western', 'Gampaha', 'uploads/hospitals/gampaha.png', '0332222261', 'https://www.gdh.lk', 'gdh_admin', 'gdh@gmail.com', '$2y$10$r8UpEQE56//6PG2GgMpwVO6woAMektbbYDKpdwVLuSvUKu.xp3h06'),
(118, 'Kalutara District General Hospital', 'Kalutara', '0115825655', '2025-02-12 10:32:08', 'Western', 'Kalutara', 'uploads/hospitals/kalutara.png', '0342222261', 'https://www.kdh.lk', 'kdh_admin', 'kdh@gmail.com', 'password123'),
(119, 'Kandy Teaching Hospital', 'Kandy', '0115825655', '2025-02-12 10:32:08', 'Central', 'Kandy', 'uploads/hospitals/kandy.png', '0812222261', 'https://www.kgh.lk', 'kgh_admin', 'kgh@gmail.com', 'password123'),
(120, 'Nuwara Eliya District General Hospital', 'Nuwara Eliya', '0115825655', '2025-02-12 10:32:08', 'Central', 'Nuwara Eliya', 'uploads/hospitals/nuwaraeliya.png', '0522222261', 'https://www.nedgh.lk', 'nedgh_admin', 'nedgh@gmail.com', 'password123'),
(121, 'Matale General Hospital', 'Matale', '0115825655', '2025-02-12 10:32:08', 'Central', 'Matale', 'uploads/hospitals/matale.png', '0662222261', 'https://www.matgh.lk', 'matgh_admin', 'matgh@gmail.com', 'password123'),
(122, 'Teaching Hospital Karapitiya', 'Galle', '0115825655', '2025-02-12 10:32:08', 'Southern', 'Galle', 'uploads/hospitals/karapitiya.png', '0912232567', 'https://www.thkarapitiya.lk', 'thk_admin', 'thk@gmail.com', 'password123'),
(123, 'Matara District General Hospital', 'Matara', '0115825655', '2025-02-12 10:32:08', 'Southern', 'Matara', 'uploads/hospitals/matara.png', '0412222261', 'https://www.mdh.lk', 'mdh_admin', 'mdh@gmail.com', 'password123'),
(124, 'Hambantota General Hospital', 'Hambantota', '0115825655', '2025-02-12 10:32:08', 'Southern', 'Hambantota', 'uploads/hospitals/hambantota.png', '0472222261', 'https://www.hgh.lk', 'hgh_admin', 'hgh@gmail.com', 'password123'),
(125, 'Batticaloa Teaching Hospital', 'Batticaloa', '0115825655', '2025-02-12 10:32:08', 'Eastern', 'Batticaloa', 'uploads/hospitals/batticaloa.png', '0652222261', 'https://www.bth.lk', 'bth_admin', 'bth@gmail.com', 'password123'),
(126, 'Ampara General Hospital', 'Ampara', '0115825655', '2025-02-12 10:32:08', 'Eastern', 'Ampara', 'uploads/hospitals/ampara.png', '0632222261', 'https://www.agh.lk', 'agh_admin', 'agh@gmail.com', 'password123'),
(127, 'Trincomalee District General Hospital', 'Trincomalee', '0115825655', '2025-02-12 10:32:08', 'Eastern', 'Trincomalee', 'uploads/hospitals/trincomalee.png', '0262222261', 'https://www.tdgh.lk', 'tdgh_admin', 'tdgh@gmail.com', 'password123'),
(128, 'Jaffna Teaching Hospital', 'Jaffna', '0115825655', '2025-02-12 10:32:08', 'Northern', 'Jaffna', 'uploads/hospitals/jaffna.png', '0212222261', 'https://www.jth.lk', 'jth_admin', 'jth@gmail.com', 'password123'),
(129, 'Vavuniya General Hospital', 'Vavuniya', '0115825655', '2025-02-12 10:32:08', 'Northern', 'Vavuniya', 'uploads/hospitals/vavuniya.png', '0242222261', 'https://www.vgh.lk', 'vgh_admin', 'vgh@gmail.com', 'password123'),
(130, 'Mannar District General Hospital', 'Mannar', '0115825655', '2025-02-12 10:32:08', 'Northern', 'Mannar', 'uploads/hospitals/mannar.png', '0232222261', 'https://www.mdgh.lk', 'mdgh', 'mannardgh@gmail.com', 'password123'),
(131, 'Kurunegala Teaching Hospital', 'Kurunegala', '0115825655', '2025-02-12 10:32:08', 'North Western', 'Kurunegala', 'uploads/hospitals/kurunegala.png', '0372222261', 'https://www.kth.lk', 'kth_admin', 'kth@gmail.com', 'password123'),
(132, 'Puttalam District General Hospital', 'Puttalam', '0115825655', '2025-02-12 10:32:08', 'North Western', 'Puttalam', 'uploads/hospitals/puttalam.png', '0322222261', 'https://www.pdgh.lk', 'pdgh_admin', 'pdgh@gmail.com', 'password123'),
(133, 'Anuradhapura Teaching Hospital', 'Anuradhapura', '0115825655', '2025-02-12 10:32:08', 'North Central', 'Anuradhapura', 'uploads/hospitals/anuradhapura.png', '0252222261', 'https://www.ath.lk', 'ath_admin', 'ath@gmail.com', 'password123'),
(134, 'Polonnaruwa General Hospital', 'Polonnaruwa', '0115825655', '2025-02-12 10:32:08', 'North Central', 'Polonnaruwa', 'uploads/hospitals/polonnaruwa.png', '0272222261', 'https://www.pgh.lk', 'pgh_admin', 'pgh@gmail.com', 'password123'),
(135, 'Badulla General Hospital', 'Badulla', '0115825655', '2025-02-12 10:32:08', 'Uva', 'Badulla', 'uploads/hospitals/badulla.png', '0552222261', 'https://www.bgh.lk', 'bgh_admin', 'bgh@gmail.com', 'password123'),
(136, 'Monaragala District General Hospital', 'Monaragala', '0115825655', '2025-02-12 10:32:08', 'Uva', 'Monaragala', 'uploads/hospitals/monaragala.png', '0552222261', 'https://www.mdgh.lk', 'mdgh_admin', 'mdgh@gmail.com', 'password123'),
(137, 'Ratnapura Teaching Hospital', 'Ratnapura', '0115825655', '2025-02-12 10:32:08', 'Sabaragamuwa', 'Ratnapura', 'uploads/hospitals/ratnapura.png', '0452222261', 'https://www.rth.lk', 'rth_admin', 'rth@gmail.com', 'password123'),
(138, 'Kegalle District General Hospital', 'Kegalle', '0115825655', '2025-02-12 10:32:08', 'Sabaragamuwa', 'Kegalle', 'uploads/hospitals/kegalle.png', '0352222261', 'https://www.kdgh.lk', 'kdgh_admin', 'kdgh@gmail.com', 'password123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nic` (`nic`);

--
-- Indexes for table `blood_camps`
--
ALTER TABLE `blood_camps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `camp_id` (`camp_id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requesting_hospital_id` (`requesting_hospital_id`),
  ADD KEY `requested_hospital_id` (`requested_hospital_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nic` (`nic`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk` (`hospital_id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `blood_camps`
--
ALTER TABLE `blood_camps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=304;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_camps`
--
ALTER TABLE `blood_camps`
  ADD CONSTRAINT `blood_camps_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_blood_camps_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`requesting_hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blood_requests_ibfk_2` FOREIGN KEY (`requested_hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
