ALTER TABLE `lead` ADD `lead_campaign` json DEFAULT NULL;
ALTER TABLE `lead` RENAME TO `leads`;
