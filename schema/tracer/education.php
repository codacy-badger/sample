<?php //-->
return [
    'singular' => 'Education',
    'plural' => 'Educations',
    'primary' => 'education_id',
    'active' => 'education_active',
    'created' => 'education_created',
    'updated' => 'education_updated',
    'relations' => [
        'information' => [
            'primary' => 'information_id',
            'many' => true
        ]
    ],
    'fields' => [
        'education_school' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string',
            ]
        ],
        'education_degree' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'education_activity' => [
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
        'education_from' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'education_to' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'education_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'education_flag' => [
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
            'education_id' => 1,
            'information_id' => 1,
            'education_school' => 'QA',
            'education_degree' => 'Openovate',
            'education_activity' => 'IT',
            'education_from' => '2018-02-04',
            'education_to' => '2018-02-04',
            'education_created' => date('Y-m-d h:i:s'),
            'education_updated' => date('Y-m-d h:i:s')
        ],
        [
            'education_id' => 2,
            'information_id' => 2,
            'education_school' => 'QA',
            'education_degree' => 'Openovate',
            'education_activity' => 'IT',
            'education_from' => '2018-02-04',
            'education_to' => '2018-02-04',
            'education_created' => date('Y-m-d h:i:s'),
            'education_updated' => date('Y-m-d h:i:s')
        ],
        [
            'education_id' => 3,
            'information_id' => 3,
            'education_school' => 'QA',
            'education_degree' => 'Openovate',
            'education_activity' => 'IT',
            'education_from' => '2018-02-04',
            'education_to' => '2018-02-04',
            'education_created' => date('Y-m-d h:i:s'),
            'education_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
