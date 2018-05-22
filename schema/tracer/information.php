<?php //-->
return [
    'singular' => 'Information',
    'plural' => 'Informations',
    'primary' => 'information_id',
    'active' => 'information_active',
    'created' => 'information_created',
    'updated' => 'information_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ]
    ],
    'fields' => [
        'information_heading' => [
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
        'information_civil_status' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'unknown'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'information_skills' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'information_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'information_flag' => [
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
            'information_id' => 1,
            'profile_id' => 1,
            'information_heading' => 'Professional Programmer',
            'information_civil_status' => 'Single',
            'information_skills' => '["Basketball"]',
            'information_created' => date('Y-m-d h:i:s'),
            'information_updated' => date('Y-m-d h:i:s')
        ],
        [
            'information_id' => 2,
            'profile_id' => 2,
            'information_heading' => 'Professional Programmer',
            'information_civil_status' => 'Single',
            'information_skills' => '["Basketball"]',
            'information_created' => date('Y-m-d h:i:s'),
            'information_updated' => date('Y-m-d h:i:s')
        ],
        [
            'information_id' => 3,
            'profile_id' => 3,
            'information_heading' => 'Professional Programmer',
            'information_civil_status' => 'Single',
            'information_skills' => '["Basketball"]',
            'information_created' => date('Y-m-d h:i:s'),
            'information_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
