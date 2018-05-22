ALTER TABLE `service` ADD `service_meta` json DEFAULT NULL, 
CHANGE `service_credits`  `service_credits` int NOT NULL, 
CHANGE `service_flag`  `service_flag` int(1) unsigned DEFAULT NULL;