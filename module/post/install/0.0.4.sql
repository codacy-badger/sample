ALTER TABLE `post` ADD `post_view` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `post_flag`;
ALTER TABLE `post` ADD `post_image` VARCHAR(255) NULL AFTER `post_expires`;