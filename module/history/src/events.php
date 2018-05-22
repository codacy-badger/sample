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

    if (isset($data['history_value'])) {
        $data['history_value'] = json_encode($data['history_value']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    //save history to database
    $results = $historySql->create($data);
    
    //link profile
    if (isset($data['profile_id'])) {
        $historySql->linkProfile($results['history_id'], $data['profile_id']);
    }

    //link blog
    if (isset($data['blog_id'])) {
        $historySql->linkBlog($results['history_id'], $data['blog_id']);
    }

    //link feature
    if (isset($data['feature_id'])) {
        $historySql->linkFeature($results['history_id'], $data['feature_id']);
    }

    //link position
    if (isset($data['position_id'])) {
        $historySql->linkPosition($results['history_id'], $data['position_id']);
    }

    //link post
    if (isset($data['post_id'])) {
        $historySql->linkPost($results['history_id'], $data['post_id']);
    }

    //link research
    if (isset($data['research_id'])) {
        $historySql->linkResearch($results['history_id'], $data['research_id']);
    }

    //link role
    if (isset($data['role_id'])) {
        $historySql->linkRole($results['history_id'], $data['role_id']);
    }

    //link service
    if (isset($data['service_id'])) {
        $historySql->linkService($results['history_id'], $data['service_id']);
    }

    //link transaction
    if (isset($data['transaction_id'])) {
        $historySql->linkTransaction($results['history_id'], $data['transaction_id']);
    }

    //link utm
    if (isset($data['utm_id'])) {
        $historySql->linkUtm($results['history_id'], $data['utm_id']);
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

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
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

    if (isset($data['history_value'])) {
        $data['history_value'] = json_encode($data['history_value']);
    }

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

/**
 * Links History to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkProfile(
        $data['history_id'],
        $data['profile_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkProfile(
        $data['history_id'],
        $data['profile_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllProfile($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to blog
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-blog', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['blog_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkBlog(
        $data['history_id'],
        $data['blog_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from blog
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-blog', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['blog_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkBlog(
        $data['history_id'],
        $data['blog_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from blog
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-blog', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllBlog($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to feature
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-feature', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['feature_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkFeature(
        $data['history_id'],
        $data['feature_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from feature
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-feature', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['feature_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkFeature(
        $data['history_id'],
        $data['feature_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from feature
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-feature', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllFeature($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to position
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-position', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['position_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkPosition(
        $data['history_id'],
        $data['position_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from position
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-position', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['position_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkPosition(
        $data['history_id'],
        $data['position_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from position
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-position', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllPosition($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to post
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-post', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['post_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkPost(
        $data['history_id'],
        $data['post_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from post
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-post', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['post_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkPost(
        $data['history_id'],
        $data['post_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from post
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-post', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllPost($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to research
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-research', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['research_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkResearch(
        $data['history_id'],
        $data['research_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from research
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-research', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['research_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkResearch(
        $data['history_id'],
        $data['research_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from research
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-research', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllResearch($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to role
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-role', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['role_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkRole(
        $data['history_id'],
        $data['role_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from role
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-role', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['role_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkRole(
        $data['history_id'],
        $data['role_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from role
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-role', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllRole($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to service
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-service', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['service_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkService(
        $data['history_id'],
        $data['service_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from service
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-service', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['service_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkService(
        $data['history_id'],
        $data['service_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from service
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-service', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllService($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to transaction
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-transaction', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['transaction_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkTransaction(
        $data['history_id'],
        $data['transaction_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from transaction
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-transaction', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['transaction_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkTransaction(
        $data['history_id'],
        $data['transaction_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from transaction
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-transaction', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllTransaction($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links History to utm
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-link-utm', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['utm_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->linkUtm(
        $data['history_id'],
        $data['utm_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks History from utm
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlink-utm', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'], $data['utm_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkUtm(
        $data['history_id'],
        $data['utm_id']
    );

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all History from utm
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('history-unlinkall-utm', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $historySql = HistoryService::get('sql');
    $historyRedis = HistoryService::get('redis');
    // $historyElastic = HistoryService::get('elastic');

    $results = $historySql->unlinkAllUtm($data['history_id']);

    //index post
    // $historyElastic->update($data['history_id']);

    //invalidate cache
    $historyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
