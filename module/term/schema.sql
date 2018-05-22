DROP TABLE IF EXISTS `term`;

CREATE TABLE `term` (`term_id` int(10) UNSIGNED NOT NULL auto_increment, `term_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `term_created` datetime NOT NULL, `term_updated` datetime NOT NULL, `term_name` varchar(255) NOT NULL, `term_hits` int(8) unsigned DEFAULT 1, `term_type` varchar(255) DEFAULT 'search', `term_flag` int(1) unsigned DEFAULT '0', PRIMARY KEY (`term_id`), KEY `term_active` (`term_active`), 
KEY `term_created` (`term_created`), 
KEY `term_updated` (`term_updated`), 
KEY `term_name` (`term_name`), 
KEY `term_type` (`term_type`), 
KEY `term_flag` (`term_flag`));