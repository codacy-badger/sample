<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Webpage\Service as WebpageService;
use Cradle\Module\Crawler\Webpage\Validator as WebpageValidator;

/**
 * Webpage Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('webpage-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = WebpageValidator::getCreateErrors($data);

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
    //save webpage to database
    $results = WebpageService::get('sql')->create($data);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Webpage Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('webpage-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['webpage_id'])) {
        $id = $data['webpage_id'];
    } else if (isset($data['webpage_link'])) {
        $id = $data['webpage_link'];
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
    $results = WebpageService::get('sql')->get($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Webpage Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('webpage-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the webpage detail
    $this->trigger('webpage-detail', $request, $response);

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
    $results = WebpageService::get('sql')->remove($data['webpage_id']);

    $response->setError(false)->setResults($results);
});

/**
 * Webpage Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('webpage-search', function ($request, $response) {
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
    $results = WebpageService::get('sql')->search($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Webpage Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('webpage-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the webpage detail
    $this->trigger('webpage-detail', $request, $response);

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
    $errors = WebpageValidator::getUpdateErrors($data);

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
    //save webpage to database
    $results = WebpageService::get('sql')->update($data);

    //return response format
    $response->setError(false)->setResults($results);
});
