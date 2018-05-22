<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\School\Service as SchoolService;
use Cradle\Module\School\Validator as SchoolValidator;

/**
 * School Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = SchoolValidator::getCreateErrors($data);

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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    //save school to database
    $results = $schoolSql->create($data);

    //index school
    // $schoolElastic->create($results['school_id']);

    //invalidate cache
    $schoolRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * School Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['school_id'])) {
        $id = $data['school_id'];
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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $schoolRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $schoolElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $schoolSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $schoolRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * School Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the school detail
    $this->trigger('school-detail', $request, $response);

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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    //save to database
    $results = $schoolSql->update([
        'school_id' => $data['school_id'],
        'school_active' => 0
    ]);

    //remove from index
    // $schoolElastic->remove($data['school_id']);

    //invalidate cache
    $schoolRedis->removeDetail($data['school_id']);
    $schoolRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * School Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the school detail
    $this->trigger('school-detail', $request, $response);

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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    //save to database
    $results = $schoolSql->update([
        'school_id' => $data['school_id'],
        'school_active' => 1
    ]);

    //create index
    // $schoolElastic->create($data['school_id']);

    //invalidate cache
    $schoolRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * School Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-search', function ($request, $response) {
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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $schoolRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $schoolElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $schoolSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $schoolRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * School Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('school-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the school detail
    $this->trigger('school-detail', $request, $response);

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
    $errors = SchoolValidator::getUpdateErrors($data);

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
    $schoolSql = SchoolService::get('sql');
    $schoolRedis = SchoolService::get('redis');
    // $schoolElastic = SchoolService::get('elastic');

    //save school to database
    $results = $schoolSql->update($data);

    //index school
    // $schoolElastic->update($response->getResults('school_id'));

    //invalidate cache
    $schoolRedis->removeDetail($response->getResults('school_id'));
    $schoolRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
