<?php //-->
return [
    'singular' => 'Accomplishment',
    'plural' => 'Accomplishments',
    'primary' => 'accomplishment_id',
    'active' => 'accomplishment_active',
    'created' => 'accomplishment_created',
    'updated' => 'accomplishment_updated',
    'relations' => [
        'information' => [
            'primary' => 'information_id',
            'many' => true
        ]
    ],
    'fields' => [
        'accomplishment_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string',
            ]
        ],
        'accomplishment_description' => [
            'sql' => [
                'type' => 'text'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'accomplishment_from' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'accomplishment_to' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ]
        ],
        'accomplishment_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'accomplishment_flag' => [
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
            'accomplishment_id' => 1,
            'information_id' => 1,
            'accomplishment_name' => 'QA',
            'accomplishment_description' => 'Openovate',
            'accomplishment_from' => '2018-02-04',
            'accomplishment_to' => '2018-02-04',
            'accomplishment_created' => date('Y-m-d h:i:s'),
            'accomplishment_updated' => date('Y-m-d h:i:s')
        ],
        [
            'accomplishment_id' => 2,
            'information_id' => 2,
            'accomplishment_name' => 'QA',
            'accomplishment_description' => 'Openovate',
            'accomplishment_from' => '2018-02-04',
            'accomplishment_to' => '2018-02-04',
            'accomplishment_created' => date('Y-m-d h:i:s'),
            'accomplishment_updated' => date('Y-m-d h:i:s')
        ],
        [
            'accomplishment_id' => 3,
            'information_id' => 3,
            'accomplishment_name' => 'QA',
            'accomplishment_description' => 'Openovate',
            'accomplishment_from' => '2018-02-04',
            'accomplishment_to' => '2018-02-04',
            'accomplishment_created' => date('Y-m-d h:i:s'),
            'accomplishment_updated' => date('Y-m-d h:i:s')
        ],
    ]
];
