<?php //-->
return [
    'key' => '/tmp/travis_rsa',
    //see: https://github.com/visionmedia/deploy for config
    'servers' => [
        'app-1' => [
            'user' => 'root',
            'host' =>  '45.79.77.15',
            'repo' => 'git@github.com:Openovate/Jobayan.git',
            'path' => '/server/public/Jobayan',
            'ref' => 'origin/master',
            'post-deploy' => 'composer update'
        ],
        'worker-main' => [
            'user' => 'root',
            'host' =>  '23.92.26.102',
            'repo' => 'git@github.com:Openovate/Jobayan.git',
            'path' => '/server/public/Jobayan/repo',
            'ref' => 'origin/master',
            'post-deploy' => 'composer update'
        ],
        'mysql-1' => [
           'deploy' => false,
           'user' => 'root',
           'host' =>  '45.33.56.180'
       ],
       'rabbitmq-main' => [
           'deploy' => false,
           'user' => 'admin',
           'host' => '173.255.215.94'
       ]
    ]
];
