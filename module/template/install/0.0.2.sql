DROP TABLE IF EXISTS `template`;

CREATE TABLE `template` (`template_id` int(10) UNSIGNED NOT NULL auto_increment, `template_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `template_created` datetime NOT NULL, `template_updated` datetime NOT NULL, `template_title` varchar(255) NOT NULL, `template_type` varchar(255) DEFAULT NULL, `template_html` varchar(255) DEFAULT NULL, `template_unsubscribe` varchar(255) DEFAULT NULL, `template_webversion` varchar(255) DEFAULT NULL, `template_uid` varchar(255) DEFAULT NULL, PRIMARY KEY (`template_id`), KEY `template_active` (`template_active`), 
KEY `template_created` (`template_created`), 
KEY `template_updated` (`template_updated`), 
KEY `template_title` (`template_title`), 
KEY `template_type` (`template_type`), 
KEY `template_html` (`template_html`), 
KEY `template_unsubscribe` (`template_unsubscribe`), 
KEY `template_webversion` (`template_webversion`), 
KEY `template_uid` (`template_uid`));