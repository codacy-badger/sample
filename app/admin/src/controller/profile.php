<?php //-->
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);

/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;
use Cradle\Sql\SqlFactory;

/**
 * Render the Profile Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:listing', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    // if import is done
    if ($request->hasStage('done') && $request->getStage('done') == 'true') {
        //set message
        $message = ' ['. $_SESSION['profile']['successCreateCtr']. '] Profile Created <br>' .' ['.
                   $_SESSION['profile']['errorCtr']. '] Error(s) <br>' . '['.
                   $_SESSION['profile']['existsCtr']. '] Exist(s) <br/><br/>' . '['.
                   $_SESSION['post']['successCreateCtr'] . '] Post Created <br>' .' ['.
                   $_SESSION['post']['errorCtr']. '] Error(s) <br>' . '['.
                   $_SESSION['post']['existsCtr']. '] Exist(s)';

        if ($_SESSION['profile']['errorCtr'] > 0) {
            $message .= ' Errors: ' . (implode(' ', $_SESSION['profile']['errors']));
        }

        if ($_SESSION['post']['errorCtr'] > 0) {
            $message .= ' Errors: ' . (implode(' ', $_SESSION['post']['errors']));
        }

        $messageType = $_SESSION['profile']['errorCtr'] > 0 ? 'danger' : 'success';

        if ($messageType === 'success') {
            $messageType = $_SESSION['post']['errorCtr'] > 0 ? 'danger' : 'success';
        }

        cradle('global')->flash($message, $messageType, 10000);
        unset($_SESSION['profile']);
        unset($_SESSION['post']);

        //redirect
        $redirect = '/control/profile/search';
        if ($request->getStage('redirect_uri')) {
            $redirect = $request->getStage('redirect_uri');
        }

        cradle('global')->redirect($redirect);
        exit;
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'profile_active', '1');
    }

    if ($request->hasStage('filter', 'profile_active')
        && $request->getStage('filter', 'profile_active') == 0) {
        $request->setStage('filter', 'profile_active', '0');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_id',
            'profile_name',
            'profile_slug',
            'profile_company',
            'profile_email',
            'profile_phone',
            'profile_credits',
            'profile_phone',
            'profile_type',
            'profile_created'

        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'type',
            'profile_active',
            'profile_company,'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            // Checks if the filter is not allowed
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    $keyword = [];
    if ($request->hasStage('select_filter') && $request->hasStage('q')) {
        $keyword = $originalKeyword = $request->getStage('q');

        if (is_array($keyword) && isset($keyword[0])) {
            $keyword = $keyword[0];
        }

        if (!empty($keyword)) {
            $setFilter = 'like_filter';
            if ($request->getStage('select_filter') == 'profile_id') {
                $setFilter = 'filter';
            }

            $request->setStage(
                $setFilter,
                $request->getStage('select_filter'),
                $keyword
            );

            $request->removeStage('q');
        }
    }

    //sort desc
    if (!$request->getStage('order', 'profile_id')) {
        $request->setStage('order', 'profile_id', 'DESC');
    }

    $request->setStage('auth_profile', true);
    $request->setGet('noindex', true);

    // trigger job
    cradle()->trigger('profile-search', $request, $response);

    // set result data
    $data = array_merge($request->getStage(), $response->getResults());
    $data['q'] = $keyword;

     //convert profile_package from array to
    foreach ($data['rows'] as $index => $row) {
        if (is_array($row['profile_package']) && !is_null($row['profile_package'])) {
            $data['rows'][$index]['profile_package'] = implode(', ', $row['profile_package']);
        }
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-profile-search page-admin';
    $data['title'] = cradle('global')->translate('Profiles');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body = cradle('/app/admin')->template('profile/search', $data, ['profile_banner']);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Profile Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/profile/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $data['achievements'] = cradle('global')->config('achievements');

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-profile-create page-admin';
    $data['title'] = cradle('global')->translate('Create Profile');
    $body = cradle('/app/admin')->template('profile/form', $data, ['profile_banner']);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:profile:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/profile/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('profile-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/profile/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //get all post by profile
    $request->setStage('post_expires', true);
    $request->setStage('filter', 'profile_id', $request->getStage('profile_id'));
    $request->setGet('noindex', true);
    $request->setStage('range', 0); // solution for including old posts
    cradle()->trigger('post-search', $request, $response);
    $data['posts'] = $response->getResults();
    $data['total_email_count'] = 0;
    $data['total_sms_match_count'] = 0;
    $data['total_sms_interested_count'] = 0;

    //get total count sms | email
    if ($data['posts']['total'] > 0) {
        foreach ($data['posts']['rows'] as $key => $value) {
            $data['total_sms_match_count'] += $value['post_sms_match_count'];
            $data['total_email_count'] += $value['post_email_count'];
            $data['total_sms_interested_count'] += $value['post_sms_interested_count'];
        }
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    $data['achievements'] = cradle('global')->config('achievements');

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-profile-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Profile');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate(ucwords($data['item']['profile_name']) . ' Details');
    }
    $body = cradle('/app/admin')->template('profile/form', $data, [
        'profile_banner']);

    //Set Content
    $response
        ->setPage('profile_banner', 'profile_banner')
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Profile Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/detail/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/profile/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/profile/update/%s',
            $request->getStage('profile_id')
        ),
        $request,
        $response
    );
});

/**
 * Resends the email verification to the user again
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/resend-verification/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:resend-verification', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data

    cradle()->trigger('auth-profile-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/profile/search');
    }

    $data['item'] = $response->getResults();

    //just to be sure remove sensitive data
    if (isset($data['item']['auth_id'])) {
        unset($data['item']['auth_password']);
        unset($data['item']['auth_token']);
        unset($data['item']['auth_secret']);
    }

    if (!empty($data['item']['auth_slug'])) {
        //resend verification email
        //if (!$this->package('global')->queue('auth-verify-mail', $data['item'])) {
            $request->setStage($data['item']);
            cradle()->trigger('auth-verify-mail', $request, $response);
        //}
    }

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/profile/search');
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' resends verification');
    $request->setStage('history_attribute', 'profile-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //redirect page
    cradle('global')->flash('Verification Email Successfully Sent!', 'success');
    return cradle('global')->redirect('/control/profile/search');
});

/**
 * Process the Profile Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/profile/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //if profile_image has no value make it null
    if ($request->hasStage('profile_image') && !$request->getStage('profile_image')) {
        $request->setStage('profile_image', null);
    }

    //if profile_email has no value make it null
    if ($request->hasStage('profile_email') && !$request->getStage('profile_email')) {
        $request->setStage('profile_email', null);
    }

    //if profile_phone has no value make it null
    if ($request->hasStage('profile_phone') && !$request->getStage('profile_phone')) {
        $request->setStage('profile_phone', null);
    }

    //profile_slug is disallowed
    $request->removeStage('profile_slug');

    //if profile_detail has no value make it null
    if ($request->hasStage('profile_detail') && !$request->getStage('profile_detail')) {
        $request->setStage('profile_detail', null);
    }

    //if profile_job has no value make it null
    if ($request->hasStage('profile_job') && !$request->getStage('profile_job')) {
        $request->setStage('profile_job', null);
    }

    //if profile_gender has no value use the default value
    if ($request->hasStage('profile_gender') && !$request->getStage('profile_gender')) {
        $request->setStage('profile_gender', 'unknown');
    }

    //if profile_verified has no value use the default value
    if ($request->hasStage('profile_verified') && !$request->getStage('profile_verified')) {
        $request->setStage('profile_verified', 0);
    }

    //if profile_birth has no value make it null
    if ($request->hasStage('profile_birth') && !$request->getStage('profile_birth')) {
        $request->setStage('profile_birth', null);
    }

    //if profile_website has no value make it null
    if ($request->hasStage('profile_website') && !$request->getStage('profile_website')) {
        $request->setStage('profile_website', null);
    }

    //if profile_facebook has no value make it null
    if ($request->hasStage('profile_facebook') && !$request->getStage('profile_facebook')) {
        $request->setStage('profile_facebook', null);
    }

    //if profile_linkedin has no value make it null
    if ($request->hasStage('profile_linkedin') && !$request->getStage('profile_linkedin')) {
        $request->setStage('profile_linkedin', null);
    }

    //if profile_twitter has no value make it null
    if ($request->hasStage('profile_twitter') && !$request->getStage('profile_twitter')) {
        $request->setStage('profile_twitter', null);
    }

    //if profile_google has no value make it null
    if ($request->hasStage('profile_google') && !$request->getStage('profile_google')) {
        $request->setStage('profile_google', null);
    }

    //profile_type is disallowed
    $request->removeStage('profile_type');

    //profile_flag is disallowed
    $request->removeStage('profile_flag');

    // json encode profile package
    $request->setStage('profile_package', json_encode($request->getStage('profile_package')));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/profile/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created profile');
    $request->setStage('history_attribute', 'profile-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Profile was Created', 'success');

    if ($request->getStage('profile_verified') == 1) {
        $request->setStage('profile_achievement', 'verified_company');
        $request->setStage('profile_id', $response->getResults('profile_id'));
        cradle()->trigger('profile-add-achievement', $request, $response);
    }

    if ($request->getStage('profile_verified') == 2) {
        $request->setStage('profile_achievement', 'verified_recruiter');
        $request->setStage('profile_id', $response->getResults('profile_id'));
        cradle()->trigger('profile-add-achievement', $request, $response);
    }

    //redirect
    cradle('global')->redirect('/control/profile/search');
});

/**
 * Process the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');
    //----------------------------//
    // 2. Prepare Data
    //if profile_parent has no value make it null
    if ($request->hasStage('profile_parent') && !$request->getStage('profile_parent')) {
        $request->setStage('profile_parent', 0);
    }

    //if profile_image has no value make it null
    if ($request->hasStage('profile_image') && !$request->getStage('profile_image')) {
        $request->setStage('profile_image', null);
    }

    //if profile_email has no value make it null
    if ($request->hasStage('profile_email') && !$request->getStage('profile_email')) {
        $request->setStage('profile_email', null);
    }

    //if profile_phone has no value make it null
    if ($request->hasStage('profile_phone') && !$request->getStage('profile_phone')) {
        $request->setStage('profile_phone', null);
    }

    //if profile_slug has no value make it null
    if ($request->hasStage('profile_slug') && !$request->getStage('profile_slug')) {
        $request->setStage('profile_slug', null);
    }

    //if profile_detail has no value make it null
    if ($request->hasStage('profile_detail') && !$request->getStage('profile_detail')) {
        $request->setStage('profile_detail', null);
    }

    //if profile_job has no value make it null
    if ($request->hasStage('profile_job') && !$request->getStage('profile_job')) {
        $request->setStage('profile_job', null);
    }

    //if profile_gender has no value use the default value
    if ($request->hasStage('profile_gender') && !$request->getStage('profile_gender')) {
        $request->setStage('profile_gender', 'unknown');
    }

    //if profile_birth has no value make it null
    if ($request->hasStage('profile_birth') && !$request->getStage('profile_birth')) {
        $request->setStage('profile_birth', null);
    }

    //if profile_website has no value make it null
    if ($request->hasStage('profile_website') && !$request->getStage('profile_website')) {
        $request->setStage('profile_website', null);
    }

    //if profile_facebook has no value make it null
    if ($request->hasStage('profile_facebook') && !$request->getStage('profile_facebook')) {
        $request->setStage('profile_facebook', null);
    }

    //if profile_linkedin has no value make it null
    if ($request->hasStage('profile_linkedin') && !$request->getStage('profile_linkedin')) {
        $request->setStage('profile_linkedin', null);
    }

    //if profile_twitter has no value make it null
    if ($request->hasStage('profile_twitter') && !$request->getStage('profile_twitter')) {
        $request->setStage('profile_twitter', null);
    }

    //if profile_google has no value make it null
    if ($request->hasStage('profile_google') && !$request->getStage('profile_google')) {
        $request->setStage('profile_google', null);
    }

    // update posts email related to this profile
    if ($request->hasStage('check_email')) {
        $results = [
            'profile_id' => $request->getStage('profile_id'),
            'profile_email' => $request->getStage('profile_email')
        ];

        if (!$this->package('global')->queue('post-update-email', $results)) {
            cradle()->trigger('post-update-email', $request, $response);
        }
    }

    // update posts email related to this profile
    if ($request->hasStage('check_phone')) {
        $results = [
            'profile_id' => $request->getStage('profile_id'),
            'profile_phone' => $request->getStage('profile_phone')
        ];

        if (!$this->package('global')->queue('post-update-phone', $results)) {
            cradle()->trigger('post-update-phone', $request, $response);
        }
    }

    if ($request->hasStage('profile_banner')
        && empty($request->getStage('profile_banner'))) {
        $request->removeStage('profile_banner');
    }

    //profile_type is disallowed
    $request->removeStage('profile_type');

    //profile_flag is disallowed
    $request->removeStage('profile_flag');

    //set default package
    if (!$request->hasStage('profile_package')) {
        $request->setStage('profile_package', []);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/profile/update/' . $request->getStage('profile_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated profile id ' . $response->getResults('profile_id'));
    $request->setStage('history_attribute', 'profile-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Profile was Updated', 'success');

    if ($request->getStage('profile_verified') == 1) {
        $request->setStage('profile_achievement', 'verified_company');
        $request->setStage('profile_id', $response->getResults('profile_id'));
        cradle()->trigger('profile-add-achievement', $request, $response);
    }

    if ($request->getStage('profile_verified') == 2) {
        $request->setStage('profile_achievement', 'verified_recruiter');
        $request->setStage('profile_id', $response->getResults('profile_id'));
        cradle()->trigger('profile-add-achievement', $request, $response);
    }

    //redirect
    $redirect = '/control/profile/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Profile Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/remove/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/profile/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed profile id #' . $response->getResults('profile_id'));
    $request->setStage('history_attribute', 'profile-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Profile was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/profile/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Profile Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/restore/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/profile/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored profile id #' . $response->getResults('profile_id'));
    $request->setStage('history_attribute', 'profile-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Profile was Restored');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/profile/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});


/**
 * Process the Profile Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/claim/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:profile:send-claim-email', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-claim', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id']
                        . ' sends claim email to Profile id #' . $response->getResults('profile_id'));
    $request->setStage('history_attribute', 'profile-claim');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Claim email has been sent successfully');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/profile/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/profile/import', function ($request, $response) {
    $columns = array(
        'keys' => array(
            'profile_name',
            'profile_email',
            'profile_slug',
            'profile_phone',
            'profile_detail',
            'profile_company',
            'profile_active',
            'profile_type',
            'profile_gender',
            'profile_birth',
            'profile_address_street',
            'profile_address_city',
            'profile_address_state',
            'profile_address_country',
            'profile_address_postal',
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/profile/search');
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' imported a profile csv');
    $request->setStage('history_attribute', 'profile-import');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;

    foreach ($data['csv'] as $key => $value) {
        $request->setStage($value);
        $request->setStage('profile_type', 'import');
        if (!isset($value['profile_id']) || empty($value['profile_id'])) {
            cradle()->trigger('profile-create', $request, $response);
        } else {
            cradle()->trigger('profile-update', $request, $response);
        }

        //get error response
        if ($response->isError()) {
            if ($response->getValidation()) {
                $errors[] = '<br>#'. $value['profile_name'] .' - '. implode(' ', $response->getValidation());
                $errorCtr++;
            }
        } else {
            if (!$value['profile_id']) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Profile Created <br>' .' ['. $successUpdateCtr. '] Profile Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 20000);

    //redirect
    $redirect = '/control/profile/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process Profile Post the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/profile/post/import', function ($request, $response) {
    $columns = array(
        'keys' => array(
            'profile_name',
            'auth_password',
            'profile_email',
            'post_phone',
            'post_position',
            'post_industry',
            'post_location',
            'post_experience',
            'post_tags',
            'post_type'
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/profile/search');
    }

    $data = $response->getResults();

    // save data to session
    $_SESSION['profile']['data'] = $data;
    // tell users that you are importing profile and post
    cradle('global')->flash('Importing profile and post...', 'info');
    // redirect back to search
    cradle('global')->redirect('/control/profile/search?import=run');
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/profile/post/import/run', function ($request, $response) {
    //only for admin
    cradle('global')->requireLogin('admin');

    // check if data is set
    if (!isset($_SESSION['profile']['data']) || empty($_SESSION['profile']['data'])) {
        // set error message
        $content = ['error' => true,
                    'message'  => 'Nothing to import.'];
        // return response
        return $response->setContent($content);
    }

    $data = $_SESSION['profile']['data']['csv'];
    // check if there are data to import
    if (empty($data)) {
        // set error message
        $content = ['error' => true,
                    'message' => 'Nothing to import.'];
        // return response
        return $response->setContent($content);
    }

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
    // if page is 1, set all session variables
    if ($page <= 1) {
        $page = 1;
        $_SESSION['post'] = [];
        $_SESSION['profile']['successCreateCtr'] = $_SESSION['profile']['errorCtr'] = $_SESSION['profile']['existsCtr'] = 0;
        $_SESSION['profile']['errors'] = [];

        $_SESSION['post']['successCreateCtr'] = $_SESSION['post']['errorCtr'] = $_SESSION['post']['existsCtr'] = 0;
        $_SESSION['post']['errors'] = [];
    }
    // this is the csv index
    $index = $page - 1;
    // check if data[index] exist
    if (!isset($data[$index]) || empty($data[$index])) {
        // finish
        $content = ['error' => false,
                    'done' => true];
        return $response->setContent($content);
    }

    $value = $data[$index];

    if (!$request->hasStage('auth_permissions')) {
        $request->setStage('auth_permissions', [
            'public_profile',
            'personal_profile'
        ]);
    }

    $request->setStage($value);

    if (isset($value['post_tags'])) {
        //stripped whitespace/s
        $postTags = array_map('trim', explode(',', $value['post_tags']));

        if (empty($value['post_tags'])) {
            $postTags = [];
        }

        // include post industry to tags
        if (!empty($value['post_industry'])) {
            $postTags[] = trim(strtolower($value['post_industry']));
        }

        // set post tags
        $request->setStage(
            'post_tags',
            $postTags
        );

        // if empty
        if (empty($postTags)) {
            $request->removeStage('post_tags');
        }
    }

    $request->setStage('auth_password', $value['auth_password']);
    $request->setStage('confirm', $value['auth_password']);

    // request for subdomain and reference check
    $authRequest = \Cradle\Http\Request::i();
    // response for subdomain and reference check
    $authResponse = \Cradle\Http\Response::i();

    $authRequest->setStage('auth_slug', $value['profile_email']);

    cradle()->trigger('auth-detail', $authRequest, $authResponse);

    $auth = [];
    if (!empty($authResponse->getResults())) {
        $_SESSION['profile']['existsCtr']++;
        $auth = $authResponse->getResults();
    } else {
        $request->setStage('auth_active', 1);
        // means coming from import
        $request->setStage('auth_flag', 3);
        cradle()->trigger('auth-create', $request, $response);

        if ($response->isError()) {
            $_SESSION['profile']['errors'][] = '<br>#'. $value['profile_name'] .' - '. implode(' ', $response->getValidation());
            $_SESSION['profile']['errorCtr']++;

            $content = ['error' => false,
                        'message' => $page . '/' . count($data),
                        'percentage' => ($page / count($data)) * 100,
                        'page' => $page];

            return $response->setContent($content);
        } else {
            $_SESSION['profile']['successCreateCtr']++;
            $auth = $response->getResults();
        }
    }

    $database = SqlFactory::load(cradle('global')->service('sql-main'));

    $searchPost = $database
        ->search('post')
        ->filterByPostPosition($request->getStage('post_position'))
        ->filterByPostLocation($request->getStage('post_location'))
        ->filterByPostEmail($request->getStage('profile_email'))
        ->filterByPostActive(1)
        ->getRow();

    $postCreated = false;
    // check profile post to avoid duplication
    if (!$searchPost) {
        $postType = empty($value['post_type']) ? 'seeker' : strtolower($value['post_type']);

        // set seeker as default
        if (!in_array($postType, ['seeker', 'poster'])) {
            $postType = 'seeker';
        }

        // set post parameters
        $request->setStage('post_type', $postType);
        $request->setStage('post_email', $value['profile_email']);
        $request->setStage('post_name', utf8_encode(trim(preg_replace('/\s+/', ' ', $value['profile_name']))));
        $request->setStage('post_phone', utf8_encode($value['post_phone']));
        $request->setStage('post_position', utf8_encode(trim(preg_replace('/\s+/', ' ', $value['post_position']))));
        $request->setStage('post_location', utf8_encode(trim(preg_replace('/\s+/', ' ', $value['post_location']))));

        $request->setStage('profile_id', $auth['profile_id']);
        // flag as 3 means coming from import
        $request->setStage('post_flag', 3);

        // post create job
        cradle()->trigger('post-create', $request, $response);
        if (!$response->isError()) {
            $postCreated = true;
        }
    } else {
        $_SESSION['post']['existsCtr']++;
    }

    //get error response
    if ($postCreated && $response->isError()) {
        if ($response->getValidation()) {
            $_SESSION['post']['errors'][] = '<br>#'. $value['profile_name'] .' - '. implode(' ', $response->getValidation());
            $_SESSION['post']['errorCtr']++;
        }
    } else {
        if ($postCreated) {
            $_SESSION['post']['successCreateCtr']++;
        }
    }

    $content = ['error' => false,
                'message' => $page . '/' . count($data),
                'percentage' => ($page / count($data)) * 100,
                'page' => $page];

    return $response->setContent($content);
});
