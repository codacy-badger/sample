<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Area\Service as AreaService;
use Cradle\Module\Area\Validator as AreaValidator;

/**
 * Area Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AreaValidator::getCreateErrors($data);

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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    //save area to database
    $results = $areaSql->create($data);

    //index area
    // $areaElastic->create($results['area_id']);

    //invalidate cache
    $areaRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Area Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['area_id'])) {
        $id = $data['area_id'];
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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $areaRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $areaElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $areaSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $areaRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Area Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the area detail
    $this->trigger('area-detail', $request, $response);

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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    //save to database
    $results = $areaSql->update([
        'area_id' => $data['area_id'],
        'area_active' => 0
    ]);

    //remove from index
    // $areaElastic->update($response->getResults('area_id'));

    //invalidate cache
    $areaRedis->removeDetail($data['area_id']);
    $areaRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Area Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the area detail
    $this->trigger('area-detail', $request, $response);

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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    //save to database
    $results = $areaSql->update([
        'area_id' => $data['area_id'],
        'area_active' => 1
    ]);

    //create index
    // $areaElastic->create($data['area_id']);

    //invalidate cache
    $areaRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Area Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-search', function ($request, $response) {
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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $areaRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $areaElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $areaSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $areaRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Area Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('area-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the area detail
    $this->trigger('area-detail', $request, $response);

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
    $errors = AreaValidator::getUpdateErrors($data);

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
    $areaSql = AreaService::get('sql');
    $areaRedis = AreaService::get('redis');
    // $areaElastic = AreaService::get('elastic');

    //save area to database
    $results = $areaSql->update($data);

    //index area
    // $areaElastic->update($response->getResults('area_id'));

    //invalidate cache
    $areaRedis->removeDetail($response->getResults('area_id'));
    $areaRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
