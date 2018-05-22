DROP TABLE IF EXISTS `blog`;

CREATE TABLE `blog` (`blog_id` int(10) UNSIGNED NOT NULL auto_increment, `blog_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `blog_created` datetime NOT NULL, `blog_updated` datetime NOT NULL, `blog_title` varchar(255) DEFAULT NULL, `blog_image` varchar(255) DEFAULT NULL, `blog_slug` varchar(255) DEFAULT NULL, `blog_article` text(1000) DEFAULT NULL, `blog_description` varchar(255) DEFAULT NULL, `blog_keywords` json DEFAULT NULL, `blog_tags` json DEFAULT NULL, `blog_view_count` int DEFAULT NULL, `blog_facebook_title` varchar(255) DEFAULT NULL, `blog_facebook_image` varchar(255) DEFAULT NULL, `blog_facebook_description` varchar(255) DEFAULT NULL, `blog_twitter_title` varchar(255) DEFAULT NULL, `blog_twitter_image` varchar(255) DEFAULT NULL, `blog_twitter_description` varchar(255) DEFAULT NULL, `blog_author` varchar(255) DEFAULT NULL, `blog_author_image` varchar(255) DEFAULT NULL, `blog_author_title` varchar(255) DEFAULT NULL, `blog_published` datetime DEFAULT NULL, PRIMARY KEY (`blog_id`), KEY `blog_active` (`blog_active`), 
KEY `blog_created` (`blog_created`), 
KEY `blog_updated` (`blog_updated`), 
KEY `blog_title` (`blog_title`), 
KEY `blog_slug` (`blog_slug`), 
KEY `blog_description` (`blog_description`), 
KEY `blog_facebook_title` (`blog_facebook_title`), 
KEY `blog_facebook_image` (`blog_facebook_image`), 
KEY `blog_facebook_description` (`blog_facebook_description`), 
KEY `blog_twitter_title` (`blog_twitter_title`), 
KEY `blog_twitter_image` (`blog_twitter_image`), 
KEY `blog_twitter_description` (`blog_twitter_description`), 
KEY `blog_author` (`blog_author`), 
KEY `blog_author_title` (`blog_author_title`));

DROP TABLE IF EXISTS `blog_profile`;

CREATE TABLE `blog_profile` (`blog_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`blog_id`, `profile_id`));

ALTER TABLE `blog` ADD `blog_type` VARCHAR(255) NULL AFTER `blog_twitter_description`, ADD `blog_flag` INT NOT NULL DEFAULT '0' AFTER `blog_type`;

ALTER TABLE `blog` ADD `blog_tags` JSON NULL AFTER `blog_keywords`;