DROP TABLE IF EXISTS `position`;

CREATE TABLE `position` (
    `position_id` int(10) UNSIGNED NOT NULL auto_increment,
    `position_active` int(1) UNSIGNED NOT NULL DEFAULT 1,
    `position_created` datetime NOT NULL,
    `position_updated` datetime NOT NULL,
    `position_name` varchar(255) NOT NULL,
    `position_description` text DEFAULT NULL,
    `position_type` varchar(255) DEFAULT NULL,
    `position_parent` int(10) DEFAULT 0,
    `position_skills` JSON NULL DEFAULT NULL,
    `position_flag` int(1) unsigned DEFAULT 0,
PRIMARY KEY (`position_id`), KEY `position_active` (`position_active`),
KEY `position_created` (`position_created`),
KEY `position_updated` (`position_updated`),
KEY `position_name` (`position_name`),
KEY `position_type` (`position_type`),
KEY `position_parent` (`position_parent`));
