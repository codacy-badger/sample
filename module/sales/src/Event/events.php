<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Sales\Event\Service as EventService;
use Cradle\Module\Sales\Event\Validator as EventValidator;

/**
 * Event Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = EventValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if(isset($data['event_stages'])) {
        $data['event_stages'] = json_encode($data['event_stages']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //save event to database
    $results = $eventSql->create($data);

    //index event
    // $eventElastic->create($results['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Event Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];

    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['event_id'])) {
        $id = $data['event_id'];
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
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $eventRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $eventElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $eventSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $eventRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Event Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the event detail
    $this->trigger('event-detail', $request, $response);

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
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //save to database
    $results = $eventSql->update([
        'event_id' => $data['event_id'],
        'event_active' => 0
    ]);

    //remove from index
    // $eventElastic->remove($data['event_id']);

    //invalidate cache
    $eventRedis->removeDetail($data['event_id']);
    $eventRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Event Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the event detail
    $this->trigger('event-detail', $request, $response);

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
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //save to database
    $results = $eventSql->update([
        'event_id' => $data['event_id'],
        'event_active' => 1
    ]);

    //create index
    // $eventElastic->create($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Event Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-search', function ($request, $response) {
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
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $eventRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $eventElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $eventSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $eventRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Event Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the event detail
    $this->trigger('event-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $event = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = EventValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if (isset($data['add_stages'])) {
        $data['event_stages'] = array_merge($event['event_stages'], $data['add_stages']);
    }

    if (isset($data['remove_stages'])) {
        $data['event_stages'] = array_diff($event['event_stages'], $data['remove_stages']);
    }

    if(isset($data['event_stages'])) {
        $data['event_stages'] = json_encode($data['event_stages']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //save event to database
    $results = $eventSql->update($data);

    //index event
    // $eventElastic->update($response->getResults('event_id'));

    //invalidate cache
    $eventRedis->removeDetail($response->getResults('event_id'));
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Event Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-bulk-action', function ($request, $response) {
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
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //bulk deactivate
    if ($data['bulk'] == 'remove') {
        $results = $eventSql->bulkActive($data['bulk_rows'], 0);
    }

    // bulk activate
    if ($data['bulk'] == 'restore') {
        $results = $eventSql->bulkActive($data['bulk_rows'], 1);
    }

    // invalidate all ids
    foreach ($data['bulk_rows'] as $ids) {
        //index event
        // $eventElastic->update($ids);

        //invalidate cache
        $eventRedis->removeDetail($ids);
        $eventRedis->removeSearch();
    }

    //return response format
    $response->setError(false)->setResults($results);

});
