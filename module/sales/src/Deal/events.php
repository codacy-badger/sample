<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Deal\Service as DealService;
use Cradle\Module\Sales\Deal\Validator as DealValidator;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Deal Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = DealValidator::getCreateErrors($data);
    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['deal_close'])) {
        $data['deal_close'] = date('Y-m-d', strtotime($data['deal_close']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    //save deal to database
    $results = $dealSql->create($data);

    //link company
    if(isset($data['deal_company']) && !empty($data['deal_company'])) {
        $dealSql->linkCompany($results['deal_id'], $data['deal_company']);
    }

    //link agent
    if(isset($data['deal_agent']) && !empty($data['deal_agent'])) {
        $dealSql->linkAgent($results['deal_id'], $data['deal_agent']);
    }

    //link pipeline
    if(isset($data['pipeline_id']) && !empty($data['pipeline_id'])) {
        $dealSql->linkPipeline($results['deal_id'], $data['pipeline_id']);
    }

    //index deal
    // $dealElastic->create($results['deal_id']);

    //invalidate cache
    $dealRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Deal Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['deal_id'])) {
        $id = $data['deal_id'];
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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $dealRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $dealElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $dealSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $dealRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Deal Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the deal detail
    $this->trigger('deal-detail', $request, $response);

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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    //save to database
    $results = $dealSql->update([
        'deal_id' => $data['deal_id'],
        'deal_active' => 0
    ]);

    //remove from index
    // $dealElastic->remove($data['deal_id']);

    //invalidate cache
    $dealRedis->removeDetail($data['deal_id']);
    $dealRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Deal Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the deal detail
    $this->trigger('deal-detail', $request, $response);

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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    //save to database
    $results = $dealSql->update([
        'deal_id' => $data['deal_id'],
        'deal_active' => 1
    ]);

    //create index
    // $dealElastic->create($data['deal_id']);

    //invalidate cache
    $dealRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Deal Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-search', function ($request, $response) {
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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $dealRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $dealElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $dealSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $dealRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Deal Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-summary', function ($request, $response) {
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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $dealRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $dealElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $dealSql->getSummary($data);
        }

        if ($results) {
            //cache it from database or index
            $dealRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Deal Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the deal detail
    $this->trigger('deal-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $deal = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['deal_status'])) {
        $data['deal_status'] = $deal['deal_status'];
    }

    //----------------------------//
    // 2. Validate Data
    $errors = DealValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['deal_close'])) {
        $data['deal_close'] = date('Y-m-d', strtotime($data['deal_close']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');
    // $dealElastic = DealService::get('elastic');

    //save deal to database
    $results = $dealSql->update($data);


    //if there's a deal_company change
    if(isset($data['deal_company']) &&
        $data['deal_company'] != $deal['company']['profile_id']) {
        // unlink deal
        $dealSql->unlinkCompany(
            $data['deal_id'],
            $deal['company']['profile_id']
        );

        // link to new one
        $dealSql->linkCompany($data['deal_id'], $data['deal_company']);
    }

    //if there's agent change
    if(isset($data['deal_agent']) &&
        $data['deal_agent'] != $deal['agent']['profile_id']) {
        // unlink agent
        $dealSql->unlinkAgent($data['deal_id'], $deal['agent']['profile_id']);
        // link agent
        $dealSql->linkAgent($data['deal_id'], $data['deal_agent']);
    }

    //if there's pipeline change
    if(isset($data['pipeline_id']) &&
        $data['pipeline_id'] != $deal['pipeline_id']) {
        // unlink pipeline
        $dealSql->unlinkPipeline($data['deal_id'], $deal['pipeline_id']);
        // link pipeline
        $dealSql->linkPipeline($data['deal_id'], $data['pipeline_id']);
    }

    //index deal
    // $dealElastic->update($response->getResults('deal_id'));

    //invalidate cache
    $dealRedis->removeDetail($response->getResults('deal_id'));
    $dealRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Deal Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('deal-bulk-action', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    $data = $request->getStage();

    //if incomplete data
    if (!isset($data['bulk']) || !isset($data['bulk_rows'])) {
        $response->setError(true, 'Invalid Action');
    }

    //----------------------------//
    // 2. Validate Data
    if (!is_array($data['bulk_rows'])) {
        $errors = ['bulk_rows' => 'Invalid ids'];
    }

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
    $dealSql = DealService::get('sql');
    $dealRedis = DealService::get('redis');

    //bulk deactivate
    if ($data['bulk'] == 'remove') {
        $results = $dealSql->bulkActive($data['bulk_rows'], 0);
    }

    // bulk activate
    if ($data['bulk'] == 'restore') {
        $results = $dealSql->bulkActive($data['bulk_rows'], 1);
    }

    // invalidate all ids
    foreach ($data['bulk_rows'] as $ids) {
        //invalidate cache
        $dealRedis->removeDetail($ids);
        $dealRedis->removeSearch();
    }

    //return response format
    $response->setError(false)->setResults($results);

});
