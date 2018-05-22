ALTER TABLE `template` CHANGE `template_title`  `template_title` varchar(255) DEFAULT NULL;
ALTER TABLE `template` DROP INDEX `template_html`;
ALTER TABLE `template` CHANGE `template_html` `template_html` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;
