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
 * Render the Post Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:post:listing', 'admin', $request)) {
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
        $request->setStage('filter', 'post_active', '1');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_created',
            'post_download_count',
            'post_email',
            'post_experience',
            'post_expires',
            'post_id',
            'post_like_count',
            'post_location',
            'post_name',
            'post_phone',
            'post_position',
            'post_type',
        ];

        // Loops through the orders
        foreach ($request->getStage('order') as $key => $direction) {
            // Checks if the sorting value is not in the allowed sorting
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                // Checks if the sorting
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_active',
            'post_location',
            'post_experience',
            'post_type',
            'post_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) || $value == '') {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    if ($request->getStage('profile')) {
        $request->setStage(
            'filter',
            'profile_id',
            $request->getStage('profile')
        );
    }

    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }

    if (!$request->hasStage('order')) {
        //sort desc
        $request->setStage('order', 'post_id', 'DESC');
    }

    $request->setStage('range', 10);

    //export inactive posts
    if ($request->hasStage('filter', 'post_active')
        && $request->getStage('filter', 'post_active') == '0') {
        $request->setStage('filter', 'post_active', '0');
    }

    $data = $request->getStage();

    if (isset($data['date']['start']) && $data['date']['end']) {
        $date = [
            'start_date' => $data['date']['start'],
            'end_date'   => $data['date']['end']
        ];
    }

    if (isset($data['date'])) {
        $date = $data['date'];
    }

    if (isset($date)) {
        $request->setStage('groupDate', ['post_created' => $date]);
    }

    $request->setGet('noindex', true);

    //trigger job
    cradle()->trigger('post-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class                = 'page-admin-post-search page-admin';
    $data['title']        = cradle('global')->translate('Posts');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body                 = cradle('/app/admin')->template('post/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Post Create Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:post:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/post/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config             = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-developer-post-create page-admin';
    $data['title'] = cradle('global')->translate('Create Post');
    $body          = cradle('/app/admin')->template('post/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Post Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/update/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');


    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:post:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/post/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config             = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('post-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');

            return cradle('global')->redirect('/control/post/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-developer-post-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Post');

    if ($request->hasStage('view')) {
        $data['view']  = true;
        $data['title'] = cradle('global')->translate($data['item']['post_name'] . ' Details');
    }
    $body = cradle('/app/admin')->template('post/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Post Detail Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/detail/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:post:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/post/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/post/update/%s',
            $request->getStage('post_id')
        ),
        $request,
        $response
    );
});
/**
 * Process the Post Create Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/post/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if post_email has no value make it null
    if ($request->hasStage('post_email') && !$request->getStage('post_email')) {
        $request->setStage('post_email', null);
    }

    //if post_phone has no value make it null
    if ($request->hasStage('post_phone') && !$request->getStage('post_phone')) {
        $request->setStage('post_phone', null);
    }

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_resume has no value make it null
    if ($request->hasStage('post_resume') && !$request->getStage('post_resume')) {
        $request->setStage('post_resume', null);
    }

    //if post_verify has no value make it null
    if ($request->hasStage('post_verify') && !$request->getStage('post_verify')) {
        $request->setStage('post_verify', null);
    }

    //if post_notify has no value make it null
    if ($request->hasStage('post_notify') && !$request->getStage('post_notify')) {
        $request->setStage('post_notify', null);
    }

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //if post_link has no value make it null
    if ($request->hasStage('post_link') && !$request->getStage('post_link')) {
        $request->setStage('post_link', null);
    }

    //if profile_id has no value, get it from request-get else get from session
    if ($request->hasStage('profile_id') && !$request->getStage('profile_id')) {
        $request->setStage('profile_id', $request->getGet('profile_id'));
    } else {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    //if post_banner has no value make it null
    if ($request->hasStage('post_banner') && !$request->getStage('post_banner')) {
        $request->setStage('post_banner', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/post/create', $request, $response);
    }

    $data  = $request->getStage();
    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created post');
    $request->setStage('history_attribute', 'post-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Post was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/post/search');
});

/**
 * Process the Post Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/post/update/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if post_email has no value make it null
    if ($request->hasStage('post_email') && !$request->getStage('post_email')) {
        $request->setStage('post_email', null);
    }

    //if post_phone has no value make it null
    if ($request->hasStage('post_phone') && !$request->getStage('post_phone')) {
        $request->setStage('post_phone', null);
    }

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_resume has no value make it null
    if ($request->hasStage('post_resume') && !$request->getStage('post_resume')) {
        $request->setStage('post_resume', null);
    }

    //if post_banner has no value make it null
    if ($request->hasStage('post_banner') && !$request->getStage('post_banner')) {
        $request->setStage('post_banner', null);
    }

    //if post_verify has no value make it null
    if ($request->hasStage('post_verify') && !$request->getStage('post_verify')) {
        $request->setStage('post_verify', null);
    }

    //if post_notify has no value make it null
    if ($request->hasStage('post_notify') && !$request->getStage('post_notify')) {
        $request->setStage('post_notify', null);
    }

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //if post_link has no value make it null
    if ($request->hasStage('post_link') && !$request->getStage('post_link')) {
        $request->setStage('post_link', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/post/update/' . $request->getStage('post_id');

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id'] . ' updated post id ' . $response->getResults('post_id')
    );
    $request->setStage('history_attribute', 'post-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Post was Updated', 'success');

    //redirect
    $redirect = '/control/post/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Post Remove
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/remove/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:post:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/post/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-remove', $request, $response);

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id'] . ' removed post id #' . $response->getResults('post_id')
    );
    $request->setStage('history_attribute', 'post-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Post was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/post/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Post Restore
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/restore/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-restore', $request, $response);

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id'] . ' restored post id #' . $response->getResults('post_id')
    );
    $request->setStage('history_attribute', 'post-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Post was Restored');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/post/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});


/**
 * Render the Post Copy Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/post/copy/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:post:copy', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/post/search');
    }

    cradle()->trigger('post-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/post/search');
    }

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id'] . ' copy post id ' . $response->getResults('post_id')
    );
    $request->setStage('history_attribute', 'post-copy');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    $request
        ->setPost($response->getResults())
        ->setPost('action', '/control/post/create');

    cradle()->triggerRoute('get', '/control/post/create', $request, $response);
});

/**
 * Process the Csv Import
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/post/import', function ($request, $response) {
    $columns = [
        'keys' => [
            'post_id',
            'post_name',
            'post_email',
            'post_phone',
            'post_position',
            'post_location',
            'post_experience',
            'post_detail',
            'post_tags',
            'post_type',
        ]
    ];

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/control/post/search');
    }

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('post_id', $response->getResults('post_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' imported a post file');
    $request->setStage('history_attribute', 'post-import');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        if (empty($value['post_tags'])) {
            $value['post_tags'] = '[]';
        }

        $request->setStage($value);

        if ($value['post_tags']) {
            $request->setStage(
                'post_tags',
                (explode(',', $value['post_tags']))
            );
        }

        if (!isset($value['post_id']) || empty($value['post_id'])) {
            $request->removeStage('post_id');
            cradle()->trigger('post-create', $request, $response);
        } else {
            cradle()->trigger('post-update', $request, $response);
        }

        //get error response
        if ($response->isError()) {
            if ($response->getValidation()) {
                $errors[] = '<br>#' . $value['post_name'] . ' - ' . implode(' ', $response->getValidation());
                $errorCtr++;
            }
        } else {
            if (!$value['post_id']) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' [' . $successCreateCtr . '] Post Created <br>'
        . ' [' . $successUpdateCtr . '] Post Updated ' . '<br>[' . $errorCtr . '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 20000);

    //redirect
    $redirect = '/control/post/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
