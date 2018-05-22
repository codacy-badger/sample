<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\History\Service as HistoryService;
use Cradle\Module\History\Validator as HistoryValidator;

/**
 * History Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = HistoryValidator::getCreateErrors($data);

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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    //save history to database
    $results = $historySql->create($data);
    //link deal
    if(isset($data['deal_id'])) {
        $historySql->linkDeal($results['history_id'], $data['deal_id']);
    }
    //link comment
    if(isset($data['comment_id'])) {
        $historySql->linkComment($results['history_id'], $data['comment_id']);
    }
    if(isset($data['thread_id'])) {
        $historySql->linkThread($results['history_id'], $data['thread_id']);
    }
    if(isset($data['user_history'])) {
        $historySql->linkProfile($results['history_id'], $data['user_history']);
    }

    //index history
    // $historyElastic->create($results['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * History Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['history_id'])) {
        $id = $data['history_id'];
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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $historyRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $historyElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $historySql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $historyRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * History Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the history detail
    $this->trigger('history-detail', $request, $response);

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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    //save to database
    $results = $historySql->update([
        'history_id' => $data['history_id'],
        'history_active' => 0
    ]);

    //remove from index
    // $historyElastic->remove($data['history_id']);

    //invalidate cache
    $historyRedis->removeDetail($data['history_id']);
    $historyRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * History Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the history detail
    $this->trigger('history-detail', $request, $response);

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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    //save to database
    $results = $historySql->update([
        'history_id' => $data['history_id'],
        'history_active' => 1
    ]);

    //create index
    // $historyElastic->create($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * History Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-search', function ($request, $response) {
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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $historyRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $historyElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $historySql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $historyRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * History Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the history detail
    $this->trigger('history-detail', $request, $response);

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
    $errors = HistoryValidator::getUpdateErrors($data);

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
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    //save history to database
    $results = $historySql->update($data);

    //index history
    // $historyElastic->update($response->getResults('history_id'));

    //invalidate cache
    $historyRedis->removeDetail($response->getResults('history_id'));
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
