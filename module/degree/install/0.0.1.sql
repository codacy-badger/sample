DROP TABLE IF EXISTS `degree`;

CREATE TABLE `degree` (`degree_id` int(10) UNSIGNED NOT NULL auto_increment, `degree_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `degree_created` datetime NOT NULL, `degree_updated` datetime NOT NULL, `degree_name` varchar(255) NOT NULL, `degree_type` varchar(255) DEFAULT NULL, `degree_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`degree_id`), KEY `degree_active` (`degree_active`), 
KEY `degree_created` (`degree_created`), 
KEY `degree_updated` (`degree_updated`), 
KEY `degree_name` (`degree_name`), 
KEY `degree_type` (`degree_type`));