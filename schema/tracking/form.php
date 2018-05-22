<?php //-->
return [
    'singular' => 'Form',
    'plural' => 'Forms',
    'primary' => 'form_id',
    'active' => 'form_active',
    'created' => 'form_created',
    'updated' => 'form_updated',
    'relations' => [
        'question' => [
            'primary' => 'question_id',
            'many' => true
        ]
    ],
    'fields' => [
        'form_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'test' => [
                'pass' => 'Project Manager Form',
                'fail' => ''
            ]
        ],
        'form_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'form_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ]
    ],
    'fixtures' => [
        [
            'form_id' => 1,
            'profile_id' => 1,
            'form_name' => 'Project Manager Form',
            'form_created' => date('Y-m-d h:i:s'),
            'form_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
