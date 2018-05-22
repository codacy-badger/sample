# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.17)
# Database: jobayan
# Generation Time: 2017-11-09 07:08:07 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table widget
# ------------------------------------------------------------

CREATE TABLE `widget` (
  `widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `widget_button_title` varchar(100) DEFAULT '',
  `widget_header_color` varchar(100) DEFAULT '',
  `widget_button_color` varchar(100) DEFAULT '',
  `widget_button_position` varchar(100) DEFAULT NULL,
  `widget_domain` varchar(255) NOT NULL DEFAULT '',
  `widget_key` varchar(255) NOT NULL DEFAULT '',
  `widget_flag` int(1) NOT NULL DEFAULT '0',
  `widget_meta` JSON  NULL DEFAULT NULL,
  `widget_type` varchar(255) NOT NULL DEFAULT '',
  `widget_active` int(1) NOT NULL DEFAULT '1',
  `widget_created` datetime NOT NULL,
  `widget_updated` datetime NOT NULL,
  PRIMARY KEY (`widget_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dump of table widget_profile
# ------------------------------------------------------------

CREATE TABLE `widget_profile` (
  `widget_id` int(10) unsigned NOT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`widget_id`,`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;