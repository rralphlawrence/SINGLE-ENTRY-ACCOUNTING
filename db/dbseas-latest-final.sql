-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2021 at 04:49 AM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 7.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbseas`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblaccounts`
--

CREATE TABLE `tblaccounts` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(60) NOT NULL,
  `business_name` text NOT NULL,
  `business_owner` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tblcashbookentry`
--

CREATE TABLE `tblcashbookentry` (
  `cbe_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `order_by` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  `inflows` int(255) NOT NULL,
  `outflows` int(255) NOT NULL,
  `balance` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tblcashflow`
--

CREATE TABLE `tblcashflow` (
  `cf_id` int(50) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `date_month` varchar(4) NOT NULL,
  `date_year` varchar(4) NOT NULL,
  `type` varchar(9) NOT NULL,
  `category` varchar(25) NOT NULL,
  `description` varchar(100) NOT NULL,
  `first_balance` int(255) NOT NULL,
  `amount` int(255) NOT NULL,
  `sign` varchar(8) NOT NULL,
  `details` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tblincomestatement`
--

CREATE TABLE `tblincomestatement` (
  `is_id` int(50) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `date_month` varchar(4) NOT NULL,
  `date_year` varchar(4) NOT NULL,
  `type` varchar(9) NOT NULL,
  `category` varchar(8) NOT NULL,
  `description` varchar(100) NOT NULL,
  `amount` int(255) NOT NULL,
  `details` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tblcashbookentry`
--
ALTER TABLE `tblcashbookentry`
  ADD PRIMARY KEY (`cbe_id`);

--
-- Indexes for table `tblcashflow`
--
ALTER TABLE `tblcashflow`
  ADD PRIMARY KEY (`cf_id`);

--
-- Indexes for table `tblincomestatement`
--
ALTER TABLE `tblincomestatement`
  ADD PRIMARY KEY (`is_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblaccounts`
--
ALTER TABLE `tblaccounts`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblcashbookentry`
--
ALTER TABLE `tblcashbookentry`
  MODIFY `cbe_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblcashflow`
--
ALTER TABLE `tblcashflow`
  MODIFY `cf_id` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblincomestatement`
--
ALTER TABLE `tblincomestatement`
  MODIFY `is_id` int(50) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
