ALTER TABLE `feature` CHANGE `feature_type`  `feature_type` varchar(255) NOT NULL DEFAULT 'industry';
ALTER TABLE `feature` ADD `feature_map` varchar(255) DEFAULT NULL;
ALTER TABLE `feature` ADD `feature_subcolor` varchar(10) DEFAULT NULL;