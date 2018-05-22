ALTER TABLE `action` ADD `action_title` varchar(255) DEFAULT NULL,
ADD INDEX (`action_title`);

ALTER TABLE `action` DROP INDEX `action_when`;
ALTER TABLE `action` CHANGE `action_when` `action_when` JSON NULL DEFAULT NULL;
ALTER TABLE `action` ADD `action_medium` VARCHAR(255) NULL DEFAULT NULL AFTER `action_title`, ADD INDEX (`action_medium`);
