<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 use Cradle\Module\Utility\File;

/**
 * Render the Feature Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:feature:listing', 'admin', $request)) {
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
        $request->setStage('filter', 'feature_active', '1');
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'feature_active',
            'feature_title'

        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    $request->setGet('noindex', true);

    //trigger job
    cradle()->trigger('feature-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-feature-search page-admin';
    $data['title'] = cradle('global')->translate('Features');
    $body = cradle('/app/admin')->template('feature/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Feature Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:feature:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/feature/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if (!isset($data['item']['feature_keywords'])) {
        $data['item']['feature_keywords'] = ['job search','job hiring','job listing'];
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-feature-create page-admin';
    $data['title'] = cradle('global')->translate('Create Feature');
    $body = cradle('/app/admin')->template('feature/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Feature Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/update/:feature_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:feature:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/feature/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('feature-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/feature/search');
        }

        $data['item'] = $response->getResults();
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-feature-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Feature');
    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate($data['item']['feature_name'] . ' Details');
    }

    $body = cradle('/app/admin')->template('feature/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Feature Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/detail/:feature_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:feature:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/feature/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/feature/update/%s',
            $request->getStage('feature_id')
        ),
        $request,
        $response
    );
});
/**
 * Process the Feature Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/feature/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if feature_type has no value use the default value
    if ($request->hasStage('feature_type') && !$request->getStage('feature_type')) {
        $request->setStage('feature_type', 'industry');
    }

    //if feature_color has no value use the default value
    if ($request->hasStage('feature_color') && !$request->getStage('feature_color')) {
        $request->setStage('feature_color', '#CA1551');
    }

    //if feature_subcolor has no value use the default value
    if ($request->hasStage('feature_subcolor') && !$request->getStage('feature_subcolor')) {
        $request->setStage('feature_subcolor', '#CA1551');
    }

    //if feature_image has no value make it null
    if ($request->hasStage('feature_image') && !$request->getStage('feature_image')) {
        $request->setStage('feature_image', null);
    }

    //if feature_map has no value make it null
    if ($request->hasStage('feature_map') && !$request->getStage('feature_map')) {
        $request->setStage('feature_map', null);
    }

    //if feature_description has no value make it null
    if ($request->hasStage('feature_description') && !$request->getStage('feature_description')) {
        $request->setStage('feature_description', null);
    }

    //if feature_keywords has no value make it null
    if ($request->hasStage('feature_keywords') && !$request->getStage('feature_keywords')) {
        $request->setStage('feature_keywords', null);
    }

    //if feature_slug has no value make it null
    if ($request->hasStage('feature_slug') && !$request->getStage('feature_slug')) {
        $request->setStage('feature_slug', null);
    }

    //if feature_detail has no value make it null
    if ($request->hasStage('feature_detail') && !$request->getStage('feature_detail')) {
        $request->setStage('feature_detail', null);
    }

    //if feature_links has no value make it null
    if ($request->hasStage('feature_links') && !$request->getStage('feature_links')) {
        $request->setStage('feature_links', null);
    } else if ($request->hasStage('feature_links')) {
        //remove the last element if its empty.
        $feature_links = $request->getStage('feature_links');
        if (end($feature_links) == '') {
            array_pop($feature_links);
        }

        $request->setStage('feature_links', $feature_links);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('feature-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/feature/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('feature_id', $response->getResults('feature_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created feature');
    $request->setStage('history_attribute', 'feature-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Feature was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/feature/search');
});

/**
 * Process the Feature Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/feature/update/:feature_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if feature_type has no value use the default value
    if ($request->hasStage('feature_type') && !$request->getStage('feature_type')) {
        $request->setStage('feature_type', 'Industry');
    }

    //if feature_color has no value use the default value
    if ($request->hasStage('feature_color') && !$request->getStage('feature_color')) {
        $request->setStage('feature_color', '#CA1551');
    }

    //if feature_subcolor has no value use the default value
    if ($request->hasStage('feature_subcolor') && !$request->getStage('feature_subcolor')) {
        $request->setStage('feature_subcolor', '#CA1551');
    }

    //if feature_image has no value make it null
    if ($request->hasStage('feature_image') && !$request->getStage('feature_image')) {
        $request->setStage('feature_image', null);
    }

    //if feature_map has no value make it null
    if ($request->hasStage('feature_map') && !$request->getStage('feature_map')) {
        $request->setStage('feature_map', null);
    }

    //if feature_description has no value make it null
    if ($request->hasStage('feature_description') && !$request->getStage('feature_description')) {
        $request->setStage('feature_description', null);
    }

    //if feature_keywords has no value make it null
    if ($request->hasStage('feature_keywords') && !$request->getStage('feature_keywords')) {
        $request->setStage('feature_keywords', null);
    }

    //if feature_slug has no value make it null
    if ($request->hasStage('feature_slug') && !$request->getStage('feature_slug')) {
        $request->setStage('feature_slug', null);
    }

    //if feature_detail has no value make it null
    if ($request->hasStage('feature_detail') && !$request->getStage('feature_detail')) {
        $request->setStage('feature_detail', null);
    }

    //if feature_links has no value make it null
    if ($request->hasStage('feature_links') && !$request->getStage('feature_links')) {
        $request->setStage('feature_links', null);
    } else {
        //remove the last element if its empty.
        $feature_links = $request->getStage('feature_links');
        if (end($feature_links) == '') {
            array_pop($feature_links);
        }

        $request->setStage('feature_links', $feature_links);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('feature-update', $request, $response);

     //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/feature/update/' . $request->getStage('feature_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('feature_id', $response->getResults('feature_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated feature id ' . $response->getResults('feature_id'));
    $request->setStage('history_attribute', 'feature-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Feature was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/feature/search');
});

/**
 * Process the Feature Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/remove/:feature_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:feature:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/feature/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('feature-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('feature_id', $response->getResults('feature_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed feature id #' . $response->getResults('feature_id'));
    $request->setStage('history_attribute', 'feature-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Feature was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/feature/search');
});

/**
 * Process the Feature Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/feature/restore/:feature_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('feature-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('feature_id', $response->getResults('feature_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored feature id #' . $response->getResults('feature_id'));
    $request->setStage('history_attribute', 'feature-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Feature was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/feature/search');
});
