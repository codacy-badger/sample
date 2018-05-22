<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Thread\Service as ThreadService;
use Cradle\Module\Thread\Validator as ThreadValidator;

/**
 * Thread Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ThreadValidator::getCreateErrors($data);

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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    //save thread to database
    $results = $threadSql->create($data);

    if(isset($data['deal_id'])) {
        $threadSql->linkDeal($results['thread_id'], $data['deal_id']);
    }

    //invalidate cache
    $threadRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Thread Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['thread_id'])) {
        $id = $data['thread_id'];
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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $threadRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $threadElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $threadSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $threadRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Thread Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the thread detail
    $this->trigger('thread-detail', $request, $response);

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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    //save to database
    $results = $threadSql->update([
        'thread_id' => $data['thread_id'],
        'thread_active' => 0
    ]);

    //remove from index
    // $threadElastic->remove($data['thread_id']);

    //invalidate cache
    $threadRedis->removeDetail($data['thread_id']);
    $threadRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Thread Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the thread detail
    $this->trigger('thread-detail', $request, $response);

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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    //save to database
    $results = $threadSql->update([
        'thread_id' => $data['thread_id'],
        'thread_active' => 1
    ]);

    //create index
    // $threadElastic->create($data['thread_id']);

    //invalidate cache
    $threadRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Thread Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-search', function ($request, $response) {
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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $threadRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $threadElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $threadSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $threadRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Thread Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the thread detail
    $this->trigger('thread-detail', $request, $response);

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
    $errors = ThreadValidator::getUpdateErrors($data);

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
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    //save thread to database
    $results = $threadSql->update($data);

    //index thread
    // $threadElastic->update($response->getResults('thread_id'));

    //invalidate cache
    $threadRedis->removeDetail($response->getResults('thread_id'));
    $threadRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links Thread to history
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-link-history', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['thread_id'], $data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    $results = $threadSql->linkHistory(
        $data['thread_id'],
        $data['history_id']
    );

    //index post
    // $threadElastic->update($data['thread_id']);

    //invalidate cache
    $threadRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks Thread from history
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('thread-unlink-history', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['thread_id'], $data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $threadSql = ThreadService::get('sql');
    $threadRedis = ThreadService::get('redis');
    // $threadElastic = ThreadService::get('elastic');

    $results = $threadSql->unlinkHistory(
        $data['thread_id'],
        $data['history_id']
    );

    //index post
    // $threadElastic->update($data['thread_id']);

    //invalidate cache
    $threadRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
