DROP TABLE IF EXISTS `research`;

CREATE TABLE `research` (`blog_id` int(10) UNSIGNED NOT NULL auto_increment, `blog_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `blog_created` datetime NOT NULL, `blog_updated` datetime NOT NULL, `research_position` json DEFAULT NULL, `research_location` json DEFAULT NULL, `research_type` varchar(255) DEFAULT 'seeker', `research_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`blog_id`), KEY `blog_active` (`blog_active`), 
KEY `blog_created` (`blog_created`), 
KEY `blog_updated` (`blog_updated`), 
KEY `research_type` (`research_type`), 
KEY `research_flag` (`research_flag`));

DROP TABLE IF EXISTS `research_profile`;

CREATE TABLE `research_profile` (`blog_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`blog_id`, `profile_id`));