<?php //-->

return [
    'sql-build' => [
        'host' => '127.0.0.1',
        'user' => '<DATABASE USER>',
        'pass' => '<DATABASE PASS>'
    ],
    'sql-main' => [
        'host' => '127.0.0.1',
        'name' => '<DATABASE NAME>',
        'user' => '<DATABASE USER>',
        'pass' => '<DATABASE PASS>',
        'charset' => 'UTF8MB4'
    ],
    'sql-crawler' => [
        'host' => '127.0.0.1',
        'name' => 'jobayan_crawler',
        'user' => 'root',
        'pass' => ''
    ],
    'elastic-main' => [
        '<ELASTIC HOST:PORT>'
    ],
    'elastic-populate' => [
        '<ELASTIC HOST:PORT>'
    ],
    'redis-main' => [
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379
    ],
    'rabbitmq-main' => [
        'host' => '127.0.0.1',
        'port' => 5672,
        'user' => '<RABBIT USER>',
        'pass' => '<RABBIT PASS>'
    ],
    's3-main' => [
        'region' => '<AWS REGION>',
        'token' => '<AWS TOKEN>',
        'secret' => '<AWS SECRET>',
        'bucket' => '<S3 BUCKET>',
        'host' => 'https://<AWS REGION>.amazonaws.com'
    ],
    'mail-main' => [
        'host' => 'smtp.gmail.com',
        'port' => '587',
        'type' => 'tls',
        'name' => 'Project Name',
        'user' => '<EMAIL ADDRESS>',
        'pass' => '<EMAIL PASSWORD>'
    ],
    'semafore-main' => [
        'sender_name' => 'Jobayan',
        'endpoint' => 'http://api.semaphore.co/api/v4/messages',
        'token' => '0ca99eecd3db965fa02b0f03cf6645f4'
    ],
    'magpie' => [
        'token_endpoint' => 'https://api.magpie.im/v1/tokens',
        'charge_endpoint' => 'https://api.magpie.im/v1/charges',
        'statement' => 'Jobayan Credits',
        'key' => 'pk_test_n6AhdwtjyXu9r6ZYmSjBzg',
        'secret' => 'sk_test_Pk4TL2CZulGNJHgoAnRLVQ',
    ],
    'facebook-graph' => [
        'app_id' => '<APP ID>',
        'admin_id' => '<ADMIN ID>',
        'app_secret' => '<APP SECRET>',
        'default_graph_version' => '<VERSION>'
    ],
    'ses' => [
      'region' => '<AWS REGION>',
      'token' => '<AWS TOKEN>',
      'secret' => '<AWS SECRET>',
      'sender' => '<sample@example.com>'
    ],
    'google' => [
        'endpoint' => 'https://maps.googleapis.com/maps/api/geocode',
        'secret'   => '<GOOGLE SECRET>'
    ]
];
