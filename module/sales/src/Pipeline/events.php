<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Pipeline\Service as PipelineService;
use Cradle\Module\Sales\Pipeline\Validator as PipelineValidator;

/**
 * Pipeline Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = PipelineValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if(isset($data['pipeline_stages'])) {
        $data['pipeline_stages'] = json_encode($data['pipeline_stages']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    //save pipeline to database
    $results = $pipelineSql->create($data);

    //index pipeline
    // $pipelineElastic->create($results['pipeline_id']);

    //invalidate cache
    $pipelineRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Pipeline Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];

    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['pipeline_id'])) {
        $id = $data['pipeline_id'];
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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $pipelineRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $pipelineElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $pipelineSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $pipelineRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});


/**
 * Pipeline Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-board', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];

    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['pipeline_id'])) {
        $id = $data['pipeline_id'];
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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $pipelineRedis->getBoard($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $pipelineElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $pipelineSql->getBoard($id);
        }

        if ($results) {
            //cache it from database or index
            $pipelineRedis->createBoard($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});
/**
 * Pipeline Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the pipeline detail
    $this->trigger('pipeline-detail', $request, $response);

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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    //save to database
    $results = $pipelineSql->update([
        'pipeline_id' => $data['pipeline_id'],
        'pipeline_active' => 0
    ]);

    //remove from index
    // $pipelineElastic->remove($data['pipeline_id']);

    //invalidate cache
    $pipelineRedis->removeDetail($data['pipeline_id']);
    $pipelineRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Pipeline Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the pipeline detail
    $this->trigger('pipeline-detail', $request, $response);

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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    //save to database
    $results = $pipelineSql->update([
        'pipeline_id' => $data['pipeline_id'],
        'pipeline_active' => 1
    ]);

    //create index
    // $pipelineElastic->create($data['pipeline_id']);

    //invalidate cache
    $pipelineRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Pipeline Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-search', function ($request, $response) {
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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $pipelineRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $pipelineElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $pipelineSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $pipelineRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Pipeline Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the pipeline detail
    $this->trigger('pipeline-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $pipeline = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = PipelineValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if (isset($data['add_stages'])) {
        $data['pipeline_stages'] = array_merge($pipeline['pipeline_stages'], $data['add_stages']);
    }

    if (isset($data['remove_stages'])) {
        $data['pipeline_stages'] = array_diff($pipeline['pipeline_stages'], $data['remove_stages']);
    }

    if(isset($data['pipeline_stages'])) {
        $data['pipeline_stages'] = json_encode($data['pipeline_stages']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    //save pipeline to database
    $results = $pipelineSql->update($data);

    //index pipeline
    // $pipelineElastic->update($response->getResults('pipeline_id'));

    //invalidate cache
    $pipelineRedis->removeDetail($response->getResults('pipeline_id'));
    $pipelineRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Pipeline Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('pipeline-bulk-action', function ($request, $response) {
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
    $pipelineSql = PipelineService::get('sql');
    $pipelineRedis = PipelineService::get('redis');
    // $pipelineElastic = PipelineService::get('elastic');

    //bulk deactivate
    if ($data['bulk'] == 'remove') {
        $results = $pipelineSql->bulkActive($data['bulk_rows'], 0);
    }

    // bulk activate
    if ($data['bulk'] == 'restore') {
        $results = $pipelineSql->bulkActive($data['bulk_rows'], 1);
    }

    // invalidate all ids
    foreach ($data['bulk_rows'] as $ids) {
        //index pipeline
        // $pipelineElastic->update($ids);

        //invalidate cache
        $pipelineRedis->removeDetail($ids);
        $pipelineRedis->removeSearch();
    }

    //return response format
    $response->setError(false)->setResults($results);

});
