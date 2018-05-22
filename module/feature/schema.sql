DROP TABLE IF EXISTS `feature`;

CREATE TABLE `feature` (`feature_id` int(10) UNSIGNED NOT NULL auto_increment, `feature_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `feature_created` datetime NOT NULL, `feature_updated` datetime NOT NULL, `feature_name` varchar(255) NOT NULL, `feature_title` varchar(255) NOT NULL, `feature_type` varchar(255) NOT NULL DEFAULT 'industry', `feature_color` varchar(10) DEFAULT '#CA1551', `feature_image` varchar(255) DEFAULT NULL, `feature_meta_title` varchar(255) DEFAULT NULL, `feature_meta_description` text DEFAULT NULL, `feature_keywords` json DEFAULT NULL, `feature_slug` varchar(50) DEFAULT NULL, `feature_detail` text DEFAULT NULL, `feature_links` json DEFAULT NULL,`feature_map` varchar(255) DEFAULT NULL,`feature_subcolor` varchar(10) DEFAULT '#CA1551', PRIMARY KEY (`feature_id`), KEY `feature_active` (`feature_active`),
KEY `feature_created` (`feature_created`),
KEY `feature_updated` (`feature_updated`),
KEY `feature_name` (`feature_name`),
KEY `feature_title` (`feature_title`),
KEY `feature_type` (`feature_type`),
KEY `feature_slug` (`feature_slug`));

ALTER TABLE `feature` ADD `feature_meta` JSON NULL DEFAULT NULL AFTER `feature_links`;
