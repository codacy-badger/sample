ALTER TABLE `profile`
ADD `profile_tags` json DEFAULT NULL,
ADD `profile_story` json DEFAULT NULL,
ADD `profile_campaigns` json DEFAULT NULL;

ALTER TABLE `profile`
ADD `profile_subscribe` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'newsletter subscription value' AFTER `profile_story`,
ADD INDEX (`profile_subscribe`);

ALTER TABLE `profile`  ADD `profile_bounce` INT(2) UNSIGNED NOT NULL DEFAULT '0'  AFTER `profile_subscribe`,  ADD   INDEX  (`profile_bounce`);
DROP TABLE IF EXISTS `profile_form`;

CREATE TABLE `profile_form` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile_form` ADD PRIMARY KEY (`profile_id`,`form_id`);
