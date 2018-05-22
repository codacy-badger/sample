DROP TABLE IF EXISTS `currency`;

CREATE TABLE `currency` (`currency_id` int(10) UNSIGNED NOT NULL auto_increment, `currency_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `currency_created` datetime NOT NULL, `currency_updated` datetime NOT NULL, `currency_type` varchar(255) DEFAULT NULL, `currency_flag` int(1) unsigned DEFAULT '0', `currency_symbol` varchar(255) DEFAULT NULL, PRIMARY KEY (`currency_id`), KEY `currency_active` (`currency_active`), 
KEY `currency_created` (`currency_created`), 
KEY `currency_updated` (`currency_updated`), 
KEY `currency_type` (`currency_type`), 
KEY `currency_flag` (`currency_flag`), 
KEY `currency_symbol` (`currency_symbol`));