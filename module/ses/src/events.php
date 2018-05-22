<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Ses\Service as SesService;
use Cradle\Module\Ses\Validator as SesValidator;
use Aws\Ses\SesClient;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Ses\Exception\SesException;

/**
 * Ses Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = SesValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['ses_emails'])) {
        $data['ses_emails'] = json_encode($data['ses_emails']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    //save ses to database
    $results = $sesSql->create($data);

    //index ses
    // $sesElastic->create($results['ses_id']);

    //invalidate cache
    $sesRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Ses Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['ses_id'])) {
        $id = $data['ses_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $sesRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $sesElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $sesSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $sesRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Ses Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the ses detail
    $this->trigger('ses-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    //save to database
    $results = $sesSql->update([
        'ses_id' => $data['ses_id'],
        'ses_active' => 0
    ]);

    //remove from index
    // $sesElastic->remove($data['ses_id']);

    //invalidate cache
    $sesRedis->removeDetail($data['ses_id']);
    $sesRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Ses Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the ses detail
    $this->trigger('ses-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    //save to database
    $results = $sesSql->update([
        'ses_id' => $data['ses_id'],
        'ses_active' => 1
    ]);

    //create index
    // $sesElastic->create($data['ses_id']);

    //invalidate cache
    $sesRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Ses Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $sesRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $sesElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $sesSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $sesRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Ses Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('ses-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the ses detail
    $this->trigger('ses-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = SesValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['ses_emails'])) {
        $data['ses_emails'] = json_encode($data['ses_emails']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    //save ses to database
    $results = $sesSql->update($data);

    //index ses
    // $sesElastic->update($response->getResults('ses_id'));

    //invalidate cache
    $sesRedis->removeDetail($response->getResults('ses_id'));
    $sesRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Ses Sending Quota
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('get-ses-quota', function ($request, $response) {
    $config = cradle('global')->config('services', 's3-main');

    // load ses client
    $ses = SesClient::factory([
        'version' => 'latest',
        'region'  => $config['region'],
        'credentials' => array(
            'key'    => $config['token'],
            'secret' => $config['secret'],
        )
    ]);

    $quota = $ses->getSendQuota();

    $quota = [
        'limit' => $quota->get('Max24HourSend'),
        'used' => $quota->get('SentLast24Hours'),
        'rate' => $quota->get('MaxSendRate'),
        'left' => $quota->get('Max24HourSend') - $quota->get('SentLast24Hours')
    ];

    $response->setResults($quota);
});

/**
 * Ses Sending Quota
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('get-ses-statistics', function ($request, $response) {
    $config = cradle('global')->config('services', 's3-main');

    // load ses client
    $ses = SesClient::factory([
        'version' => 'latest',
        'region'  => $config['region'],
        'credentials' => array(
            'key'    => $config['token'],
            'secret' => $config['secret'],
        )
    ]);

    $statistics = $ses->getSendStatistics()->get('SendDataPoints');

    $stats = [
        'bounces' => 0,
        'complaints' => 0,
        'rejects' => 0,
        'deliveries' => 0,
        'data_points' => []
    ];

    foreach ($statistics as $stat) {
        $stats['bounces'] += $stat['Bounces'];
        $stats['rejects'] += $stat['Rejects'];
        $stats['complaints'] += $stat['Complaints'];
        $stats['deliveries'] += $stat['DeliveryAttempts'];

        $stat['Timestamp'] = date('Y-m-d H:i:s', strtotime((string)$stat['Timestamp']));

        $day = date('Y/m/d', strtotime($stat['Timestamp']));

        if(!isset($stats['data_points'][$day])) {
            $stats['data_points'][$day] = [
                'bounces' => 0,
                'complaints' => 0,
                'rejects' => 0,
                'deliveries' => 0,
                'data' => []
            ];
        }

        $stats['data_points'][$day]['bounces'] += $stat['Bounces'];
        $stats['data_points'][$day]['rejects'] += $stat['Rejects'];
        $stats['data_points'][$day]['complaints'] += $stat['Complaints'];
        $stats['data_points'][$day]['deliveries'] += $stat['DeliveryAttempts'];
        $stats['data_points'][$day]['data'][] = $stat;
    }

    ksort($stats['data_points']);

    $response->setResults($stats);
});

/**
 * Ses Sending Quota
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('get-ses-metrics', function ($request, $response) {
    $config = cradle('global')->config('services', 's3-main');

    // load ses client
    $ses = CloudWatchClient::factory([
        'version' => 'latest',
        'region'  => $config['region'],
        'credentials' => array(
            'key'    => $config['token'],
            'secret' => $config['secret'],
        )
    ]);

    $statistics = $ses->listMetrics();

    // ->get('SendDataPoints');
    //
    // $stats = [
    //     'bounces' => 0,
    //     'complaints' => 0,
    //     'rejects' => 0,
    //     'total' => 0
    // ];
    //
    // foreach ($statistics as $stat) {
    //     $stats['bounces'] += $stat['Bounces'];
    //     $stats['rejects'] += $stat['Rejects'];
    //     $stats['complaints'] += $stat['Complaints'];
    //     $stats['total'] += $stat['DeliveryAttempts'];
    // }
    //
    // $stats['data_points'] = $statistics;
    //
    // $response->setResults($stats);
});

/**
 * Ses Subscription Confirmation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('complete-ses-subscription', function ($request, $response) {
    $url = $request->getStage('subscription_url');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
    echo $data; exit;
});

/**
 * Ses Subscription Confirmation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('update-subscriber-bounce', function ($request, $response) {
    // get data
    $data = $request->getStage();

    // check which email first
    // is it a user
    $request->setStage('filter', ['profile_email' => $data['email']]);
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults('rows');

    if ($results) {
        $request->setStage('profile_id', $results[0]['profile_id']);
        cradle()->trigger('profile-update-bounce', $request, $response);
    }

    $request->setStage('filter', ['lead_email' => $data['email']]);
    cradle()->trigger('lead-search', $request, $response);
    $results = $response->getResults('rows');

    if ($results) {
        $request->setStage('lead_id', $results[0]['lead_id']);
        cradle()->trigger('lead-update-bounce', $request, $response);
    }
});

/**
 * Ses Subscription Confirmation
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('process-subscriber-complaint', function ($request, $response) {
    // get data
    $data = $request->getStage();

    // check which email first
    // is it a user
    $request->setStage('filter', ['profile_email' => $data['email']]);
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults('rows');

    if ($results) {
        $request->setStage('profile_id', $results[0]['profile_id']);
        cradle()->trigger('profile-unsubscribe', $request, $response);
    }

    $request->setStage('filter', ['lead_email' => $data['email']]);
    cradle()->trigger('lead-search', $request, $response);
    $results = $response->getResults('rows');

    if ($results) {
        $request->setStage('lead_id', $results[0]['lead_id']);
        cradle()->trigger('lead-unsubscribe', $request, $response);
    }
});
