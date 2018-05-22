<?php //--> 
return [
    'singular' => 'History',
    'plural' => 'Histories',
    'primary' => 'history_id',
    'active' => 'history_active',
    'created' => 'history_created',
    'updated' => 'history_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => true,
        ],
        'blog' => [
            'primary' => 'blog_id',
            'many' => true,
        ],
        'feature' => [
            'primary' => 'feature_id',
            'many' => true,
        ],
        'position' => [
            'primary' => 'position_id',
            'many' => true,
        ],
        'post' => [
            'primary' => 'post_id',
            'many' => true,
        ],
        'research' => [
            'primary' => 'research_id',
            'many' => true,
        ],
        'role' => [
            'primary' => 'role_id',
            'many' => true,
        ],
        'service' => [
            'primary' => 'service_id',
            'many' => true,
        ],
        'transaction' => [
            'primary' => 'transaction_id',
            'many' => true,
        ],
        'utm' => [
            'primary' => 'utm_id',
            'many' => true,
        ],
        
    ],
    'fields' => [
        'history_value' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'history_note' => [
            'sql' => [
                'type' => 'text',
            ],
            'test' => [
                'pass' => 'admin created a meta',
                'fail' => '',
            ],
        ],
        'history_attribute' => [
            'sql' => [
                'type' => 'varchar',
                'length' => '255',
                'index' => true,
                'searchble' => true,
                'filterable' => true
            ],
            'elastic' => [
                'type' => 'string',
            ],
            'validation' => [
                [
                    'method' => 'char_lte',
                    'message' => 'Attribute should be less than 256 characters',
                    'parameters' => '255',
                ],
            ],
            'test' => [
                'pass' => '',
            ],
        ],
        'history_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => '255',
                'filterable' => true,
                'index' => true,
            ],
            'elastic' => [
                'type' => 'string',
            ],
            'validation' => [
                [
                    'method' => 'char_lte',
                    'message' => 'Type should be less than 256 characters',
                    'parameters' => '255',
                ],
            ],
            'test' => [
                'pass' => '',
            ],
        ],
        'history_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => '0',
                'index' => true,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ],
    ],
    'fixtures' => [
        [
            'profile_id' => 1,
            'history_id' => 1,
            'history_value' => json_encode(['GET' => '', 'POST' => '', 'SERVER' => '']),
            'history_note' => 'Profile id #1 created position Vendor',
            'history_attribute' => 'position-create',
            'history_type' => null,
            'history_created' => date('Y-m-d h:i:s'),
            'history_updated' => date('Y-m-d h:i:s')
        ]
    ]
];