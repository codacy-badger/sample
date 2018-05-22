INSERT INTO transaction (transaction_id, transaction_status, transaction_payment_method, transaction_statement, transaction_payment_reference, transaction_profile, transaction_currency, transaction_total, transaction_credits, transaction_created, transaction_updated)
VALUES (1, 'pending', 'paypal', 'Test Transaction', '1234567890', '{\"profile_id\":1,\"profile_name\":\"Jane Doe\",\"profile_email\":\"jane@doe.com\",\"profile_phone\":\"555-2424\"}', 'PHP', 1000, 1000, '2017-06-17 06:01:27', '2017-06-17 06:01:27');

INSERT INTO transaction (transaction_id, transaction_status, transaction_payment_method, transaction_statement, transaction_payment_reference, transaction_profile, transaction_currency, transaction_total, transaction_credits, transaction_created, transaction_updated)
VALUES (2, 'pending', 'bdo', 'Test Transaction', '1234567890', '{\"profile_name\":\"Jane Doe\",\"profile_email\":\"jane@doe.com\",\"profile_phone\":\"555-2424\",\"merchant_tin\":\"555-555-2424\",\"address_street\":\"123 Sesame Street\",\"address_city\":\"New York City\",\"address_state\":\"New York\",\"address_country\":\"US\",\"address_postal\":\"12345\"}', 'PHP', 5000, 5000, '2017-06-17 06:01:27', '2017-06-17 06:01:27');

INSERT INTO transaction_profile (transaction_id, profile_id) VALUES (1, 1),
(2, 1);
