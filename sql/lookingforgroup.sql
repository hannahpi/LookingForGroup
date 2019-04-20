SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lookingforgroup`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `ID` bigint(20) NOT NULL,
  `LeaderID` bigint(20) DEFAULT NULL COMMENT 'Leader''s user id',
  `Name` varchar(50) DEFAULT NULL,
  `Desc` longtext,
  `StartTime` time NOT NULL DEFAULT '22:00:00' COMMENT '24 hr start time',
  `Duration` time NOT NULL DEFAULT '03:00:00' COMMENT 'duration'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group_tech`
--

CREATE TABLE `group_tech` (
  `GroupID` bigint(20) NOT NULL,
  `TechID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `technologies`
--

CREATE TABLE `technologies` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Desc` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` bigint(20) NOT NULL,
  `GroupID` bigint(20) DEFAULT NULL,
  `Email` varchar(50) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `StartTime` time NOT NULL DEFAULT '22:00:00',
  `Duration` int(11) NOT NULL,
  `TimezoneOffset` decimal(10,0) NOT NULL,
  `DST` tinyint(1) NOT NULL DEFAULT '1',
  `Location` text,
  `TechPrefsID` int(11) NOT NULL,
  `VerificationHash` varchar(50) NOT NULL DEFAULT '99999999999999999999'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_tech`
--

CREATE TABLE `user_tech` (
  `UserID` bigint(20) NOT NULL,
  `TechID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `group_leader_fk` (`LeaderID`);

--
-- Indexes for table `group_tech`
--
ALTER TABLE `group_tech`
  ADD KEY `grouptech_tech_fk` (`TechID`),
  ADD KEY `grouptech_group_fk` (`GroupID`);

--
-- Indexes for table `technologies`
--
ALTER TABLE `technologies`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `users_group_fk` (`GroupID`);

--
-- Indexes for table `user_tech`
--
ALTER TABLE `user_tech`
  ADD KEY `usertech_fk` (`UserID`),
  ADD KEY `usertech_tech_fk` (`TechID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `technologies`
--
ALTER TABLE `technologies`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `group_leader_fk` FOREIGN KEY (`LeaderID`) REFERENCES `users` (`ID`);

--
-- Constraints for table `group_tech`
--
ALTER TABLE `group_tech`
  ADD CONSTRAINT `grouptech_group_fk` FOREIGN KEY (`GroupID`) REFERENCES `groups` (`ID`),
  ADD CONSTRAINT `grouptech_tech_fk` FOREIGN KEY (`TechID`) REFERENCES `technologies` (`ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_group_fk` FOREIGN KEY (`GroupID`) REFERENCES `groups` (`ID`);

--
-- Constraints for table `user_tech`
--
ALTER TABLE `user_tech`
  ADD CONSTRAINT `usertech_fk` FOREIGN KEY (`UserID`) REFERENCES `users` (`ID`),
  ADD CONSTRAINT `usertech_tech_fk` FOREIGN KEY (`TechID`) REFERENCES `technologies` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
