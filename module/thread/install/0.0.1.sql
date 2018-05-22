DROP TABLE IF EXISTS `thread`;

CREATE TABLE `thread` (`thread_id` int(10) UNSIGNED NOT NULL auto_increment, `thread_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `thread_created` datetime NOT NULL, `thread_updated` datetime NOT NULL, `thread_gmail_id` varchar(255) NOT NULL, `thread_subject` varchar(255) NOT NULL, `thread_snippet` varchar(255) DEFAULT NULL, PRIMARY KEY (`thread_id`), KEY `thread_active` (`thread_active`),
KEY `thread_created` (`thread_created`),
KEY `thread_updated` (`thread_updated`),
KEY `thread_gmail_id` (`thread_gmail_id`),
KEY `thread_subject` (`thread_subject`),
KEY `thread_snippet` (`thread_snippet`));

DROP TABLE IF EXISTS `thread_deal`;

CREATE TABLE `thread_deal` (`thread_id` int(10) UNSIGNED NOT NULL, `deal_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`thread_id`, `deal_id`));
