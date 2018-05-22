<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utm\Service as UtmService;
use Cradle\Module\Utm\Validator as UtmValidator;

use Cradle\Module\Utility\File;

/**
 * Utm Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = UtmValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['utm_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['utm_image'] = File::base64ToS3($data['utm_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['utm_image'] = File::base64ToUpload($data['utm_image'], $upload);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    //save utm to database
    $results = $utmSql->create($data);

    //index utm
    // $utmElastic->create($results['utm_id']);

    //invalidate cache
    $utmRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Utm Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    $index = null;
    if (isset($data['utm_id'])) {
        $id = $data['utm_id'];
        $index = $id;
    }

    if (isset($data['utm_source']) &&
        isset($data['utm_medium']) &&
        isset($data['utm_campaign'])) {
        $id = $data;
        $index = $data['utm_source'].'-'.$data['utm_medium']
            .'-'.$data['utm_campaign'];
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
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $utmRedis->getDetail($index);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $utmElastic->get($index);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $utmSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $utmRedis->createDetail($index, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Utm Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the utm detail
    $this->trigger('utm-detail', $request, $response);

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
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    //save to database
    $results = $utmSql->update([
        'utm_id' => $data['utm_id'],
        'utm_active' => 0
    ]);

    // $utmElastic->update($response->getResults('utm_id'));

    //invalidate cache
    $utmRedis->removeDetail($data['utm_id']);
    $utmRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Utm Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the utm detail
    $this->trigger('utm-detail', $request, $response);

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
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    //save to database
    $results = $utmSql->update([
        'utm_id' => $data['utm_id'],
        'utm_active' => 1
    ]);

    //create index
    // $utmElastic->create($data['utm_id']);

    //invalidate cache
    $utmRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Utm Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-search', function ($request, $response) {
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
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $utmRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $utmElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $utmSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $utmRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Utm Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the utm detail
    $this->trigger('utm-detail', $request, $response);

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
    $errors = UtmValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['utm_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['utm_image'] = File::base64ToS3($data['utm_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['utm_image'] = File::base64ToUpload($data['utm_image'], $upload);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    //save utm to database
    $results = $utmSql->update($data);

    //index utm
    // $utmElastic->update($response->getResults('utm_id'));

    //invalidate cache
    $utmRedis->removeDetail($response->getResults('utm_id'));
    $utmRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * UTM Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('utm-bulk-action', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();
    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $utmSql = UtmService::get('sql');
    $utmRedis = UtmService::get('redis');
    // $utmElastic = UtmService::get('elastic');

    //save to database
    $results = $utmSql->bulkAction(
        $data['bulk_ids'],
        $data['bulk_value'],
        $data['bulk_field']
    );

    foreach ($data['bulk_ids'] as $id) {
        //remove from index
        // $UtmElastic->remove($id);

        //invalidate cache
        $UtmRedis->removeDetail($id);
        $UtmRedis->removeSearch();
    }

    $response->setError(false)->setResults($results);
});