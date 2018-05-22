ALTER TABLE `feature` DROP `feature_description`, 
ADD `feature_meta_title` varchar(255) DEFAULT NULL, 
ADD `feature_meta_description` text DEFAULT NULL, 
CHANGE `feature_type`  `feature_type` varchar(255) NOT NULL DEFAULT 'Industry', 
CHANGE `feature_color`  `feature_color` varchar(10) DEFAULT NULL;