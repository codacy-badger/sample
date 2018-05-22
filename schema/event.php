<?php //-->
return [
    'singular' => 'event',
    'plural' => 'events',
    'primary' => 'event_id',
    'active' => 'event_active',
    'created' => 'event_created',
    'updated' => 'event_updated',
    'relations' => [
        'deal' => [
            'primary' => 'deal_id',
            'many' => false
        ],
        'profile' => [
            'primary' => 'profile_id',
            'many' => true
        ]
    ],
    'fields' => [
        'event_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'event_start' => [
            'sql' => [
                'type' => 'datetime',
                'index' => true,
                'required' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'event_end' => [
            'sql' => [
                'type' => 'datetime',
                'index' => true,
                'required' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'event_type' => [
            'sql' => [
                'type' => 'varchar',
                'default' => 'meeting',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
    ]
];
