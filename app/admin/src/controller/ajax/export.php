<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\Queue;

/**
 * Control AJAX export stream
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/ajax/export/stream', function ($request, $response) {
    //----------------------------//
    // 1. Override headers
    header("Content-Type: text/event-stream");
    header('Cache-Control: no-cache');
    header("Connection: keep-alive");
    header("Access-Control-Allow-Origin: *");

    // will not work unless session is closed
    session_write_close();

    //----------------------------//
    // 2. Start stream
    try {
        $queue = (new Queue)->setExchange('jobayan-admin-export')
            ->subscribe(function ($msg) {
                if (!$msg) {
                    die();
                }

                $raw   = json_decode($msg->body, true);
                $event = $raw['event'];
                $data  = $raw['data'];

                // check if event belongs to this session
                if (!isset($data['session_id'])
                    || $data['session_id'] != session_id()
                    || $data['type'] != $_GET['type']) {
                    die();
                }

                // unset session id
                unset($data['session_id']);

                echo "id: " . time() . PHP_EOL;
                echo "retry: " . 2000 . PHP_EOL;
                echo "event: " . $event . PHP_EOL;
                echo "data: " . json_encode($data) . PHP_EOL;
                echo PHP_EOL;

                if ($event == 'export-complete' || $event == 'export-error') {
                    exit;
                }

                ob_flush();
                flush();
                sleep(1);
            });
    } catch (\PhpAmqpLib\Exception\AMQPProtocolChannelException $exception) {
    }

    return;
});

/**
 * Control AJAX profile export
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/ajax/profile/export', function ($request, $response) {
    //----------------------------//
    // 1. Check permissions, configs and date filter
    if (!cradle('global')->role('admin:profile:export', 'admin', $request)) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => cradle('global')->translate('Request not Permitted')
        ]));
    }

    // get rabbitmq and s3 config
    $s3Config       = $this->package('global')->service('s3-main');
    $rabbitmqConfig = $this->package('global')->service('rabbitmq-main');
    $errorMsg       = cradle('global')->translate('Export is not yet properly configured please contact admin');

    // check s3 configuration
    if (!$s3Config
        || $s3Config['token'] === '<AWS TOKEN>'
        || $s3Config['secret'] === '<AWS SECRET>'
        || $s3Config['bucket'] === '<S3 BUCKET>'
        || $s3Config['region'] === '<AWS REGION>'
    ) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $errorMsg
        ]));
    }

    // check rabbitmq config
    if (!$rabbitmqConfig) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $errorMsg
        ]));
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'profile_active', '1');
    }

    //export inactive posts
    if ($request->hasStage('filter', 'profile_active')
        && $request->getStage('filter', 'profile_active') == '0') {
        $request->setStage('filter', 'profile_active', '0');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_id',
            'profile_name',
            'profile_slug',
            'profile_company',
            'profile_email',
            'profile_phone',
            'profile_credits',
            'profile_phone',
            'profile_type',
            'profile_created'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'type',
            'profile_active',
            'profile_company,'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            // Checks if the filter is not allowed
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if ($request->hasStage('select_filter') && $request->hasStage('q')) {
        $keyword = $originalKeyword = $request->getStage('q');

        if (is_array($keyword) && isset($keyword[0])) {
            $keyword = $keyword[0];
        }

        if (!empty($keyword)) {
            $setFilter = 'like_filter';
            if ($request->getStage('select_filter') == 'profile_id') {
                $setFilter = 'filter';
            }

            $request->setStage(
                $setFilter,
                $request->getStage('select_filter'),
                $keyword
            );

            $request->removeStage('q');
        }
    }

    //sort desc
    if (!$request->getStage('order', 'profile_id')) {
        $request->setStage('order', 'profile_id', 'DESC');
    }

    $request->setStage('auth_profile', true);

    // get session
    $me = [
        'auth_id'    => $_SESSION['me']['auth_id'],
        'session_id' => session_id()
    ];

    // set session
    $request->setStage('session', $me);
    $request->setStage('export', true);

    //----------------------------//
    // 3. Process Export
    $data = $request->getStage();
    // process import
    $this->package('global')->queue('profile-export-csv', $data);

    //----------------------------//
    // 4. Add to History
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];

    // set history data
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a profile csv');
    $request->setStage('history_attribute', 'profile-export');
    $request->setStage('history_value', $value);

    // create history
    cradle()->trigger('history-create', $request, $response);

    return $response->setContent(json_encode([
        'error'   => false,
        'message' => cradle('global')->translate('Processing export...')
    ]));
});

/**
 * Control AJAX post export
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/ajax/post/export', function ($request, $response) {
    //----------------------------//
    // 1. Check Permissions, configs and date filter
    if (!cradle('global')->role('admin:post:export', 'admin', $request)) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => cradle('global')->translate('Request not Permitted')
        ]));
    }

    // get rabbitmq and s3 config
    $s3Config       = $this->package('global')->service('s3-main');
    $rabbitmqConfig = $this->package('global')->service('rabbitmq-main');
    $errorMsg       = cradle('global')->translate('Export is not yet properly configured please contact admin');

    // check s3 configuration
    if (!$s3Config
        || $s3Config['token'] === '<AWS TOKEN>'
        || $s3Config['secret'] === '<AWS SECRET>'
        || $s3Config['bucket'] === '<S3 BUCKET>'
        || $s3Config['region'] === '<AWS REGION>'
    ) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $errorMsg
        ]));
    }

    // check rabbitmq config
    if (!$rabbitmqConfig) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $errorMsg
        ]));
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'post_active', '1');
    }

    //export inactive posts
    if ($request->hasStage('filter', 'post_active')
        && $request->getStage('filter', 'post_active') == '0') {
        $request->setStage('filter', 'post_active', '0');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_created',
            'post_download_count',
            'post_email',
            'post_experience',
            'post_expires',
            'post_id',
            'post_like_count',
            'post_location',
            'post_name',
            'post_phone',
            'post_position',
            'post_type',
        ];

        // Loops through the orders
        foreach ($request->getStage('order') as $key => $direction) {
            // Checks if the sorting value is not in the allowed sorting
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                // Checks if the sorting
                $request->removeStage('order', $key);
            }
        }
    }
    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_active',
            'post_location',
            'post_experience',
            'post_type',
            'post_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) || $value == '') {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    if ($request->getStage('profile')) {
        $request->setStage(
            'filter',
            'profile_id',
            $request->getStage('profile')
        );
    }

    if (!$request->hasStage('order')) {
        //sort desc
        $request->setStage('order', 'post_id', 'DESC');
    }

    if (isset($_GET['filter']['post_active']) &&
        empty($_GET['filter']['post_active'])) {
        $request->setStage('filter', 'post_active', '0');
    }

    $data = $request->getStage();

    if (isset($data['date']['start']) && $data['date']['end']) {
        $date = [
            'start_date' => $data['date']['start'],
            'end_date'   => $data['date']['end']
        ];
    }
    if (isset($data['date'])) {
        $date = $data['date'];
    }

    if (isset($date)) {
        $request->setStage('groupDate', ['post_created' => $date]);
    }

    // get session
    $me = [
        'auth_id'    => $_SESSION['me']['auth_id'],
        'session_id' => session_id()
    ];

    // set session
    $request->setStage('session', $me);
    $request->setStage('export', true);

    //----------------------------//
    // 3. Process Export
    $data = $request->getStage();
    // process import
    $this->package('global')->queue('post-export-csv', $data);

    //----------------------------//
    // 4. Add to History
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];

    // set history data
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a post file');
    $request->setStage('history_attribute', 'post-export');
    $request->setStage('history_value', $value);

    // create history
    cradle()->trigger('history-create', $request, $response);

    return $response->setContent(json_encode([
        'error'   => false,
        'message' => cradle('global')->translate('Processing export...')
    ]));
});
