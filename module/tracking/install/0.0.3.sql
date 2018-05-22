DROP TABLE IF EXISTS `answer`;

CREATE TABLE `answer` (`answer_id` int(10) UNSIGNED NOT NULL auto_increment, `answer_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `answer_created` datetime NOT NULL, `answer_updated` datetime NOT NULL, `answer_name` varchar(255) NOT NULL, `answer_choices` json DEFAULT NULL, `answer_type` varchar(255) DEFAULT NULL, `answer_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`answer_id`), KEY `answer_active` (`answer_active`), 
KEY `answer_created` (`answer_created`), 
KEY `answer_updated` (`answer_updated`), 
KEY `answer_name` (`answer_name`), 
KEY `answer_type` (`answer_type`));

DROP TABLE IF EXISTS `applicant`;

CREATE TABLE `applicant` (`applicant_id` int(10) UNSIGNED NOT NULL auto_increment, `applicant_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `applicant_created` datetime NOT NULL, `applicant_updated` datetime NOT NULL, `applicant_status` varchar(255) NOT NULL, `applicant_type` varchar(255) DEFAULT NULL, `applicant_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`applicant_id`), KEY `applicant_active` (`applicant_active`), 
KEY `applicant_created` (`applicant_created`), 
KEY `applicant_updated` (`applicant_updated`), 
KEY `applicant_status` (`applicant_status`), 
KEY `applicant_type` (`applicant_type`));

DROP TABLE IF EXISTS `applicant_profile`;

CREATE TABLE `applicant_profile` (`applicant_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`applicant_id`, `profile_id`));

DROP TABLE IF EXISTS `applicant_form`;

CREATE TABLE `applicant_form` (`applicant_id` int(10) UNSIGNED NOT NULL, `form_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`applicant_id`, `form_id`));

DROP TABLE IF EXISTS `applicant_answer`;

CREATE TABLE `applicant_answer` (`applicant_id` int(10) UNSIGNED NOT NULL, `answer_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`applicant_id`, `answer_id`));

DROP TABLE IF EXISTS `form`;

CREATE TABLE `form` (`form_id` int(10) UNSIGNED NOT NULL auto_increment, `form_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `form_created` datetime NOT NULL, `form_updated` datetime NOT NULL, `form_name` varchar(255) NOT NULL, `form_type` varchar(255) DEFAULT NULL, `form_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`form_id`), KEY `form_active` (`form_active`), 
KEY `form_created` (`form_created`), 
KEY `form_updated` (`form_updated`), 
KEY `form_name` (`form_name`), 
KEY `form_type` (`form_type`));

DROP TABLE IF EXISTS `form_question`;

CREATE TABLE `form_question` (`form_id` int(10) UNSIGNED NOT NULL, `question_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`form_id`, `question_id`));

DROP TABLE IF EXISTS `question`;

CREATE TABLE `question` (`question_id` int(10) UNSIGNED NOT NULL auto_increment, `question_active` int(1) UNSIGNED NOT NULL DEFAULT 1, `question_created` datetime NOT NULL, `question_updated` datetime NOT NULL, `question_name` varchar(255) NOT NULL, `question_choices` json DEFAULT NULL, `question_type` varchar(255) DEFAULT NULL, `question_flag` int(1) unsigned DEFAULT 0, PRIMARY KEY (`question_id`), KEY `question_active` (`question_active`), 
KEY `question_created` (`question_created`), 
KEY `question_updated` (`question_updated`), 
KEY `question_name` (`question_name`), 
KEY `question_type` (`question_type`));

DROP TABLE IF EXISTS `question_answer`;

CREATE TABLE `question_answer` (`question_id` int(10) UNSIGNED NOT NULL, `answer_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`question_id`, `answer_id`));