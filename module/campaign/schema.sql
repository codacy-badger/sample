DROP TABLE IF EXISTS `campaign`;

CREATE TABLE `campaign` (
    `campaign_id` int(10) UNSIGNED NOT NULL auto_increment,
    `campaign_active` int(1) UNSIGNED NOT NULL DEFAULT 1,
    `campaign_created` datetime NOT NULL,
    `campaign_updated` datetime NOT NULL,
    `campaign_title` varchar(255) DEFAULT NULL,
    `campaign_medium` varchar(255) DEFAULT 'unknown',
    `campaign_source` varchar(255) DEFAULT 'unknown',
    `campaign_audience` varchar(255) DEFAULT 'unknown',
    `campaign_tags` json DEFAULT NULL,
PRIMARY KEY (`campaign_id`),
KEY `campaign_active` (`campaign_active`),
KEY `campaign_created` (`campaign_created`),
KEY `campaign_updated` (`campaign_updated`),
KEY `campaign_title` (`campaign_title`));

DROP TABLE IF EXISTS `campaign_template`;

CREATE TABLE `campaign_template` (`campaign_id` int(10) UNSIGNED NOT NULL, `template_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`campaign_id`, `template_id`));

DROP TABLE IF EXISTS `campaign_lead`;

CREATE TABLE `campaign_lead` (`campaign_id` int(10) UNSIGNED NOT NULL, `lead_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`campaign_id`, `lead_id`));

DROP TABLE IF EXISTS `campaign_profile`;

CREATE TABLE `campaign_profile` (`campaign_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`campaign_id`, `profile_id`));

ALTER TABLE `campaign`
ADD `campaign_queue` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_sent` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_converted` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_bounced` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_opened` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_unopened` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_spam` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_clicked` int(1) UNSIGNED NOT NULL DEFAULT 0,
ADD `campaign_unsubscribed` int(1) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `campaign` ADD `campaign_message_id` VARCHAR(255) NULL DEFAULT NULL AFTER `campaign_tags`, ADD INDEX (`campaign_message_id`);

ALTER TABLE `campaign` ADD `campaign_message_id` VARCHAR(255) NULL DEFAULT NULL
    AFTER `campaign_tags`, ADD INDEX (`campaign_message_id`);
ALTER TABLE `campaign` ADD `campaign_type` VARCHAR(255) NOT NULL DEFAULT 'manual'
    AFTER `campaign_audience`, ADD INDEX (`campaign_type`);
