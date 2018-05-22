DROP TABLE IF EXISTS `comment`;

CREATE TABLE `comment` (`comment_id` int(10) UNSIGNED NOT NULL auto_increment, `comment_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `comment_created` datetime NOT NULL, `comment_updated` datetime NOT NULL, `comment_detail` text NOT NULL, `comment_published` datetime DEFAULT NULL, `comment_type` varchar(254) DEFAULT NULL, PRIMARY KEY (`comment_id`), KEY `comment_active` (`comment_active`), 
KEY `comment_created` (`comment_created`),
KEY `comment_updated` (`comment_updated`),
KEY `comment_type` (`comment_type`));

DROP TABLE IF EXISTS `comment_profile`;

CREATE TABLE `comment_profile` (`comment_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`comment_id`, `profile_id`));

DROP TABLE IF EXISTS `comment_deal`;

CREATE TABLE `comment_deal` (`comment_id` int(10) UNSIGNED NOT NULL, `deal_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`comment_id`, `deal_id`));
