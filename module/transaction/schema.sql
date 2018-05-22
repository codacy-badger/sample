DROP TABLE IF EXISTS `transaction`;

CREATE TABLE `transaction` (
  `transaction_id` int(10) UNSIGNED NOT NULL,
  `transaction_active` int(1) UNSIGNED NOT NULL DEFAULT '1',
  `transaction_created` datetime NOT NULL,
  `transaction_updated` datetime NOT NULL,
  `transaction_paid_date` datetime DEFAULT NULL,
  `transaction_status` varchar(255) NOT NULL DEFAULT 'pending',
  `transaction_payment_method` varchar(255) DEFAULT NULL,
  `transaction_payment_reference` varchar(255) DEFAULT NULL,
  `transaction_profile` json NOT NULL,
  `transaction_meta` json DEFAULT NULL,
  `transaction_statement` varchar(255) NOT NULL,
  `transaction_currency` varchar(255) NOT NULL,
  `transaction_total` int(11) NOT NULL,
  `transaction_credits` int(11) NOT NULL,
  `transaction_type` varchar(255) DEFAULT NULL,
  `transaction_flag` int(1) UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `transaction`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `transaction_active` (`transaction_active`),
  ADD KEY `transaction_created` (`transaction_created`),
  ADD KEY `transaction_updated` (`transaction_updated`),
  ADD KEY `transaction_currency` (`transaction_currency`),
  ADD KEY `transaction_type` (`transaction_type`);

ALTER TABLE `transaction`
  MODIFY `transaction_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

DROP TABLE IF EXISTS `transaction_profile`;

CREATE TABLE `transaction_profile` (`transaction_id` int(10) UNSIGNED NOT NULL, `profile_id` int(10) UNSIGNED NOT NULL, PRIMARY KEY (`transaction_id`, `profile_id`));
