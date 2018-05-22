DROP TABLE IF EXISTS `history`;

CREATE TABLE `history` (`history_id` int(10) UNSIGNED NOT NULL auto_increment, `history_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `history_created` datetime NOT NULL, `history_updated` datetime NOT NULL, `history_action` text NOT NULL, `history_type` varchar(255) DEFAULT NULL, PRIMARY KEY (`history_id`), KEY `history_active` (`history_active`),
KEY `history_created` (`history_created`),
KEY `history_updated` (`history_updated`),
KEY `history_type` (`history_type`));

DROP TABLE IF EXISTS `history_deal`;

CREATE TABLE `history_deal` (`history_id` int(10) UNSIGNED NOT NULL, `deal_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `deal_id`));

DROP TABLE IF EXISTS `history_comment`;

CREATE TABLE `history_comment` (`history_id` int(10) UNSIGNED NOT NULL, `comment_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `comment_id`));

DROP TABLE IF EXISTS `history_profile`;

CREATE TABLE `history_profile` (`history_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`history_id`, `profile_id`));
