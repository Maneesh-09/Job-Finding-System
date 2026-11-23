-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306 
-- Generation Time: Oct 06, 2026 at 12:54 PM
-- Server version: 8.3.0
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jfc`
--

-- --------------------------------------------------------

--
-- Table structure for table `accepted_application`
--

DROP TABLE IF EXISTS `accepted_application`;
CREATE TABLE IF NOT EXISTS `accepted_application` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `job_id` int NOT NULL,
  `fullname` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `skills` text COLLATE utf8mb4_general_ci NOT NULL,
  `experiences` text COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cv` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `accepted_at` datetime NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `cid` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('demo_admin', '$2y$10$KHnj3EdYKy7csMPihPez5.2jBUoNoHhazrW8GhNjTo42bq61D1VCm');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
CREATE TABLE IF NOT EXISTS `company` (
  `cid` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `company_pan` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `company_license` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `pan_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `company_logo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `est_year` varchar(4) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `employees` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `company_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `datecreated` datetime NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`cid`, `company_name`, `username`, `password`, `email`, `address`, `company_pan`, `company_license`, `pan_image`, `company_logo`, `est_year`, `employees`, `company_type`, `datecreated`) VALUES
(1, 'Himalayan Tech Demo', 'demo_tech', '$2y$12$SORPcXpiAWYsoazKySPWpeHunP.Y3WFXXwfi40hJbBtLTgQRAiM9u', 'tech@example.test', 'Kathmandu', 'DEMO-PAN-001', 'DEMO-LICENSE', '', '', '2020', '10-50', 'IT & Software', '2026-10-06 18:38:02'),
(2, 'Pokhara Creative Demo', 'demo_creative', '$2y$12$hX23CwexUOxXdawsZq9UEu1e/edtEH8mOPpKz9KwmWS0cIYLoUmSq', 'creative@example.test', 'Pokhara', 'DEMO-PAN-002', 'DEMO-LICENSE', '', '', '2020', '10-50', 'Design & Creative', '2026-10-06 18:38:02');

-- --------------------------------------------------------

--
-- Table structure for table `declined_application`
--

DROP TABLE IF EXISTS `declined_application`;
CREATE TABLE IF NOT EXISTS `declined_application` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `job_id` int NOT NULL,
  `fullname` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `skills` text COLLATE utf8mb4_general_ci NOT NULL,
  `experiences` text COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cv` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rejected_at` datetime NOT NULL,
  `username` varchar(111) COLLATE utf8mb4_general_ci NOT NULL,
  `cid` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobapplication`
--

DROP TABLE IF EXISTS `jobapplication`;
CREATE TABLE IF NOT EXISTS `jobapplication` (
  `id` int NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL,
  `fullname` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci NOT NULL,
  `skills` text COLLATE utf8mb4_general_ci NOT NULL,
  `experiences` text COLLATE utf8mb4_general_ci NOT NULL,
  `cv` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `applied_date` datetime NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `qualification` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `salary` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `openeddate` datetime NOT NULL,
  `expirydate` date NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `company_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `location`, `qualification`, `salary`, `image`, `username`, `openeddate`, `expirydate`, `category`, `company_id`) VALUES
(1, 'Junior PHP Developer', 'Build web applications using PHP and MySQL. Work with HTML, CSS and JavaScript.', 'Kathmandu', 'Bachelor or equivalent experience', 'NPR 35,000-50,000', '', 'demo_tech', '2026-10-06 18:38:02', '2026-12-05', 'IT & Software', 1),
(2, 'Frontend Developer', 'Create responsive interfaces with HTML, CSS and JavaScript.', 'Remote', 'Bachelor or equivalent experience', 'NPR 45,000-65,000', '', 'demo_tech', '2026-10-06 18:38:02', '2026-12-05', 'IT & Software', 1),
(3, 'Python Developer', 'Develop APIs and data processing tools using Python and MySQL.', 'Kathmandu', 'Bachelor or equivalent experience', 'NPR 50,000-75,000', '', 'demo_tech', '2026-10-06 18:38:02', '2026-12-05', 'IT & Software', 1),
(4, 'Graphic Designer', 'Use Graphic Design skills to create branding and social media artwork.', 'Pokhara', 'Bachelor or equivalent experience', 'NPR 30,000-45,000', '', 'demo_creative', '2026-10-06 18:38:02', '2026-12-05', 'Design & Creative', 2),
(5, 'Customer Support Associate', 'Provide Customer Service and use Communication skills to assist clients.', 'Pokhara', 'Bachelor or equivalent experience', 'NPR 25,000-35,000', '', 'demo_creative', '2026-10-06 18:38:02', '2026-12-05', 'Customer Service', 2),
(6, 'Marketing Assistant', 'Support campaigns, content planning and client Communication.', 'Pokhara', 'Bachelor or equivalent experience', 'NPR 28,000-40,000', '', 'demo_creative', '2026-10-06 18:38:02', '2026-12-05', 'Marketing & Sales', 2);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `lname` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `qualification` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `skills` text COLLATE utf8mb4_general_ci NOT NULL,
  `gender` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `datecreated` datetime NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `fname`, `lname`, `username`, `password`, `email`, `qualification`, `skills`, `gender`, `datecreated`) VALUES
(1, 'Aarav', 'Shrestha', 'demo_user', '$2y$12$Gkdo572idMVNysPOspBQOeoMq5mW5B82RyBgQkBghnetC6MjMZa7y', 'aarav@example.test', 'Bachelor', 'PHP, MySQL, JavaScript, HTML, CSS', 'Other', '2026-10-06 18:38:02'),
(2, 'Maya', 'Gurung', 'demo_designer', '$2y$12$wnPSgLjmXaRXs0mbBcKO6OqZLin/tCMLcL6axkrPlfA42kFOda/iy', 'maya@example.test', 'Bachelor', 'Graphic Design, Communication, Customer Service', 'Other', '2026-10-06 18:38:02');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
