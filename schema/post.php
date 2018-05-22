<?php //-->
return [
    'singular' => 'Post',
    'plural' => 'Posts',
    'primary' => 'post_id',
    'active' => 'post_active',
    'created' => 'post_created',
    'updated' => 'post_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ],
        'form' => [
            'primary' => 'form_id',
            'many' => true
        ],
        'comment' => [
            'primary' => 'comment_id',
            'many' => true
        ]
    ],
    'fields' => [
        'post_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Post Image',
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
                'label' => 'Logo',
                'format' => 'image',
                'parameters' => [200, 200]
            ],
            'test' => [
                'pass' => 'https://www.google.com.ph/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png',
                'fail' => 'not a good image',
            ]
        ],
        'post_name' => [
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
                'label' => 'Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'John Doe',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Name is required'
                ]
            ],
            'list' => [
                'label' => 'Name',
                'searchable' => true,
                'sortable' => true
            ],
            'test' => [
                'pass' => 'John Doe',
                'fail' => ''
            ]
        ],
        'post_email' => [
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
                'label' => 'Email',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'john@doe.com',
                ]
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Must be a valid email',
                    'parameters' => '/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]'.
                    '\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]'.
                    '\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62'.
                    '}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|'.
                    '[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})'.
                    '(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]'.
                    '+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25'.
                    '[0-5])){3}\])$/'
                ]
            ],
            'list' => [
                'label' => 'Email',
                'searchable' => true
            ],
            'detail' => [
                'label' => 'Email'
            ],
            'test' => [
                'pass' => 'john@doe.com',
                'fail' => 'a bad email'
            ]
        ],
        'post_phone' => [
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
                'label' => 'Phone',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'John Doe',
                ]
            ],
            'list' => [
                'label' => 'Phone',
                'searchable' => true
            ],
            'detail' => [
                'label' => 'Phone'
            ]
        ],
        'post_position' => [
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
                'label' => 'Job Position',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a Title',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ],
            'list' => [
                'label' => 'Job Position',
                'format' => 'link',
                'parameters' => [
                    'href' => '/post/{{post_slug}}',
                    'target' => '_blank'
                ]
            ],
            'detail' => [
                'label' => 'Job Position'
            ],
            'test' => [
                'pass' => 'Foobar Title',
                'fail' => 'Foobar'
            ]
        ],
        'post_location' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'searchable' => true,
                'default' => 'NULL',
                'filterable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Location',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter job location',
                    'data-do' => 'suggest',
                    'data-table'=> 'term',
                    'data-type' => 'location'
                ]
            ]
        ],
        'post_geo_location' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'post_experience' => [
            'sql' => [
                'type' => 'int',
                'length' => 4,
                'sortable' => true,
                'filterable' => true,
                'comment' => 'Experience in years'
            ],
            'elastic' => [
                'type' => 'number'
            ],
            'form' => [
                'label' => 'Experience in years',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => '2',
                ]
            ],
            'validation' => [
                [
                    'method' => 'number',
                    'message' => 'Experience should be a number.'
                ]
            ],
            'list' => [
                'label' => 'Experience'
            ]
        ],
        'post_resume' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
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
        'post_detail' => [
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
        'post_verify' => [
            'form' => [
                'label' => 'Verify me with',
                'type' => 'radios',
                'options' => [
                    'facebook' => 'Facebook',
                    'linkedin' => 'LinkedIn'
                ]
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'message' => 'Must choose a verification method',
                    'parameters' => [
                        'facebook',
                        'linkedin'
                    ]
                ]
            ],
        ],
        'post_notify' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Notify',
                'type' => 'checkboxes',
                'options' => [
                    'matches' => 'Notify me when a job post matches this description.',
                    'likes' => 'Notify me when a company <i class="fa fa-heart"></i> this post.'
                ]
            ]
        ],
        'post_expires' => [
            'sql' => [
                'type' => 'datetime',
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ],
            'list' => [
                'label' => 'Expires',
                'format' => 'date',
                'parameters' => 'M d'
            ],
            'detail' => [
                'label' => 'Expires On',
                'format' => 'date',
                'parameters' => 'F d, y g:iA'
            ]
        ],
        'post_banner' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Post Banner',
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
                'label' => 'Logo',
                'format' => 'image',
                'parameters' => [200, 200]
            ],
            'test' => [
                'pass' => 'https://www.google.com.ph/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png',
                'fail' => 'not a good image',
            ]
        ],
        'post_salary_min' => [
            'sql' => [
                'type' => 'int',
                'length' => 7,
                'required' => false,
                'searchable' => false
            ],
            'elastic' => [
                'type' => 'number'
            ],
            'form' => [
                'label' => 'Min salary',
                'type' => 'number',
                'attributes' => [
                    'placeholder' => '12500',
                ]
            ],
            'validation' => [
                [
                    'method' => 'number',
                    'message' => 'Must be a valid number'
                ]
            ]
        ],
        'post_salary_max' => [
            'sql' => [
                'type' => 'int',
                'length' => 7,
                'required' => false,
                'searchable' => false
            ],
            'elastic' => [
                'type' => 'number'
            ],
            'form' => [
                'label' => 'Max Salary',
                'type' => 'number',
                'attributes' => [
                    'placeholder' => '12500',
                ]
            ],
            'validation' => [
                [
                    'method' => 'number',
                    'message' => 'Must be a valid number'
                ]
            ]
        ],
        'post_link' => [
            'sql' => [
                'type'          => 'text',
                'required'      => false,
                'searchable'    => false
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Job Link',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'http://www.acme.com/',
                ]
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Must be a valid URL',
                    'parameters' => '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
                    '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i'
                ]
            ]
        ],
        'post_like_count' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'default' => 0,
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'number'
            ]
        ],
        'post_download_count' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'default' => 0,
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'number'
            ]
        ],
        'post_email_count' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'default' => 0,
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'number'
            ]
        ],
        'post_phone_count' => [
            'sql' => [
                'type' => 'int',
                'length' => 10,
                'default' => 0,
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'number'
            ]
        ],
        'post_tags' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Tags',
                'type' => 'tag-field'
            ],
            'detail' => [
                'label' => 'Tags',
                'format' => 'link',
                'parameters' => [
                    'href' => '/post/search?product_tag=:product_tag'
                ]
            ]
        ],
        'post_arrangement' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'NULL'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Arrangement',
                'type' => 'radios',
                'options' => [
                    'full time' => 'Full Time',
                    'part time' => 'Part Time',
                    'freelance' => 'Freelance'
                ]
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'message' => 'Must choose an arragement',
                    'parameters' => [
                        'full time',
                        'part time',
                        'freelance'
                    ]
                ]
            ]
        ],
        'post_type' => [
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
        'post_flag' => [
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
        ],
        'post_view' => [
            'sql' => [
                'type' => 'int',
                'length' => 11,
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
            'post_id' => 1,
            'profile_id' => 1,
            'app_id' => 1,
            'post_name' => 'Google Ventures',
            'post_email' => 'john@doe.com',
            'post_phone' => '555-2424',
            'post_position' => 'Senior Backend Developer',
            'post_location' => 'Cebu City',
            'post_experience' => 2,
            'post_detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis, lectus vitae faucibus elementum, erat lorem maximus nulla, at condimentum nulla nibh a magna. Donec rutrum magna non mauris sodales pharetra. Duis ullamcorper augue at dolor lacinia sodales. Quisque consectetur magna in justo pulvinar placerat. Etiam eget arcu ut eros auctor porta sed a est. Curabitur sed neque eu sapien interdum vehicula. Aliquam vel finibus eros. Praesent auctor neque luctus, ultricies risus ut, vulputate lacus. Donec commodo elit non mauris congue feugiat. Nam eu purus porta, pulvinar justo vel, tempus augue. Duis rutrum augue justo, at sodales magna euismod nec. Vestibulum vel pretium velit. Cras mollis ligula nec odio tincidunt auctor. Cras faucibus consectetur ullamcorper.',
            'post_notify' => json_encode(['matches', 'likes']),
            'post_type' => 'poster',
            'post_salary_min' => 20000,
            'post_salary_max' => 40000,
            'post_banner' => '/images/placeholder/banner/banner-1.jpg',
            'post_like_count' => 4,
            'post_link' => 'https://used.com.ph',
            'post_tags' => '["IT", "Software"]',
            'post_expires' => date('Y-m-d', strtotime("+30 days")),
            'post_created' => date('Y-m-d h:i:s'),
            'post_updated' => date('Y-m-d h:i:s')
        ],
        [
            'post_id' => 2,
            'profile_id' => 2,
            'app_id' => 1,
            'post_name' => 'Jane Doe',
            'post_email' => 'jane@doe.com',
            'post_phone' => '555-2525',
            'post_position' => 'Customer Support',
            'post_location' => 'Metro Manila',
            'post_experience' => 2,
            'post_detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis, lectus vitae faucibus elementum, erat lorem maximus nulla, at condimentum nulla nibh a magna. Donec rutrum magna non mauris sodales pharetra. Duis ullamcorper augue at dolor lacinia sodales. Quisque consectetur magna in justo pulvinar placerat. Etiam eget arcu ut eros auctor porta sed a est. Curabitur sed neque eu sapien interdum vehicula. Aliquam vel finibus eros. Praesent auctor neque luctus, ultricies risus ut, vulputate lacus. Donec commodo elit non mauris congue feugiat. Nam eu purus porta, pulvinar justo vel, tempus augue. Duis rutrum augue justo, at sodales magna euismod nec. Vestibulum vel pretium velit. Cras mollis ligula nec odio tincidunt auctor. Cras faucibus consectetur ullamcorper.',
            'post_notify' => json_encode(['matches', 'likes']),
            'post_type' => 'seeker',
            'post_link' => 'https://used.com.ph',
            'post_expires' => date('Y-m-d', strtotime("+30 days")),
            'post_created' => date('Y-m-d h:i:s'),
            'post_updated' => date('Y-m-d h:i:s')
        ],
        [
            'post_id' => 3,
            'profile_id' => 2,
            'app_id' => 1,
            'post_name' => 'Google Maps',
            'post_email' => 'jane@doe.com',
            'post_phone' => '555-2525',
            'post_position' => 'Customer Support',
            'post_location' => 'Metro Manila',
            'post_experience' => 2,
            'post_detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis, lectus vitae faucibus elementum, erat lorem maximus nulla, at condimentum nulla nibh a magna. Donec rutrum magna non mauris sodales pharetra. Duis ullamcorper augue at dolor lacinia sodales. Quisque consectetur magna in justo pulvinar placerat. Etiam eget arcu ut eros auctor porta sed a est. Curabitur sed neque eu sapien interdum vehicula. Aliquam vel finibus eros. Praesent auctor neque luctus, ultricies risus ut, vulputate lacus. Donec commodo elit non mauris congue feugiat. Nam eu purus porta, pulvinar justo vel, tempus augue. Duis rutrum augue justo, at sodales magna euismod nec. Vestibulum vel pretium velit. Cras mollis ligula nec odio tincidunt auctor. Cras faucibus consectetur ullamcorper.',
            'post_notify' => json_encode(['matches', 'likes']),
            'post_type' => 'poster',
            'post_salary_min' => 15000,
            'post_link' => 'https://used.com.ph',
            'post_expires' => date('Y-m-d', strtotime("+30 days")),
            'post_created' => date('Y-m-d h:i:s'),
            'post_updated' => date('Y-m-d h:i:s')
        ],
        [
            'post_id' => 4,
            'profile_id' => 3,
            'app_id' => 1,
            'post_name' => 'Jack Doe',
            'post_email' => 'jack@doe.com',
            'post_phone' => '555-2424',
            'post_position' => 'Customer Support',
            'post_location' => 'Metro Manila',
            'post_experience' => 1,
            'post_detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis, lectus vitae faucibus elementum, erat lorem maximus nulla, at condimentum nulla nibh a magna. Donec rutrum magna non mauris sodales pharetra. Duis ullamcorper augue at dolor lacinia sodales. Quisque consectetur magna in justo pulvinar placerat. Etiam eget arcu ut eros auctor porta sed a est. Curabitur sed neque eu sapien interdum vehicula. Aliquam vel finibus eros. Praesent auctor neque luctus, ultricies risus ut, vulputate lacus. Donec commodo elit non mauris congue feugiat. Nam eu purus porta, pulvinar justo vel, tempus augue. Duis rutrum augue justo, at sodales magna euismod nec. Vestibulum vel pretium velit. Cras mollis ligula nec odio tincidunt auctor. Cras faucibus consectetur ullamcorper.',
            'post_notify' => json_encode(['matches', 'likes']),
            'post_type' => 'seeker',
            'post_link' => 'https://used.com.ph',
            'post_expires' => date('Y-m-d', strtotime("+30 days")),
            'post_created' => date('Y-m-d h:i:s'),
            'post_updated' => date('Y-m-d h:i:s')
        ],
        [
            'post_id' => 5,
            'profile_id' => 3,
            'app_id' => 1,
            'post_name' => 'Google Search',
            'post_email' => 'jack@doe.com',
            'post_phone' => '555-2626',
            'post_position' => 'Junior Web Developer',
            'post_location' => 'Metro Manila',
            'post_experience' => 2,
            'post_detail' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi mattis, lectus vitae faucibus elementum, erat lorem maximus nulla, at condimentum nulla nibh a magna. Donec rutrum magna non mauris sodales pharetra. Duis ullamcorper augue at dolor lacinia sodales. Quisque consectetur magna in justo pulvinar placerat. Etiam eget arcu ut eros auctor porta sed a est. Curabitur sed neque eu sapien interdum vehicula. Aliquam vel finibus eros. Praesent auctor neque luctus, ultricies risus ut, vulputate lacus. Donec commodo elit non mauris congue feugiat. Nam eu purus porta, pulvinar justo vel, tempus augue. Duis rutrum augue justo, at sodales magna euismod nec. Vestibulum vel pretium velit. Cras mollis ligula nec odio tincidunt auctor. Cras faucibus consectetur ullamcorper.',
            'post_notify' => json_encode(['matches', 'likes']),
            'post_type' => 'poster',
            'post_salary_min' => 12500,
            'post_salary_max' => 15000,
            'post_like_count' => 4,
            'post_tags' => '["IT", "Software"]',
            'post_banner' => '/images/placeholder/banner/banner-1.jpg',
            'post_link' => 'https://used.com.ph',
            'post_expires' => date('Y-m-d', strtotime("+30 days")),
            'post_created' => date('Y-m-d h:i:s'),
            'post_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
