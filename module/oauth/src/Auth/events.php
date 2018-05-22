<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Oauth\Auth\Service as AuthService;
use Cradle\Module\Oauth\Auth\Validator as AuthValidator;

use Cradle\Module\Profile\Validator as ProfileValidator;

use Cradle\Module\Utility\Email;

/**
 * Auth Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
        $data['auth_slug'] = $data['profile_email'];
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getCreateErrors($data);
    if (!$request->getStage('profile')) {
        $errors = ProfileValidator::getCreateErrors($data, $errors);
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Registration Failed')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //salt on password
    $data['auth_password'] = md5($data['auth_password']);

    //deflate permissions
    $data['auth_permissions'] = json_encode($data['auth_permissions']);

    //active account checker
    $authActive = 0;
    if (isset($data['auth_active'])) {
        $authActive = $data['auth_active'];
    }

    // check if auth is for claiming profile
    if ($request->getStage('profile')) {
        $authActive = 1;
    }

    $data['auth_active'] = $authActive;

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //save item to database
    $results = $authSql->create($data);

    // check if auth is for claiming profile
    if ($request->getStage('profile')) {
        $request->setStage('profile_id', $request->getStage('profile'));
        $this->trigger('profile-detail', $request, $response);
    } else {
        //also create profile
        $this->trigger('profile-create', $request, $response);
    }

    $results = array_merge($results, $response->getResults());

    //link item to profile
    $authSql->linkProfile($results['auth_id'], $results['profile_id']);

    //index item
    // $authElastic->create($results['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    //set response format
    $response->setError(false)->setResults($results);

    //send mail
    $request->setSoftStage($response->getResults());
    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    $authRequest  = Cradle\Http\Request::i();
    $authResponse  = Cradle\Http\Response::i();

    if ($request->hasStage('profile_type')
        && $request->getStage('profile_type') == 'interested') {
        //try to queue, and if not
        // if (!$this->package('global')->queue('auth-interested-mail', $data)) {
            //send mail manually
            $this->trigger('auth-interested-mail', $request, $authResponse);
        // }

        return;
    // check if auth is for claiming profile
    } else if (!$request->getStage('profile')
        //social login auto verified
        && !isset($results['auth_facebook_token'])
        && !isset($results['auth_linkedin_token'])
        && $data['auth_active'] === 0
    ) {
        //try to queue, and if not
        // if (!$this->package('global')->queue('auth-verify-mail', $data)) {
            //send mail manually
            $this->trigger('auth-verify-mail', $request, $authResponse);
        // }
    }

    $actionData = [
        'action_event' => 'auth-create',
        'profile_id' => $results['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'auth-create');
        $actionRequest->setStage('profile_id', $results['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // At this point all the checking if user exists is done.
    // Now, check if the signer's email is in the leads table
    $leadRequest  = Cradle\Http\Request::i();
    $leadResponse  = Cradle\Http\Response::i();
    $leadRequest->getStage('lead_email', $results['profile_email']);
    cradle()->trigger('lead-detail', $leadRequest, $leadResponse);
    $lead = $leadResponse->getResults();
    // has results?
    if ($leadResponse->getResults()) {
        // if so, add: Lead and Signed Up to stage('add_tags')
        // so that marketing know that this user came from the leads table
        $leadRequest->setStage('add_tags', array('Lead', 'Signed Up'));
        // don't forget about profile_story
        $leadRequest->setStage('add_story', array('Lead'));
        $leadRequest->getStage('profile_id', $results['profile_id']);
        cradle()->trigger('profile-update', $leadRequest, $leadResponse);
        $leadRequest->setStage('lead_id', $lead['lead_id']);
        cradle()->trigger('lead-remove', $leadRequest, $leadResponse);
    }
    // add story
    $story = cradle('global')->config('story', 'auth-create');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $results['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);
});

/**
 * Auth Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['auth_id'])) {
        $id = $data['auth_id'];
    } else if (isset($data['auth_slug'])) {
        $id = $data['auth_slug'];
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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $authRedis->getDetail($id);
    }

    $allColumns = false;
    //add flag to filter all columns
    if (isset($data['all']) && $data['all'] == 1) {
        $allColumns = true;
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $authElastic->get($id, $allColumns);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $authSql->get($id, $allColumns);
        }

        if ($results) {
            //cache it from database or index
            $authRedis->createDetail($id, $results);
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

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Forgot Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-forgot', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

    if ($response->isError()) {
        // check if profile exist then create auth
        if ($response->getJson()['message'] == 'Not Found') {
            $request->setStage('filter', 'profile_email', $request->getStage('auth_slug'));

            $this->trigger('profile-search', $request, $response);
            // if there's a profile
            if ($response->getResults('total') > 0) {
                //get profile detail
                $profile = $response->getResults('rows')[0];

                //set auth data
                $request->setStage('profile', $profile['profile_id']);
                $request->setStage('profile_id', $profile['profile_id']);
                $request->setStage('profile_email', $profile['profile_email']);

                //set defaults
                if (!$request->hasStage('auth_permissions')) {
                    $request->setStage('auth_permissions', [
                        'public_profile',
                        'personal_profile'
                    ]);
                }

                //auth password
                $tempPassword = md5(rand());
                $request->setStage('auth_password', $tempPassword);
                $request->setStage('confirm', $tempPassword);

                // create auth
                $this->trigger('auth-create', $request, $response);
            } else {
                // not found
                return $response->setError(true, 'Not Found');
            }
        }
    }

    $user = $response->getResults();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 3. Validate Data
    //validate
    $errors = AuthValidator::getForgotErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //send mail
    $request->setSoftStage($response->getResults());

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    //try to queue, and if not
    // if (!$this->package('global')->queue('auth-forgot-mail', $data)) {
        //send mail manually
        $this->trigger('auth-forgot-mail', $request, $response);
    // }

    $actionData = [
        'action_event' => 'auth-forgot',
        'profile_id' => $user['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'auth-forgot');
        $actionRequest->setStage('profile_id', $user['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'auth-forgot');
    $storyRequest->setStage('profile_id', $user['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    //return response format
    $response->setError(false);
});

/**
 * Auth Forgot Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-forgot-mail', function ($request, $response) {
    $config = $this->package('global')->service('ses');

    //form hash
    $authId = $request->getStage('auth_id');
    $authUpdated = $request->getStage('auth_updated');
    $hash = md5($authId.$authUpdated);

    //form link
    $host = $request->getStage('host');
    $link = $host . '/recover/' . $authId . '/' . $hash;

    //prepare data
    $data = [];
    $data['from'] = $config['sender'];

    $data['to'] = [$request->getStage('auth_slug')] ;

    $data['subject'] = $this->package('global')->translate('Password Recovery from Jobayan! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/recover.txt');
    $template = $handlebars->compile($contents);
    $data['text'] = $template(['link' => $link]);

    $contents = file_get_contents(__DIR__ . '/template/email/recover.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host' => $host,
        'link' => $link
    ]);

    $request->setStage($data);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Auth Login Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-login', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getLoginErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Log in Failed')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Process Data
    $this->trigger('auth-detail', $request, $response);
});

/**
 * Auth SSO Login Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-sso-login', function ($request, $response) {
    //get data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //this/these will be used a lot
    $authSql = AuthService::get('sql');

    //load up the detail
    $this->trigger('auth-detail', $request, $response);

    if ($request->getStage('profile') && !$response->isError()) {
        return $response->setError(true);
    }

    //if there's an error
    if ($response->isError()) {
        //they don't exist
        $this->trigger('auth-create', $request, $response);
    }

    $response->setError(false)->remove('json', 'message');

    // if auth is not active yet, update
    if (!$response->getResults('auth_active')) {
        $request->setStage('auth_active', 1);
        $request->setStage('auth_id', $response->getResults('auth_id'));
        $this->trigger('auth-update', $request, $response);
    }

    //load up the detail
    $this->trigger('auth-detail', $request, $response);
});

/**
 * Auth Recover Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-recover', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getRecoverErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Change Password Failed')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //update
    $this->trigger('auth-update', $request, $response);

    //return response format
    $response->setError(false);
});

/**
 * Auth Refresh Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-refresh', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //save item to database
    $results = $authSql->update([
        'auth_id' => $data['auth_id'],
        'auth_token' => md5(uniqid()),
        'auth_secret' => md5(uniqid())
    ]);

    //index item
    // $authElastic->update($data['auth_id']);

    //invalidate cache
    $authRedis->removeDetail($data['auth_id']);
    $authRedis->removeDetail($data['auth_slug']);
    $authRedis->removeSearch();

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //save item to database
    $results = $authSql->update([
        'auth_id' => $data['auth_id'],
        'auth_active' => 0
    ]);

    // $authElastic->update($response->getResults('auth_id'));

    //invalidate cache
    $authRedis->removeDetail($data['auth_id']);
    $authRedis->removeDetail($data['auth_slug']);
    $authRedis->removeSearch();

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Refresh Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-profile-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $authSql = AuthService::get('sql');

    $results = $authSql->getAuthProfile($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //save item to database
    $results = $authSql->update([
        'auth_id' => $data['auth_id'],
        'auth_active' => 1
    ]);

    //remove from index
    // $authElastic->create($data['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-search', function ($request, $response) {
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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $authRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $authElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $authSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $authRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $user = $response->getResults();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getUpdateErrors($data);

    //check for profile errors if profile is being updated
    if (isset($data['profile_id'])) {
        $errors = ProfileValidator::getUpdateErrors($data, $errors);
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if (isset($data['auth_password'])) {
        //salt on password
        $data['auth_password'] = md5($data['auth_password']);
    }

    //deflate permissions
    if (isset($data['auth_permissions'])) {
        $data['auth_permissions'] = json_encode($data['auth_permissions']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //save item to database
    $results = $authSql->update($data);

    //index item
    // $authElastic->update($response->getResults('auth_id'));

    //invalidate cache
    $authRedis->removeDetail($response->getResults('auth_id'));
    $authRedis->removeDetail($response->getResults('auth_slug'));
    $authRedis->removeSearch();

    //if profile id
    if (isset($data['profile_id'])) {
        //also update profile
        $this->trigger('profile-update', $request, $response);
        $results = array_merge($results, $response->getResults());
    }

    $actionData = [
        'action_event' => 'auth-update',
        'profile_id' => $user['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
        // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'auth-update');
        $actionRequest->setStage('profile_id', $user['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // we don't have to initiate request because we really
    // have to update the profile
    $story = cradle('global')->config('story', 'auth-update');
    $request->setStage('add_story', [$story]);
    $this->trigger('profile-update', $request, $response);

    $results = array_merge($results, $user);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Verify Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-verify', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getVerifyErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //get the auth detail
    $this->trigger('auth-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $user = $response->getResults();

    //send mail
    $request->setSoftStage($response->getResults());

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    //----------------------------//
    // 3. Process Data
    //try to queue, and if not
    // if (!$this->package('global')->queue('auth-verify-mail', $data)) {
        //send mail manually
        $this->trigger('auth-verify-mail', $request, $response);
    // }

    $actionData = [
        'action_event' => 'auth-verify',
        'profile_id' => $user['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'auth-verify');
        $actionRequest->setStage('profile_id', $user['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    // pull event layman's equivalent term
    $story = cradle('global')->config('story', 'auth-verify');

    // add this event to profile_story
    $storyRequest->setStage('add_story', [$story]);
    $storyRequest->setStage('profile_id', $user['profile_id']);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    //return response format
    $response->setError(false);
});

/**
 * Auth Verify Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-verify-mail', function ($request, $response) {
    $config = $this->package('global')->service('ses');

    //form hash
    $authId = $request->getStage('auth_id');
    $authUpdated = $request->getStage('auth_updated');
    $hash = md5($authId.$authUpdated);
    $utm = '?utm_source=smtp&utm_medium=email&utm_campaign=Jobayan_verifyaccount';

    //form link
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $link = $host . '/activate/' . $authId . '/' . $hash . $utm;

    //prepare data
    $data = [];
    $data['from'] = $config['sender'];

    $data['to'] = [$request->getStage('auth_slug')];

    $data['subject'] = $this->package('global')->translate('Account Verification from Jobayan! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/verify.txt');
    $template = $handlebars->compile($contents);
    $data['text'] = $template(['link' => $link]);

    $contents = file_get_contents(__DIR__ . '/template/email/verify.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host' => $host,
        'link' => $link
    ]);

    $request->setStage($data);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Auth Claim Email
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-claim', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('profile-detail', $request, $response);

    if ($response->isError()) {
        return;
    }


    if (is_null($response->getResults('profile_email'))) {
        $response->setError(true, 'Profile Email is null')
            ->remove('json', 'results');

        return;
    }

    $user = $response->getResults();
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    // $authElastic = AuthService::get('elastic');

    //send mail
    $request->setSoftStage($response->getResults());

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    //----------------------------//
    // 3. Process Data
    //try to queue, and if not
    // if (!$this->package('global')->queue('auth-claim-mail', $data)) {
        //send mail manually
        $this->trigger('auth-claim-mail', $request, $response);
    // }

    if (!$response->isError()) {
        // set profile claim
        $request->setStage('profile_type', 'claim');
        $request->setStage('profile_flag', 1);

        // add story
        $story = cradle('global')->config('story', 'auth-claim');
        $request->setStage('add_story', [$story]);
        $this->trigger('profile-update', $request, $response);
    }

    $actionData = [
        'action_event' => 'auth-claim',
        'profile_id' => $user['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
         // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'auth-claim');
        $actionRequest->setStage('profile_id', $user['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // story is already added above

    //return response format
    $response->setError(false);
});

/**
 * Claim Profile Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-claim-mail', function ($request, $response) {
    $config = $this->package('global')->service('ses');
    // get data
    $data = $request->getStage();

    //form link
    // get reference id
    $hash = json_encode(['profile_id' => $data['profile_id']]);
    $hash = base64_encode($hash);
    // get suffix
    $suffix = substr($hash, -5);
    // rearrange referenc
    $hash =  $suffix . substr($hash, 0, -5);

    //form link
    if (!$request->getStage('host')) {
        $response->setError(true, 'Host is required')
            ->remove('json', 'results');

        return;
    }

    $host = $request->getStage('host');
    $profileLink = $host . '/' . $data['profile_slug'] . '/profile-post';
    $claimLink = $host . '/claim?ref=' . $hash;

    //prepare data
    $emailData = [];
    $emailData['from'] = $config['sender'];

    $emailData['to'] = [];
    $emailData['to'][] = $data['profile_email'];

    $emailData['subject'] = $this->package('global')->translate('Claim Profile from Jobayan! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/claim.txt');
    $template = $handlebars->compile($contents);
    $emailData['text'] = $template(['claimLink' => $claimLink]);

    $contents = file_get_contents(__DIR__ . '/template/email/claim.html');
    $template = $handlebars->compile($contents);
    $emailData['html'] = $template([
        'host' => $host,
        'profileLink' => $profileLink,
        'claimLink' => $claimLink
    ]);

    //send mail
    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Profile Interested Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-interested-mail', function ($request, $response) {
    $config = $this->package('global')->service('ses');

    //form hash
    $authId = $request->getStage('auth_id');
    $authUpdated = $request->getStage('auth_updated');
    $hash = md5($authId.$authUpdated);

    //form link
    $host = $request->getStage('host');
    $link = $host . '/activate/' . $authId . '/' . $hash;

    //prepare data
    $data = [];
    $data['from'] = $config['sender'];

    $data['to'] = [];
    $data['to'][] = $request->getStage('profile_email');

    $data['subject'] = $this->package('global')->translate('Account Verification from Jobayan! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/interested.txt');
    $template = $handlebars->compile($contents);
    $data['text'] = $template(['link' => $link]);
    $contents = file_get_contents(__DIR__ . '/template/email/interested.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host' => $host,
        'link' => $link,
        'company_name' => $request->getStage('company_name'),
        'company_image' => $request->getStage('company_image'),
    ]);

    //send mail
    $request->setStage($data);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Auth Get Total of Signups
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-search-chart', function ($request, $response) {
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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $authRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no results
        if (!$results) {
            //get it from database
            $results = $authSql->getChartTotalSignup($data);
        }

        if ($results) {
            //cache it from database or index
            $authRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});


/**
 * Joined Auth and Profile Search Using Profile ID only
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-profile-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $authSql = AuthService::get('sql');

    if (isset($data['marketing'])) {
        $results = $authSql->getProfileDetail($data);

        //set response format
        return $response->setError(false)->setResults($results);
    }

    $results = $authSql->getAuthProfileDetail($data);

    //set response format
    $response->setError(false)->setResults($results);
});
