DROP TABLE IF EXISTS `utm`;

CREATE TABLE `utm` (`utm_id` int(10) UNSIGNED NOT NULL auto_increment, `utm_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `utm_created` datetime NOT NULL, `utm_updated` datetime NOT NULL, `utm_title` varchar(255) NOT NULL, `utm_source` varchar(255) DEFAULT NULL, `utm_medium` varchar(255) DEFAULT NULL, `utm_campaign` varchar(255) NOT NULL, `utm_detail` text DEFAULT NULL, `utm_image` varchar(255) DEFAULT NULL, `utm_type` varchar(255) DEFAULT NULL, `utm_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`utm_id`), KEY `utm_active` (`utm_active`),
KEY `utm_created` (`utm_created`),
KEY `utm_updated` (`utm_updated`),
KEY `utm_title` (`utm_title`),
KEY `utm_source` (`utm_source`),
KEY `utm_medium` (`utm_medium`),
KEY `utm_campaign` (`utm_campaign`),
KEY `utm_type` (`utm_type`),
KEY `utm_flag` (`utm_flag`));

ALTER TABLE `utm` ADD `utm_page` VARCHAR(255) NULL AFTER `utm_detail`;
