DROP TABLE IF EXISTS `history`;

CREATE TABLE `history` (`history_id` int(10) UNSIGNED NOT NULL auto_increment, `history_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `history_created` datetime NOT NULL, `history_updated` datetime NOT NULL, `history_value` json DEFAULT NULL, `history_note` text DEFAULT NULL, `history_attribute` varchar(255) DEFAULT NULL, `history_type` varchar(255) DEFAULT NULL, `history_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`history_id`), KEY `history_active` (`history_active`), 
KEY `history_created` (`history_created`), 
KEY `history_updated` (`history_updated`), 
KEY `history_attribute` (`history_attribute`), 
KEY `history_type` (`history_type`), 
KEY `history_flag` (`history_flag`));

DROP TABLE IF EXISTS `history_profile`;

CREATE TABLE `history_profile` (`history_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `profile_id`));

DROP TABLE IF EXISTS `history_blog`;

CREATE TABLE `history_blog` (`history_id` int(10) UNSIGNED NOT NULL, `blog_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `blog_id`));

DROP TABLE IF EXISTS `history_feature`;

CREATE TABLE `history_feature` (`history_id` int(10) UNSIGNED NOT NULL, `feature_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `feature_id`));

DROP TABLE IF EXISTS `history_position`;

CREATE TABLE `history_position` (`history_id` int(10) UNSIGNED NOT NULL, `position_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `position_id`));

DROP TABLE IF EXISTS `history_post`;

CREATE TABLE `history_post` (`history_id` int(10) UNSIGNED NOT NULL, `post_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `post_id`));

DROP TABLE IF EXISTS `history_research`;

CREATE TABLE `history_research` (`history_id` int(10) UNSIGNED NOT NULL, `research_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `research_id`));

DROP TABLE IF EXISTS `history_role`;

CREATE TABLE `history_role` (`history_id` int(10) UNSIGNED NOT NULL, `role_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `role_id`));

DROP TABLE IF EXISTS `history_service`;

CREATE TABLE `history_service` (`history_id` int(10) UNSIGNED NOT NULL, `service_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `service_id`));

DROP TABLE IF EXISTS `history_transaction`;

CREATE TABLE `history_transaction` (`history_id` int(10) UNSIGNED NOT NULL, `transaction_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `transaction_id`));

DROP TABLE IF EXISTS `history_utm`;

CREATE TABLE `history_utm` (`history_id` int(10) UNSIGNED NOT NULL, `utm_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `utm_id`));

ALTER TABLE `history` CHANGE `history_flag`  `history_flag` int(1) unsigned DEFAULT NULL;