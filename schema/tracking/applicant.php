<?php //-->
return [
    'singular' => 'Applicant',
    'plural' => 'Applicants',
    'primary' => 'applicant_id',
    'active' => 'applicant_active',
    'created' => 'applicant_created',
    'updated' => 'applicant_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => true
        ],
        'post' => [
            'primary' => 'post_id',
            'many' => true
        ],
        'form' => [
            'primary' => 'form_id',
            'many' => true
        ],
        'answer' => [
            'primary' => 'answer_id',
            'many' => true
        ]
    ],
    'fields' => [
        'applicant_status' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Status',
                'type' => 'status-field'
            ],
            'detail' => [
                'label' => 'Status'
            ]
        ],
        'applicant_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'applicant_flag' => [
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
            'applicant_id' => 1,
            'profile_id' => 1,
            'applicant_status' => 'PENDING',
            'applicant_created' => date('Y-m-d h:i:s'),
            'applicant_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
