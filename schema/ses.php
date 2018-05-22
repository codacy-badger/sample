<?php //-->
return [
    'singular' => 'SES Email Event',
    'plural' => 'SES Email Events',
    'primary' => 'ses_id',
    'active' => 'ses_active',
    'created' => 'ses_created',
    'updated' => 'ses_updated',
    'relations' => [
    ],
    'fields' => [
        'ses_message' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true,
                'comment' => 'messageId'
            ],
            'elastic' => [
                'type' => 'string',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ]
        ],
        'ses_link' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ]
        ],
        'ses_emails' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'ses_total' => [
            'sql' => [
                'type' => 'int',
                'length' => 11,
                'sortable' => true,
                'filterable' => true,
            ],
            'elastic' => [
                'type' => 'number'
            ]
        ],
        'ses_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true,
            ],
            'elastic' => [
                'type' => 'string',
            ]
        ],

    ]
];
