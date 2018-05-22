<?php //-->
return [
    'singular' => 'campaign',
    'plural' => 'campaigns',
    'primary' => 'campaign_id',
    'active' => 'campaign_active',
    'created' => 'campaign_created',
    'updated' => 'campaign_updated',
    'relations' => [
        'template' => [
            'primary' => 'template_id',
            'many' => false
        ],
        'lead' => [
            'primary' => 'lead_id',
            'many' => true
        ],
        'profile' => [
            'primary' => 'profile_id',
            'many' => true
        ]
    ],
    'fields' => [
        'campaign_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
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
        'campaign_medium' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'unknown'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'campaign_source' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'unknown'
            ]
        ],
        'campaign_audience' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'unknown'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'campaign_tags' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ]
    ],
    'fixtures' => [
        [
            'campaign_id' => 2001,
            'template_id' => 201,
            'lead_id' => 100,
            'campaign_title' => 'Welcome to Jobayan',
            'campaign_template' => 'Welcome Jobayan',
            'campaign_medium' => 'Email',
            'campaign_source' => 'Leads',
            'campaign_audience' => 'Seekers',
            'campaign_tags' => json_encode(['Signed Up','Posted','Interested']),
            'campaign_created' => date('Y-m-d h:i:s'),
            'campaign_updated' => date('Y-m-d h:i:s')
        ],
        [
            'campaign_id' => 2002,
            'template_id' => 202,
            'lead_id' => 100,
            'campaign_title' => 'Thank You for visiting Jobayan',
            'campaign_template' => 'Goodbye Jobayan',
            'campaign_medium' => 'SMS',
            'campaign_source' => 'Users',
            'campaign_audience' => 'Seekers',
            'campaign_tags' => json_encode(['Signed Up','Posted']),
            'campaign_created' => date('Y-m-d h:i:s'),
            'campaign_updated' => date('Y-m-d h:i:s')
        ]
    ]
];