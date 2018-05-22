<?php //-->
return [
    'singular' => 'Research',
    'plural' => 'Researches',
    'primary' => 'research_id',
    'active' => 'research_active',
    'created' => 'research_created',
    'updated' => 'research_updated',
    'fields' => [
        'research_position' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Position',
                'type' => 'keyword-field'
            ],
            'detail' => [
                'label' => 'Position'
            ]
        ],
        'research_location' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Position',
                'type' => 'keyword-field'
            ],
            'detail' => [
                'label' => 'Position'
            ]
        ],
        'research_type' => [
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
        'research_flag' => [
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
    'fixtures' => [
        [
            'research_location' => '{
                "manila": {
                    "unemployment_rate": ".3",
                    "hiring_rate": "10",
                    "top_positions": 
                        [   
                            "Project Manager",
                            "Web Developer"
                        ],
                    "top_companies": 
                        [
                            1,
                            2
                        ],
                    "salary_range": "5000",
                    "ad_space": [
                        1,
                        2
                    ]
                }
            }'
            ,
            'research_position' => '
                {
                    "Web Developer": {
                        "average_salary": "10000",
                        "salary_range": "10000",
                        "top_companies":
                            [
                                1,
                                2
                            ],
                        "job_details": "Web Developer Details",
                        "qualifications": "College Graduate",
                        "ad_space": [
                                1,
                                2
                            ],
                        "location":  {
                            "manila": {
                                "average_salary": "10000",
                                "seeker_count": "10",
                                "salary_range": "10000",
                                "top_companies":
                                    [
                                        1,
                                        2
                                    ],
                                "ad_space":  
                                    [
                                        1,
                                        2
                                    ]
                            }
                        }
                    }
                }',
            'research_created' => date('Y-m-d H:i:s'),
            'research_updated' => date('Y-m-d H:i:s')
        ]
    ]
];
