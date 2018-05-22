DROP TABLE IF EXISTS `accomplishment`;

CREATE TABLE `accomplishment` (`accomplishment_id` int(10) UNSIGNED NOT NULL auto_increment, `accomplishment_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `accomplishment_created` datetime NOT NULL, `accomplishment_updated` datetime NOT NULL, `accomplishment_name` varchar(255) DEFAULT NULL, `accomplishment_description` text DEFAULT NULL, `accomplishment_from` date DEFAULT NULL, `accomplishment_to` date DEFAULT NULL, `accomplishment_type` varchar(255) DEFAULT NULL, `accomplishment_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`accomplishment_id`), KEY `accomplishment_active` (`accomplishment_active`),
KEY `accomplishment_created` (`accomplishment_created`),
KEY `accomplishment_updated` (`accomplishment_updated`),
KEY `accomplishment_type` (`accomplishment_type`),
KEY `accomplishment_flag` (`accomplishment_flag`));

DROP TABLE IF EXISTS `accomplishment_information`;

CREATE TABLE `accomplishment_information` (`accomplishment_id` int(10) UNSIGNED NOT NULL, `information_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`accomplishment_id`, `information_id`));

DROP TABLE IF EXISTS `education`;

CREATE TABLE `education` (`education_id` int(10) UNSIGNED NOT NULL auto_increment, `education_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `education_created` datetime NOT NULL, `education_updated` datetime NOT NULL, `education_school` varchar(255) DEFAULT NULL, `education_degree` varchar(255) DEFAULT NULL, `education_activity` varchar(255) DEFAULT NULL, `education_from` date DEFAULT NULL, `education_to` date DEFAULT NULL, `education_type` varchar(255) DEFAULT NULL, `education_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`education_id`), KEY `education_active` (`education_active`),
KEY `education_created` (`education_created`),
KEY `education_updated` (`education_updated`),
KEY `education_type` (`education_type`),
KEY `education_flag` (`education_flag`));

DROP TABLE IF EXISTS `education_information`;

CREATE TABLE `education_information` (`education_id` int(10) UNSIGNED NOT NULL, `information_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`education_id`, `information_id`));

DROP TABLE IF EXISTS `experience`;

CREATE TABLE `experience` (`experience_id` int(10) UNSIGNED NOT NULL auto_increment, `experience_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `experience_created` datetime NOT NULL, `experience_updated` datetime NOT NULL, `experience_title` varchar(255) DEFAULT NULL, `experience_company` varchar(255) DEFAULT NULL, `experience_industry` varchar(255) DEFAULT NULL, `experience_related` varchar(255) DEFAULT 'unknown', `experience_from` date DEFAULT NULL, `experience_to` date DEFAULT NULL, `experience_type` varchar(255) DEFAULT NULL, `experience_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`experience_id`), KEY `experience_active` (`experience_active`),
KEY `experience_created` (`experience_created`),
KEY `experience_updated` (`experience_updated`),
KEY `experience_type` (`experience_type`),
KEY `experience_flag` (`experience_flag`));

DROP TABLE IF EXISTS `experience_information`;

CREATE TABLE `experience_information` (`experience_id` int(10) UNSIGNED NOT NULL, `information_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`experience_id`, `information_id`));

DROP TABLE IF EXISTS `information`;

CREATE TABLE `information` (`information_id` int(10) UNSIGNED NOT NULL auto_increment, `information_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `information_created` datetime NOT NULL, `information_updated` datetime NOT NULL, `information_heading` varchar(255) DEFAULT NULL, `information_civil_status` varchar(255) DEFAULT 'unknown', `information_skills` json DEFAULT NULL, `information_type` varchar(255) DEFAULT NULL, `information_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`information_id`), KEY `information_active` (`information_active`),
KEY `information_created` (`information_created`),
KEY `information_updated` (`information_updated`),
KEY `information_type` (`information_type`),
KEY `information_flag` (`information_flag`));

DROP TABLE IF EXISTS `information_profile`;

CREATE TABLE `information_profile` (`information_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`information_id`, `profile_id`));

ALTER TABLE `experience` ADD `experience_description` TEXT NULL DEFAULT NULL AFTER `experience_to`;