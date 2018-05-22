DROP TABLE IF EXISTS `lead`;

CREATE TABLE `lead` (
    `lead_id` int(10) UNSIGNED NOT NULL auto_increment,
    `lead_active` int(1) UNSIGNED NOT NULL DEFAULT 1,
    `lead_created` datetime NOT NULL,
    `lead_updated` datetime NOT NULL,
    `lead_name` varchar(255) NOT NULL,
    `lead_email` varchar(255) DEFAULT NULL,
    `lead_type` varchar(255) DEFAULT NULL,
    `lead_gender` varchar(255) DEFAULT NULL,
    `lead_birth` date DEFAULT NULL,
    `lead_phone` varchar(255) DEFAULT NULL,
    `lead_location` varchar(255) DEFAULT NULL,
    `lead_school` varchar(255) DEFAULT NULL,
    `lead_study` varchar(255) DEFAULT NULL,
    `lead_company` varchar(255) DEFAULT NULL,
    `lead_job_title` varchar(255) DEFAULT NULL,
    `lead_tags` json DEFAULT NULL,
    `lead_campaigns` json DEFAULT NULL,
    `lead_facebook` varchar(255) DEFAULT NULL,
    `lead_linkedin` varchar(255) DEFAULT NULL,
    `lead_image` varchar(255) DEFAULT NULL,
    `lead_flag` int(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`lead_id`),
    KEY `lead_active` (`lead_active`),
    KEY `lead_created` (`lead_created`),
    KEY `lead_updated` (`lead_updated`),
    KEY `lead_name` (`lead_name`),
    KEY `lead_email` (`lead_email`),
    KEY `lead_type` (`lead_type`),
    KEY `lead_gender` (`lead_gender`),
    KEY `lead_phone` (`lead_phone`),
    KEY `lead_location` (`lead_location`),
    KEY `lead_school` (`lead_school`),
    KEY `lead_study` (`lead_study`),
    KEY `lead_company` (`lead_company`),
    KEY `lead_job_title` (`lead_job_title`),
    KEY `lead_facebook` (`lead_facebook`),
    KEY `lead_linkedin` (`lead_linkedin`),
    KEY `lead_flag` (`lead_flag`)
);

ALTER TABLE `lead` ADD `lead_campaign` json DEFAULT NULL;
ALTER TABLE `lead` RENAME TO `leads`;