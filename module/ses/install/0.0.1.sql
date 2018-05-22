DROP TABLE IF EXISTS `ses`;

CREATE TABLE `ses` (`ses_id` int(10) UNSIGNED NOT NULL auto_increment, `ses_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `ses_created` datetime NOT NULL, `ses_updated` datetime NOT NULL, `ses_message` varchar(255) DEFAULT NULL, `ses_link` varchar(255) DEFAULT NULL, `ses_emails` json DEFAULT NULL, `ses_total` int(11) DEFAULT NULL, `ses_type` varchar(255) DEFAULT NULL, PRIMARY KEY (`ses_id`), KEY `ses_active` (`ses_active`), 
KEY `ses_created` (`ses_created`), 
KEY `ses_updated` (`ses_updated`), 
KEY `ses_message` (`ses_message`), 
KEY `ses_link` (`ses_link`), 
KEY `ses_type` (`ses_type`));