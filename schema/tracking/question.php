<?php //-->
return [
    'singular' => 'Question',
    'plural' => 'Questions',
    'primary' => 'question_id',
    'active' => 'question_active',
    'created' => 'question_created',
    'updated' => 'question_updated',
    'relations' => [
        'answer' => [
            'primary' => 'answer_id',
            'many' => true
        ]
    ],
    'fields' => [
        'question_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'test' => [
                'pass' => 'What is your name?',
                'fail' => ''
            ]
        ],
        'question_choices' => [
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
        'question_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'question_flag' => [
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
            'question_id' => 1,
            'profile_id' => 1,
            'question_name' => 'What is your name?',
            'question_created' => date('Y-m-d h:i:s'),
            'question_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
