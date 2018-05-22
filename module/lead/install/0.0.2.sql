ALTER TABLE `lead` ADD `lead_flag` int(1) UNSIGNED NOT NULL DEFAULT 1,
ADD INDEX (`lead_flag`);
