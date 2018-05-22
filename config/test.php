<?php //-->

return [
    'sql-build' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => ''
    ],
    'sql-main' => [
        'host' => '127.0.0.1',
        'name' => 'testing_db',
        'user' => 'root',
        'pass' => 'root'
    ],
    'elastic-main' => [
        '127.0.0.1:9200'
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
    'captcha-main' => [
        'token' => '<GOOGLE CAPTCHA TOKEN>',
        'secret' => '<GOOGLE CAPTCHA SECRET>'
    ],
    'facebook-graph' => [
        'app_id' => '1291626527573156',
        'admin_id' => '1411511133',
        'app_secret' => '30e20e3c455c0dba81ad94c26923e2cb',
        'default_graph_version' => 'v2.8'
    ],
];
