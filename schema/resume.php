<?php //-->
return [
    'singular' => 'Resume',
    'plural' => 'Resumes',
    'primary' => 'resume_id',
    'active' => 'resume_active',
    'created' => 'resume_created',
    'updated' => 'resume_updated',
    'fields' => [
        'resume_position' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ],
        ],
        'resume_link' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'File is required'
                ]
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Resume',
                'type' => 'file',
                'attributes' => [
                    'accept' => implode(',', [
                        'application/xml-dtd',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/pdf',
                        'text/plain',
                        'application/rtf'
                    ])
                ]
            ]
        ],
        'resume_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'seeker',
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'resume_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'index' => true,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ]
    ]
];
