ALTER TABLE `post` ADD `post_geo_location` JSON NULL DEFAULT NULL AFTER `post_location`;

ALTER TABLE `post` ADD `post_currency` VARCHAR(255) NULL DEFAULT NULL AFTER `post_banner`;