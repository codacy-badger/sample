-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 19, 2018 at 04:02 PM
-- Server version: 5.7.20-0ubuntu0.17.04.1
-- PHP Version: 7.0.26-1+ubuntu17.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `jobayan_stage`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `answer_id` int(10) UNSIGNED NOT NULL,
  `answer_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `answer_created` datetime NOT NULL,
  `answer_updated` datetime NOT NULL,
  `answer_name` varchar(255) NOT NULL,
  `answer_choices` json DEFAULT NULL,
  `answer_type` varchar(255) DEFAULT NULL,
  `answer_flag` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `applicant`
--

DROP TABLE IF EXISTS `applicant`;
CREATE TABLE `applicant` (
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `applicant_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `applicant_created` datetime NOT NULL,
  `applicant_updated` datetime NOT NULL,
  `applicant_status` json DEFAULT NULL,
  `applicant_type` varchar(255) DEFAULT NULL,
  `applicant_flag` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_answer`
--

DROP TABLE IF EXISTS `applicant_answer`;
CREATE TABLE `applicant_answer` (
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `answer_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_form`
--

DROP TABLE IF EXISTS `applicant_form`;
CREATE TABLE `applicant_form` (
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_post`
--

DROP TABLE IF EXISTS `applicant_post`;
CREATE TABLE `applicant_post` (
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_profile`
--

DROP TABLE IF EXISTS `applicant_profile`;
CREATE TABLE `applicant_profile` (
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `form`
--

DROP TABLE IF EXISTS `form`;
CREATE TABLE `form` (
  `form_id` int(10) UNSIGNED NOT NULL,
  `form_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `form_created` datetime NOT NULL,
  `form_updated` datetime NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `form_type` varchar(255) DEFAULT NULL,
  `form_flag` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `form_question`
--

DROP TABLE IF EXISTS `form_question`;
CREATE TABLE `form_question` (
  `form_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `post_form`
--

DROP TABLE IF EXISTS `post_form`;
CREATE TABLE `post_form` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profile_form`
--

DROP TABLE IF EXISTS `profile_form`;
CREATE TABLE `profile_form` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `profile_label`
--

DROP TABLE IF EXISTS `profile_label`;
CREATE TABLE `profile_label` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `label_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
CREATE TABLE `question` (
  `question_id` int(10) UNSIGNED NOT NULL,
  `question_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `question_created` datetime NOT NULL,
  `question_updated` datetime NOT NULL,
  `question_name` varchar(255) NOT NULL,
  `question_choices` json DEFAULT NULL,
  `question_type` varchar(255) DEFAULT NULL,
  `question_flag` int(1) UNSIGNED DEFAULT '0',
  `question_priority` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `question_answer`
--

DROP TABLE IF EXISTS `question_answer`;
CREATE TABLE `question_answer` (
  `question_id` int(10) UNSIGNED NOT NULL,
  `answer_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `answer_active` (`answer_active`),
  ADD KEY `answer_created` (`answer_created`),
  ADD KEY `answer_updated` (`answer_updated`),
  ADD KEY `answer_name` (`answer_name`),
  ADD KEY `answer_type` (`answer_type`);

--
-- Indexes for table `applicant`
--
ALTER TABLE `applicant`
  ADD PRIMARY KEY (`applicant_id`),
  ADD KEY `applicant_active` (`applicant_active`),
  ADD KEY `applicant_created` (`applicant_created`),
  ADD KEY `applicant_updated` (`applicant_updated`),
  ADD KEY `applicant_type` (`applicant_type`);

--
-- Indexes for table `applicant_answer`
--
ALTER TABLE `applicant_answer`
  ADD PRIMARY KEY (`applicant_id`,`answer_id`);

--
-- Indexes for table `applicant_form`
--
ALTER TABLE `applicant_form`
  ADD PRIMARY KEY (`applicant_id`,`form_id`);

--
-- Indexes for table `applicant_post`
--
ALTER TABLE `applicant_post`
  ADD PRIMARY KEY (`applicant_id`,`post_id`);

--
-- Indexes for table `applicant_profile`
--
ALTER TABLE `applicant_profile`
  ADD PRIMARY KEY (`applicant_id`,`profile_id`);

--
-- Indexes for table `form`
--
ALTER TABLE `form`
  ADD PRIMARY KEY (`form_id`),
  ADD KEY `form_active` (`form_active`),
  ADD KEY `form_created` (`form_created`),
  ADD KEY `form_updated` (`form_updated`),
  ADD KEY `form_name` (`form_name`),
  ADD KEY `form_type` (`form_type`);

--
-- Indexes for table `form_question`
--
ALTER TABLE `form_question`
  ADD PRIMARY KEY (`form_id`,`question_id`);


--
-- Indexes for table `post_form`
--
ALTER TABLE `post_form`
  ADD PRIMARY KEY (`post_id`,`form_id`);

--
-- Indexes for table `profile_form`
--
ALTER TABLE `profile_form`
  ADD PRIMARY KEY (`profile_id`,`form_id`);

--
-- Indexes for table `profile_label`
--
ALTER TABLE `profile_label`
  ADD PRIMARY KEY (`profile_id`,`label_id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `question_active` (`question_active`),
  ADD KEY `question_created` (`question_created`),
  ADD KEY `question_updated` (`question_updated`),
  ADD KEY `question_name` (`question_name`),
  ADD KEY `question_type` (`question_type`);

--
-- Indexes for table `question_answer`
--
ALTER TABLE `question_answer`
  ADD PRIMARY KEY (`question_id`,`answer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `answer_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `applicant`
--
ALTER TABLE `applicant`
  MODIFY `applicant_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `form`
--
ALTER TABLE `form`
  MODIFY `form_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `label`
--
--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `question_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;


  
DROP TABLE IF EXISTS `label`;
CREATE TABLE `label` (
  `label_id` int(10) UNSIGNED NOT NULL,
  `label_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `label_created` datetime NOT NULL,
  `label_updated` datetime NOT NULL,
  `label_custom` json DEFAULT NULL,
  `label_type` varchar(255) DEFAULT NULL,
  `label_flag` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`label_id`),
  ADD KEY `label_active` (`label_active`),
  ADD KEY `label_created` (`label_created`),
  ADD KEY `label_updated` (`label_updated`),
  ADD KEY `label_type` (`label_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `label`
--
ALTER TABLE `label`
  MODIFY `label_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `answer` DROP INDEX `answer_name`;
ALTER TABLE `answer` CHANGE `answer_name`  `answer_name` text NOT NULL;


ALTER TABLE `question` DROP INDEX `question_name`;
ALTER TABLE `question` CHANGE `question_name` `question_name` text NOT NULL;