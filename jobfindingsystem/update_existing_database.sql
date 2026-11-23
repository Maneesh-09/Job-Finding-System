-- Update an EXISTING Job Finding System database; keeps accounts and applications.
-- Select your existing database (normally jfc) in phpMyAdmin, then import this file.
-- Back up the database first. MySQL/MariaDB schema changes commit independently.
-- You can import this file again. Do not import allsql.sql over an existing database.

DELIMITER $$
DROP PROCEDURE IF EXISTS jfc_update_existing_database$$
CREATE PROCEDURE jfc_update_existing_database()
BEGIN
    DECLARE previous_sql_mode TEXT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET SESSION sql_mode = previous_sql_mode;
        RESIGNAL;
    END;
    SET previous_sql_mode = @@SESSION.sql_mode;
    -- Make unsupported/truncating conversions fail instead of changing values.
    SET SESSION sql_mode = CONCAT_WS(',', NULLIF(previous_sql_mode, ''), 'STRICT_ALL_TABLES');

    IF DATABASE() IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Select the existing database before running this migration.';
    END IF;
    IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users') THEN
        IF EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user') THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Both user and users tables exist. Resolve the duplicate tables before migrating.';
        END IF;
        RENAME TABLE `users` TO `user`;
    END IF;

    -- Create missing tables without replacing tables that already contain records.
    CREATE TABLE IF NOT EXISTS `accepted_application` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    CREATE TABLE IF NOT EXISTS `admin` (
      `username` varchar(20) NOT NULL,
      `password` varchar(60) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE IF NOT EXISTS `company` (
      `cid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    CREATE TABLE IF NOT EXISTS `declined_application` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    CREATE TABLE IF NOT EXISTS `jobapplication` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    CREATE TABLE IF NOT EXISTS `jobs` (
      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    CREATE TABLE IF NOT EXISTS `user` (
      `uid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_license') THEN
        ALTER TABLE `company` ADD `company_license` VARCHAR(20) NOT NULL DEFAULT '';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company' AND COLUMN_NAME = 'pan_image') THEN
        ALTER TABLE `company` ADD `pan_image` VARCHAR(255) NOT NULL DEFAULT '';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company' AND COLUMN_NAME = 'company_logo') THEN
        ALTER TABLE `company` ADD `company_logo` VARCHAR(255) NOT NULL DEFAULT '';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company' AND COLUMN_NAME = 'est_year') THEN
        ALTER TABLE `company` ADD `est_year` VARCHAR(4) NOT NULL DEFAULT '';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'company' AND COLUMN_NAME = 'employees') THEN
        ALTER TABLE `company` ADD `employees` VARCHAR(30) NOT NULL DEFAULT '';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobs' AND COLUMN_NAME = 'company_id') THEN
        ALTER TABLE `jobs` ADD `company_id` INT NOT NULL DEFAULT 0;
    END IF;
    -- Reconnect older jobs only when their company username identifies one company.
    UPDATE `jobs` j JOIN (
        SELECT username, MIN(cid) AS cid FROM `company` GROUP BY username HAVING COUNT(*) = 1
    ) c ON j.username = c.username
    SET j.company_id = c.cid WHERE j.company_id IS NULL OR j.company_id = 0;

    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobapplication' AND COLUMN_NAME = 'experiences') THEN
        IF EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jobapplication' AND COLUMN_NAME = 'experience') THEN
            ALTER TABLE `jobapplication` CHANGE `experience` `experiences` TEXT;
        ELSE
            ALTER TABLE `jobapplication` ADD `experiences` TEXT NULL;
        END IF;
    END IF;

    -- Older schemas allowed NULL profile/application fields. Keep those rows and
    -- represent missing text as an empty string before making fields required.
    UPDATE `user` SET skills = COALESCE(skills, ''), fname = COALESCE(fname, ''),
        lname = COALESCE(lname, ''), email = COALESCE(email, '');
    ALTER TABLE `user` MODIFY skills TEXT NOT NULL, MODIFY fname VARCHAR(80) NOT NULL,
        MODIFY lname VARCHAR(80) NOT NULL, MODIFY email VARCHAR(254) NOT NULL;
    UPDATE `company` SET company_license = COALESCE(company_license, ''),
        company_name = COALESCE(company_name, ''), email = COALESCE(email, ''),
        address = COALESCE(address, '');
    ALTER TABLE `company` MODIFY company_license VARCHAR(20) NOT NULL DEFAULT '',
        MODIFY username VARCHAR(20) NOT NULL, MODIFY company_name VARCHAR(150) NOT NULL,
        MODIFY email VARCHAR(254) NOT NULL, MODIFY address VARCHAR(255) NOT NULL;
    ALTER TABLE `jobs` MODIFY title VARCHAR(150) NOT NULL;

    ALTER TABLE `jobapplication` MODIFY phone VARCHAR(30) NULL;
    UPDATE `jobapplication` SET phone = COALESCE(phone, ''), cv = COALESCE(cv, ''),
        photo = COALESCE(photo, ''), fullname = COALESCE(fullname, ''),
        email = COALESCE(email, ''), address = COALESCE(address, ''),
        skills = COALESCE(skills, ''), experiences = COALESCE(experiences, '');
    ALTER TABLE `jobapplication` MODIFY phone VARCHAR(30) NOT NULL,
        MODIFY cv VARCHAR(255) NOT NULL, MODIFY photo VARCHAR(255) NOT NULL,
        MODIFY fullname VARCHAR(150) NOT NULL, MODIFY email VARCHAR(254) NOT NULL,
        MODIFY address TEXT NOT NULL, MODIFY skills TEXT NOT NULL, MODIFY experiences TEXT NOT NULL;

    ALTER TABLE `accepted_application` MODIFY phone VARCHAR(30) NULL;
    UPDATE `accepted_application` SET phone = COALESCE(phone, ''), cv = COALESCE(cv, ''),
        photo = COALESCE(photo, ''), fullname = COALESCE(fullname, ''),
        email = COALESCE(email, ''), address = COALESCE(address, ''),
        skills = COALESCE(skills, ''), experiences = COALESCE(experiences, '');
    ALTER TABLE `accepted_application` MODIFY phone VARCHAR(30) NOT NULL,
        MODIFY cv VARCHAR(255) NOT NULL, MODIFY photo VARCHAR(255) NOT NULL,
        MODIFY fullname VARCHAR(150) NOT NULL, MODIFY email VARCHAR(254) NOT NULL,
        MODIFY address TEXT NOT NULL, MODIFY skills TEXT NOT NULL, MODIFY experiences TEXT NOT NULL;

    ALTER TABLE `declined_application` MODIFY phone VARCHAR(30) NULL;
    UPDATE `declined_application` SET phone = COALESCE(phone, ''), cv = COALESCE(cv, ''),
        photo = COALESCE(photo, ''), fullname = COALESCE(fullname, ''),
        email = COALESCE(email, ''), address = COALESCE(address, ''),
        skills = COALESCE(skills, ''), experiences = COALESCE(experiences, '');
    ALTER TABLE `declined_application` MODIFY phone VARCHAR(30) NOT NULL,
        MODIFY cv VARCHAR(255) NOT NULL, MODIFY photo VARCHAR(255) NOT NULL,
        MODIFY fullname VARCHAR(150) NOT NULL, MODIFY email VARCHAR(254) NOT NULL,
        MODIFY address TEXT NOT NULL, MODIFY skills TEXT NOT NULL, MODIFY experiences TEXT NOT NULL;
    SET SESSION sql_mode = previous_sql_mode;
END$$
CALL jfc_update_existing_database()$$
DROP PROCEDURE jfc_update_existing_database$$
DELIMITER ;
