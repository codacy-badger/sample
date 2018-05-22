ALTER TABLE `question` DROP INDEX `question_name`;
ALTER TABLE `question` CHANGE `question_name` `question_name` text NOT NULL;

ALTER TABLE `question` ADD `question_priority` INT(1) UNSIGNED NULL DEFAULT '0' AFTER `question_flag`;
