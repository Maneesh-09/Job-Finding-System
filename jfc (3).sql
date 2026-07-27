-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3336
-- Generation Time: Oct 30, 2025 at 06:12 PM
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
-- Database: `jfc`
--

-- --------------------------------------------------------

--
-- Table structure for table `accepted_application`
--

CREATE TABLE `accepted_application` (
  `id` int(111) NOT NULL,
  `application_id` int(111) NOT NULL,
  `job_id` int(111) NOT NULL,
  `fullname` varchar(111) NOT NULL,
  `email` varchar(111) NOT NULL,
  `phone` int(10) NOT NULL,
  `address` text NOT NULL,
  `skills` text NOT NULL,
  `experiences` text NOT NULL,
  `photo` varchar(100) NOT NULL,
  `cv` varchar(20) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `username` varchar(50) NOT NULL,
  `cid` int(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('', ''),
('Bashanta', '$2y$10$.tVw8w0cuITCMuelpzBFaO7SrHIsIvAE4.Hg31zUc1myCzhZHMvFy'),
('kiran', '$2y$10$VPlzfeIg1AdZoM8GD5d4/eFzZkW8x3cCDn8SR8RZrkgUC5/RV9Cv2'),
('admin', '$2y$10$Nv8L8S7L7cZvfYlY0xGZLOP9UJO1n5fs./oxbvutVmaQVPvEThO8q');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `cid` int(3) NOT NULL,
  `company_name` varchar(40) NOT NULL,
  `username` varchar(10) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(30) NOT NULL,
  `address` varchar(50) NOT NULL,
  `company_pan` varchar(20) NOT NULL,
  `company_license` varchar(20) NOT NULL,
  `company_type` varchar(50) NOT NULL,
  `datecreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`cid`, `company_name`, `username`, `password`, `email`, `address`, `company_pan`, `company_license`, `company_type`, `datecreated`) VALUES
(9, 'Sweetisweethouse pvt.ltd', 'sweekriti', '$2y$10$Ztmn8ANAVv219/5OY5QqOeS0QlUjPGKMwCgdeP99bZgre3JxkOGhC', 'sweethouse@gmail.com', 'jorpati,narayantar', '122-23-004', '1997-94-95', 'Manufacturing', '2025-10-29 14:58:52'),
(10, 'bpsolution pvt.ltd', 'bpsolution', '$2y$10$mARlTKVQ8DfYxqcKTBhpremFsrAtLo7OwZKKhbP/2dysBBXaaCJf2', 'bpsolution@gmail.com', 'chabahel,Kathmandu', '122-23-005', '1997-94-99', 'IT', '2025-10-29 15:00:21'),
(11, 'bpcleaningservices pvt.ltd', 'bpcleaning', '$2y$10$TkqKI16awO/SH3oZCSruZOJexKZjIP0mDxFpBI2/1OrTGIbxdlIWq', 'bpcleaning@gmail.com', 'putaalisadak,Kathmandu', '122-23-009', '1997-94-199', 'Service', '2025-10-29 15:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `declined_application`
--

CREATE TABLE `declined_application` (
  `id` int(111) NOT NULL,
  `application_id` int(111) NOT NULL,
  `job_id` int(111) NOT NULL,
  `fullname` varchar(111) NOT NULL,
  `email` varchar(111) NOT NULL,
  `phone` int(10) NOT NULL,
  `address` text NOT NULL,
  `skills` text NOT NULL,
  `experiences` text NOT NULL,
  `photo` varchar(111) NOT NULL,
  `cv` varchar(111) NOT NULL,
  `rejected_at` datetime NOT NULL,
  `username` varchar(111) NOT NULL,
  `cid` int(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobapplication`
--

CREATE TABLE `jobapplication` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `fullname` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `phone` int(10) NOT NULL,
  `address` varchar(100) NOT NULL,
  `skills` varchar(200) NOT NULL,
  `experiences` text NOT NULL,
  `cv` varchar(200) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `applied_date` datetime NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(50) NOT NULL,
  `qualification` varchar(100) NOT NULL,
  `salary` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  `username` varchar(20) NOT NULL,
  `openeddate` datetime NOT NULL,
  `expirydate` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `company_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `location`, `qualification`, `salary`, `image`, `username`, `openeddate`, `expirydate`, `category`, `company_id`) VALUES
(25, 'Java Developer', 'we need a java  programmer who has a great control in java programming language with a better experience of 2 years or any great project he/she have made and in market running ', 'Putalisadak,Kathmandu', 'Bachelor degree in any it course', '100k to 150K per month/Rs', 'programmer.jpg', 'bpsolution', '2025-10-29 15:12:27', '2025-11-08', 'IT & Software', 10);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `qualification` varchar(50) NOT NULL,
  `skills` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `datecreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`uid`, `fname`, `lname`, `username`, `password`, `email`, `qualification`, `skills`, `gender`, `datecreated`) VALUES
(8, 'sweekriti', 'karki', 'sweekriti', '$2y$10$tYljZ57IeWyuPDA5uxUQ3eP2rGqAJleVReutBVMvYaoSPc6kYjB3i', 'sweekriti@gmail.com', '+2', 'JavaScript, Python, Java, C++, Other', 'Female', '2025-10-29 14:52:18'),
(9, 'Bashanta', 'Pokharel', 'bashanta', '$2y$10$uiw9KjUtD8JLwVddSjnVm.ojQGZ69NHZKqiXTiRzhAjBDhT6Wnb9W', 'pokharelbashantabb@gmail.com', 'Bachelor', 'Python, Java, C++, Other', 'Male', '2025-10-29 14:52:58'),
(10, 'Motiram', 'subedi', 'moti', '$2y$10$GrJnLy/RB9K.Q.k6eaiF9OeaeV2FNhHP6LprmCfTZULEuvRQ5M8ay', 'moti@gmail.com', '+2', 'HTML, CSS, PHP, MySQL', 'Male', '2025-10-29 14:55:30'),
(11, 'kusum', 'dahal', 'kusum', '$2y$10$fhHKopVM7VGmt2zlgaCuTeCNYkEVhsGrjT4a8Q7jBKzJxLvg3E68q', 'kusum@gmail.com', 'Bachelor', 'Other', 'Female', '2025-10-29 14:56:17'),
(12, 'alisha', 'poudel', 'alisha', '$2y$10$xL/1LIDYfODCVulH320D5.xT2y3nA0p93EovhhKlTU3eNx.ZXB7Ky', 'alisha@gmail.com', 'Bachelor', 'HTML, Other', 'Female', '2025-10-29 14:57:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accepted_application`
--
ALTER TABLE `accepted_application`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `declined_application`
--
ALTER TABLE `declined_application`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobapplication`
--
ALTER TABLE `jobapplication`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accepted_application`
--
ALTER TABLE `accepted_application`
  MODIFY `id` int(111) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `cid` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `declined_application`
--
ALTER TABLE `declined_application`
  MODIFY `id` int(111) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobapplication`
--
ALTER TABLE `jobapplication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
