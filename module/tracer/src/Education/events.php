<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Education\Service as EducationService;
use Cradle\Module\Tracer\Education\Validator as EducationValidator;

/**
 * Education Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = EducationValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['education_from'])) {
        $data['education_from'] = date('Y-m-d', strtotime($data['education_from']));
    }

    if(isset($data['education_to'])) {
        $data['education_to'] = date('Y-m-d', strtotime($data['education_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    //save education to database
    $results = $educationSql->create($data);
    //link information
    if(isset($data['information_id'])) {
        $educationSql->linkInformation($results['education_id'], $data['information_id']);
    }

    //index education
    // $educationElastic->create($results['education_id']);

    //invalidate cache
    $educationRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Education Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['education_id'])) {
        $id = $data['education_id'];
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
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $educationRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $educationElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $educationSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $educationRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Education Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the education detail
    $this->trigger('education-detail', $request, $response);

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
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    //save to database
    $results = $educationSql->update([
        'education_id' => $data['education_id'],
        'education_active' => 0
    ]);

    //remove from index
    // $educationElastic->remove($data['education_id']);

    //invalidate cache
    $educationRedis->removeDetail($data['education_id']);
    $educationRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Education Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the education detail
    $this->trigger('education-detail', $request, $response);

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
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    //save to database
    $results = $educationSql->update([
        'education_id' => $data['education_id'],
        'education_active' => 1
    ]);

    //create index
    // $educationElastic->create($data['education_id']);

    //invalidate cache
    $educationRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Education Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-search', function ($request, $response) {
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
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $educationRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $educationElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $educationSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $educationRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Education Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('education-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the education detail
    $this->trigger('education-detail', $request, $response);

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
    $errors = EducationValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['education_from'])) {
        $data['education_from'] = date('Y-m-d', strtotime($data['education_from']));
    }

    if(isset($data['education_to'])) {
        $data['education_to'] = date('Y-m-d', strtotime($data['education_to']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $educationSql = EducationService::get('sql');
    $educationRedis = EducationService::get('redis');
    // $educationElastic = EducationService::get('elastic');

    //save education to database
    $results = $educationSql->update($data);

    //index education
    // $educationElastic->update($response->getResults('education_id'));

    //invalidate cache
    $educationRedis->removeDetail($response->getResults('education_id'));
    $educationRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
