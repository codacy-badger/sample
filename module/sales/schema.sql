DROP TABLE IF EXISTS `deal`;

CREATE TABLE `deal` (`deal_id` int(10) UNSIGNED NOT NULL auto_increment, `deal_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `deal_created` datetime NOT NULL, `deal_updated` datetime NOT NULL, `deal_amount` float(10,2) unsigned NOT NULL, `deal_close` date DEFAULT NULL, `deal_type` varchar(255) DEFAULT NULL, `deal_status` varchar(255) DEFAULT NULL, PRIMARY KEY (`deal_id`), KEY `deal_active` (`deal_active`),
KEY `deal_created` (`deal_created`),
KEY `deal_updated` (`deal_updated`),
KEY `deal_type` (`deal_type`),
KEY `deal_status` (`deal_status`));

DROP TABLE IF EXISTS `deal_company`;

CREATE TABLE `deal_company` (`deal_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`deal_id`, `profile_id`));

DROP TABLE IF EXISTS `deal_agent`;

CREATE TABLE `deal_agent` (`deal_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`deal_id`, `profile_id`));

DROP TABLE IF EXISTS `deal_pipeline`;

CREATE TABLE `deal_pipeline` (`deal_id` int(10) UNSIGNED NOT NULL, `pipeline_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`deal_id`, `pipeline_id`));

DROP TABLE IF EXISTS `label`;

CREATE TABLE `label` (`label_id` int(10) UNSIGNED NOT NULL auto_increment, `label_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `label_created` datetime NOT NULL, `label_updated` datetime NOT NULL, `label_name` varchar(255) NOT NULL, `label_type` varchar(255) DEFAULT NULL, PRIMARY KEY (`label_id`), KEY `label_active` (`label_active`),
KEY `label_created` (`label_created`),
KEY `label_updated` (`label_updated`),
KEY `label_name` (`label_name`),
KEY `label_type` (`label_type`));

DROP TABLE IF EXISTS `label_pipeline`;

CREATE TABLE `label_pipeline` (`label_id` int(10) UNSIGNED NOT NULL, `pipeline_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`label_id`, `pipeline_id`));

DROP TABLE IF EXISTS `pipeline`;

CREATE TABLE `pipeline` (`pipeline_id` int(10) UNSIGNED NOT NULL auto_increment, `pipeline_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `pipeline_created` datetime NOT NULL, `pipeline_updated` datetime NOT NULL, `pipeline_name` varchar(255) NOT NULL, `pipeline_type` varchar(255) DEFAULT NULL, PRIMARY KEY (`pipeline_id`), KEY `pipeline_active` (`pipeline_active`),
KEY `pipeline_created` (`pipeline_created`),
KEY `pipeline_updated` (`pipeline_updated`),
KEY `pipeline_name` (`pipeline_name`),
KEY `pipeline_type` (`pipeline_type`));

ALTER TABLE `pipeline` ADD `pipeline_stages` JSON NULL AFTER `pipeline_name`;
ALTER TABLE `deal` ADD `deal_name` VARCHAR(255) NOT NULL AFTER `deal_updated`, ADD INDEX (`deal_name`);

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
ALTER TABLE `lead` ADD `lead_close` DATE NULL DEFAULT NULL AFTER `lead_value`, ADD INDEX (`lead_close`);
ALTER TABLE `deal` CHANGE `deal_amount` `deal_amount` FLOAT(10,2) UNSIGNED NOT NULL DEFAULT '100';
