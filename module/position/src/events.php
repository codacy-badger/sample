<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Position\Service as PositionService;
use Cradle\Module\Position\Validator as PositionValidator;

/**
 * Position Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = PositionValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if(isset($data['position_skills'])) {
        $data['position_skills'] = json_encode($data['position_skills']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    //save position to database
    $results = $positionSql->create($data);
    //link skills
    if(isset($data['skill_id'])) {
        $positionSql->linkSkills($results['position_id'], $data['skill_id']);
    }

    //index position
    // $positionElastic->create($results['position_id']);

    //invalidate cache
    $positionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Position Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['position_id'])) {
        $id = $data['position_id'];
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
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $positionRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $positionElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $positionSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $positionRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Position Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the position detail
    $this->trigger('position-detail', $request, $response);

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
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    //save to database
    $results = $positionSql->update([
        'position_id' => $data['position_id'],
        'position_active' => 0
    ]);

    // $positionElastic->update($response->getResults('position_id'));

    //invalidate cache
    $positionRedis->removeDetail($data['position_id']);
    $positionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Position Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the position detail
    $this->trigger('position-detail', $request, $response);

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
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    //save to database
    $results = $positionSql->update([
        'position_id' => $data['position_id'],
        'position_active' => 1
    ]);

    //create index
    // $positionElastic->create($data['position_id']);

    //invalidate cache
    $positionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Position Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-search', function ($request, $response) {
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
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $positionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $positionElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $positionSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $positionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Position Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the position detail
    $this->trigger('position-detail', $request, $response);

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
    $errors = PositionValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if(isset($data['position_skills'])) {
        $data['position_skills'] = json_encode($data['position_skills']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $positionSql = PositionService::get('sql');
    $positionRedis = PositionService::get('redis');
    // $positionElastic = PositionService::get('elastic');

    //save position to database
    $results = $positionSql->update($data);

    //index position
    // $positionElastic->update($response->getResults('position_id'));

    //invalidate cache
    $positionRedis->removeDetail($response->getResults('position_id'));
    $positionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Position Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('position-parse', function ($request, $response) {
    $positions = cradle('global')->config('position');
    foreach ($positions as $key => $jobs) {
        $request->setStage('position_name', $key);
        $request->setStage('position_type', 'parent');
        $request->setStage('position_parent', 0);
        $request->setStage('position_description', $jobs['description']);
        $request->setStage('position_skills', $jobs['skills']);

        $this->trigger('position-create', $request, $response);
        $position = $response->getResults();

        foreach ($jobs['jobs'] as $jkey => $job) {
            $request->setStage('position_name', $job);
            $request->setStage('position_type', 'child');
            $request->setStage('position_parent', $position['position_id']);
            $request->removeStage('position_description');
            $request->removeStage('position_skills');

            $this->trigger('position-create', $request, $response);
        }
    }
});
