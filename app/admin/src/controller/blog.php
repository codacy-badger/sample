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
 * Render the Blog Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:article:listing', 'admin', $request)) {
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
            'blog_title',
            'blog_id'
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
            'blog_title',
            'blog_description',
            'blog_keywords',
            'blog_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
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

    if (!$request->hasStage('order')) {
        //sort desc
        $request->setStage('order', 'blog_id', 'DESC');
    }

    $request->setStage('blog_published', true);
    //trigger job
    cradle()->trigger('blog-search', $request, $response);


    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-blog-search page-admin';
    $data['title'] = cradle('global')->translate('Articles');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body = cradle('/app/admin')->template('blog/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    // render admin page
}, 'render-admin-page');

/**
 * Render the Blog Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if (!cradle('global')->role('admin:article:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/blog/search');
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
    $class = 'page-developer-blog-create page-admin';
    $data['title'] = cradle('global')->translate('Create Article');
    $body = cradle('/app/admin')->template('blog/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Blog Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/update/:blog_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permissions
        if (!cradle('global')->role('admin:article:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/blog/search');
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
        cradle()->trigger('blog-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/control/blog/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $data['locations'] = array_keys($this->package('global')->config('location'));
    $data['positions'] = $this->package('global')->config('positions');
    $data['industries'] = $this->package('global')->config('industries');

    $data['item']['blog_published'] = date('Y-m-d', strtotime($data['item']['blog_published']));

    if (isset($data['item']['blog_published'])
        && $data['item']['blog_published'] < '1971-01-01') {
        $data['item']['blog_published'] = date('Y-m-d');
    }
    //----------------------------//

    // 3. Render Template
    $class = 'page-developer-blog-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Article');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate(ucwords($data['item']['blog_title']) . ' Details');
    }
    $body = cradle('/app/admin')->template('blog/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Render the Blog Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/detail/:blog_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:article:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/blog/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/blog/update/%s',
            $request->getStage('blog_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Blog Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/blog/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if (!cradle('global')->role('admin:article:update', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/blog/search');
    }

    //----------------------------//
    // 2. Prepare Data

    //if blog_image has no value make it null
    if ($request->hasStage('blog_image') && !$request->getStage('blog_image')) {
        $request->setStage('blog_image', null);
    }

    //if blog_title has no value make it null
    if ($request->hasStage('blog_title') && !$request->getStage('blog_title')) {
        $request->setStage('blog_title', null);
    }

    //if blog_description has no value make it null
    if ($request->hasStage('blog_description') && !$request->getStage('blog_description')) {
        $request->setStage('blog_description', null);
    }

    //if blog_slug has no value make it null
    if ($request->hasStage('blog_slug') && !$request->getStage('blog_slug')) {
        $request->setStage('blog_slug', null);
    }

    //if blog_keywords has no value use the default value
    if ($request->hasStage('blog_keywords') && !$request->getStage('blog_keywords')) {
        $request->setStage('blog_keywords', null);
    }

    //if blog_article has no value make it null
    if ($request->hasStage('blog_article') && !$request->getStage('blog_article')) {
        $request->setStage('blog_article', null);
    }

    //make the blog_view_count 0
    $request->setStage('blog_view_count', 0);

    //if blog_facebook_title has no value use the default value
    if ($request->hasStage('blog_facebook_title') && !$request->getStage('blog_facebook_title')) {
        $request->setStage('blog_facebook_title', null);
    }

    //if blog_facebook_image has no value make it null
    if ($request->hasStage('blog_facebook_image') && !$request->getStage('blog_facebook_image')) {
        $request->setStage('blog_facebook_image', null);
    }

    //if blog_facebook_description has no value make it null
    if ($request->hasStage('blog_facebook_description') && !$request->getStage('blog_facebook_description')) {
        $request->setStage('blog_facebook_description', null);
    }

    //if blog_twitter_title has no value make it null
    if ($request->hasStage('blog_twitter_title') && !$request->getStage('blog_twitter_title')) {
        $request->setStage('blog_twitter_title', null);
    }

    //if blog_twitter_image has no value make it null
    if ($request->hasStage('blog_twitter_image') && !$request->getStage('blog_twitter_image')) {
        $request->setStage('blog_twitter_image', null);
    }

    //if blog_twitter_description has no value make it null
    if ($request->hasStage('blog_twitter_description') && !$request->getStage('blog_twitter_description')) {
        $request->setStage('blog_twitter_description', null);
    }

    //if profile_id has no value, get it from request-get else get from session
    if ($request->hasStage('profile_id') && !$request->getStage('profile_id')) {
        $request->setStage('profile_id', $request->getGet('profile_id'));
    } else {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    //if blog_tags has no value use the default value
    if (!$request->hasStage('blog_tags') && !$request->getStage('blog_tags')) {
        $request->setStage('blog_tags', 'location', null);
        $request->setStage('blog_tags', 'position', null);
        $request->setStage('blog_tags', 'industry', null);
    } else {
        // check blog tags if has value of all
        if ($request->hasStage('blog_tags', 'location') && in_array('all', $request->getStage('blog_tags', 'location'))) {
            // Prepare all position
            $request->setStage('filter', 'feature_type', 'location');
            cradle()->trigger('feature-search', $request, $response);
            foreach ($response->getResults('rows') as $key => $value) {
                $locations[] = $value['feature_name'];
            }
            $request->setStage('blog_tags', 'location', $locations);
        }
        if ($request->hasStage('blog_tags', 'position') && in_array('all', $request->getStage('blog_tags', 'position'))) {
            // Prepare all positions
            $position = $this->package('global')->config('positions');
            $request->setStage('blog_tags', 'position', $position);
        }
        if ($request->hasStage('blog_tags', 'industry') && in_array('all', $request->getStage('blog_tags', 'industry'))) {
            // Prepare all industry
            $industries = $this->package('global')->config('industries');
            $request->setStage('blog_tags', 'industry', $industries);
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('blog-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/control/blog/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('blog_id', $response->getResults('blog_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created blog');
    $request->setStage('history_attribute', 'blog-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Article was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/blog/search');
});

/**
 * Process the Blog Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/blog/update/:blog_id', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if blog_image has no value make it null
    if ($request->hasStage('blog_image') && !$request->getStage('blog_image')) {
        $request->setStage('blog_image', null);
    }

    //if blog_title has no value make it null
    if ($request->hasStage('blog_title') && !$request->getStage('blog_title')) {
        $request->setStage('blog_title', null);
    }

    //if blog_description has no value make it null
    if ($request->hasStage('blog_description') && !$request->getStage('blog_description')) {
        $request->setStage('blog_description', null);
    }

    //if blog_slug has no value make it null
    if ($request->hasStage('blog_slug') && !$request->getStage('blog_slug')) {
        $request->setStage('blog_slug', null);
    }

    //if blog_keywords has no value use the default value
    if ($request->hasStage('blog_keywords') && !$request->getStage('blog_keywords')) {
        $request->setStage('blog_keywords', null);
    }

    //if blog_article has no value make it null
    if ($request->hasStage('blog_article') && !$request->getStage('blog_article')) {
        $request->setStage('blog_article', null);
    }

    //if blog_facebook_title has no value use the default value
    if ($request->hasStage('blog_facebook_title') && !$request->getStage('blog_facebook_title')) {
        $request->setStage('blog_facebook_title', null);
    }

    //if blog_facebook_image has no value make it null
    if ($request->hasStage('blog_facebook_image') && !$request->getStage('blog_facebook_image')) {
        $request->setStage('blog_facebook_image', null);
    }

    //if blog_facebook_description has no value make it null
    if ($request->hasStage('blog_facebook_description') && !$request->getStage('blog_facebook_description')) {
        $request->setStage('blog_facebook_description', null);
    }

    //if blog_twitter_title has no value make it null
    if ($request->hasStage('blog_twitter_title') && !$request->getStage('blog_twitter_title')) {
        $request->setStage('blog_twitter_title', null);
    }

    //if blog_twitter_image has no value make it null
    if ($request->hasStage('blog_twitter_image') && !$request->getStage('blog_twitter_image')) {
        $request->setStage('blog_twitter_image', null);
    }

    //if blog_twitter_description has no value make it null
    if ($request->hasStage('blog_twitter_description') && !$request->getStage('blog_twitter_description')) {
        $request->setStage('blog_twitter_description', null);
    }

    //if profile_id has no value, get it from request-get else get from session
    if ($request->hasStage('profile_id') && !$request->getStage('profile_id')) {
        $request->setStage('profile_id', $request->getGet('profile_id'));
    } else {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    //if blog_tags has no value use the default value
    if (!$request->hasStage('blog_tags') && !$request->getStage('blog_tags')) {
        $request->setStage('blog_tags', 'location', null);
        $request->setStage('blog_tags', 'position', null);
        $request->setStage('blog_tags', 'industry', null);
    } else {
        // check blog tags if has value of all
        if ($request->hasStage('blog_tags', 'location') && in_array('all', $request->getStage('blog_tags', 'location'))) {
            // Prepare all position
            $request->setStage('filter', 'feature_type', 'location');
            cradle()->trigger('feature-search', $request, $response);
            foreach ($response->getResults('rows') as $key => $value) {
                $locations[] = $value['feature_name'];
            }
            $request->setStage('blog_tags', 'location', $locations);
        }
        if ($request->hasStage('blog_tags', 'position') && in_array('all', $request->getStage('blog_tags', 'position'))) {
            // Prepare all positions
            $position = $this->package('global')->config('positions');
            $request->setStage('blog_tags', 'position', $position);
        }
        if ($request->hasStage('blog_tags', 'industry') && in_array('all', $request->getStage('blog_tags', 'industry'))) {
            // Prepare all industry
            $industries = $this->package('global')->config('industries');
            $request->setStage('blog_tags', 'industry', $industries);
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('blog-update', $request, $response);
    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/blog/update/' . $request->getStage('blog_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('blog_id', $response->getResults('blog_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated blog id ' . $response->getResults('blog_id'));
    $request->setStage('history_attribute', 'blog-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Article was Updated', 'success');

    //redirect
    $redirect = '/control/blog/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Blog Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/remove/:blog_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:article:remove', 'admin', $request)) {
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
    cradle()->trigger('blog-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('blog_id', $response->getResults('blog_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed blog id #' . $response->getResults('blog_id'));
    $request->setStage('history_attribute', 'blog-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Article was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/blog/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Blog Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/blog/restore/:blog_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:article:restore', 'admin', $request)) {
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
    cradle()->trigger('blog-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('blog_id', $response->getResults('blog_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored blog id #' . $response->getResults('blog_id'));
    $request->setStage('history_attribute', 'blog-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Article was Restored');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/blog/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
