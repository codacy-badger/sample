<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\File\Service as FileService;
use Cradle\Module\File\Validator as FileValidator;

/**
 * File Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = FileValidator::getCreateErrors($data);

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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    //save file to database
    $results = $fileSql->create($data);
    //link comment
    if(isset($data['comment_id'])) {
        $fileSql->linkComment($results['file_id'], $data['comment_id']);
    }

    //index file
    // $fileElastic->create($results['file_id']);

    //invalidate cache
    $fileRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * File Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['file_id'])) {
        $id = $data['file_id'];
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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $fileRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $fileElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $fileSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $fileRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * File Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the file detail
    $this->trigger('file-detail', $request, $response);

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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    //save to database
    $results = $fileSql->update([
        'file_id' => $data['file_id'],
        'file_active' => 0
    ]);

    //remove from index
    // $fileElastic->remove($data['file_id']);

    //invalidate cache
    $fileRedis->removeDetail($data['file_id']);
    $fileRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * File Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the file detail
    $this->trigger('file-detail', $request, $response);

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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    //save to database
    $results = $fileSql->update([
        'file_id' => $data['file_id'],
        'file_active' => 1
    ]);

    //create index
    // $fileElastic->create($data['file_id']);

    //invalidate cache
    $fileRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * File Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-search', function ($request, $response) {
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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $fileRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $fileElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $fileSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $fileRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * File Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('file-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the file detail
    $this->trigger('file-detail', $request, $response);

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
    $errors = FileValidator::getUpdateErrors($data);

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
    $fileSql = FileService::get('sql');
    $fileRedis = FileService::get('redis');
    // $fileElastic = FileService::get('elastic');

    //save file to database
    $results = $fileSql->update($data);

    //index file
    // $fileElastic->update($response->getResults('file_id'));

    //invalidate cache
    $fileRedis->removeDetail($response->getResults('file_id'));
    $fileRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
