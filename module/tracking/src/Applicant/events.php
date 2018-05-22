<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Applicant\Service as ApplicantService;
use Cradle\Module\Tracking\Applicant\Validator as ApplicantValidator;

/**
 * Applicant Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ApplicantValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['applicant_status'])) {
        $data['applicant_status'] = json_encode($data['applicant_status']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    //save applicant to database
    $results = $applicantSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $applicantSql->linkProfile($results['applicant_id'], $data['profile_id']);
    }

    //link form
    if(isset($data['form_id'])) {
        $applicantSql->linkForm($results['applicant_id'], $data['form_id']);
    }

    //link post
    if(isset($data['post_id'])) {
        $applicantSql->linkPost($results['applicant_id'], $data['post_id']);
    }

    //link answer
    if(isset($data['answer_ids'])) {
        foreach ($data['answer_ids'] as $answerId) {
            $applicantSql->linkAnswer($results['applicant_id'], $answerId);
        }
    }

    //index applicant
    // $applicantElastic->create($results['applicant_id']);

    //invalidate cache
    $applicantRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['applicant_id'])) {
        $id = $data['applicant_id'];
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
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $applicantRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $applicantElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $applicantSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $applicantRedis->createDetail($id, $results);
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
 * Applicant Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the applicant detail
    $this->trigger('applicant-detail', $request, $response);

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
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    //save to database
    $results = $applicantSql->update([
        'applicant_id' => $data['applicant_id'],
        'applicant_active' => 0
    ]);

	try {
		//remove from index
		// $applicantElastic->remove($data['applicant_id']);

		//invalidate cache
		$applicantRedis->removeDetail($data['applicant_id']);
		$applicantRedis->removeSearch();
	} catch(\Throwable $e) {
	}

    $response->setError(false)->setResults($results);
});

/**
 * Applicant Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the applicant detail
    $this->trigger('applicant-detail', $request, $response);

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
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    //save to database
    $results = $applicantSql->update([
        'applicant_id' => $data['applicant_id'],
        'applicant_active' => 1
    ]);

    //create index
    // $applicantElastic->create($data['applicant_id']);

    //invalidate cache
    $applicantRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Applicant Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-search', function ($request, $response) {
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
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $applicantRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $applicantElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $applicantSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $applicantRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-profile-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $data['join'][] = 'profile';

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $applicantRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $applicantElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $applicantSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $applicantRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Remove Deleted Tags Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-remove-label', function ($request, $response) {
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
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    $results = $applicantSql->searchPosterApplicants($data);

    // Checks if there were results returned
    if ($results['total']) {
        // Loops through the applicants
        foreach ($results['rows'] as $row) {
            $request->removeStage();

            // Get the key in the array to unset
            $row['applicant_status'] = json_decode($row['applicant_status']);
            if (($key = array_search($data['label_name'], $row['applicant_status'])) !== false) {
                unset($row['applicant_status'][$key]);
            }

            // Update the applicant data
            $request->setStage('applicant_id', $row['applicant_id']);
            $request->setStage('applicant_status', $row['applicant_status']);
            $this->trigger('applicant-update', $request, $response);
        }
    }

    //set response format
    $response->setError(false);
});

/**
 * Applicant Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the applicant detail
    $this->trigger('applicant-detail', $request, $response);

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
    $errors = ApplicantValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['applicant_status'])) {
        $data['applicant_status'] = json_encode($data['applicant_status']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    //save applicant to database
    $results = $applicantSql->update($data);

    //index applicant
    // $applicantElastic->update($response->getResults('applicant_id'));

    //invalidate cache
    $applicantRedis->removeDetail($response->getResults('applicant_id'));
    $applicantRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant View Form Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-view-form', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
     if (!$data['post_id']) {
        return $response->setError(true, 'Invalid ID');
    }
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $applicantSql = ApplicantService::get('sql');
    $applicantRedis = ApplicantService::get('redis');
    // $applicantElastic = ApplicantService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $applicantRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $applicantElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $applicantSql->viewForm($data);
        }

        if ($results) {
            //cache it from database or index
            $applicantRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Link Answer Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-link-answer', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
     if (!$data['applicant_id'] || !$data['answer_id']) {
        return $response->setError(true, 'Invalid ID');
    }
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $results = false;

    $applicantSql = ApplicantService::get('sql');
    $results = $applicantSql->linkAnswer($data['applicant_id'], $data['answer_id']);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Link Form Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('applicant-link-form', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Checks for applicant id / applicant_id
    // Checks for form id / form_id
    if (!$data['applicant_id'] || !$data['form_id']) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $results = false;

    $applicantSql = ApplicantService::get('sql');
    $results = $applicantSql->linkForm($data['applicant_id'], $data['form_id']);

    //set response format
    $response->setError(false)->setResults($results);
});