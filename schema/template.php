<?php //-->
return [
    'singular'  => 'template',
    'plural'    => 'templates',
    'primary'   => 'template_id',
    'active'    => 'template_active',
    'created'   => 'template_created',
    'updated'   => 'template_updated',
    'relations' => [],
    'fields'    => [
        'template_title'       => [
            'sql'     => [
                'type'       => 'varchar',
                'length'     => 255,
                'required'   => false,
                'index'      => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'template_type'        => [
            'sql'     => [
                'type'   => 'varchar',
                'length' => 255,
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'template_html'        => [
            'sql'     => [
                'type'  => 'text',
            ],
            'elastic' => [
                'type' => 'text'
            ]
        ],
        'template_text'        => [
            'sql'     => [
                'type'  => 'text',
            ],
            'elastic' => [
                'type' => 'text'
            ]
        ],
        'template_unsubscribe' => [
            'sql'     => [
                'type'   => 'varchar',
                'length' => 255,
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'template_webversion'  => [
            'sql'     => [
                'type'   => 'varchar',
                'length' => 255,
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'template_uid'         => [
            'sql'     => [
                'type'   => 'varchar',
                'length' => 255,
                'index'  => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ]
    ],
    'fixtures'  => [
        [
            'template_id'      => 1,
            'template_title'   => 'Default',
            'template_type'    => 'email',
            'template_html'    => '',
            'template_text'    => '',
            'template_created' => date('Y-m-d h:i:s'),
            'template_updated' => date('Y-m-d h:i:s')
        ]
    ]
];