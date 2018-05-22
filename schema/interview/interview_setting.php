<?php //-->
return [
    'singular' => 'Interview Setting',
    'plural' => 'Interview Settings',
    'primary' => 'interview_setting_id',
    'active' => 'interview_setting_active',
    'created' => 'interview_setting_created',
    'updated' => 'interview_setting_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ],
    ],
    'fields' => [
        'interview_setting_date' => [
            'sql' => [
                'type' => 'date',
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd'
            ],
            'list' => [
                'label' => 'Date',
                'format' => 'date',
                'parameters' => 'M d, Y'
            ],
            'detail' => [
                'label' => 'Date',
                'format' => 'date',
                'parameters' => 'M d, Y'
            ]
        ],
        'interview_setting_start_time' => [
            'sql' => [
                'type' => 'time'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_setting_end_time' => [
            'sql' => [
                'type' => 'time'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_setting_slots' => [
            'sql' => [
                'type' => 'int',
                'length' => 11,
                'default' => 0,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ],
        'interview_setting_meta' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Choices',
                'type' => 'choice-field'
            ],
            'detail' => [
                'label' => 'Choices'
            ]
        ],
        'interview_setting_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_setting_flag' => [
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
    'fixtures' => []
];
