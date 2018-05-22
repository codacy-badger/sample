ALTER TABLE `profile` ADD `profile_billing_name` VARCHAR(255) NULL DEFAULT NULL AFTER `profile_google`;
ALTER TABLE `profile` ADD COLUMN `profile_verified` INT(1) UNSIGNED  DEFAULT 0;
ALTER TABLE `profile` ADD `profile_achievements` JSON NULL DEFAULT NULL AFTER `profile_address_postal`,
    ADD `profile_experience` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `profile_achievements`;
