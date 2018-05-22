<?php //-->
return [
    'singular' => 'Answer',
    'plural' => 'Answers',
    'primary' => 'answer_id',
    'active' => 'answer_active',
    'created' => 'answer_created',
    'updated' => 'answer_updated',
    'fields' => [
        'answer_name' => [
            'sql' => [
                'type' => 'text',
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'test' => [
                'pass' => 'John Doe',
                'fail' => ''
            ]
        ],
        'answer_choices' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Choices',
                'type' => 'choice-field'
            ],
            'detail' => [
                'label' => 'Choices'
            ]
        ],
        'answer_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'answer_flag' => [
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
            'answer_id' => 1,
            'profile_id' => 1,
            'answer_name' => 'John Doe',
            'answer_created' => date('Y-m-d h:i:s'),
            'answer_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
