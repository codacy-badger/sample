<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Degree\Service as DegreeService;
use Cradle\Module\Degree\Validator as DegreeValidator;

/**
 * Degree Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = DegreeValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    //save degree to database
    $results = $degreeSql->create($data);

    //index degree
    // $degreeElastic->create($results['degree_id']);

    //invalidate cache
    $degreeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Degree Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['degree_id'])) {
        $id = $data['degree_id'];
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
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $degreeRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $degreeElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $degreeSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $degreeRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Degree Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the degree detail
    $this->trigger('degree-detail', $request, $response);

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
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    //save to database
    $results = $degreeSql->update([
        'degree_id' => $data['degree_id'],
        'degree_active' => 0
    ]);

    //remove from index
    // $degreeElastic->remove($data['degree_id']);

    //invalidate cache
    $degreeRedis->removeDetail($data['degree_id']);
    $degreeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Degree Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the degree detail
    $this->trigger('degree-detail', $request, $response);

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
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    //save to database
    $results = $degreeSql->update([
        'degree_id' => $data['degree_id'],
        'degree_active' => 1
    ]);

    //create index
    // $degreeElastic->create($data['degree_id']);

    //invalidate cache
    $degreeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Degree Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-search', function ($request, $response) {
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
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $degreeRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $degreeElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $degreeSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $degreeRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Degree Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('degree-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the degree detail
    $this->trigger('degree-detail', $request, $response);

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
    $errors = DegreeValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $degreeSql = DegreeService::get('sql');
    $degreeRedis = DegreeService::get('redis');
    // $degreeElastic = DegreeService::get('elastic');

    //save degree to database
    $results = $degreeSql->update($data);

    //index degree
    // $degreeElastic->update($response->getResults('degree_id'));

    //invalidate cache
    $degreeRedis->removeDetail($response->getResults('degree_id'));
    $degreeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
