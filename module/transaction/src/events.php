<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Transaction\Service as TransactionService;
use Cradle\Module\Transaction\Validator as TransactionValidator;

/**
 * Transaction Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(isset($data['profile_id'])) {
        $this->trigger('profile-detail', $request, $response);
        $profile = $response->getResults();

        if(!$profile) {
            return $response->setError(true, 'Invalid User');
        }

        if (isset($profile['profile_detail'])) {
            $profile['profile_detail'] = '...';
        }

        $data['transaction_profile'] = json_encode($profile);
    }

    if(isset($data['transaction_meta'])) {
        $data['transaction_meta'] = json_encode($data['transaction_meta']);
    }

    //----------------------------//
    // 2. Validate Data
    $errors = TransactionValidator::getCreateErrors($data);

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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    //save transaction to database
    $results = $transactionSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $transactionSql->linkProfile($results['transaction_id'], $data['profile_id']);
        //update credits
        $this->trigger('profile-update-credits', $request, $response);

        $actionData = [
            'action_event' => 'transaction-create',
            'profile_id' => $data['profile_id']
        ];

        // check action event
        // if (!$this->package('global')->queue('action-check-event', $actionData)) {
             // if no queue manually do it
            $actionRequest  = Cradle\Http\Request::i();
            $actionResponse  = Cradle\Http\Response::i();
            $actionRequest->setStage('action_event', 'transaction-create');
            $actionRequest->setStage('profile_id', $data['profile_id']);
            $this->trigger('action-check-event', $actionRequest, $actionResponse);
        // }

        // add story
        $storyRequest  = Cradle\Http\Request::i();
        $storyResponse  = Cradle\Http\Response::i();
        $story = cradle('global')->config('story', 'transaction-create');
        $storyRequest->setStage('profile_id', $data['profile_id']);
        $storyRequest->setStage('add_story', [$story]);
        $this->trigger('profile-update', $storyRequest, $storyResponse);
    }

    //index transaction
    // $transactionElastic->create($results['transaction_id']);

    //invalidate cache
    $transactionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Transaction Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['transaction_id'])) {
        $id = $data['transaction_id'];
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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $transactionRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $transactionElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $transactionSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $transactionRedis->createDetail($id, $results);
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
 * Transaction Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the transaction detail
    $this->trigger('transaction-detail', $request, $response);

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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    //save to database
    $results = $transactionSql->update([
        'transaction_id' => $data['transaction_id'],
        'transaction_active' => 0
    ]);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);

    // $transactionElastic->update($response->getResults('transaction_id'));

    //invalidate cache
    $transactionRedis->removeDetail($data['transaction_id']);
    $transactionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Transaction Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the transaction detail
    $this->trigger('transaction-detail', $request, $response);

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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    //save to database
    $results = $transactionSql->update([
        'transaction_id' => $data['transaction_id'],
        'transaction_active' => 1
    ]);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);

    //create index
    // $transactionElastic->create($data['transaction_id']);

    //invalidate cache
    $transactionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Transaction Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-search', function ($request, $response) {
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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $transactionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $transactionElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $transactionSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $transactionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Transaction Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the transaction detail
    $this->trigger('transaction-detail', $request, $response);

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
    $errors = TransactionValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['transaction_profile'])) {
        $data['transaction_profile'] = json_encode($data['transaction_profile']);
    }

    if(isset($data['transaction_meta'])) {
        $data['transaction_meta'] = json_encode($data['transaction_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');
    // $transactionElastic = TransactionService::get('elastic');

    //save transaction to database
    $results = $transactionSql->update($data);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);

    //index transaction
    // $transactionElastic->update($response->getResults('transaction_id'));

    //invalidate cache
    $transactionRedis->removeDetail($response->getResults('transaction_id'));
    $transactionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Transaction, Get Total of transaction credits, For Dashbpard Chart
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('transaction-search-chart', function ($request, $response) {
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
    $transactionSql = TransactionService::get('sql');
    $transactionRedis = TransactionService::get('redis');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $transactionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no results
        if (!$results) {
                //get it from database
                $results = $transactionSql->getChartTotalCredits($data);
        }

        if ($results) {
            //cache it from database or index
            $transactionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});
