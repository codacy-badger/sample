<?php //-->
return [
    'singular' => 'Service',
    'plural' => 'Services',
    'primary' => 'service_id',
    'active' => 'service_active',
    'created' => 'service_created',
    'updated' => 'service_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ]
    ],
    'fields' => [
        'service_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'A Service',
                ]
            ],
            'list' => [
                'label' => 'Name'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Name is required'
                ]
            ],
            'test' => [
                'pass' => 'A Service',
                'fail' => 'validated'
            ]
        ],
        'service_credits' => [
            'sql' => [
                'type' => 'int',
                'required' => true
            ],
            'elastic' => [
                'type' => 'integer'
            ],
            'form' => [
                'label' => 'Credits',
                'type' => 'number',
                'attributes' => [
                    'placeholder' => '5',
                    'min' => '0',
                ]
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
        'service_meta' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'object'
            ]
        ],
        'service_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'service_flag' => [
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
            'service_id' => 1,
            'profile_id' => 1,
            'service_name' => 'A Service',
            'service_credits' => 3,
            'service_created' => date('Y-m-d H:i:s'),
            'service_updated' => date('Y-m-d H:i:s')
        ],
        [
            'service_id' => 2,
            'profile_id' => 2,
            'service_name' => 'A Service',
            'service_credits' => 3,
            'service_created' => date('Y-m-d H:i:s'),
            'service_updated' => date('Y-m-d H:i:s')
        ],
    ]
];
