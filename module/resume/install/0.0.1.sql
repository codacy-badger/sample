DROP TABLE IF EXISTS `resume`;

CREATE TABLE `resume` (`resume_id` int(10) UNSIGNED NOT NULL auto_increment, `resume_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `resume_created` datetime NOT NULL, `resume_updated` datetime NOT NULL, `resume_position` varchar(255) NOT NULL, `resume_link` varchar(255) NOT NULL, `resume_type` varchar(255) DEFAULT 'seeker', `resume_flag` int(1) unsigned DEFAULT NULL, PRIMARY KEY (`resume_id`), KEY `resume_active` (`resume_active`), 
KEY `resume_created` (`resume_created`), 
KEY `resume_updated` (`resume_updated`), 
KEY `resume_position` (`resume_position`), 
KEY `resume_type` (`resume_type`), 
KEY `resume_flag` (`resume_flag`));