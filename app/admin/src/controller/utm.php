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
 * Render the Utm Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:utm:listing', 'admin', $request)) {
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
            'utm_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('utm-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-utm-search page-admin';
    $data['title'] = cradle('global')->translate('UTMs');
    $body = cradle('/app/admin')->template('utm/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Utm Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:utm:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/utm/search');
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

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-utm-create page-admin';
    $data['title'] = cradle('global')->translate('Create UTM');
    $body = cradle('/app/admin')->template('utm/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Utm Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/update/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');


    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:utm:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/utm/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('utm-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/utm/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-utm-update page-admin';
    $data['title'] = cradle('global')->translate('Updating UTM');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate(ucwords($data['item']['utm_title']) . ' Details');
    }

    $body = cradle('/app/admin')->template('utm/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Utm Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/detail/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:utm:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/utm/search');
    }

    // set view to true to handle view settings
    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/utm/update/%s',
            $request->getStage('utm_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Utm Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/utm/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if utm_source has no value make it null
    if ($request->hasStage('utm_source') && !$request->getStage('utm_source')) {
        $request->setStage('utm_source', null);
    }

    //if utm_medium has no value make it null
    if ($request->hasStage('utm_medium') && !$request->getStage('utm_medium')) {
        $request->setStage('utm_medium', null);
    }

    //if utm_detail has no value make it null
    if ($request->hasStage('utm_detail') && !$request->getStage('utm_detail')) {
        $request->setStage('utm_detail', null);
    }

    //if utm_image has no value make it null
    if ($request->hasStage('utm_image') && !$request->getStage('utm_image')) {
        $request->setStage('utm_image', null);
    }

    //utm_type is disallowed
    $request->removeStage('utm_type');

    //utm_flag is disallowed
    $request->removeStage('utm_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/utm/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('utm_id', $response->getResults('utm_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created position '
                        . ucfirst($request->getStage('utm_title')));
    $request->setStage('history_attribute', 'utm-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('UTM was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/utm/search');
});

/**
 * Process the Utm Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/utm/update/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if utm_source has no value make it null
    if ($request->hasStage('utm_source') && !$request->getStage('utm_source')) {
        $request->setStage('utm_source', null);
    }

    //if utm_medium has no value make it null
    if ($request->hasStage('utm_medium') && !$request->getStage('utm_medium')) {
        $request->setStage('utm_medium', null);
    }

    //if utm_detail has no value make it null
    if ($request->hasStage('utm_detail') && !$request->getStage('utm_detail')) {
        $request->setStage('utm_detail', null);
    }

    //if utm_image has no value make it null
    if ($request->hasStage('utm_image') && !$request->getStage('utm_image')) {
        $request->setStage('utm_image', null);
    }

    //utm_type is disallowed
    $request->removeStage('utm_type');

    //utm_flag is disallowed
    $request->removeStage('utm_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/utm/update/' . $request->getStage('utm_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('utm_id', $response->getResults('utm_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated utm id ' . $response->getResults('utm_id'));
    $request->setStage('history_attribute', 'utm-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);
    //it was good
    //add a flash
    cradle('global')->flash('UTM was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/utm/search');
});

/**
 * Process the Utm Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/remove/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:utm:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/utm/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('UTM was Removed');
        cradle('global')->flash($message, 'success');
    }


    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('utm_id', $response->getResults('utm_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed utm id #' . $response->getResults('utm_id'));
    $request->setStage('history_attribute', 'utm-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    cradle('global')->redirect('/control/utm/search');
});

/**
 * Process the Utm Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/utm/restore/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:utm:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/utm/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('UTM was Restored');
        cradle('global')->flash($message, 'success');
    }


    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('utm_id', $response->getResults('utm_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored utm id #' . $response->getResults('utm_id'));
    $request->setStage('history_attribute', 'utm-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    cradle('global')->redirect('/control/utm/search');
});
