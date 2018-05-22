<?php //-->
ini_set('memory_limit', '-1');
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Profile\Service as ProfileService;
use Cradle\Module\Profile\Validator as ProfileValidator;

use Cradle\Module\Transaction\Service as TransactionService;
use Cradle\Module\Service\Service as ServiceService;

use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Queue;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Profile Add Experience (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-add-achievement', function ($request, $response) {
    //get data
    $data = $request->getStage();
    //this is what we need
    if (!isset($data['profile_id'], $data['profile_achievement'])) {
        return;
    }

    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');
    $profile = $profileSql->get($data['profile_id']);

    //if it's already there
    if (in_array($data['profile_achievement'], $profile['profile_achievements'])) {
        //no need to update
        return;
    }

    $profile['profile_achievements'][] = $data['profile_achievement'];
    $profileSql->update([
        'profile_id' => $profile['profile_id'],
        'profile_achievements' => json_encode($profile['profile_achievements'])
    ]);

    //update index
    $profileElastic->update($profile['profile_id']);
    //So this job is called in a good number of jobs some that caches data,
    //but if this job invalidates that same cache which de-purposes that logic.
    //So what we should do is build the again cache here
    $profileRedis->createDetail($profile['profile_id'], $profile);
    $profileRedis->createDetail($profile['profile_slug'], $profile);

    $actionData = [
        'action_event' => 'profile-add-achievement',
        'profile_id' => $profile['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'profile-add-achievement');
        $actionRequest->setStage('profile_id', $profile['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $story = cradle('global')->config('story', 'profile-add-achievement');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $profile['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);
});

/**
 * Profile Add Experience (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-add-experience', function ($request, $response) {
    //get data
    $data = $request->getStage();

    //this is what we need
    if (!isset($data['profile_id'], $data['profile_experience'])) {
        return;
    }

    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');
    //add view
    $profileSql->addExperience($data['profile_id'], $data['profile_experience']);
    //update index
    $profileElastic->update($data['profile_id']);
    //So this job is called in a good number of jobs some that caches data,
    //but if this job invalidates that same cache which de-purposes that logic.
    //So what we should do is build the again cache here
    $data = $profileSql->get($data['profile_id']);
    $profileRedis->createDetail($data['profile_id'], $data);
    $profileRedis->createDetail($data['profile_slug'], $data);

    $actionData = [
        'action_event' => 'profile-add-experience',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'profile-add-experience');
        $actionRequest->setStage('profile_id', $data['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $story = cradle('global')->config('story', 'profile-add-experience');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);
});

/**
 * Profile Add Free Credits (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-add-free-credits', function ($request, $response) {
    //get data
    $data = $request->getStage();
    //this is what we need
    if (!isset($data['profile_id'], $data['profile_experience'])) {
        return;
    }

    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');

    //So this job is called in a good number of jobs some that caches data,
    //but if this job invalidates that same cache which de-purposes that logic.
    //So what we should do is build the again cache here
    $data = $profileSql->get($data['profile_id']);

    //free credits to be added
    $freeCredits = 0;
    // actions to be inserted
    $actionEvent = '';
    // message to be added
    $level = '';

    // conditions whenever reached levels
    if ($data['profile_experience'] >= 1229 &&
        $data['profile_experience'] <= 1433 &&
        !in_array("Achieved Level 10", $data['profile_story'])) {
        $freeCredits = 100;
        $actionEvent = 'achieve-level-10';
        $level = 'lvl_10';
    } else if ($data['profile_experience'] >= 4545 &&
        $data['profile_experience'] <= 5093 &&
        !in_array("Achieved Level 20", $data['profile_story'])) {
        $freeCredits = 200;
        $actionEvent = 'achieve-level-20';
        $level = 'lvl_20';
    } else if ($data['profile_experience'] >= 13438 &&
        $data['profile_experience'] <= 14908 &&
        !in_array("Achieved Level 30", $data['profile_story'])) {
        $freeCredits = 300;
        $actionEvent = 'achieve-level-30';
        $level = 'lvl_30';
    } else if ($data['profile_experience'] >= 37299 &&
        $data['profile_experience'] <= 41246 &&
        !in_array("Achieved Level 40", $data['profile_story'])) {
        $freeCredits = 400;
        $actionEvent = 'achieve-level-40';
        $level = 'lvl_40';
    } else if ($data['profile_experience'] >= 101408 &&
        $data['profile_experience'] <= 112020 &&
        !in_array("Achieved Level 50", $data['profile_story'])) {
        $freeCredits = 500;
        $actionEvent = 'achieve-level-50';
        $level = 'lvl_50';
    }

    // add rank badge
    $rank = cradle('global')->config('rank', $level);
    if (!empty($level)) {
        cradle('global')->setRank(
            $rank['image'],
            $rank['level'],
            $rank['title'],
            $rank['credits'],
            $rank['action']
        );
    }

    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    // add story if there's an action done
    if (!empty($actionEvent)) {
        $story = cradle('global')->config('story', $actionEvent);
        $storyRequest->setStage('profile_id', $data['profile_id']);
        $storyRequest->setStage('add_story', [$story]);
        $storyRequest->setStage('profile_credits', $data['profile_credits'] + $freeCredits);

        $this->trigger('profile-update', $storyRequest, $storyResponse);
    }

    // create "Free Credits" in Service
    $request
        ->setStage('profile_id', $request->getSession('me', 'profile_id'))
        ->setStage('service_name', 'Free Credits')
        ->setStage('service_meta', [
            'profile_id' => $data['profile_id']
        ])
        ->setStage('service_credits', $freeCredits);

    cradle()->trigger('service-create', $request, $response);

    // update profile_credits in profile session
        // if (!$response->isError()) {
        //     $request->setSession(
        //         'me',
        //         'profile_credits',
        //         $storyResponse('profile_credits')
        //     );
        // }

    //update profile session
    // $this->trigger('profile-session', $request, $response);
});

/**
 * Profile Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ProfileValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    // set profile parent to null if value is not numeric
    if (isset($data['profile_parent']) &&
        !is_numeric($data['profile_parent'])) {
        $data['profile_parent'] = null;
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['profile_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['profile_image'] = File::base64ToS3($data['profile_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['profile_image'] = File::base64ToUpload($data['profile_image'], $upload);
    }

    //if there is a banner
    if (isset($data['profile_banner'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['profile_banner'] = File::base64ToS3($data['profile_banner'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['profile_banner'] = File::base64ToUpload($data['profile_banner'], $upload);
    }

    if (isset($data['profile_birth'])) {
        $data['profile_birth'] = date('Y-m-d', strtotime($data['profile_birth']));
    }

    if (isset($data['profile_achievements'])) {
        $data['profile_achievements'] = json_encode($data['profile_achievements']);
    }

    if (!isset($data['profile_experience']) || empty($data['profile_experience'])) {
        $data['profile_experience'] = 0;
    }

    // Checks if the profile_company exists and is empty
    if (isset($data['profile_company']) && (empty($data['profile_company']))) {
        $data['profile_company'] = null;
    }

    if (isset($data['profile_close']) && (empty($data['profile_close']))) {
        $data['profile_close'] = date('Y-m-d', strtotime($data['profile_close']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //save profile to database
    $results = $profileSql->create($data);

    // slugify
    $results['profile_slug'] = $profileSql->slugify($results['profile_name'], $results['profile_id']);

    $profileSql->update($results);

    //TODO::doens't need this events anymore but will retain.
    // someone might ask for this in the future
    if (isset($data['profile_company']) && $data['profile_company']) {
        // create deal
        $request->setStage('deal_name', $data['profile_company']);
        $request->setStage('deal_close', date('Y-m-d', strtotime('+3 months')));
        $request->setStage('deal_type', 'profile');
        $request->setStage('deal_company', $results['profile_id']);

        // Checks for a pipeline id
        if (!isset($data['pipeline_id'])) {
            $this->trigger('pipeline-search', $request, $response);
            $pipeline = $response->getResults('rows');

            // Checks if the pipeline exists
            if (isset($pipeline[0])) {
                $pipeline = $pipeline[0];
                $request->getStage('pipeline_id', $pipeline['pipeline_id']);
                $request->getStage('deal_status', $pipeline['pipeline_stages'][0]);
            }
        }

        $this->trigger('deal-create', $request, $response);
    }

    //index profile
    $profileElastic->create($results['profile_id']);

    //invalidate cache
    $profileRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);

    $request->setStage('profile_id', $results['profile_id']);

    //try to queue, and if not
    if (!$this->package('global')->queue('profile-image-s3', $results)) {
        //hit the terms manually
        $this->trigger('profile-image-s3', $request, $response);
    }

    //try to queue, and if not
    if (!$this->package('global')->queue('profile-email-validate', $results)) {
        //hit the terms manually
        $this->trigger('profile-email-validate', $request, $response);
    }
});

/**
 * Profile Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['profile_id'])) {
        $id = $data['profile_id'];
    } else if (isset($data['profile_slug'])) {
        $slug = explode('-', $data['profile_slug']);
        $id = substr($slug[count($slug)  -1], 1);
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $profileRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            $results = $profileElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $profileRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Profile Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-email-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $email = null;
    if (isset($data['profile_email'])) {
        $email = $data['profile_email'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an email
    if (!$email) {
        return $response->setError(true, 'Invalid Email');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');

    //get it from database
    $results = $profileSql->getByEmail($email);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Profile Email Validate
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-email-validate', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    $email = null;
    if ($request->hasStage('profile_email')) {
        $email = $request->getStage('profile_email');
    }

    //----------------------------//
    // 2. Validate Data
    //we need an email
    if (!$email) {
        return $response->setError(true, 'Invalid Email');
    }

    //----------------------------//
    // 3. Prepare Data
    $request->setStage('profile_email', $email);
    cradle()->trigger('validate-email', $request, $response);

    $emailFlag = 1;
    if ($response->isError()) {
        $emailFlag = -1;
    }

    $data['profile_email_flag'] = $emailFlag;
    $data['profile_id'] = $request->getStage('profile_id');
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');

    //get it from database
    $results = $profileSql->update($data);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});


/**
 * Profile Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the profile detail
    $this->trigger('profile-detail', $request, $response);

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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //save to database
    $results = $profileSql->update([
        'profile_id' => $data['profile_id'],
        'profile_active' => 0
    ]);

    $profileElastic->update($response->getResults('profile_id'));

    //invalidate cache
    $profileRedis->removeDetail($data['profile_id']);
    $profileRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Profile Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the profile detail
    $this->trigger('profile-detail', $request, $response);

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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //save to database
    $results = $profileSql->update([
        'profile_id' => $data['profile_id'],
        'profile_active' => 1
    ]);

    //create index
    $profileElastic->update($data['profile_id']);

    //invalidate cache
    $profileRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Profile Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $profileRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            $results = $profileElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $profileRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Profile Employed Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-employment-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $profileRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $profileElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->searchEmployment($data);
        }

        if ($results) {
            //cache it from database or index
            $profileRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Profile Search Job with
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-applicant-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $profileRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $profileElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->searchApplicant($data);
        }

        if ($results) {
            //cache it from database or index
            $profileRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Profile Update Session Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-session', function ($request, $response) {
    if (!$request->hasSession('me')) {
        return $response
            ->setError(true, 'Unauthorized')
            ->set('json', 'validation', ['request' => 'Unauthorized']);
    }

    //get profile id
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 1. Get Data
    //get the profile detail
    $this->trigger('profile-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $me = array_merge($request->getSession('me'), $response->getResults());

    // get the profile information
    cradle()->trigger('profile-information', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    // set the profile information
    $me['profile_information'] = $response->getResults();

    $request->setSession('me', $me);
});

/**
 * Profile Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the profile detail
    $this->trigger('profile-detail', $request, $response);
    $profile = $response->getResults();

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
    $errors = ProfileValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //if there is an image
    if (isset($data['profile_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['profile_image'] = File::base64ToS3($data['profile_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['profile_image'] = File::base64ToUpload($data['profile_image'], $upload);
    }

    //if there is a banner
    if (isset($data['profile_banner'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['profile_banner'] = File::base64ToS3($data['profile_banner'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['profile_banner'] = File::base64ToUpload($data['profile_banner'], $upload);
    }

    if (isset($data['profile_birth'])) {
        $data['profile_birth'] = date('Y-m-d', strtotime($data['profile_birth']));
    }

    if (isset($data['profile_package'])) {
         $data['profile_package'] = json_encode(array_map('strtolower', $data['profile_package']));
    }

    if (isset($data['profile_achievements'])) {
        $data['profile_achievements'] = json_encode($data['profile_achievements']);
    }

    if (isset($data['profile_interviewer'])) {
        $data['profile_interviewer'] = json_encode($data['profile_interviewer']);
    }

    //remove tags (in update)
    if (isset($data['remove_profile_tags']) && $data['remove_profile_tags']) {
        $data['profile_tags'] = [];
    }

    //remove stories (in update)
    if (isset($data['remove_profile_story']) && $data['remove_profile_story']) {
        $data['profile_story'] = [];
    }

    //remove campaigns (in update)
    if (isset($data['remove_profile_campaigns']) && $data['remove_profile_campaigns']) {
        $data['profile_campaigns'] = [];
    }

    // add tags
    if (isset($data['add_tags'])) {
        if ($profile['profile_tags']) {
            $data['profile_tags'] = array_unique(array_merge(
                $profile['profile_tags'],
                $data['add_tags']
            ));
        } else {
            $data['profile_tags'] = $data['add_tags'];
        }
    }

    // add story
    if (isset($data['add_story'])) {
        if ($profile['profile_story']) {
            $data['profile_story'] = array_unique(array_merge(
                $profile['profile_story'],
                $data['add_story']
            ));
        } else {
            $data['profile_story'] = $data['add_story'];
        }
    }

    // add campaigns
    if (isset($data['add_campaigns'])) {
        if ($profile['profile_campaigns']) {
            $data['profile_campaigns'] = array_unique(array_merge(
                $profile['profile_campaigns'],
                $data['add_campaigns']
            ));
        } else {
            $data['profile_campaigns'] = $data['add_campaigns'];
        }
    }

    // remove tag
    if (isset($data['remove_tags']) && $profile['profile_tags']) {
        $data['profile_tags'] = array_diff(
            $profile['profile_tags'],
            $data['remove_tags']
        );
    }

    // remove story
    if (isset($data['remove_story']) && $profile['profile_story']) {
        $data['profile_story'] = array_diff(
            $profile['profile_story'],
            $data['remove_story']
        );
    }

    // remove campaign
    if (isset($data['remove_campaigns']) && $profile['profile_campaigns']) {
        $data['profile_campaigns'] = array_diff(
            $profile['profile_campaigns'],
            $data['remove_campaigns']
        );
    }

    if (isset($data['profile_tags'])) {
        $data['profile_tags'] = json_encode($data['profile_tags']);
    }

    if (isset($data['profile_story'])) {
        $data['profile_story'] = json_encode($data['profile_story']);
    }

    if (isset($data['profile_campaigns'])) {
        $data['profile_campaigns'] = json_encode($data['profile_campaigns']);
    }

    if (isset($data['profile_close']) && (empty($data['profile_close']))) {
        $data['profile_close'] = date('Y-m-d', strtotime($data['profile_close']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //save profile to database
    $results = $profileSql->update($data);

    //link agent
    if (isset($data['profile_agent']) && $data['profile_agent'] != $profile['agent_profile_id']) {
        if ($profile['agent_profile_id']) {
            $profileSql->unlinkAgent($results['profile_id'], $profile['agent_profile_id']);
        }

        $profileSql->linkAgent($results['profile_id'], $data['profile_agent']);
    }

    //index profile
    $profileElastic->update($response->getResults('profile_id'));

    //invalidate cache
    $profileRedis->removeDetail($response->getResults('profile_id'));
    $profileRedis->removeSearch();

    // if (isset($profile['deal_id'])
    //     && !empty($profile['deal_id'])) {
    //     $request->setStage('deal_id', $profile['deal_id']);
    //     $this->trigger('deal-update', $request, $response);
    // }

    //return response format
    $response->setError(false)->setResults($results);

    //update profile session
    if ($request->hasSession('me')) {
        $this->trigger('profile-session', $request, $response);
    }

    $request->setStage('profile_id', $results['profile_id']);

    if (isset($data['profile_image'])) {
        //try to queue, and if not
        if (!$this->package('global')->queue('profile-image-s3', $results)) {
            //hit the terms manually
            $this->trigger('profile-image-s3', $request, $response);
        }
    }
});

/**
 * Profile Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-update-credits', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $profile = $request->getStage('profile_id');

    //----------------------------//
    // 2. Validate Data
    if (!$profile) {
        return $response->setError(true, 'Invalid Parameters');
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $transactionSql = TransactionService::get('sql');
    $serviceSql = ServiceService::get('sql');

    $purchased = $transactionSql->getTotalCredits($profile);
    $used = $serviceSql->getTotalCredits($profile);
    $profileCredits = $purchased - $used;

    $profileRequest = new Request();

    $profileRequest
        ->load()
        ->setStage('profile_id', $profile)
        ->setStage('profile_credits', max(0, $profileCredits));

    $this->trigger('profile-update', $profileRequest, $response);

    cradle()->trigger('profile-detail', $request, $response);
    $profileDetail = $response->getResults();

    // if credit is less than 20
    if ($profileCredits < 21) {
        $request->setStage([
            'profile_name' => $profileDetail['profile_name'],
            'profile_email' => $profileDetail['profile_email']
        ]);

        $data = $request->getStage();

        // queue the send if queueing is available
        if (!$this->package('global')->queue('profile-credits-notify-mail', $data)) {
            // if no queue manually do it
            $this->trigger('profile-credits-notify-mail', $request, $response);
        }
    }

    // check for auth id and update
    if (isset($data['auth_id'])) {
        $this->trigger('auth-update', $request, $response);
    }
});

/**
 * Move Profile Image to S3 (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-image-s3', function ($request, $response) {
    //try cdn if enabled
    $config = $this->package('global')->service('s3-main');

    if (!$config) {
        return;
    }

    if ($response->hasResults('profile_id')) {
        $request->setStage('profile_id', $response->getResults('profile_id'));
    }

    //get the profile detail
    $this->trigger('profile-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data
    $data = $response->getResults();

    //post banner
    if (trim($data['profile_image'])) {
        //is it base 64 ?
        if (strpos($data['profile_image'], 'data:') === 0) {
            $data['profile_image'] = File::base64ToS3($data['profile_image'], $config);
        } else {
            $data['profile_image'] = File::linkToS3($data['profile_image'], $config);
        }
    }

    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //save to database
    $results = $profileSql->update([
    'profile_id' => $data['profile_id'],
    'profile_image' => $data['profile_image']
    ]);

    //remove from index
    $profileElastic->update($data['profile_id']);

    //invalidate cache
    $profileRedis->removeDetail($data['profile_id']);
    $profileRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * unsubscribe profile
 *
 * @param Request $request
 * @param Response $response
 */

$cradle->on('profile-unsubscribe', function ($request, $response) {
    $data = $request->getStage();

    // update type _subscribe to 0
    // add unsubscribe to tags
    $request->setStage('profile_subscribe', 0);
    $request->setStage('add_tags', ['unsubscribed']);
    cradle()->trigger('profile-update', $request, $response);

    if ($response->isError()) {
        return $response->setError(true, 'Invalid Request');
    }

    // update campaign unsubsribe count
    $campaignRequest = new Request();
    $campaignResponse = new Response();

    $campaignRequest->setStage(
        'filter',
        ['campaign_message_id' => $data['message_id']]
    );

    cradle()->trigger('campaign-search', $campaignRequest, $campaignResponse);
    $campaign = $response->getResults('rows');

    if ($campaign) {
        $campaign = $campaign[0];

        $campaignRequest->setStage('campaign_id', $campaign['campaign_id']);
        $campaignRequest->setStage('field', 'unsubscribed');
        cradle()->trigger('campaign-update', $request, $response);
    }

    $actionData = [
        'action_event' => 'profile-unsubscribe',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'profile-unsubscribe');
        $actionRequest->setStage('profile_id', $data['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $story = cradle('global')->config('story', 'profile-unsubscribe');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    $response->setError(false);
});

/**
 * Update Profile Bounce
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-update-bounce', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the subscriber detail
    $this->trigger('profile-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $profile = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    // if soft bounce update flag
    if ($data['bounce_type'] == 'Transient') {
        //save profile to database
        $request->setStage('profile_bounce', $profile['profile_bounce'] + 1);
        $this->trigger('profile-update', $request, $response);

        // if soft bounced 2 times already plus this transaction
        // it means it has bounced 3 times, so we'll unsubscribe it
        if ($profile['profile_bounce'] >= 2) {
            $this->trigger('profile-unsubscribe', $request, $response);
        }
    }

    // if permanent remove profile
    if ($data['bounce_type'] == 'Permanent') {
        $this->trigger('profile-unsubscribe', $request, $response);
    }

    $results = $response->getResults();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Applicant Inform About Job post Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-post-inform', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the applicant detail
    $this->trigger('profile-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    $data = $response->getResults();

    //get the applicant detail
    $this->trigger('post-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    $post = $response->getResults();

    //----------------------------//
    // 3. Prepare Data
    if (!trim($data['profile_email'])) {
        return;
    }

    $email = $this->package('global')->service('mail-main');
    $config = $this->package('global')->service('ses');

    //----------------------------//
    // 4. Send Email Notification

    $emailData = [];
    $emailData['from'] = $config['sender'];

    $name = $data['profile_name'];

    $emailData['subject'] = $this->package('global')->translate('AN EMPLOYER WANTS TO REACH OUT TO YOU!');

    $handlebars = $this->package('global')->handlebars();

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];

    $link = $host
        .'/profile/tracking/application/seeker/update/'
        .$request->getStage('post_id').'/'
        .$data['profile_id'];

    $profileLink = $host . '/' . $data['profile_slug'] . '/profile-post';

    $contents = file_get_contents(__DIR__ . '/template/email/inform.txt');
    $textTemplate = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/inform.html');
    $htmlTemplate = $handlebars->compile($contents);

    $emailData['to'] = [];
    $emailData['to'][] = $data['profile_email'];

    $defaultAvatar = $host.'/images/avatar/avatar-' . ($data['profile_id'] % 5) . '.png';

    $newData = [
        'profile_name' => $name,
        'profile_avatar' => $defaultAvatar,
        'post_name' => $post['post_name'],
        'post_position' => $post['post_position'],
        'post_location' => $post['post_location'],
        'post_experience' => $post['post_experience'],
        'profile_link' => $profileLink,
        'profile_slug' => $data['profile_slug'],
        'host' => $host,
        'link' => $link
    ];

    $data = array_merge($data, $newData);

    //text
    $emailData['text'] = $textTemplate($data);

    //html
    $emailData['html'] = $htmlTemplate($data);

    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);

    $results = $data;

    $response->setError(false)->setResults($results);
});

/**
 * Profile Credits Notify Mail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-credits-notify-mail', function ($request, $response) {
    $mailConfig = $this->package('global')->service('mail-main');

    //form link
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $addCreditLink = $host . '/profile/credit/checkout';
    $quotationLink = $host . '/contact-us?request=quote';

    //prepare data
    $data = [];
    $data['from'] = $mailConfig['name'];
    $data['to'] = [];
    $data['to'][] = $request->getStage('profile_email');
    $data['subject'] = $this->package('global')->translate('Running Low on Credits! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();
    $contents = file_get_contents(__DIR__ . '/template/email/credits.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host' => $host,
        'addCreditLink' => $addCreditLink,
        'quotationLink' => $quotationLink,
        'profile_name' => $request->getStage('profile_name')
    ]);

    // send email
    $request->setStage($data);
    $this->trigger('send-smtp-email', $request, $response);

    if (!$response->isError()) {
        $response->setError(false);
    }
});

/**
 * Profile search for profiles that are less than 20 credits then sends an email
 *
 * This will be run by a cron
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-search-low-credits', function ($request, $response) {
    $newRequest = new Request();
    $newResponse = new Response();

    //get the profiles
    $this->trigger('profile-search-poster-credits', $newRequest, $newResponse);
    $resultSet = $newResponse->getResults();

    //if there's an error
    if ($newResponse->isError()) {
        return;
    }

    // loop through the result and set: profile_name and email
    foreach ($resultSet['rows'] as $key => $value) {
        $newRequest->setStage('profile_name', $value['profile_name']);
        $newRequest->setStage('profile_email', $value['profile_email']);

        $data = [
            'profile_name' => $value['profile_name'],
            'profile_email' => $value['profile_email']
        ];

        // check if queued
        if (!$this->package('global')->queue('profile-credits-notify-mail', $data)) {
            // run the job for setting up the email and sending
            $this->trigger('profile-credits-notify-mail', $newRequest, $newResponse);
        }
    }
});

$cradle->on('queue-insufficient-credit', function ($request, $response) {
    // run every 14 days
    $runEvery = strtotime('+14 days') - strtotime('now');
    // set date and time to send to queue for checking
    $current = (int) date('Hi');

    // queue sending low credit customers email
    if ($this->package('global')
        ->queue()
        ->send('profile-search-low-credits')
            && $current >= 0000 && $current <= 0200) {
        $this->trigger('profile-search-low-credits', $request, $response);
    }

    // after running all the queues requeue itself
    if ($this->package('global')
        ->queue()
        ->setDelay($runEvery)
        ->send('queue-insufficient-credit')
            && $current >= 0000 && $current <= 0200) {
        $this->trigger('queue-insufficient-credit', $request, $response);
    }
});

$cradle->on('profile-search-poster-credits', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    // no validation needed

    //----------------------------//
    // 3. Prepare Data
    // set filters, range and indicator for less credit
    $data['filter'] = [
        'profile_active' => '1',
        'type' => 'poster'
    ];
    $data['range'] = 0;
    $data['less_credit'] = 1;

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $profileRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $profileElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->getInsufficientCreditProfiles($data);
        }

        if ($results) {
            //cache it from database or index
            $profileRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Updates profiles with stories. Execute through cli.
 *
 * @param $request
 * @param $response
 */
$cradle->on('profile-update-story', function ($request, $response) {

    // Signed Up - assumes all records in profiles table have signed up
    // query
    $request->setStage(
        'filter_string',
        'profile_story NOT LIKE \'%Signed Up%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', ''); // which table to join
    $request->setStage('story', 'Signed Up'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Update Credentials - auth created != update
    // query
    $request->setStage(
        'filter_string',
        'auth_created <> auth_updated AND profile_story
        NOT LIKE \'%Update Credentials%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'auth'); // which table to join
    $request->setStage('story', 'Update Credentials'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Verified Email - auth table
    // query
    $request->setStage(
        'filter_string',
        'auth_active = 1 AND profile_story NOT LIKE \'%Verified Email%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'auth'); // which table to join
    $request->setStage('story', 'Verified Email'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Updated Profile
    // query
    $request->setStage(
        'filter_string',
        'profile_created <> profile_updated AND profile_story
        NOT LIKE \'%Updated Profile%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', ''); // which table to join
    $request->setStage('story', 'Updated Profile'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Gained Achievement
    // query
    $request->setStage(
        'filter_string',
        'profile_achievements IS NOT NULL AND profile_story
        NOT LIKE \'%Gained Achievement%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', ''); // which table to join
    $request->setStage('story', 'Gained Achievement'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Gained Experience
    // query
    $request->setStage(
        'filter_string',
        'profile_experience IS NOT NULL AND profile_experience > 0
        AND profile_story NOT LIKE \'%Gained Experience%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', ''); // which table to join
    $request->setStage('story', 'Gained Experience'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // User Unsubscribed
    // query
    $request->setStage(
        'filter_string',
        'profile_subscribe = 0 AND profile_story NOT LIKE \'%User Unsubscribed%\'
        OR profile_story IS NULL'
    );
    $request->setStage('inner_join', ''); // which table to join
    $request->setStage('story', 'User Unsubscribed'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // User Email Bounced
    // query
    $request->setStage(
        'filter_string',
        'profile_bounce > 0 AND profile_story NOT LIKE \'%User Email Bounced%\'
        OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'post'); // which table to join
    $request->setStage('story', 'User Email Bounced'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Created a Post
    // query
    $request->setStage(
        'filter_string',
        'profile_story NOT LIKE \'%Created a Post%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'post'); // which table to join
    $request->setStage('story', 'Created a Post'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Updated a Post
    // query
    $request->setStage(
        'filter_string',
        'post.post_created <> post.post_updated
        AND profile_story NOT LIKE \'%Updated a Post%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'post'); // which table to join
    $request->setStage('story', 'Updated a Post'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Downloaded Resume
    // query
    $request->setStage(
        'filter_string',
        'service_active = 1 AND service_name = \'Resume Download\'
        AND profile_story NOT LIKE \'%Downloaded Resume%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'service'); // which table to join
    $request->setStage('story', 'Downloaded Resume'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Interested a Post
    // query
    $request->setStage(
        'filter_string',
        'profile_story NOT LIKE \'%Interested a Post%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'liked'); // which table to join
    $request->setStage('story', 'Interested a Post'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Notified for a like
    // query
    $request->setStage(
        'filter_string',
        'post_notify LIKE \'%likes%\'
        AND post_like_count > 0 AND profile_story NOT LIKE \'%Notified for a like%\'
        OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'post'); // which table to join
    $request->setStage('story', 'Notified for a like'); // what story to add
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);

    // Buys Credits - get from transaction table
    $request->setStage(
        'filter_string',
        'profile_story NOT LIKE \'%Buy Credits%\' OR profile_story IS NULL'
    );
    $request->setStage('inner_join', 'transaction');
    $request->setStage('story', 'Buy Credits');
    // update the records
    $this->trigger('profile-update-story-loop', $request, $response);
});

/**
 * Loops in chunks to prevent memory leak.
 *
 * @param $request
 * @param $response
 */
$cradle->on('profile-update-story-loop', function ($request, $response) {
    //this/these will be used a lot
    $profileSql = ProfileService::get('sql');
    $data = [];
    $data['filter'][] = 'profile_active = 1'; // get the active ones only
    $data['filter'][] = $request->getStage('filter_string'); // the AND-ORs
    // check if inner join is needed
    if ($request->hasStage('inner_join') && !empty($request->getStage('inner_join'))) {
        $data[$request->getStage('inner_join')] = true;
    }

    $profileStory = array();
    $profileStory[] = $request->getStage('story'); // theres always a story to tell
    $range = 1000;

    // get records in chunks to prevent PHP memory leak
    while (true) {
        $data['range'] = $range;
        //get it from database
        $results = $profileSql->searchByFilters($data);
        foreach ($results['rows'] as $key => $column) {
            // these are the data that we all need
            $request->setStage('profile_id', $column['profile_id']);
            $request->setStage('add_story', $profileStory);
            $this->trigger('profile-update', $request, $response);
        }

        // if empty break the while-loop
        if ($results['total'] == 0) {
            break;
        }

        $range += 1000; // get next 1000 records
    }
});

/**
 * Email Profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-message', function ($request, $response) {
    $email = $this->package('global')->service('mail-main');
    $config = $this->package('global')->service('ses');
    $settings = $this->package('global')->config('settings');

    $data = $request->getStage();

    $server = $request->getServer();
    $data['host'] = $settings['host'];
    $data['link'] = $settings['host'] . '/' . 'Job-Seekers/' .$data['sender']['profile_slug'];

    // Check if the sender is a company
    if (!empty($data['sender']['profile_company'])) {
        $data['link'] = $settings['host'] . '/' . 'Companies/' .$data['sender']['profile_slug'];
    }

    $emailData = [];
    $emailData['from'] = $config['sender'];

    $emailData['subject'] = $this->package('global')->translate('Someone sent a message to you!');

    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/message.html');
    $htmlTemplate = $handlebars->compile($contents);

    $emailData['to'] = [];
    $emailData['to'][] = $data['receiver']['profile_email'];

    //html
    $emailData['html'] = $htmlTemplate($data);

    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);

    $response->remove('json', 'results');
    $response->setError(false);
});

/**
 * Profile Resume
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-resume', function ($request, $response) {
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
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        // $results = $profileRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $profileElastic->getProfileResume($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $profileSql->getProfileResume($id);
        }

        if ($results) {
            //cache it from database or index
            // $profileRedis->createDetail($id, $results);
        }
    }

    $response->setError(false)->setResults($results);
});

/**
 * Profile Resume
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('profile-export-csv', function ($request, $response) {
    // get data
    $data = $request->getStage();
    // get session
    $me = $data['session'];

    // date filter
    $dates = $request->getStage('date');
    $invalidDates = false;

    // date range error
    $dateError = cradle('global')->translate('Exporting of more than 1 month of records is not yet allowed for now. Please filter by date range');

    // must have date range
    if (!$dates
        || !isset($dates['start_date'])
        || !isset($dates['end_date'])
        || empty($dates['start_date'])
        || empty($dates['end_date'])) {
        $invalidDates = true;
    } else {
        $startdate = new DateTime($dates['start_date']);
        $enddate = new DateTime($dates['end_date']);

        // get date difference
        $diff = $enddate->diff($startdate)->format("%a");

        if ($diff > 31) {
            $invalidDates = true;
        }
    }

    if ($invalidDates) {
        // notify client side for errors
        return (new Queue)->setExchange('jobayan-admin-export')
            ->setData(array(
                'event' => 'export-error',
                'data' => [
                    'message' => $dateError,
                    'session_id' => $me['session_id'],
                    'type' => 'profile'
                ]
            ))->publish();
    }

    // notify client side if export is in progress
    (new Queue)->setExchange('jobayan-admin-export')
        ->setData(array(
            'event' => 'export-progress',
            'data' => [
                'session_id' => $me['session_id'],
                'type' => 'profile'
            ],
        ))->publish();

    // trigger profile search job
    $request->setGet('noindex', 1);
    $request->setGet('nocache', 1);
    cradle()->trigger('profile-search', $request, $response);
    // set result data
    $data = array_merge($request->getStage(), $response->getResults());

    // set csv headers
    $header = [
        'profile_id'              => 'Profile ID',
        'profile_name'            => 'Profile Name',
        'profile_email'           => 'Profile Email',
        'profile_slug'            => 'Profile Slug',
        'profile_phone'           => 'Profile Phone',
        'profile_detail'          => 'Profile Detail',
        'profile_company'         => 'Profile Company',
        'profile_active'          => 'Profile Active',
        'profile_type'            => 'Profile Type',
        'profile_gender'          => 'Profile Gender',
        'profile_birth'           => 'Profile Birthdate',
        'profile_address_street'  => 'Address Street',
        'profile_address_city'    => 'Address City',
        'profile_address_state'   => 'Address State',
        'profile_address_country' => 'Address Country',
        'profile_address_postal'  => 'Address Postal',
    ];

    // set csv filename, headers, and data
    $request->setStage('filename', 'Profiles-' . date('Y-m-d-His') . '.csv');
    $request->setStage('header', $header);
    $request->setStage('csv', $data['rows']);

    // upload csv to s3
    cradle()->trigger('csv-s3-export', $request, $response);
    // get results
    $results = $response->getResults();

    // notify client side if export is completed
    (new Queue)->setExchange('jobayan-admin-export')
        ->setData(array(
            'event' => 'export-complete',
            'data' => array_merge($results, [
                'session_id' => $me['session_id'],
                'type' => 'profile'
            ])
        ))->publish();

    // set response
    return $response->setError(false)->setResults($results);
});
