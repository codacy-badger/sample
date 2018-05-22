DROP TABLE IF EXISTS `profile`;

CREATE TABLE `profile` (
  `profile_id` int(10) UNSIGNED NOT NULL COMMENT 'Database Generated',
  `profile_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `profile_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `profile_phone` varchar(255) DEFAULT NULL,
  `profile_slug` varchar(255) DEFAULT NULL,
  `profile_credits` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `profile_detail` text CHARACTER SET utf8 COLLATE utf8_bin,
  `profile_image` varchar(255) DEFAULT NULL,
  `profile_company` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `profile_gender` varchar(255) DEFAULT NULL,
  `profile_birth` date DEFAULT NULL,
  `profile_website` varchar(255) DEFAULT NULL,
  `profile_facebook` varchar(255) DEFAULT NULL,
  `profile_linkedin` varchar(255) DEFAULT NULL,
  `profile_twitter` varchar(255) DEFAULT NULL,
  `profile_google` varchar(255) DEFAULT NULL,
  `profile_address_street` varchar(255) DEFAULT NULL,
  `profile_address_city` varchar(255) DEFAULT NULL,
  `profile_address_state` varchar(255) DEFAULT NULL,
  `profile_address_country` varchar(255) DEFAULT NULL,
  `profile_address_postal` varchar(255) DEFAULT NULL,
  `profile_package` json DEFAULT NULL,
  `profile_achievements` json DEFAULT NULL,
  `profile_experience` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `profile_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `profile_type` varchar(255) DEFAULT NULL,
  `profile_flag` int(1) UNSIGNED DEFAULT NULL,
  `profile_created` datetime NOT NULL,
  `profile_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `profile_active` (`profile_active`),
  ADD KEY `profile_type` (`profile_type`),
  ADD KEY `profile_flag` (`profile_flag`),
  ADD KEY `profile_created` (`profile_created`),
  ADD KEY `profile_updated` (`profile_updated`),
  ADD KEY `profile_address_country` (`profile_address_country`),
  ADD KEY `profile_address_state` (`profile_address_state`),
  ADD KEY `profile_address_city` (`profile_address_city`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `profile_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Database Generated';

DROP TABLE IF EXISTS `profile_resume`;

CREATE TABLE `profile_resume` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `resume_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile_resume` ADD PRIMARY KEY (`profile_id`,`resume_id`);

ALTER TABLE `profile` ADD COLUMN `profile_verified` INT(1) UNSIGNED  DEFAULT 0;

ALTER TABLE `profile`
ADD `profile_tags` json DEFAULT NULL,
ADD `profile_story` json DEFAULT NULL,
ADD `profile_campaigns` json DEFAULT NULL;

ALTER TABLE `profile`
ADD `profile_subscribe` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'newsletter subscription value' AFTER `profile_story`,
ADD INDEX (`profile_subscribe`);

ALTER TABLE `profile`  ADD `profile_bounce` INT(2) UNSIGNED NOT NULL DEFAULT '0'  AFTER `profile_subscribe`,  ADD   INDEX  (`profile_bounce`);

DROP TABLE IF EXISTS `profile_form`;

CREATE TABLE `profile_form` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile_form` ADD PRIMARY KEY (`profile_id`,`form_id`);

DROP TABLE IF EXISTS `profile_label`;

CREATE TABLE `profile_label` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `label_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile_label` ADD PRIMARY KEY (`profile_id`,`label_id`);

ALTER TABLE `profile` ADD `profile_interviewer` JSON NULL DEFAULT NULL AFTER `profile_story`;
ALTER TABLE `profile` ADD `profile_campaigns` json DEFAULT NULL;
ALTER TABLE `profile` ADD `profile_email_flag` INT NOT NULL DEFAULT '0' AFTER `profile_flag`;