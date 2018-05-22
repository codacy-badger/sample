<?php //-->
return [
    'singular' => 'Thread',
    'plural' => 'Threads',
    'primary' => 'thread_id',
    'active' => 'thread_active',
    'created' => 'thread_created',
    'updated' => 'thread_updated',
    'relations' => [
        'history' => [
            'primary' => 'history_id',
            'many' => false
        ]
    ],
    'fields' => [
        'thread_gmail_id' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Gmail thread id is required'
                ]
            ],
            'list' => [
                'label' => 'Gmail thread id',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => '263721638',
                'fail' => ''
            ]
        ],
        'thread_subject' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
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
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Subject is required'
                ]
            ],
            'list' => [
                'label' => 'Subject',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => 'Meeting',
                'fail' => ''
            ]
        ],
        'thread_snippet' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
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
            ],
            'list' => [
                'label' => 'Snippet',
                'searchable' => true,
                'sortable' => true
            ]
        ]
    ]
];
