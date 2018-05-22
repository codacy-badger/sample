ALTER TABLE `widget` ADD `widget_tags` JSON NULL DEFAULT NULL AFTER `widget_flag`;
ALTER TABLE `widget` ADD `widget_type_flag` varchar(255) NOT NULL AFTER `widget_tags`;

