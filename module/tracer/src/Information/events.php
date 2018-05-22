<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracer\Information\Service as InformationService;
use Cradle\Module\Tracer\Information\Validator as InformationValidator;

/**
 * Information Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = InformationValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['information_skills'])) {
        $data['information_skills'] = json_encode($data['information_skills']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    //save information to database
    $results = $informationSql->create($data);

    if(isset($data['profile_id'])) {
        //link profile
        $informationSql->linkProfile($results['information_id'], $data['profile_id']);
        // update the profile
        $this->trigger('profile-update', $request, $response);
    }

    //index information
    // $informationElastic->create($results['information_id']);

    //invalidate cache
    $informationRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Information Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['information_id'])) {
        $id = $data['information_id'];
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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $informationRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $informationElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $informationSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $informationRedis->createDetail($id, $results);
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
 * Information Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the information detail
    $this->trigger('information-detail', $request, $response);

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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    //save to database
    $results = $informationSql->update([
        'information_id' => $data['information_id'],
        'information_active' => 0
    ]);

    //remove from index
    // $informationElastic->remove($data['information_id']);

    //invalidate cache
    $informationRedis->removeDetail($data['information_id']);
    $informationRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Information Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the information detail
    $this->trigger('information-detail', $request, $response);

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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    //save to database
    $results = $informationSql->update([
        'information_id' => $data['information_id'],
        'information_active' => 1
    ]);

    //create index
    // $informationElastic->create($data['information_id']);

    //invalidate cache
    $informationRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Information Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-search', function ($request, $response) {
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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $informationRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $informationElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $informationSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $informationRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Information Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the information detail
    $this->trigger('information-detail', $request, $response);

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
    $errors = InformationValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['information_skills'])) {
        $data['information_skills'] = json_encode($data['information_skills']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    //save information to database
    $results = $informationSql->update($data);

    //index information
    // $informationElastic->update($response->getResults('information_id'));

    //invalidate cache
    $informationRedis->removeDetail($response->getResults('information_id'));
    $informationRedis->removeSearch();

    // update profile
    if (isset($data['profile_id'])) {
        $this->trigger('profile-update', $request, $response);
    }

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Profile Information Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-information', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['profile_id'])) {
        $id = $data['profile_id'];
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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        // $results = $informationRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $informationElastic->getProfileInformation($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $informationSql->getProfileInformation($id);
        }

        if ($results) {
            //cache it from database or index
            // $informationRedis->createDetail($id, $results);
        }
    }

    $response->setError(false)->setResults($results);
});

/**
 * Information Location
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-location', function ($request, $response) {
    // remove the stage
    $request->removeStage();

    // set stage for city
    $request->setStage([
        'range' => 0,
        'filter' => [
            'area_type' => 'city'
        ]
    ]);

    // trigget the job
    cradle()->trigger('area-search', $request, $response);

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/post/search');
    }

    // set the data for city
    $results['city'] = $response->getResults('rows');

    // set stage for state
    $request->setStage([
        'range' => 0,
        'filter' => [
            'area_type' => 'state'
        ]
    ]);

    // trigget the job
    cradle()->trigger('area-search', $request, $response);

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/post/search');
    }

    // set the data for state
    $results['state'] = $response->getResults('rows');

    $response->setError(false)->setResults($results);
});

/**
 * Information Industry
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-industry', function ($request, $response) {
    // remove the stage
    $request->removeStage();

    // set stage for city
    $request->setStage([
        'range' => 0,
        'filter' => [
            'feature_type' => 'industry'
        ]
    ]);

    // trigget the job
    cradle()->trigger('feature-search', $request, $response);

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/post/search');
    }

    $response->setError(false)->setResults($response->getResults('rows'));
});

/**
 * Information Download
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-download', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['information_id'])) {
        $id = $data['information_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $informationSql = InformationService::get('sql');

    //save post to database
    $results = $informationSql->addDownload($data['information_id'], $data['profile_id']);

    // Checks if this was already downloaded
    if (!$results) {
        //return response format
        return $response
            ->setError(false)
            ->setResults('downloaded');
    }

    return $results;
});

/**
 * Check Information Download
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('check-information-download', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['information_id'])) {
        $id = $data['information_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $informationSql = InformationService::get('sql');

    //save post to database
    $results = $informationSql->alreadyDownloaded($data['information_id'], $data['profile_id']);

    // Checks if this was already downloaded
    if (!$results) {
        //return response format
        return $response
            ->setError(false)
            ->setResults('downloaded');
    }

    return $results;
});

/**
 * Information Experience
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-experience', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['profile_id'])) {
        $id = $data['profile_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 4. Process Data
    $informationSql = InformationService::get('sql');
    // add create resume experience
    $experience = cradle('global')->config('experience', 'resume_download');
    $request->setStage('profile_experience', $experience);
    cradle()->trigger('profile-add-experience', $request, $response);

    $results['credits'] = $experience;

    $activityCount = $informationSql->getUserInformationDownloaded($data['profile_id']);

    // 10th download badge
    if ($activityCount == 10) {
        $achievement = cradle('global')->config('achievements', 'downloaded_10');
        $request->setStage('profile_achievement', 'downloaded_10');
        cradle()->trigger('profile-add-achievement', $request, $response);
        $results['badge'] = [
            'image' => $achievement['image'],
            'message' => cradle('global')->translate($achievement['modal'])
        ];
    }

    // 50th download badge
    if ($activityCount == 50) {
        $achievement = cradle('global')->config('achievements', 'downloaded_50');
        $request->setStage('profile_achievement', 'downloaded_50');
        cradle()->trigger('profile-add-achievement', $request, $response);
        $results['badge'] = [
            'image' => $achievement['image'],
            'message' => cradle('global')->translate($achievement['modal'])
        ];
    }

    // 100th download badge
    if ($activityCount == 100) {
        $achievement = cradle('global')->config('achievements', 'downloaded_100');
        $request->setStage('profile_achievement', 'downloaded_100');
        cradle()->trigger('profile-add-achievement', $request, $response);
        $results['badge'] = [
            'image' => $achievement['image'],
            'message' => cradle('global')->translate($achievement['modal'])
        ];
    }

    return $response
        ->setError(false)
        ->setResults($results);
});

/**
 * Information Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-search-tracer', function ($request, $response) {
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
    $informationSql = InformationService::get('sql');
    $informationRedis = InformationService::get('redis');
    // $informationElastic = InformationService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $informationRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $informationElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $informationSql->searchTracer($data);
        }

        if ($results) {
            //cache it from database or index
            $informationRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Information Search Civil Status Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-search-civil-status', function ($request, $response) {
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
    $informationSql = InformationService::get('sql');
   
    //get it from database
    $results = $informationSql->searchCivilStatus($data);

    //set response format
    $response->setError(false)->setResults($results);
});


/**
 * Information Search Job Related Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-search-job-related', function ($request, $response) {
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
    $informationSql = InformationService::get('sql');
   
    //get it from database
    $results = $informationSql->searchJobRelated($data);

    //set response format
    $response->setError(false)->setResults($results);
});


/**
 * Information Search Job Related Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('information-search-employment-rate', function ($request, $response) {
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
    $informationSql = InformationService::get('sql');
   
    //get it from database
    $results = $informationSql->searchEmploymentRate($data);

    //set response format
    $response->setError(false)->setResults($results);
});
