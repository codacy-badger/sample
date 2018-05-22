<?php //-->

return [
    'sql-build' => [
        'host' => '127.0.0.1',
        'user' => '<DATABASE USER>',
        'pass' => '<DATABASE PASS>'
    ],
    'sql-main' => [
        'host' => '127.0.0.1',
        'name' => 'jobayan_v2',
        'user' => 'root',
        'pass' => 'root',
        'charset' => 'UTF8MB4'
    ],
    'sql-crawler' => [
        'host' => '127.0.0.1',
        'name' => 'jobayan_crawler',
        'user' => 'root',
        'pass' => 'root'
    ],
    // 'elastic-main' => [
    //     'localhost:9200'
    // ],
    // 'elastic-main' => [
    //     '45.33.43.154:9200'
    // ],
    // 'redis-main' =>  [
    //     'scheme' => 'tcp',
    //     'host' => '127.0.0.1',
    //     'port' => 6379
    // ],
    'rabbitmq-main' => [
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => 'guest',
        'pass' => 'guest'
    ],
   's3-main' => [
       'region' => 'ap-southeast-1',
       'token' => 'AKIAJV4XLW5ENXC3FPCA',
       'secret' => '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',
       'bucket' => 'jobayan',
       'host' => 'https://s3-ap-southeast-1.amazonaws.com'
   ],

'mail-main' => [
        'host' => 'smtp.gmail.com',
        'port' => '587',
        'type' => 'tls',
        'name' => 'Jobayan',
        'user' => 'info@jobayan.com',
        'pass' => 'Jobayan!.com'
//'user' => 'cgalgo@openovate.com',
//'pass' => 'cgalgo201105'
    ],
    // 'mail-main' => [
    //     'host' => 'smtp.gmail.com',
    //     'port' => '587',
    //     'type' => 'tls',
    //     'name' => 'Jobayan',
    //     'user' => 'josealfonso0607@gmail.com',
    //     'pass' => 'jobayan2018'
    // ], 

    //  'mail-main' => [
    //     'host' => 'smtp.gmail.com',
    //     'port' => '587',
    //     'type' => 'tls',
    //     'name' => 'Jobayan',
    //     'user' => 'no-reply@jobayan.com',
    //     'pass' => 'Jobayan!.com'
    // ],

    // 'captcha-main' => [
    //     'token' => '<GOOGLE CAPTCHA TOKEN>',
    //     'secret' => '<GOOGLE CAPTCHA SECRET>'
    // ]
   // 'facebook-graph' => [
   //      'app_id' => '582756328779855',
   //      'app_secret' => '6999f9d504247fce49e60d2876bd46de',
   //      'default_graph_version' => 'v2.2'
   //  ]
    //live
    // 'facebook-graph' => [
    //     'app_id' => '1291626527573156',
    //     'app_secret' => '30e20e3c455c0dba81ad94c26923e2cb',
    //     'default_graph_version' => 'v2.10',
    //     'default_access_token' => 'EAASWujxbxKQBAF0srC43Pm3mzgihOrn5c1Jib2auWsZBkV25knn4Erprh4g6kgSDCZB87x89R3hagIndrGgdvPwIgqCjgZCTZBG2GxhP5VrLBh0Mp7qrZAE3YkFRhSdnGZAZA7SdhC4OUAKhYQ7r0Kd1sbEZBtoCB7lfCB4nnLEkLeEwQdg9ho5DljUyHOraO9AZD'
    // ]
    'semafore-main' => [
        'sender_name' => 'Jobayan',
        'endpoint' => 'http://api.semaphore.co/api/v4/messages',
        'token' => '0ca99eecd3db965fa02b0f03cf6645f4'
    ],

    'linkedin-api' => [
        'api_key' => '86kw2a0cfh69wa',
        'api_secret' => '9uuFvnmcs26Re4O8',
        'redirect_uri' => 'http://jobayan.dev/linkedin/information'
    ],

   'facebook-graph' => [
        'app_id' => '582756328779855',
        'admin_id' => '1411511133',
        'app_secret' => '6999f9d504247fce49e60d2876bd46de',
        'default_graph_version' => 'v2.8'
    ],
    // 'ses' => [
    //    'region' => 'us-west-2',
    //    'token' => 'AKIAJ4X5NLGZ6HUP37UA',
    //    'secret' => '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',
    //    'sender' => 'no-reply@jobayan.com'
    //  ],

     'ses' => [
       'region' => 'us-west-2',
       'token' => 'AKIAJTJU3RMC67KUSRQA',
       'secret' => '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',
       'sender' => 'no-reply@jobayan.com'
     ],

    // 'google-api' => [
    //     'geo_code' => [
    //       'api_key' => 'AIzaSyCbI16iPZV2U0Li68uCDBGen9fDIxUyI10',
    //       'endpoint' => 'https://maps.googleapis.com/maps/api/geocode/json',
    //     ]
    // ],

    'google' => [
        'endpoint' => 'https://maps.googleapis.com/maps/api/geocode',
        'secret'   => 'AIzaSyByyN9JEmw0mr3DW3_cpkipBE9vtQIb0EY'
    ]
];
