<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Event\Service as EventService;
use Cradle\Module\Event\Validator as EventValidator;

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

    if(isset($data['event_start'])) {
        $data['event_start'] = date('Y-m-d H:i:s', strtotime($data['event_start']));
    }

    if(isset($data['event_end'])) {
        $data['event_end'] = date('Y-m-d H:i:s', strtotime($data['event_end']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    //save event to database
    $results = $eventSql->create($data);
    //link deal
    if(isset($data['deal_id'])) {
        $eventSql->linkDeal($results['event_id'], $data['deal_id']);
    }
    //link profile
    if(isset($data['profile_id'])) {
        $eventSql->linkProfile($results['event_id'], $data['profile_id']);
        $request->setStage('user_history', $data['profile_id']);
    }

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

    if(isset($data['event_start'])) {
        $data['event_start'] = date('Y-m-d H:i:s', strtotime($data['event_start']));
    }

    if(isset($data['event_end'])) {
        $data['event_end'] = date('Y-m-d H:i:s', strtotime($data['event_end']));
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
 * Links event to deal
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-link-deal', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['event_id'], $data['deal_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = $eventSql->linkDeal(
        $data['event_id'],
        $data['deal_id']
    );

    //index post
    // $eventElastic->update($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks event from deal
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-unlink-deal', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['event_id'], $data['deal_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = $eventSql->unlinkDeal(
        $data['event_id'],
        $data['deal_id']
    );

    //index post
    // $eventElastic->update($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links event to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-link-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['event_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = $eventSql->linkProfile(
        $data['event_id'],
        $data['profile_id']
    );

    //index post
    // $eventElastic->update($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks event from profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-unlink-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['event_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = $eventSql->unlinkProfile(
        $data['event_id'],
        $data['profile_id']
    );

    //index post
    // $eventElastic->update($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all event from profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('event-unlinkall-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['event_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $eventSql = EventService::get('sql');
    $eventRedis = EventService::get('redis');
    // $eventElastic = EventService::get('elastic');

    $results = $eventSql->unlinkAllProfile($data['event_id']);

    //index post
    // $eventElastic->update($data['event_id']);

    //invalidate cache
    $eventRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
