<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Worker\Service as WorkerService;
use Cradle\Module\Crawler\Worker\Validator as WorkerValidator;

/**
 * Worker Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('worker-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = WorkerValidator::getCreateErrors($data);

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
    //save worker to database
    $results = WorkerService::get('sql')->create($data);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Worker Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('worker-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['worker_id'])) {
        $id = $data['worker_id'];
    } else if (isset($data['worker_link'])) {
        $id = $data['worker_link'];
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
    $workerSql = WorkerService::get('sql');
    $workerRedis = WorkerService::get('redis');
    $workerElastic = WorkerService::get('elastic');

    //get it from database
    $results = WorkerService::get('sql')->get($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Worker Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('worker-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the worker detail
    $this->trigger('worker-detail', $request, $response);

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
    //remove from database
    $results = WorkerService::get('sql')->remove($data['worker_id']);

    $response->setError(false)->setResults($results);
});

/**
 * Worker Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('worker-search', function ($request, $response) {
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
    //get it from database
    $results = WorkerService::get('sql')->search($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Worker Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('worker-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the worker detail
    $this->trigger('worker-detail', $request, $response);

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
    $errors = WorkerValidator::getUpdateErrors($data);

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

    //save worker to database
    $results = WorkerService::get('sql')->update($data);

    //return response format
    $response->setError(false)->setResults($results);
});
