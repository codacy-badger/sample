<?php //-->
/**
 * This file is part of the Dealcha Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Signup Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/signup', function ($request, $response) {
    //redirect
    $redirect = urlencode('/control');
    cradle('global')->redirect('/signup?redirect_uri=' . $redirect);
});

/**
 * Render the Login Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/login', function ($request, $response) {
    //redirect
    $redirect = urlencode('/control/transaction/search');
    cradle('global')->redirect('/login?redirect_uri=' . $redirect);
});

/**
 * Render the Account Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/account', function ($request, $response) {
    //redirect
    $redirect = urlencode('/control');
    cradle('global')->redirect('/account?redirect_uri=' . $redirect);
});

/**
 * Render the Auth Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/auth/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:auth:listing', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'auth_slug',
            'auth_type',
            'auth_created'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'type',
            'auth_active',
            'profile_id',
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        //TODO::enable to export without limit
        $request->setStage('range', 100);
        $request->setGet('noindex', true);
    }

    //sort desc
    $request->setStage('order', 'auth_id', 'DESC');

    //this will include
    $request->setStage('profile', 1);

    //trigger job
    cradle()->trigger('auth-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'auth_id'          => 'Auth ID',
            'auth_slug'        => 'Auth Slug',
            'auth_token'       => 'Auth Token',
            'auth_secret'      => 'Auth Secret',
            'auth_permissions' => 'Auth Permissions',
            'auth_active'      => 'Auth Active',
            'auth_type'        => 'Auth Type',
            'auth_flag'        => 'Auth Flag',
            'auth_created'     => 'Auth Created',
            'auth_updated'     => 'Auth Updated'
        ];

        //convert post_notify from array to
        foreach ($data['rows'] as $index => $row) {
            if (is_array($row['auth_permissions']) && !is_null($row['auth_permissions'])) {
                $data['rows'][$index]['auth_permissions'] = implode(', ', $row['auth_permissions']);
            }
        }

        //Set Filename
        $request->setStage('filename', 'Auth-' . date("Y-m-d") . ".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        // check permission
        if (!cradle('global')->role('admin:auth:export', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/auth/search');
        }
        cradle()->trigger('csv-export', $request, $response);

        $value = [
            'SERVER' => $_SERVER
        ];
        $request->setStage('auth_id', $response->getResults('auth_id'));
        $request->setStage('profile_id', $_SESSION['me']['profile_id']);
        $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a auth file');
        $request->setStage('history_attribute', 'auth-export');
        $request->setStage('history_value', $value);

        cradle()->trigger('history-create', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-admin-auth-search page-admin';
    $data['title'] = cradle('global')->translate('Auth');
    $body          = cradle('/app/admin')->template('auth/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Auth Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/auth/update/:auth_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:auth:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/auth/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];
    //default permissions
    $data['listPermissions'] = [
        'public_profile',
        'personal_profile',
        'user_profile',
        'admin_profile',
        'marketing_dashboard',
        'business_dashboard'
    ];

    //if no item
    if (empty($data['item'])) {
        //retrieve all columns
        $request->setStage('all', true);
        //trigger get detail
        cradle()->trigger('auth-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');

            return cradle('global')->redirect('/control/auth/search');
        }

        $data['item'] = $response->getResults();
    }

    //list active and inactive permissions
    foreach ($data['listPermissions'] as $index => $permission) {
        if (isset($data['item']['auth_permissions'])
            && in_array($permission, $data['item']['auth_permissions'])) {
            $data['permissions'][$permission] = 1;
        } else {
            $data['permissions'][$permission] = 0;
        }
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $roleRequest  = Cradle\Http\Request::i();
    $roleResponse = Cradle\Http\Response::i();

    cradle()->trigger('role-search', $roleRequest, $roleResponse);

    if ($roleResponse->getResults('rows')) {
        $data['roles'] = $roleResponse->getResults();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-developer-auth-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Auth');

    if ($request->hasStage('view')) {
        $data['view']  = true;
        $data['title'] = cradle('global')->translate('Viewing Auth Details');
    }
    $body = cradle('/app/admin')->template('auth/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Auth Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/auth/detail/:auth_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:auth:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/auth/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/auth/update/%s',
            $request->getStage('auth_id')
        ),
        $request,
        $response
    );
});
/**
 * Process the Auth Remove
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/auth/remove/:auth_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:auth:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/auth/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-remove', $request, $response);

    $data  = $request->getStage();
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('auth_id', $response->getResults('auth_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed auth id #' . $response->getResults('auth_id'));
    $request->setStage('history_attribute', 'auth-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Auth was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/auth/search');
});

/**
 * Process the Auth Restore
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/auth/restore/:auth_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');
    // check permission
    if (!cradle('global')->role('admin:auth:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/auth/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-restore', $request, $response);

    $data  = $request->getStage();
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('auth_id', $response->getResults('auth_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored auth id #' . $response->getResults('auth_id'));
    $request->setStage('history_attribute', 'auth-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Auth was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/auth/search');
});

/**
 * Process the Auth Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/auth/update/:auth_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //if auth_website has no value make it null
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->removeStage('auth_password');
    }

    if ($request->hasStage('auth_website') && !$request->getStage('auth_website')) {
        $request->setStage('auth_website', null);
    }

    //if auth_facebook has no value make it null
    if ($request->hasStage('auth_facebook') && !$request->getStage('auth_facebook')) {
        $request->setStage('auth_facebook', null);
    }

    //if auth_linkedin has no value make it null
    if ($request->hasStage('auth_linkedin') && !$request->getStage('auth_linkedin')) {
        $request->setStage('auth_linkedin', null);
    }

    //if auth_twitter has no value make it null
    if ($request->hasStage('auth_twitter') && !$request->getStage('auth_twitter')) {
        $request->setStage('auth_twitter', null);
    }

    //if auth_google has no value make it null
    if ($request->hasStage('auth_google') && !$request->getStage('auth_google')) {
        $request->setStage('auth_google', null);
    }

    //if auth_regenerate. new token and secret will be applied
    if ($request->hasStage('auth_regenerate')) {
        $request->setStage('auth_token', md5(uniqid()));
        $request->setStage('auth_secret', md5(uniqid()));
    } else {
        //auth_token is disallowed
        $request->removeStage('auth_token');

        //auth_secret is disallowed
        $request->removeStage('auth_secret');
    }

    //auth_flag is disallowed
    $request->removeStage('auth_flag');

    if (empty($request->getStage('auth_password'))) {
        $request->removeStage('auth_password');
    }

    if (!$request->hasStage('auth_password') && empty($request->getStage('confirm'))) {
        $request->removeStage('confirm');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/auth/update/' . $request->getStage('auth_id');

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data  = $request->getStage();
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('auth_id', $response->getResults('auth_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' updated auth id ' . $response->getResults('auth_id'));
    $request->setStage('history_attribute', 'auth-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Auth was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/auth/update/' . $request->getStage('auth_id'));
});
