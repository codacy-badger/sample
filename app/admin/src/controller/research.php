<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Research Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:research:listing', 'admin', $request)) {
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
            'research_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('research-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-research-search page-admin';
    $data['title'] = cradle('global')->translate('Researches');
    $body = cradle('/app/admin')->template('research/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Research Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:research:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/research/search');
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
    $class = 'page-admin-research-create page-admin';
    $data['title'] = cradle('global')->translate('Create Research');
    $body = cradle('/app/admin')->template('research/form', $data, [
        'research_position',
        'research_location',
        'research_position-location'
        ]);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Research Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/update/:research_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:research:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/research/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('research-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/research/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-research-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Research');
    $body = cradle('/app/admin')->template('research/form', $data, [
        'research_position',
        'research_location',
        'research_position-location'
        ]);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Research Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/research/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if research_position has no value make it null
    if ($request->hasStage('research_position') && !$request->getStage('research_position')) {
        $request->setStage('research_position', null);
    }

    //if research_location has no value make it null
    if ($request->hasStage('research_location') && !$request->getStage('research_location')) {
        $request->setStage('research_location', null);
    }

    //research_flag is disallowed
    $request->removeStage('research_flag');

    if (!$request->hasStage('profile_id')) {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('research_id', $response->getResults('research_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created research ');
    $request->setStage('history_attribute', 'research-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('research-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/research/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Research was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/research/search');
});

/**
 * Process the Research Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/research/update/:research_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    //if research_position has no value make it null
    if ($request->hasStage('research_position') && !$request->getStage('research_position')) {
        $request->setStage('research_position', null);
    }

    //if research_location has no value make it null
    if ($request->hasStage('research_location') && !$request->getStage('research_location')) {
        $request->setStage('research_location', null);
    }

    //research_flag is disallowed
    $request->removeStage('research_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('research-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/research/update/' . $request->getStage('research_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('research_id', $response->getResults('research_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated research id ' . $response->getResults('research_id'));
    $request->setStage('history_attribute', 'research-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Research was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/research/search');
});

/**
 * Process the Research Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/remove/:research_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:research:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/research/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('research-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('research_id', $response->getResults('research_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed research id #' . $response->getResults('research_id'));
    $request->setStage('history_attribute', 'research-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Research was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/research/search');
});

/**
 * Render the Research Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/detail/:research_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:research:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/research/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/research/update/%s',
            $request->getStage('research_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Research Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/research/restore/:research_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:research:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/research/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('research-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('research_id', $response->getResults('research_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored research id #' . $response->getResults('research_id'));
    $request->setStage('history_attribute', 'research-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Research was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/research/search');
});
