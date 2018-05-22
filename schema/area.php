<?php //-->
return [
    'singular' => 'Area',
    'plural' => 'areas',
    'primary' => 'area_id',
    'active' => 'area_active',
    'created' => 'area_created',
    'updated' => 'area_updated',
    'relations' => [],
    'fields' => [
        'area_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ]
        ],
        'area_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ]
        ],
        'area_parent' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'index' => true
            ]
        ],
        'area_postal' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'index' => true
            ]
        ],
        'area_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ]
        ]
    ]
];
