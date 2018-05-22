<?php //-->
return [
    'singular' => 'leads',
    'plural' => 'leads',
    'primary' => 'lead_id',
    'active' => 'lead_active',
    'created' => 'lead_created',
    'updated' => 'lead_updated',
    'relations' => [],
    'fields' => [
        'lead_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_email' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_gender' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => false,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_birth' => [
            'sql' => [
                'type' => 'date'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_phone' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_location' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_school' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_study' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_company' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_job_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_tags' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_campaigns' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_facebook' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_linkedin' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'lead_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ]
    ],
    'fixtures' => [
        [
            'lead_id' => 100,
            'lead_name' => 'Juan Dela Cruz',
            'lead_email' => 'jundelacruz@gmail.com',
            'lead_phone' => '872-5555',
            'lead_type' => 'seeker',
            'lead_gender' => 'male',
            'lead_birth' => '1984-02-02',
            'lead_facebook' => 'juantamad',
            'lead_linkedin' => 'juantamad',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Las Piñas',
            'lead_school' => 'De La Salle University',
            'lead_study' => 'Computer Science',
            'lead_company' => 'Accenture',
            'lead_position' => 'Sr Software Engineer',
            'lead_campaigns' => json_encode(['Signed Up','Posted']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ],
        [
            'lead_id' => 101,
            'lead_name' => 'Jane Dela Cruz',
            'lead_email' => 'jandelacruz@gmail.com',
            'lead_phone' => '555-2424',
            'lead_type' => 'seeker',
            'lead_gender' => 'female',
            'lead_birth' => '1989-08-16',
            'lead_facebook' => 'jandcruz',
            'lead_linkedin' => 'jandcruz',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Mandalyong',
            'lead_school' => 'Arneo',
            'lead_study' => 'Entrepreneur',
            'lead_company' => 'Openovate',
            'lead_position' => 'Co-Founder',
            'lead_campaigns' => json_encode(['Signed Up','Interested']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ],
        [
            'lead_id' => 102,
            'lead_name' => 'John Doe',
            'lead_email' => 'johndoe@gmail.com',
            'lead_phone' => '555-2424',
            'lead_type' => 'poster',
            'lead_gender' => 'male',
            'lead_birth' => '1989-08-16',
            'lead_facebook' => 'johndoe',
            'lead_linkedin' => 'johndoe',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Cavite',
            'lead_school' => 'Polytechnic University',
            'lead_study' => 'BS Communication',
            'lead_company' => 'GMA',
            'lead_position' => 'Lead Comms Officer',
            'lead_campaigns' => json_encode(['Signed Up','Interested']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ],
        [
            'lead_id' => 103,
            'lead_name' => 'Jane Doe',
            'lead_email' => 'janedoe@gmail.com',
            'lead_phone' => '555-2424',
            'lead_type' => 'poster',
            'lead_gender' => 'female',
            'lead_birth' => '1989-08-16',
            'lead_facebook' => 'janedoe',
            'lead_linkedin' => 'janedoe',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Laguna',
            'lead_school' => 'Mapua Institute',
            'lead_study' => 'ECE',
            'lead_company' => 'ABS-CBN',
            'lead_position' => 'Communications Engr',
            'lead_campaigns' => json_encode(['Signed Up','Interested']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ],
        [
            'lead_id' => 104,
            'lead_name' => 'John Smith',
            'lead_email' => 'johnsmith@gmail.com',
            'lead_phone' => '555-2424',
            'lead_type' => 'seeker',
            'lead_gender' => 'male',
            'lead_birth' => '1989-08-16',
            'lead_facebook' => 'johnsmith',
            'lead_linkedin' => 'johnsmith',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Makati',
            'lead_school' => 'University of the East',
            'lead_study' => 'Nurse',
            'lead_company' => 'Medical City',
            'lead_position' => 'Head Nurse',
            'lead_campaigns' => json_encode(['Signed Up','Interested']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ],
        [
            'lead_id' => 105,
            'lead_name' => 'Jane Smith',
            'lead_email' => 'janesmith@gmail.com',
            'lead_phone' => '555-2424',
            'lead_type' => 'seeker',
            'lead_gender' => 'female',
            'lead_birth' => '1989-08-16',
            'lead_facebook' => 'janesmith',
            'lead_linkedin' => 'janesmith',
            'lead_tags' => json_encode(['Signed Up','Posted','Interested']),
            'lead_location' => 'Mandalyong',
            'lead_school' => 'University of the Philippines - Diliman',
            'lead_study' => 'BS Pyschology',
            'lead_company' => 'ACME Elementary School',
            'lead_position' => 'Guardian',
            'lead_campaigns' => json_encode(['Signed Up','Interested']),
            'lead_created' => date('Y-m-d h:i:s'),
            'lead_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
