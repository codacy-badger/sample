DROP TABLE IF EXISTS `research`;

CREATE TABLE `research` (`research_id` int(10) UNSIGNED NOT NULL auto_increment, `research_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `research_created` datetime NOT NULL, `research_updated` datetime NOT NULL, `research_position` json DEFAULT NULL, `research_location` json DEFAULT NULL, `research_type` varchar(255) DEFAULT 'seeker', `research_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`research_id`), KEY `research_active` (`research_active`), 
KEY `research_created` (`research_created`), 
KEY `research_updated` (`research_updated`), 
KEY `research_type` (`research_type`), 
KEY `research_flag` (`research_flag`));

DROP TABLE IF EXISTS `research_profile`;

CREATE TABLE `research_profile` (`research_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`research_id`, `profile_id`));