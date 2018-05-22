<?php //-->
return [
    'singular' => 'Experience',
    'plural' => 'Experiences',
    'primary' => 'experience_id',
    'active' => 'experience_active',
    'created' => 'experience_created',
    'updated' => 'experience_updated',
    'relations' => [
        'information' => [
            'primary' => 'information_id',
            'many' => true
        ]
    ],
    'fields' => [
        'experience_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string',
            ]
        ],
        'experience_company' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'experience_industry' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
            ],
            'elastic' => [
                'type' => 'string',
            ],
            'test' => [
                'pass' => 'A great man',
                'fail' => ''
            ]
        ],
        'experience_related' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'unknown'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'message' => 'Should be either yes or no',
                    'parameters' => [
                        'yes',
                        'no'
                    ]
                ]
            ]
        ],
        'experience_from' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'experience_to' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'experience_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'experience_flag' => [
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
        ]
    ],
    'fixtures' => [
        [
            'experience_id' => 1,
            'information_id' => 1,
            'experience_title' => 'QA',
            'experience_company' => 'Openovate',
            'experience_industry' => 'IT',
            'experience_related' => 'yes',
            'experience_from' => '2018-02-04',
            'experience_to' => '2018-02-04',
            'experience_created' => date('Y-m-d h:i:s'),
            'experience_updated' => date('Y-m-d h:i:s')
        ],
        [
            'experience_id' => 2,
            'information_id' => 2,
            'experience_title' => 'QA',
            'experience_company' => 'Openovate',
            'experience_industry' => 'IT',
            'experience_related' => 'yes',
            'experience_from' => '2018-02-04',
            'experience_to' => '2018-02-04',
            'experience_created' => date('Y-m-d h:i:s'),
            'experience_updated' => date('Y-m-d h:i:s')
        ],
        [
            'experience_id' => 3,
            'information_id' => 3,
            'experience_title' => 'QA',
            'experience_company' => 'Openovate',
            'experience_industry' => 'IT',
            'experience_related' => 'yes',
            'experience_from' => '2018-02-04',
            'experience_to' => '2018-02-04',
            'experience_created' => date('Y-m-d h:i:s'),
            'experience_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
