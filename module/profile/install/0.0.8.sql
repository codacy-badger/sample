DROP TABLE IF EXISTS `profile_label`;

CREATE TABLE `profile_label` (
  `profile_id` int(10) UNSIGNED NOT NULL,
  `label_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `profile_label` ADD PRIMARY KEY (`profile_id`,`label_id`);

ALTER TABLE `profile` ADD `profile_campaigns` json DEFAULT NULL;
