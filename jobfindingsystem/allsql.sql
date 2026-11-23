-- Fresh installation schema (no demo accounts). Import into an empty jfc database.
SET NAMES utf8mb4;

CREATE TABLE `accepted_application` (
  `id` int(111) NOT NULL,
  `application_id` int(111) NOT NULL,
  `job_id` int(111) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `skills` text NOT NULL,
  `experiences` text NOT NULL,
  `photo` varchar(100) NOT NULL,
  `cv` varchar(255) NOT NULL,
  `accepted_at` datetime NOT NULL,
  `username` varchar(50) NOT NULL,
  `cid` int(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admin` (
  `username` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `company` (
  `cid` int(3) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(254) NOT NULL,
  `address` varchar(255) NOT NULL,
  `company_pan` varchar(20) NOT NULL,
  `company_license` varchar(20) NOT NULL DEFAULT '',
  `pan_image` varchar(255) NOT NULL DEFAULT '',
  `company_logo` varchar(255) NOT NULL DEFAULT '',
  `est_year` varchar(4) NOT NULL DEFAULT '',
  `employees` varchar(30) NOT NULL DEFAULT '',
  `company_type` varchar(50) NOT NULL,
  `datecreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `declined_application` (
  `id` int(111) NOT NULL,
  `application_id` int(111) NOT NULL,
  `job_id` int(111) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `skills` text NOT NULL,
  `experiences` text NOT NULL,
  `photo` varchar(111) NOT NULL,
  `cv` varchar(111) NOT NULL,
  `rejected_at` datetime NOT NULL,
  `username` varchar(111) NOT NULL,
  `cid` int(111) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `jobapplication` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(254) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `skills` text NOT NULL,
  `experiences` text NOT NULL,
  `cv` varchar(200) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `applied_date` datetime NOT NULL,
  `username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
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

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `fname` varchar(80) NOT NULL,
  `lname` varchar(80) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(254) NOT NULL,
  `qualification` varchar(50) NOT NULL,
  `skills` text NOT NULL,
  `gender` varchar(10) NOT NULL,
  `datecreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `accepted_application`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `company`
  ADD PRIMARY KEY (`cid`);

ALTER TABLE `declined_application`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `jobapplication`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `accepted_application`
  MODIFY `id` int(111) NOT NULL AUTO_INCREMENT;

ALTER TABLE `company`
  MODIFY `cid` int(3) NOT NULL AUTO_INCREMENT;

ALTER TABLE `declined_application`
  MODIFY `id` int(111) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jobapplication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
