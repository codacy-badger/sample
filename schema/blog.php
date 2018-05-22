<?php //-->
return [
    'singular' => 'Blog',
    'plural' => 'Blogs',
    'primary' => 'blog_id',
    'active' => 'blog_active',
    'created' => 'blog_created',
    'updated' => 'blog_updated',
    'relations' => [
        'profile' => [
            'primary' => 'profile_id',
            'many' => false
        ]
    ],
    'fields' => [
        'blog_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Title',
                ]
            ],
            'list' => [
                'label' => 'Title'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ],
            'test' => [
                'pass' => 'A Title',
                'fail' => 'validated'
            ]
        ],
        'blog_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Blog Image',
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
        'blog_slug' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'slug',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Slug',
                ]
            ],
            'list' => [
                'label' => 'Slug'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Slug is required'
                ]
            ],
            'test' => [
                'pass' => 'A Slug',
                'fail' => 'validated'
            ]
        ],
        'blog_article' => [
            'sql' => [
                'type' => 'text',
                'length' => 1000,
                'filterable' => true,
            ],
            'form' => [
                'label' => 'Article',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Article',
                ]
            ],
            'list' => [
                'label' => 'Article'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Article is required'
                ]
            ],
            'test' => [
                'pass' => 'A Article',
                'fail' => 'validated'
            ]
        ],
        'blog_description' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Description',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Description',
                ]
            ],
            'list' => [
                'label' => 'Description'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Description is required'
                ]
            ],
            'test' => [
                'pass' => 'A Description',
                'fail' => 'validated'
            ]
        ],
        'blog_keywords' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Keywords',
                'type' => 'keyword-field'
            ],
            'detail' => [
                'label' => 'Keywords'
            ]
        ],
        'blog_view_count' => [
            'sql' => [
                'type' => 'int',
                'filterable' => true,
            ]
        ],
        'blog_facebook_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Facebook Title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Facebook Title',
                ]
            ],
            'list' => [
                'label' => 'Facebook Title'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Facebook Title is required'
                ]
            ],
            'test' => [
                'pass' => 'A Facebook Title',
                'fail' => 'validated'
            ]
        ],
        'blog_facebook_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Facebook Image',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Facebook Image',
                ]
            ],
            'list' => [
                'label' => 'Facebook Image'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Facebook Image is required'
                ]
            ],
            'test' => [
                'pass' => 'A Facebook Image',
                'fail' => 'validated'
            ]
        ],
        'blog_facebook_description' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Facebook Description',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Facebook Description',
                ]
            ],
            'list' => [
                'label' => 'Facebook Description'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Facebook Description is required'
                ]
            ],
            'test' => [
                'pass' => 'A Facebook Description',
                'fail' => 'validated'
            ]
        ],
        'blog_twitter_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Twitter Title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Twitter Title',
                ]
            ],
            'list' => [
                'label' => 'Twitter Title'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Twitter Title is required'
                ]
            ],
            'test' => [
                'pass' => 'A Twitter Title',
                'fail' => 'validated'
            ]
        ],
        'blog_twitter_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Twitter Image',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Twitter Image',
                ]
            ],
            'list' => [
                'label' => 'Twitter Image'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Twitter Image is required'
                ]
            ],
            'test' => [
                'pass' => 'A Twitter Image',
                'fail' => 'validated'
            ]
        ],
        'blog_twitter_description' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Twitter Description',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog Twitter Description',
                ]
            ],
            'list' => [
                'label' => 'Twitter Description'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Twitter Description is required'
                ]
            ],
            'test' => [
                'pass' => 'A Twitter Description',
                'fail' => 'validated'
            ]
        ],
        'blog_author' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Author',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog author',
                ]
            ],
            'list' => [
                'label' => 'Author'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Author is required'
                ]
            ],
            'test' => [
                'pass' => 'An author',
                'fail' => 'validated'
            ]
        ],
        'blog_author_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Blog Author Image',
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
        'blog_author_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true,
                'index' => true,
            ],
            'form' => [
                'label' => 'Blog author title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Blog author title',
                ]
            ],
            'list' => [
                'label' => 'Blog Author Title'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ],
            'test' => [
                'pass' => 'A Title',
                'fail' => 'validated'
            ]
        ],
        'blog_published' => [
            'sql' => [
                'type' => 'datetime'
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ],
            'form' => [
                'label' => 'Published Date',
                'type' => 'date',
                'default' => 'NOW()'
            ],
            'list' => [
                'label' => 'Published',
                'searchable' => true,
                'sortable' => true,
                'format' => 'date',
                'parameters' => 'M d'
            ],
            'detail' => [
                'label' => 'Published On',
                'format' => 'date',
                'parameters' => 'F d, y g:iA'
            ]
        ],
    ],
    'fixtures' => [
        [
            'blog_id' => 1,
            'profile_id' => 1,
            'blog_active' => 1,
            'blog_title' => 'A Blog',
            'blog_image' => '/images/1.png',
            'blog_slug' => 'https://jobayan.com/blog/1',
            'blog_description' => 'This is a Description',
            'blog_article' => 'This is an article',
            'blog_keywords' => '["article", "blog"]',
            'blog_facebook_title' => 'A Facebook Blog',
            'blog_facebook_image' => '/images/1.png',
            'blog_facebook_slug' => 'https://jobayan.com/blog/1',
            'blog_facebook_description' => 'This is a Facebook Description',
            'blog_twitter_title' => 'A Twitter Blog',
            'blog_twitter_image' => '/images/1.png',
            'blog_twitter_slug' => 'https://jobayan.com/blog/1',
            'blog_twitter_description' => 'This is a Twitter Description',
            'blog_published' => date('Y-m-d H:i:s'),
            'blog_created' => date('Y-m-d H:i:s'),
            'blog_updated' => date('Y-m-d H:i:s')
        ],
        [
            'blog_id' => 2,
            'profile_id' => 2,
            'blog_active' => 2,
            'blog_title' => 'A 2nd Blog',
            'blog_image' => '/images/2.png',
            'blog_slug' => 'https://jobayan.com/blog/2',
            'blog_description' => 'This is a 2nd Description',
            'blog_article' => 'This is a 2nd article',
            'blog_keywords' => '["article", "blog", "2nd"]',
            'blog_facebook_title' => 'A 2nd Facebook Blog',
            'blog_facebook_image' => '/images/2.png',
            'blog_facebook_slug' => 'https://jobayan.com/blog/2',
            'blog_facebook_description' => 'This is a 2nd Facebook Description',
            'blog_twitter_title' => 'A 2nd Twitter Blog',
            'blog_twitter_image' => '/images/2.png',
            'blog_twitter_slug' => 'https://jobayan.com/blog/2',
            'blog_twitter_description' => 'This is a 2nd Twitter Description',
            'blog_published' => date('Y-m-d H:i:s'),
            'blog_created' => date('Y-m-d H:i:s'),
            'blog_updated' => date('Y-m-d H:i:s')
        ],
    ]
];
