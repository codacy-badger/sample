<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Resume\Service as ResumeService;
use Cradle\Module\Resume\Validator as ResumeValidator;

/**
 * Resume Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ResumeValidator::getCreateErrors($data);

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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //save resume to database
    $results = $resumeSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $resumeSql->linkProfile($results['resume_id'], $data['profile_id']);
    }

    //link post
    if(isset($data['post_id'])) {
        $resumeSql->linkPost($results['resume_id'], $data['post_id']);
    }

    //index resume
    // $resumeElastic->create($results['resume_id']);

    //invalidate cache
    $resumeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Resume Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['resume_id'])) {
        $id = $data['resume_id'];
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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $resumeRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $resumeElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $resumeSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $resumeRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Resume Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the resume detail
    $this->trigger('resume-detail', $request, $response);

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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //save to database
    $results = $resumeSql->update([
        'resume_id' => $data['resume_id'],
        'resume_active' => 0
    ]);

    // $resumeElastic->update($response->getResults('resume_id'));

    //invalidate cache
    $resumeRedis->removeDetail($data['resume_id']);
    $resumeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Resume Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the resume detail
    $this->trigger('resume-detail', $request, $response);

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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //save to database
    $results = $resumeSql->update([
        'resume_id' => $data['resume_id'],
        'resume_active' => 1
    ]);

    //create index
    // $resumeElastic->create($data['resume_id']);

    //invalidate cache
    $resumeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Resume Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-search', function ($request, $response) {
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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $resumeRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $resumeElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $resumeSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $resumeRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Resume Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the resume detail
    $this->trigger('resume-detail', $request, $response);

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
    $errors = ResumeValidator::getUpdateErrors($data);

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
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //save resume to database
    $results = $resumeSql->update($data);

    //index resume
    // $resumeElastic->update($response->getResults('resume_id'));

    //invalidate cache
    $resumeRedis->removeDetail($response->getResults('resume_id'));
    $resumeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});


/**
 * Resume Post Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-post', function ($request, $response) {
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
    $resumeSql = ResumeService::get('sql');

    //get it from database
    $results = $resumeSql->searchPost($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Resume Link Post Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-link-post', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //link post
    $results = $resumeSql->linkPost($data['resume_id'], $data['post_id']);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Download Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('resume-download', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the resume detail
    $this->trigger('resume-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        $response->setResults([]);
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //if there are errors
    if (!isset($data['profile_id'])) {
        $response->setResults([]);
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'profile_id' => 'ID is required'
            ]);
    }

    //----------------------------//
    // 3. Prepare Data
    $resume = $response->getResults('resume_link');

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $resumeSql = ResumeService::get('sql');
    $resumeRedis = ResumeService::get('redis');
    // $resumeElastic = ResumeService::get('elastic');

    //save resume to database
    $resumeSql->addDownload($response->getResults('resume_id'), $data['profile_id']);

    //index post
    // $resumeElastic->update($response->getResults('resume_id'));

    //invalidate cache
    $resumeRedis->removeDetail($response->getResults('resume_id'));
    $resumeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults(['resume_link' => $resume]);
});