<?php //-->
return [
    'singular' => 'action',
    'plural' => 'actions',
    'primary' => 'action_id',
    'active' => 'action_active',
    'created' => 'action_created',
    'updated' => 'action_updated',
    'relations' => [
        'template' => [
            'primary' => 'template_id',
            'many' => false
        ]
    ],
    'fields' => [
        'action_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'action_event' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'action_when' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'action_tags' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ]
    ],
    'fixtures' => [
        [
            'action_id' => 101,
            'template_id' => 201,
            'action_title' => 'post',
            'action_event' => 'auth-create',
            'action_when' => 'profile_experience > 10',
            'action_tags' => json_encode(['Signed Up', 'Interested']),
            'action_created' => date('Y-m-d h:i:s'),
            'action_updated' => date('Y-m-d h:i:s')
        ],
        [
            'action_id' => 102,
            'template_id' => 202,
            'action_title' => 'post',
            'action_event' => 'auth-signup',
            'action_when' => 'profile_experience LIKE 2',
            'action_tags' => json_encode(['Signed Up', 'Posted', 'Interested']),
            'action_created' => date('Y-m-d h:i:s'),
            'action_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
