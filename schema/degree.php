<?php //-->
return [
    'singular' => 'Degree',
    'plural' => 'degrees',
    'primary' => 'degree_id',
    'active' => 'degree_active',
    'created' => 'degree_created',
    'updated' => 'degree_updated',
    'relations' => [],
    'fields' => [
        'degree_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ]
        ],
        'degree_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ]
        ],
        'degree_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ]
        ]
    ]
];
