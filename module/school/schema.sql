DROP TABLE IF EXISTS `school`;

CREATE TABLE `school` (`school_id` int(10) UNSIGNED NOT NULL auto_increment, `school_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `school_created` datetime NOT NULL, `school_updated` datetime NOT NULL, `school_name` varchar(255) NOT NULL, `school_description` text DEFAULT NULL, `school_type` varchar(255) DEFAULT NULL, `school_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`school_id`), KEY `school_active` (`school_active`), 
KEY `school_created` (`school_created`), 
KEY `school_updated` (`school_updated`), 
KEY `school_name` (`school_name`), 
KEY `school_type` (`school_type`));