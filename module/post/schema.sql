DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (`post_id` int(10) UNSIGNED NOT NULL auto_increment, `post_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `post_created` datetime NOT NULL, `post_updated` datetime NOT NULL, `post_name` varchar(255) NOT NULL, `post_email` varchar(255) DEFAULT NULL, `post_phone` varchar(255) DEFAULT NULL, `post_position` varchar(255) NOT NULL, `post_location` varchar(255) DEFAULT NULL, `post_experience` int(4) DEFAULT NULL, `post_resume` varchar(255) DEFAULT NULL, `post_detail` text DEFAULT NULL, `post_notify` json DEFAULT NULL, `post_expires` datetime DEFAULT NULL, `post_banner` varchar(255) DEFAULT NULL, `post_currency` varchar(255) DEFAULT NULL, `post_salary_min` int(7) DEFAULT NULL, `post_salary_max` int(7) DEFAULT NULL, `post_link` text DEFAULT NULL, `post_like_count` int(10) DEFAULT 0, `post_download_count` int(10) DEFAULT 0, `post_email_count` int(10) DEFAULT 0, `post_sms_match_count` int(10) DEFAULT 0, `post_sms_interested_count` int(10) DEFAULT 0, `post_phone_count` int(10) DEFAULT 0, `post_tags` json DEFAULT NULL, `post_type` varchar(255) DEFAULT 'seeker', `post_arrangement` varchar(255) DEFAULT NULL, `post_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`post_id`), KEY `post_active` (`post_active`), `post_view` int(11) UNSIGNED NOT NULL DEFAULT '0',
KEY `post_created` (`post_created`),
KEY `post_updated` (`post_updated`),
KEY `post_name` (`post_name`),
KEY `post_email` (`post_email`),
KEY `post_phone` (`post_phone`),
KEY `post_position` (`post_position`),
KEY `post_type` (`post_type`),
KEY `post_flag` (`post_flag`));

DROP TABLE IF EXISTS `post_profile`;

CREATE TABLE `post_profile` (`post_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`post_id`, `profile_id`));

DROP TABLE IF EXISTS `post_comment`;

CREATE TABLE `post_comment` (`post_id` int(10) UNSIGNED NOT NULL, `comment_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`post_id`, `comment_id`));

DROP TABLE IF EXISTS `post_downloaded`;

CREATE TABLE `post_downloaded` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_downloaded` ADD PRIMARY KEY (`post_id`,`profile_id`);

DROP TABLE IF EXISTS `post_emailed`;

CREATE TABLE `post_emailed` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_emailed` ADD PRIMARY KEY (`post_id`,`profile_id`);

DROP TABLE IF EXISTS `post_liked`;

CREATE TABLE `post_liked` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_liked` ADD PRIMARY KEY (`post_id`,`profile_id`);

DROP TABLE IF EXISTS `post_phoned`;

CREATE TABLE `post_phoned` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `profile_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_phoned` ADD PRIMARY KEY (`post_id`,`profile_id`);

DROP TABLE IF EXISTS `post_resume`;

CREATE TABLE `post_resume` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `resume_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_resume` ADD PRIMARY KEY (`post_id`,`resume_id`);

ALTER TABLE `post` ADD `post_image` VARCHAR(255) NULL AFTER `post_expires`;
ALTER TABLE `post` ADD `post_geo_location` JSON NULL DEFAULT NULL AFTER `post_location`;

DROP TABLE IF EXISTS `post_form`;

CREATE TABLE `post_form` (
  `post_id` int(10) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `post_form` ADD PRIMARY KEY (`post_id`,`form_id`);
ALTER TABLE `post` ADD `post_package` JSON NULL DEFAULT NULL AFTER `post_arrangement`;
ALTER TABLE `post` ADD `post_restored` datetime NULL DEFAULT NULL AFTER `post_updated`;

alter table post add index `post_filters`(`post_expires`, `post_active`, `post_location`, `post_position`);

alter table post add index `post_filters_type`(`post_expires`, `post_active`, `post_location`, `post_position`, `post_type`);