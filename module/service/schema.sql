DROP TABLE IF EXISTS `service`;

CREATE TABLE `service` (`service_id` int(10) UNSIGNED NOT NULL auto_increment, `service_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `service_created` datetime NOT NULL, `service_updated` datetime NOT NULL, `service_name` varchar(255) DEFAULT NULL, `service_credits` int NOT NULL, `service_meta` json DEFAULT NULL, `service_type` varchar(255) DEFAULT NULL, `service_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`service_id`), KEY `service_active` (`service_active`), 
KEY `service_created` (`service_created`), 
KEY `service_updated` (`service_updated`), 
KEY `service_name` (`service_name`), 
KEY `service_type` (`service_type`));

DROP TABLE IF EXISTS `service_profile`;

CREATE TABLE `service_profile` (`service_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`service_id`, `profile_id`));