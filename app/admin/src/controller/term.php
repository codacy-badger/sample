<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Term Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:term:listing', 'admin', $request)) {
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

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'term_active', '1');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'term_hits',
            'term_name'
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
            'term_active',
            'term_name',
            'term_type'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    // Checks for export action
    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }
    
    //trigger job
    cradle()->trigger('term-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());
    
    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'term_id' => 'Term ID',
            'term_active' => 'Term Active',
            'term_name' => 'Term Name',
            'term_hits' => 'Term Hits',
            'term_type' => 'Term Type',
            'term_flag' => 'Term Flag',
            'term_created' => 'Term Created',
            'term_updated' => 'Term Updated'
        ];

        //Set Filename
        $request->setStage('filename', 'Terms-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        // check permission
        if (!cradle('global')->role('admin:term:export', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/term/search');
        }
        cradle()->trigger('csv-export', $request, $response);

        $data = $request->getStage();
        $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
        $request->setStage('term_id', $response->getResults('term_id'));
        $request->setStage('profile_id', $_SESSION['me']['profile_id']);
        $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a term file');
        $request->setStage('history_attribute', 'term-export');
        $request->setStage('history_value', $value);

        cradle()->trigger('history-create', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-term-search page-admin';
    $data['title'] = cradle('global')->translate('Terms');
    $body = cradle('/app/admin')->template('term/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Term Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:term:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/term/search');
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
    $class = 'page-developer-term-create page-admin';
    $data['title'] = cradle('global')->translate('Create Term');
    $body = cradle('/app/admin')->template('term/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Term Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/update/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:term:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/term/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('term-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/term/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-term-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Term');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate('Viewing Term Details');
    }
    $body = cradle('/app/admin')->template('term/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Term Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/detail/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:term:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/term/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/term/update/%s',
            $request->getStage('service_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Term Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/term/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //term_name is disallowed
    $request->removeStage('term_name');

    //term_hits is disallowed
    $request->removeStage('term_hits');

    //term_type is disallowed
    $request->removeStage('term_type');

    //term_flag is disallowed
    $request->removeStage('term_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/term/create', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Term was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/term/search');
});

/**
 * Process the Term Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/term/update/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //term_name is disallowed
    $request->removeStage('term_name');

    //term_hits is disallowed
    $request->removeStage('term_hits');

    //term_type is disallowed
    $request->removeStage('term_type');

    //term_flag is disallowed
    $request->removeStage('term_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/term/update/' . $request->getStage('term_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Term was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/term/search');
});

/**
 * Process the Term Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/remove/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:term:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/term/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('term_id', $response->getResults('term_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed term id #' . $response->getResults('term_id'));
    $request->setStage('history_attribute', 'term-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Term was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/term/search');
});

/**
 * Process the Term Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/term/restore/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:term:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/blog/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('term_id', $response->getResults('term_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored term id #' . $response->getResults('term_id'));
    $request->setStage('history_attribute', 'term-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Term was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/term/search');
});
