<?php //-->
return [
    'singular' => 'School',
    'plural' => 'Schools',
    'primary' => 'school_id',
    'active' => 'school_active',
    'created' => 'school_created',
    'updated' => 'school_updated',
    'relations' => [],
    'fields' => [
        'school_name' => [
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
            ]
        ],
        'school_description' => [
            'sql' => [
                'type' => 'text'
            ]
        ],
        'school_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ]
        ],
        'school_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ]
        ]
    ]
];
