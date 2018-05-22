<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Service Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:service:listing', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'service_active', '1');
    }
    
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_id',
            'profile_name',
            'service_credits'
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
            'service_active',
            'service_name',
            'profile_id',
            'profile_name'
        ];
        foreach ($request->getStage('filter') as $key => $value) {
            // Checks if the filter is not allowed
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //sort desc
    if (!$request->hasStage('order')) {
        $request->setStage('order', 'profile_name', 'DESC');
    }


    // Checks for export action
    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }

    $data = $request->getStage();

    $request->setGet('noindex', true);

    //trigger job
    cradle()->trigger('service-search', $request, $response);

    $data = array_merge($request->getStage(), $response->getResults());

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'profile_name'    => 'Profile Name',
            'service_name'    => 'Service Name',
            'service_credits' => 'Credits'
        ];
        //Set Filename
        $request->setStage('filename', 'Services-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        // check permission
        if (!cradle('global')->role('admin:service:export', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/service/search');
        }
        cradle()->trigger('csv-export', $request, $response);

        $data = $request->getStage();
        $value = ['GET' => $request->getStage(),
                  'POST' => $request->getPost(),
                  'SERVER' => $_SERVER];
        $request->setStage('service_id', $response->getResults('service_id'));
        $request->setStage('profile_id', $_SESSION['me']['profile_id']);
        $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a file services');
        $request->setStage('history_attribute', 'service-export');
        $request->setStage('history_value', $value);

        cradle()->trigger('history-create', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-service-search page-admin';
    $data['title'] = cradle('global')->translate('Services');
    $body = cradle('/app/admin')->template('service/search', $data);
    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Service Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/create/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:service:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/service/search');
    }
    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-service-create page-admin';
    $data['title'] = cradle('global')->translate('Create Service');
    $body = cradle('/app/admin')->template('service/form', $data);
    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
    //render page
}, 'render-admin-page');

/**
 * Render the Service Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/update/:service_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:service:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/service/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('service-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/service/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-service-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Service');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate($data['item']['service_name'] . ' Details');
    }
    $body = cradle('/app/admin')->template('service/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * View the Service Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/detail/:service_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:service:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/service/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/service/update/%s',
            $request->getStage('service_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Service Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/service/create/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //if service_name has no value make it null
    if ($request->hasStage('service_name') && !$request->getStage('service_name')) {
        $request->setStage('service_name', null);
    }

    //service_type is disallowed
    $request->removeStage('service_type');

    //service_flag is disallowed
    $request->removeStage('service_flag');
    if (!$request->hasStage('profile_id')) {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $profile = $request->getStage('profile_id');
        return cradle()->triggerRoute('get', '/control/service/create/' . $profile, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('service_id', $response->getResults('service_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created service');
    $request->setStage('history_attribute', 'service-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Service was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/service/search');
});

/**
 * Process the Service Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/service/update/:service_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //if service_name has no value make it null
    if ($request->hasStage('service_name') && !$request->getStage('service_name')) {
        $request->setStage('service_name', null);
    }

    //service_type is disallowed
    $request->removeStage('service_type');

    //service_flag is disallowed
    $request->removeStage('service_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/service/update/' . $request->getStage('service_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('service_id', $response->getResults('service_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated service id #' . $response->getResults('service_id'));
    $request->setStage('history_attribute', 'service-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Service was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/service/search');
});

/**
 * Process the Service Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/remove/:service_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:service:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/service/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('service_id', $response->getResults('service_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed service id #' . $response->getResults('service_id'));
    $request->setStage('history_attribute', 'service-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Service was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/service/search');
});

/**
 * Process the Service Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/service/restore/:service_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:service:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/service/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('service_id', $response->getResults('service_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored service id #' . $response->getResults('service_id'));
    $request->setStage('history_attribute', 'service-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Service was Restored');
        cradle('global')->flash($message, 'success');
    }
    
    cradle('global')->redirect('/control/service/search');
});
