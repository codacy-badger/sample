<?php //-->
return [
    'singular' => 'Interview Schedule',
    'plural' => 'Interview Schedules',
    'primary' => 'interview_schedule_id',
    'active' => 'interview_schedule_active',
    'created' => 'interview_schedule_created',
    'updated' => 'interview_schedule_updated',
    'relations' => [
        'interview_setting' => [
            'primary' => 'interview_setting_id',
            'many' => false
        ],
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ],
        'post' => [
            'primary' => 'post_id',
            'many' => false
        ],
        'interview_setting' => [
            'primary' => 'interview_setting_id',
            'many' => false
        ],
    ],
    'fields' => [
        'interview_schedule_date' => [
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
        'interview_schedule_start_time' => [
            'sql' => [
                'type' => 'time'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_schedule_end_time' => [
            'sql' => [
                'type' => 'time'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_schedule_status' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_schedule_meta' => [
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
        'interview_schedule_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'interview_schedule_flag' => [
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
