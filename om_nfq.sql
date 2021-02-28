-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2021 at 11:24 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 8.0.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `om_nfq`
--

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_numberOfGroups` int(11) NOT NULL,
  `p_studentsPerGroup` int(11) NOT NULL,
  `p_createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`p_id`, `p_name`, `p_numberOfGroups`, `p_studentsPerGroup`, `p_createdAt`) VALUES
(1, 'Winter Photoshoot', 5, 2, '2021-02-21 13:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `p_s_junction`
--

CREATE TABLE `p_s_junction` (
  `p_id` int(11) NOT NULL,
  `s_id` int(11) NOT NULL,
  `g_number` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `p_s_junction`
--

INSERT INTO `p_s_junction` (`p_id`, `s_id`, `g_number`) VALUES
(1, 1, 2),
(1, 2, 1),
(1, 3, NULL),
(1, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `s_id` int(11) NOT NULL,
  `s_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`s_id`, `s_name`) VALUES
(1, 'Ada Lovelace'),
(2, 'Grace Hopper'),
(4, 'Joan Clarke'),
(3, 'Margaret Hamilton');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `p_s_junction`
--
ALTER TABLE `p_s_junction`
  ADD PRIMARY KEY (`p_id`,`s_id`),
  ADD KEY `p_id` (`p_id`),
  ADD KEY `s_id` (`s_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`s_id`),
  ADD UNIQUE KEY `s_name` (`s_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `s_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `p_s_junction`
--
ALTER TABLE `p_s_junction`
  ADD CONSTRAINT `p_s_junction_ibfk_1` FOREIGN KEY (`p_id`) REFERENCES `project` (`p_id`),
  ADD CONSTRAINT `p_s_junction_ibfk_2` FOREIGN KEY (`s_id`) REFERENCES `student` (`s_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
