DROP TABLE IF EXISTS `action`;

CREATE TABLE `action` (
    `action_id` int(10) UNSIGNED NOT NULL auto_increment, 
    `action_active` int(1) UNSIGNED NOT NULL DEFAULT 1,
    `action_created` datetime NOT NULL,
    `action_updated` datetime NOT NULL, 
    `action_title` varchar(255) DEFAULT NULL, 
    `action_event` varchar(255) DEFAULT NULL, 
    `action_when` varchar(255) DEFAULT NULL, 
    `action_tags` json DEFAULT NULL, 
    PRIMARY KEY (`action_id`), 
    KEY `action_active` (`action_active`),
    KEY `action_created` (`action_created`),
    KEY `action_updated` (`action_updated`),
    KEY `action_title` (`action_title`),
    KEY `action_event` (`action_event`),
    KEY `action_when` (`action_when`));

DROP TABLE IF EXISTS `action_template`;

CREATE TABLE `action_template` (
    `action_id` int(10) UNSIGNED NOT NULL,
    `template_id` int(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`action_id`, `template_id`));

ALTER TABLE `action` DROP INDEX `action_when`;
ALTER TABLE `action` CHANGE `action_when` `action_when` JSON NULL DEFAULT NULL;
ALTER TABLE `action` ADD `action_medium` VARCHAR(255) NULL DEFAULT NULL AFTER `action_title`,
ADD INDEX (`action_medium`);
