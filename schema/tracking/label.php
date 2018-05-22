<?php //-->
return [
    'singular' => 'Label',
    'plural' => 'Labels',
    'primary' => 'label_id',
    'active' => 'label_active',
    'created' => 'label_created',
    'updated' => 'label_updated',
    'fields' => [
        'label_custom' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Labels',
                'type' => 'label-field'
            ],
            'detail' => [
                'label' => 'Labels'
            ]
        ],
        'label_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'label_flag' => [
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
    'fixtures' => [
        [
            'label_id' => 1,
            'profile_id' => 1,
            'label_custom' => '["Hired"]',
            'label_created' => date('Y-m-d h:i:s'),
            'label_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
