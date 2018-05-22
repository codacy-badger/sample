<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Experience\Service as ExperienceService;
use Cradle\Module\Tracer\Experience\Validator as ExperienceValidator;

/**
 * Experience Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ExperienceValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['experience_from'])) {
        $data['experience_from'] = date('Y-m-d', strtotime($data['experience_from']));
    }

    if(isset($data['experience_to'])) {
        $data['experience_to'] = date('Y-m-d', strtotime($data['experience_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    //save experience to database
    $results = $experienceSql->create($data);
    //link information
    if(isset($data['information_id'])) {
        $experienceSql->linkInformation($results['experience_id'], $data['information_id']);
    }

    //index experience
    // $experienceElastic->create($results['experience_id']);

    //invalidate cache
    $experienceRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Experience Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['experience_id'])) {
        $id = $data['experience_id'];
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
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $experienceRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $experienceElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $experienceSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $experienceRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Experience Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the experience detail
    $this->trigger('experience-detail', $request, $response);

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
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    //save to database
    $results = $experienceSql->update([
        'experience_id' => $data['experience_id'],
        'experience_active' => 0
    ]);

    //remove from index
    // $experienceElastic->remove($data['experience_id']);

    //invalidate cache
    $experienceRedis->removeDetail($data['experience_id']);
    $experienceRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Experience Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the experience detail
    $this->trigger('experience-detail', $request, $response);

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
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    //save to database
    $results = $experienceSql->update([
        'experience_id' => $data['experience_id'],
        'experience_active' => 1
    ]);

    //create index
    // $experienceElastic->create($data['experience_id']);

    //invalidate cache
    $experienceRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Experience Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-search', function ($request, $response) {
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
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $experienceRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $experienceElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $experienceSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $experienceRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Experience Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('experience-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the experience detail
    $this->trigger('experience-detail', $request, $response);

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
    $errors = ExperienceValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['experience_from'])) {
        $data['experience_from'] = date('Y-m-d', strtotime($data['experience_from']));
    }

    if(isset($data['experience_to'])) {
        $data['experience_to'] = date('Y-m-d', strtotime($data['experience_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $experienceSql = ExperienceService::get('sql');
    $experienceRedis = ExperienceService::get('redis');
    // $experienceElastic = ExperienceService::get('elastic');

    //save experience to database
    $results = $experienceSql->update($data);

    //index experience
    // $experienceElastic->update($response->getResults('experience_id'));

    //invalidate cache
    $experienceRedis->removeDetail($response->getResults('experience_id'));
    $experienceRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
