ALTER TABLE `blog` ADD `blog_author` varchar(255) DEFAULT NULL AFTER `blog_twitter_description`;
ALTER TABLE `blog` ADD `blog_author_image` varchar(255) DEFAULT NULL AFTER `blog_author`;
ALTER TABLE `blog` ADD `blog_author_title` varchar(255) DEFAULT NULL AFTER `blog_author_image`;
ALTER TABLE `blog` ADD `blog_published` datetime DEFAULT NULL AFTER `blog_author_title`;
ALTER TABLE `blog` DROP COLUMN `blog_tags`;
