<?php //-->
return [
    'singular' => 'Position',
    'plural' => 'Positions',
    'primary' => 'position_id',
    'active' => 'position_active',
    'created' => 'position_created',
    'updated' => 'position_updated',
    'relations' => [
        'skills' => [
            'primary' => 'skill_id',
            'many' => true,
        ]
    ],
    'fields' => [
        'position_name' => [
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
            'form' => [
                'label' => 'Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Programmer',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Name is required'
                ]
            ],
            'list' => [
                'label' => 'Position',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => 'John Doe',
                'fail' => ''
            ]
        ],
        'position_description' => [
            'sql' => [
                'type' => 'text',
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'text',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'form' => [
                'label' => 'Detail',
                'type' => 'textarea',
                'attributes' => [
                    'placeholder' => 'Write about something',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Detail is required'
                ]
            ],
            'detail' => [
                'label' => 'Detail',
                'noescape' => true
            ],
            'test' => [
                'pass' => 'One Two Three Four Five Six Seven Eight Nine Ten Eleven',
                'fail' => 'One Two Three Four'
            ]
        ],
        'position_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ]
        ],
        'position_parent' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'index' => true
            ]
        ],
        'position_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ]
        ]
    ]
];
