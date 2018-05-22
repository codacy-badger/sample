<?php //-->
return [
    'singular' => 'Term',
    'plural' => 'Terms',
    'primary' => 'term_id',
    'active' => 'term_active',
    'created' => 'term_created',
    'updated' => 'term_updated',
    'relations' => [],
    'fields' => [
        'term_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true,
                'filterable' => true
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
                    'message' => 'Name is required'
                ]
            ],
            'list' => [
                'label' => 'Name',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => 'Apple',
                'fail' => ''
            ]
        ],
        'term_hits' => [
            'sql' => [
                'type' => 'int',
                'length' => 8,
                'sortable' => true,
                'default' => 1,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'number'
            ],
            'validation' => [
                [
                    'method' => 'number',
                    'message' => 'Experience should be a number.'
                ]
            ],
            'list' => [
                'label' => 'Hits'
            ]
        ],
        'term_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'search',
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'term_flag' => [
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
            'term_name' => 'Programmer',
            'term_type' => 'position',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'Customer Support Agent',
            'term_type' => 'position',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'Metro Manila',
            'term_type' => 'location',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'Cebu City',
            'term_type' => 'location',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'PHP',
            'term_type' => 'tag',
            'term_hits' => 4,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'Elastic',
            'term_type' => 'tag',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'foobar',
            'term_type' => 'search',
            'term_hits' => 4,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
        [
            'term_name' => 'doe',
            'term_type' => 'search',
            'term_hits' => 5,
            'term_created' => date('Y-m-d H:i:s'),
            'term_updated' => date('Y-m-d H:i:s')
        ],
    ]
];
