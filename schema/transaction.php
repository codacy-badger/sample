<?php //-->
return [
    'singular' => 'Transaction',
    'plural' => 'Transactions',
    'primary' => 'transaction_id',
    'active' => 'transaction_active',
    'created' => 'transaction_created',
    'updated' => 'transaction_updated',
    'paid' = > 'transaction_paid_date',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ]
    ],
    'fields' => [
        'transaction_status' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'pending',
                'required' => true,
                'filterable' => true,
                'key' => true,
            ],
            'form' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    'pending' => 'Pending',
                    'complete' => 'Complete',
                    'verified' => 'Verified',
                    'match' => 'Match',
                    'rejected' => 'Rejected',
                    'refunded' => 'Refunded'
                ]
            ],
            'list' => [
                'label' => 'Status'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Status is required'
                ],
                [
                    'method' => 'one',
                    'parameters' => [
                        'pending',
                        'complete',
                        'verified',
                        'match',
                        'rejected',
                        'refunded'
                    ],
                    'message' => 'Invalid status'
                ]
            ],
            'test' => [
                'pass' => 'pending',
                'fail' => 'validated'
            ]
        ],
        'transaction_payment_method' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'key' => true,
            ],
            'form' => [
                'label' => 'Payment Method',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'paypal',
                ]
            ],
            'list' => [
                'label' => 'Method'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Payment method is required'
                ]
            ],
            'test' => [
                'pass' => 'paypal',
                'fail' => 'validated'
            ]
        ],
        'transaction_payment_reference' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'searchable' => true,
                'key' => true,
            ],
            'form' => [
                'label' => 'Reference',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => '1234567890',
                ]
            ],
            'list' => [
                'label' => 'Reference'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Payment reference is required'
                ]
            ],
            'test' => [
                'pass' => '123456789',
                'fail' => ''
            ]
        ],
        'transaction_profile' => [
            'sql' => [
                'type' => 'json',
                'required' => true
            ],
            'elastic' => [
                'type' => 'object'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Profile information is required'
                ]
            ],
            'list' => [
                'label' => 'Profile'
            ],
        ],
        'transaction_meta' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'object'
            ]
        ],
        'transaction_statement' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'list' => [
                'label' => 'Statement Name'
            ],
        ],
        'transaction_currency' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Currency is required'
                ]
            ],
            'list' => [
                'label' => 'Currency'
            ],
        ],
        'transaction_total' => [
            'sql' => [
                'type' => 'int',
                'required' => true
            ],
            'elastic' => [
                'type' => 'integer'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Total is required'
                ]
            ],
            'list' => [
                'label' => 'Total'
            ],
        ],
        'transaction_credits' => [
            'sql' => [
                'type' => 'int',
                'required' => true
            ],
            'elastic' => [
                'type' => 'integer'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Credits is required'
                ]
            ],
            'list' => [
                'label' => 'Credits'
            ],
        ],
        'transaction_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'transaction_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ],
            'validation' => [
                [
                    'method' => 'number',
                    'message' => 'Must be a number'
                ],
                [
                    'method' => 'gt',
                    'parameters' => -1,
                    'message' => 'Must be between 0 and 9'
                ],
                [
                    'method' => 'lt',
                    'parameters' => 10,
                    'message' => 'Must be between 0 and 9'
                ]
            ],
            'test' => [
                'success' => 4,
                'fail' => 11
            ]
        ]
    ],
    'fixtures' => [
        [
            'transaction_id' => 1,
            'profile_id' => 1,
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'paypal',
            'transaction_payment_reference' => '1234567890',
            'transaction_profile' => json_encode([
                'profile_id' => 1,
                'profile_name' => 'Jane Doe',
                'profile_email' => 'jane@doe.com',
                'profile_phone' => '555-2424'
            ]),
            'transaction_currency' => 'PHP',
            'transaction_total' => 1000,
            'transaction_credits' => 1000,
            'transaction_created' => date('Y-m-d H:i:s'),
            'transaction_updated' => date('Y-m-d H:i:s'),
            'transaction_paid_date' => date('Y-m-d H:i:s')
        ],
        [
            'transaction_id' => 2,
            'profile_id' => 1,
            'transaction_status' => 'pending',
            'transaction_payment_method' => 'bdo',
            'transaction_payment_reference' => '1234567890',
            'transaction_profile' => json_encode([
                'profile_name' => 'Jane Doe',
                'profile_email' => 'jane@doe.com',
                'profile_phone' => '555-2424',
                'merchant_tin' => '555-555-2424',
                'address_street' => '123 Sesame Street',
                'address_city' => 'New York City',
                'address_state' => 'New York',
                'address_country' => 'US',
                'address_postal' => '12345'
            ]),
            'transaction_currency' => 'PHP',
            'transaction_total' => 5000,
            'transaction_credits' => 5000,
            'transaction_created' => date('Y-m-d H:i:s'),
            'transaction_updated' => date('Y-m-d H:i:s'),
            'transaction_paid_date' => date('Y-m-d H:i:s')
        ]
    ]
];
