<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Log\Service as LogService;
use Cradle\Module\Crawler\Log\Validator as LogValidator;

/**
 * Log Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('log-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = LogValidator::getCreateErrors($data);

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

    //save log to database
    $results = LogService::get('sql')->create($data);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Log Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('log-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['log_id'])) {
        $id = $data['log_id'];
    } else if (isset($data['log_link'])) {
        $id = $data['log_link'];
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
    //get it from database
    $results = LogService::get('sql')->get($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Log Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('log-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the log detail
    $this->trigger('log-detail', $request, $response);

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
    $results = LogService::get('sql')->remove($data['log_id']);
    $response->setError(false)->setResults($results);
});

/**
 * Log Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('log-search', function ($request, $response) {
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
    $results = LogService::get('sql')->search($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Log Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('log-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the log detail
    $this->trigger('log-detail', $request, $response);

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
    $errors = LogValidator::getUpdateErrors($data);

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
    //save log to database
    $results = LogService::get('sql')->update($data);

    //return response format
    $response->setError(false)->setResults($results);
});
