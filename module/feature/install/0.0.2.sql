ALTER TABLE `feature` CHANGE `feature_type`  `feature_type` varchar(255) NOT NULL DEFAULT 'Industry', 
CHANGE `feature_color`  `feature_color` varchar(10) DEFAULT NULL;