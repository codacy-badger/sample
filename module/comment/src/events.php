<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Comment\Service as CommentService;
use Cradle\Module\Comment\Validator as CommentValidator;

/**
 * Comment Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = CommentValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['comment_published'])) {
        $data['comment_published'] = date('Y-m-d H:i:s', strtotime($data['comment_published']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    //save comment to database
    $results = $commentSql->create($data);
    //link profile
    if(isset($data['profile_id'])) {
        $commentSql->linkProfile($results['comment_id'], $data['profile_id']);
    }
    //link deal
    if(isset($data['deal_id'])) {
        $commentSql->linkDeal($results['comment_id'], $data['deal_id']);
    }

    //index comment
    // $commentElastic->create($results['comment_id']);

    //invalidate cache
    $commentRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Comment Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['comment_id'])) {
        $id = $data['comment_id'];
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
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $commentRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $commentElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $commentSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $commentRedis->createDetail($id, $results);
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
 * Comment Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the comment detail
    $this->trigger('comment-detail', $request, $response);

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
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    //save to database
    $results = $commentSql->update([
        'comment_id' => $data['comment_id'],
        'comment_active' => 0
    ]);

    //remove from index
    // $commentElastic->remove($data['comment_id']);

    //invalidate cache
    $commentRedis->removeDetail($data['comment_id']);
    $commentRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Comment Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the comment detail
    $this->trigger('comment-detail', $request, $response);

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
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    //save to database
    $results = $commentSql->update([
        'comment_id' => $data['comment_id'],
        'comment_active' => 1
    ]);

    //create index
    // $commentElastic->create($data['comment_id']);

    //invalidate cache
    $commentRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Comment Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-search', function ($request, $response) {
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
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $commentRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $commentElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $commentSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $commentRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Comment Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('comment-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the comment detail
    $this->trigger('comment-detail', $request, $response);

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
    $errors = CommentValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['comment_published'])) {
        $data['comment_published'] = date('Y-m-d H:i:s', strtotime($data['comment_published']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $commentSql = CommentService::get('sql');
    $commentRedis = CommentService::get('redis');
    // $commentElastic = CommentService::get('elastic');

    //save comment to database
    $results = $commentSql->update($data);

    //index comment
    // $commentElastic->update($response->getResults('comment_id'));

    //invalidate cache
    $commentRedis->removeDetail($response->getResults('comment_id'));
    $commentRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
