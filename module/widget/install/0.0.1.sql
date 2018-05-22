DROP TABLE IF EXISTS `widget`;

CREATE TABLE `widget` (
  `widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget_button_title` varchar(100) DEFAULT '',
  `widget_header_color` varchar(100) DEFAULT '',
  `widget_button_color` varchar(100) DEFAULT '',
  `widget_button_position` varchar(100) DEFAULT NULL,
  `widget_domain` varchar(255) NOT NULL DEFAULT '',
  `widget_key` varchar(255) NOT NULL DEFAULT '',
  `widget_flag` int(1) NOT NULL DEFAULT '0',
  `widget_active` int(1) NOT NULL DEFAULT '1',
  `widget_created` datetime NOT NULL,
  `widget_updated` datetime NOT NULL,
  PRIMARY KEY (`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `widget_profile`;

CREATE TABLE `widget_profile` (
  `widget_id` int(10) unsigned NOT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`widget_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

