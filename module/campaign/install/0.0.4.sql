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
ALTER TABLE `campaign` ADD `campaign_message_id` VARCHAR(255) NULL DEFAULT NULL
    AFTER `campaign_tags`, ADD INDEX (`campaign_message_id`);
ALTER TABLE `campaign` ADD `campaign_type` VARCHAR(255) NOT NULL DEFAULT 'manual'
    AFTER `campaign_audience`, ADD INDEX (`campaign_type`);
