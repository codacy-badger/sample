<?php //-->
return [
    'singular' => 'UTM',
    'plural' => 'UTMs',
    'primary' => 'utm_id',
    'active' => 'utm_active',
    'created' => 'utm_created',
    'updated' => 'utm_updated',
    'relations' => [],
    'fields' => [
        'utm_title' => [
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
                'label' => 'Title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Title',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ],
            'list' => [
                'label' => 'Title',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => 'Title',
                'fail' => ''
            ]
        ],
        'utm_source' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'UTM Source',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'UTM Source',
                ]
            ],
            'list' => [
                'label' => 'UTM Source',
                'searchable' => true
            ],
            'detail' => [
                'label' => 'UTM Source'
            ]
        ],
        'utm_medium' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'UTM Medium',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'UTM Medium',
                ]
            ],
            'list' => [
                'label' => 'UTM Medium',
                'searchable' => true
            ],
            'detail' => [
                'label' => 'UTM Medium'
            ]
        ],
        'utm_campaign' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'UTM Campaign',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'UTM Campaign',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'campaign is required'
                ]
            ],
            'list' => [
                'label' => 'UTM Campaign',
                'searchable' => true
            ],
            'detail' => [
                'label' => 'UTM Campaign'
            ],
            'test' => [
                'pass' => 'UTM Campaign',
                'fail' => ''
            ]
        ],
        'utm_detail' => [
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
        'utm_page' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'detail' => [
                'label' => 'UTM page'
            ]
        ],
        'utm_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Image',
                'type' => 'image-field',
                'attributes' => [
                    'data-do' => 'image-field',
                ]
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Should be a valid url',
                    'parameters' => '/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]'
                    .'*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i'
                ]
            ],
            'list' => [
                'label' => 'UTM Image',
                'format' => 'image',
                'parameters' => [200, 200]
            ],
            'test' => [
                'pass' => 'https://www.google.com.ph/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png',
                'fail' => 'not a good image',
            ]
        ],
        'utm_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => null,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'utm_flag' => [
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
    'fixtures' => []
];
