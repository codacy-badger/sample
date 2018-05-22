ALTER TABLE `transaction` ADD `transaction_meta` JSON NULL DEFAULT NULL AFTER `transaction_profile`, ADD `transaction_statement` VARCHAR(255) NOT NULL AFTER `transaction_meta`;
