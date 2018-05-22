DROP TABLE IF EXISTS `event`;

CREATE TABLE `event` (`event_id` int(10) UNSIGNED NOT NULL auto_increment, `event_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `event_created` datetime NOT NULL, `event_updated` datetime NOT NULL, `event_title` varchar(255) NOT NULL, `event_start` datetime NOT NULL, `event_end` datetime NOT NULL, `event_type` varchar(255) NOT NULL DEFAULT 'meeting', PRIMARY KEY (`event_id`), KEY `event_active` (`event_active`),
KEY `event_created` (`event_created`),
KEY `event_updated` (`event_updated`),
KEY `event_title` (`event_title`),
KEY `event_start` (`event_start`),
KEY `event_end` (`event_end`),
KEY `event_type` (`event_type`));

DROP TABLE IF EXISTS `event_deal`;

CREATE TABLE `event_deal` (`event_id` int(10) UNSIGNED NOT NULL, `deal_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`event_id`, `deal_id`));

DROP TABLE IF EXISTS `event_profile`;

CREATE TABLE `event_profile` (`event_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`event_id`, `profile_id`));

ALTER TABLE `event` ADD `event_details` TEXT NULL DEFAULT NULL AFTER `event_end`;
ALTER TABLE `event` ADD `event_location` VARCHAR(255) NULL DEFAULT NULL AFTER `event_end`, ADD INDEX (`event_location`);
