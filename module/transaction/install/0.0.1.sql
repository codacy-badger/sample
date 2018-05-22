DROP TABLE IF EXISTS `transaction`;

CREATE TABLE `transaction` (`transaction_id` int(10) UNSIGNED NOT NULL auto_increment, `transaction_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `transaction_created` datetime NOT NULL, `transaction_updated` datetime NOT NULL, `transaction_status` varchar(255) NOT NULL DEFAULT 'pending', `transaction_payment_method` varchar(255) DEFAULT NULL, `transaction_payment_reference` varchar(255) DEFAULT NULL, `transaction_profile` json NOT NULL, `transaction_currency` varchar(255) NOT NULL, `transaction_total` int NOT NULL, `transaction_credits` int NOT NULL, `transaction_type` varchar(255) DEFAULT NULL, `transaction_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`transaction_id`), KEY `transaction_active` (`transaction_active`), 
KEY `transaction_created` (`transaction_created`), 
KEY `transaction_updated` (`transaction_updated`), 
KEY `transaction_currency` (`transaction_currency`), 
KEY `transaction_type` (`transaction_type`));

DROP TABLE IF EXISTS `transaction_profile`;

CREATE TABLE `transaction_profile` (`transaction_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`transaction_id`, `profile_id`));