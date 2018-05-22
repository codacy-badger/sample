DROP TABLE IF EXISTS `interview_schedule`;

CREATE TABLE `interview_schedule` (`interview_schedule_id` int(10) UNSIGNED NOT NULL auto_increment, `interview_schedule_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `interview_schedule_created` datetime NOT NULL, `interview_schedule_updated` datetime NOT NULL, `interview_schedule_date` date DEFAULT NULL, `interview_schedule_start_time` time DEFAULT NULL, `interview_schedule_end_time` time DEFAULT NULL, `interview_schedule_status` varchar(255) DEFAULT NULL, `interview_schedule_meta` json DEFAULT NULL, `interview_schedule_type` varchar(255) DEFAULT NULL, `interview_schedule_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`interview_schedule_id`), KEY `interview_schedule_active` (`interview_schedule_active`), 
KEY `interview_schedule_created` (`interview_schedule_created`), 
KEY `interview_schedule_updated` (`interview_schedule_updated`), 
KEY `interview_schedule_status` (`interview_schedule_status`), 
KEY `interview_schedule_type` (`interview_schedule_type`));

DROP TABLE IF EXISTS `interview_schedule_interview_setting`;

CREATE TABLE `interview_schedule_interview_setting` (`interview_schedule_id` int(10) UNSIGNED NOT NULL, `interview_setting_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`interview_schedule_id`, `interview_setting_id`));

DROP TABLE IF EXISTS `interview_schedule_profile`;

CREATE TABLE `interview_schedule_profile` (`interview_schedule_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`interview_schedule_id`, `profile_id`));

DROP TABLE IF EXISTS `interview_schedule_post`;

CREATE TABLE `interview_schedule_post` (`interview_schedule_id` int(10) UNSIGNED NOT NULL, `post_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`interview_schedule_id`, `post_id`));

DROP TABLE IF EXISTS `interview_setting`;

CREATE TABLE `interview_setting` (`interview_setting_id` int(10) UNSIGNED NOT NULL auto_increment, `interview_setting_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `interview_setting_created` datetime NOT NULL, `interview_setting_updated` datetime NOT NULL, `interview_setting_date` date DEFAULT NULL, `interview_setting_start_time` time DEFAULT NULL, `interview_setting_end_time` time DEFAULT NULL, `interview_setting_slots` int(11) unsigned DEFAULT 0, `interview_setting_meta` json DEFAULT NULL, `interview_setting_type` varchar(255) DEFAULT NULL, `interview_setting_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`interview_setting_id`), KEY `interview_setting_active` (`interview_setting_active`), 
KEY `interview_setting_created` (`interview_setting_created`), 
KEY `interview_setting_updated` (`interview_setting_updated`), 
KEY `interview_setting_type` (`interview_setting_type`));

DROP TABLE IF EXISTS `interview_setting_profile`;

CREATE TABLE `interview_setting_profile` (`interview_setting_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`interview_setting_id`, `profile_id`));