DROP TABLE IF EXISTS `file`;

CREATE TABLE `file` (`file_id` int(10) UNSIGNED NOT NULL auto_increment, `file_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `file_created` datetime NOT NULL, `file_updated` datetime NOT NULL, `file_link` varchar(255) DEFAULT NULL, `file_type` varchar(255) DEFAULT NULL, PRIMARY KEY (`file_id`), KEY `file_active` (`file_active`),
KEY `file_created` (`file_created`),
KEY `file_updated` (`file_updated`),
KEY `file_link` (`file_link`),
KEY `file_type` (`file_type`));

DROP TABLE IF EXISTS `file_comment`;

CREATE TABLE `file_comment` (`file_id` int(10) UNSIGNED NOT NULL, `comment_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`file_id`, `comment_id`));
