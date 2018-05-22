<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Position Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:position:listing', 'admin', $request)) {
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

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'position_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('position-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-position-search page-admin';
    $data['title'] = cradle('global')->translate('Positions');
    $body = cradle('/app/admin')->template('position/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Position Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:position:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/position/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $request->setStage('filter', ['position_type' => 'parent']);
    cradle()->trigger('position-search', $request, $response);
    $data['parents'] = $response->getResults('rows');

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-position-create page-admin';
    $data['title'] = cradle('global')->translate('Create Position');
    $body = cradle('/app/admin')->template('position/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Position Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/update/:position_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:position:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/position/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('position-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/position/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $request->setStage('filter', ['position_type' => 'parent']);
    cradle()->trigger('position-search', $request, $response);
    $data['parents'] = $response->getResults('rows');

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-position-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Position');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate(ucwords($data['item']['position_name']) . ' Details');
    }
    $body = cradle('/app/admin')->template('position/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Position Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/detail/:position_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:position:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/position/search');
    }

    // set view to true to handle view settings
    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/position/update/%s',
            $request->getStage('position_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Position Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/position/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');
    //----------------------------//
    // 2. Prepare Data

    //if position_description has no value make it null
    if ($request->hasStage('position_description') && !$request->getStage('position_description')) {
        $request->setStage('position_description', null);
    }

    //position_flag is disallowed
    $request->removeStage('position_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('position-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/position/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('position_id', $response->getResults('position_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created position '
                        . ucfirst($request->getStage('position_name')));
    $request->setStage('history_attribute', 'position-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Position was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/position/search');
});

/**
 * Process the Position Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/position/update/:position_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if position_description has no value make it null
    if ($request->hasStage('position_description') && !$request->getStage('position_description')) {
        $request->setStage('position_description', null);
    }

    //position_flag is disallowed
    $request->removeStage('position_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('position-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/position/update/' . $request->getStage('position_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('position_id', $response->getResults('position_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated position id ' . $response->getResults('position_id'));
    $request->setStage('history_attribute', 'position-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Position was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/position/search');
});

/**
 * Process the Position Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/remove/:position_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:position:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/position/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('position-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('position_id', $response->getResults('position_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed position id #' . $response->getResults('position_id'));
    $request->setStage('history_attribute', 'position-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Position was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/position/search');
});

/**
 * Process the Position Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/position/restore/:position_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:position:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/position/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('position-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('position_id', $response->getResults('position_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored position id #' . $response->getResults('position_id'));
    $request->setStage('history_attribute', 'position-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Position was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/position/search');
});
