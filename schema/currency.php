<?php //-->
return [
    'singular' => 'Currency',
    'plural' => 'Currencies',
    'primary' => 'currency_id',
    'active' => 'currency_active',
    'created' => 'currency_created',
    'updated' => 'currency_updated',
    'relations' => [],
    'fields' => [
        'currency_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'currency_flag' => [
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
        'currency_symbol' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ]

    ],
    'fixtures' => [
        [
            'currency_id' => 1,
            'currency_type' => 'peso',
            'currency_symbol' => '₱',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 2,
            'currency_type' => 'dollar',
            'currency_symbol' => '$',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 3,
            'currency_type' => 'euro',
            'currency_symbol' => '€',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 4,
            'currency_type' => 'florin',
            'currency_symbol' => 'ƒ',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 5,
            'currency_type' => 'franc',
            'currency_symbol' => 'Fr',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 6,
            'currency_type' => 'won',
            'currency_symbol' => '₩',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 7,
            'currency_type' => 'yen',
            'currency_symbol' => '¥',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ],
        [
            'currency_id' => 8,
            'currency_type' => 'indian rupee',
            'currency_symbol' => '₹',
            'currency_created' => date('Y-m-d H:i:s'),
            'currency_updated' => date('Y-m-d H:i:s')
        ]
    ]
];