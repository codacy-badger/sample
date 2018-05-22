ALTER TABLE `profile` DROP `profile_job`, 
DROP `profile_rank`, 
CHANGE `profile_gender`  `profile_gender` varchar(255) DEFAULT NULL, 
CHANGE `profile_birth`  `profile_birth` date DEFAULT NULL, 
CHANGE `profile_flag`  `profile_flag` int(1) unsigned DEFAULT NULL;