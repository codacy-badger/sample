DROP TABLE IF EXISTS `area`;

CREATE TABLE `area` (`area_id` int(10) UNSIGNED NOT NULL auto_increment, `area_name` varchar(255) NOT NULL, `area_type` varchar(255) DEFAULT NULL, `area_parent` int(10) DEFAULT NULL, `area_postal` int(10) DEFAULT NULL, `area_flag` int(1) unsigned DEFAULT 0, `area_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `area_created` datetime NOT NULL, `area_updated` datetime NOT NULL, PRIMARY KEY (`area_id`), KEY `area_active` (`area_active`), 
KEY `area_created` (`area_created`), 
KEY `area_updated` (`area_updated`), 
KEY `area_name` (`area_name`), 
KEY `area_type` (`area_type`), 
KEY `area_parent` (`area_parent`), 
KEY `area_postal` (`area_postal`));

ALTER TABLE `area` ADD `area_location` POINT NULL AFTER `area_postal`;