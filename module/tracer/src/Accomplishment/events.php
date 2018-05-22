<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Accomplishment\Service as AccomplishmentService;
use Cradle\Module\Tracer\Accomplishment\Validator as AccomplishmentValidator;

/**
 * Accomplishment Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AccomplishmentValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['accomplishment_from'])) {
        $data['accomplishment_from'] = date('Y-m-d', strtotime($data['accomplishment_from']));
    }

    if(isset($data['accomplishment_to'])) {
        $data['accomplishment_to'] = date('Y-m-d', strtotime($data['accomplishment_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    //save accomplishment to database
    $results = $accomplishmentSql->create($data);
    //link information
    if(isset($data['information_id'])) {
        $accomplishmentSql->linkInformation($results['accomplishment_id'], $data['information_id']);
    }

    //index accomplishment
    // $accomplishmentElastic->create($results['accomplishment_id']);

    //invalidate cache
    $accomplishmentRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Accomplishment Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['accomplishment_id'])) {
        $id = $data['accomplishment_id'];
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
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $accomplishmentRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $accomplishmentElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $accomplishmentSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $accomplishmentRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Accomplishment Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the accomplishment detail
    $this->trigger('accomplishment-detail', $request, $response);

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
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    //save to database
    $results = $accomplishmentSql->update([
        'accomplishment_id' => $data['accomplishment_id'],
        'accomplishment_active' => 0
    ]);

    //remove from index
    // $accomplishmentElastic->remove($data['accomplishment_id']);

    //invalidate cache
    $accomplishmentRedis->removeDetail($data['accomplishment_id']);
    $accomplishmentRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Accomplishment Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the accomplishment detail
    $this->trigger('accomplishment-detail', $request, $response);

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
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    //save to database
    $results = $accomplishmentSql->update([
        'accomplishment_id' => $data['accomplishment_id'],
        'accomplishment_active' => 1
    ]);

    //create index
    // $accomplishmentElastic->create($data['accomplishment_id']);

    //invalidate cache
    $accomplishmentRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Accomplishment Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-search', function ($request, $response) {
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
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $accomplishmentRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $accomplishmentElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $accomplishmentSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $accomplishmentRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Accomplishment Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('accomplishment-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the accomplishment detail
    $this->trigger('accomplishment-detail', $request, $response);

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
    $errors = AccomplishmentValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['accomplishment_from'])) {
        $data['accomplishment_from'] = date('Y-m-d', strtotime($data['accomplishment_from']));
    }

    if(isset($data['accomplishment_to'])) {
        $data['accomplishment_to'] = date('Y-m-d', strtotime($data['accomplishment_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $accomplishmentSql = AccomplishmentService::get('sql');
    $accomplishmentRedis = AccomplishmentService::get('redis');
    // $accomplishmentElastic = AccomplishmentService::get('elastic');

    //save accomplishment to database
    $results = $accomplishmentSql->update($data);

    //index accomplishment
    // $accomplishmentElastic->update($response->getResults('accomplishment_id'));

    //invalidate cache
    $accomplishmentRedis->removeDetail($response->getResults('accomplishment_id'));
    $accomplishmentRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
