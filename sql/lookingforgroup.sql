--
-- Database: `lookingforgroup`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `ID` bigint(20) NOT NULL,
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
  `GroupID` int(11) DEFAULT NULL,
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
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `technologies`
--
ALTER TABLE `technologies`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
