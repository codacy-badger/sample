<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Post\Service as PostService;
use Cradle\Module\Widget\Service as WidgetService;
use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Render the career widget script.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/career-widget.js', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true, 'Not Found');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // set widget data to stage
    $request->setStage($response->getResults());

    // load the script
    $script = implode([
        cradle('global')->path('public'),
        'scripts/plugins/career-widget.js'
    ], '/');

    try {
        $script = @file_get_contents($script);

        // try to replace some variables
        $script = str_replace('{{widget_button_position}}', $response->getResults('widget_button_position'), $script);

        return $response
            ->addHeader('Content-Type', 'application/x-javascript')
            ->setContent($script);
    } catch (\Exception $e) {
        return $response->setError(true, 'Not Found');
    }
});

/**
 * Render the career page script.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/career-page.js', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true, 'Not Found');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // set widget data to stage
    $request->setStage($response->getResults());

    // load the script
    $script = implode([
        cradle('global')->path('public'),
        'scripts/plugins/career-page.js'
    ], '/');

    try {
        $script = @file_get_contents($script);

        return $response
            ->addHeader('Content-Type', 'application/x-javascript')
            ->setContent($script);
    } catch (\Exception $e) {
        return $response->setError(true, 'Not Found');
    }
});

/**
 * Render the career widget.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/career-widget', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true)->setContent('Not Found');
    }

    // is there an error?
    if ($response->isError()) {
        $request->setStage('flash', ['type' => 'error', 'message' => $response->getMessage()]);
        $request->setStage('errors', $response->getValidation());
    }

    // is there a flash?
    if ($request->getSession('flash')) {
        // set flash to stage
        $request->setStage('flash', [
            'type' => $request->getSession('flash', 'type'),
            'message' => $request->getSession('flash', 'message')
        ]);

        $request->removeSession('flash');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // extract profile package
    if (isset($widget['profile_package'])) {
        // extract profile package
        $widget['profile_package'] = @json_decode($widget['profile_package'], true);

        // is the user has widget package?
        if (!in_array('career-widget', $widget['profile_package'])) {
            // set widget branding flag
            $widget['widget_branding'] = 1;
        }
    }

    // set widget domain root
    $widget['widget_root'] = $domain;
    // set widget data
    $data['item'] = $widget;

    // filter by post type
    $request->setStage('filter', 'post_type', 'poster');
    // filter by profile id
    $request->setStage('filter', 'profile_id', $widget['profile_id']);
    // set range
    $request->setStage('range', 5);
    // order by post created
    $request->setStage('order', 'post_created', 'DESC');

    // trigger post search
    cradle()->trigger('post-search', $request, $response);

    // get initial post items
    $posts = $response->getResults('rows');

    // set rows
    $data['item']['rows'] = $posts;
    $data['item']['total'] = $response->getResults('total');

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    // add CSRF to stage
    $request->setStage('csrf', $response->getResults('csrf'));

    // merge data
    $data = array_merge($data, $request->getStage());

    // load the widget frame
    $frame = cradle('/app/www')->template(
        'profile/widget/partials/_widget',
        $data,
        [
            'profile/widget/partials_post'
        ]
    );

    // return the frame
    return $response
        ->setError(false)
        ->addHeader('Access-Control-Allow-Origin', '*')
        ->setContent($frame);
});

/**
 * Render the career launcher.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/career-launcher', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true)->setContent('Not Found');
    }

    // is there an error?
    if ($response->isError()) {
        $request->setStage('flash', ['type' => 'error', 'message' => $response->getMessage()]);
        $request->setStage('errors', $response->getValidation());
    }

    // is there a flash?
    if ($request->getSession('flash')) {
        // set flash to stage
        $request->setStage('flash', [
            'type' => $request->getSession('flash', 'type'),
            'message' => $request->getSession('flash', 'message')
        ]);

        $request->removeSession('flash');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set widget domain root
    $widget['widget_root'] = $domain;
    // override type
    $widget['widget_type'] = 'career_launcher';

    // set data
    $data['item'] = $widget;

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    // add CSRF to stage
    $request->setStage('csrf', $response->getResults('csrf'));

    // merge data
    $data = array_merge($data, $request->getStage());

    // load the widget frame
    $frame = cradle('/app/www')->template(
        'profile/widget/partials/_launcher',
        $data
    );

    // return the frame
    return $response
        ->setError(false)
        ->addHeader('Access-Control-Allow-Origin', '*')
        ->setContent($frame);
});

/**
 * Render the career page.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/career-page', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true)->setContent('Not Found');
    }

    // is there an error?
    if ($response->isError()) {
        $request->setStage('flash', ['type' => 'error', 'message' => $response->getMessage()]);
        $request->setStage('errors', $response->getValidation());
    }

    // is there a flash?
    if ($request->getSession('flash')) {
        // set flash to stage
        $request->setStage('flash', [
            'type' => $request->getSession('flash', 'type'),
            'message' => $request->getSession('flash', 'message')
        ]);

        $request->removeSession('flash');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set widget domain root
    $widget['widget_root'] = $domain;
    // override type
    $widget['widget_type'] = 'career_page';

    // set widget data
    $data['item'] = $widget;

    // clone request
    $postRequest = Cradle\Http\Request::i()->load();
    // clone response
    $postResponse = Cradle\Http\Response::i()->load();

    // re-use post search
    $postRequest->setStage('template', 'false');
    // trigger search route
    cradle()->triggerRoute('get', '/plugins/widget/post/search', $postRequest, $postResponse);

    // get initial post items
    $posts = $postResponse->getResults('rows');

    // set rows
    $data['item']['rows'] = $posts;
    $data['item']['total'] = $postResponse->getResults('total');

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    // add CSRF to stage
    $request->setStage('csrf', $response->getResults('csrf'));

    // merge data
    $data = array_merge($data, $request->getStage());

    // load the widget frame
    $frame = cradle('/app/www')->template(
        'profile/widget/partials/_page',
        $data,
        [
            'profile/widget/partials_post'
        ]
    );

    // return the frame
    return $response
        ->setError(false)
        ->addHeader('Access-Control-Allow-Origin', '*')
        ->setContent($frame);
});

/**
 * Render the job post detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/post/detail/:post_id', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true, 'Not Found');
    }

    // is there an error?
    if ($response->isError()) {
        $request->setStage('flash', ['type' => 'error', 'message' => $response->getMessage()]);
        $request->setStage('errors', $response->getValidation());
    }

    // is there a flash?
    if ($request->getSession('flash')) {
        // set flash to stage
        $request->setStage('flash', [
            'type' => $request->getSession('flash', 'type'),
            'message' => $request->getSession('flash', 'message')
        ]);

        $request->removeSession('flash');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set widget domain root
    $widget['widget_root'] = $domain;
    
    // decode meta
    $widget['widget_meta'] = @json_decode($widget['widget_meta'], true);
    // override type
    $widget['widget_type'] = 'career_modal';

    // get post detail
    cradle()->trigger('post-detail', $request, $response);

    // get results
    $results = $response->getResults();

    // get stage data
    $data = $request->getStage();

    // merge results
    $data['item'] = array_merge($widget, $results);
    $data['flash'] = $request->getStage('flash');

    // get profile data
    if (!$request->getStage('profile')) {
        $data['profile'] = $request->getSession('me');
    } else {
        $data['profile'] = $request->getStage('profile');
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    // load the widget frame
    $frame = cradle('/app/www')->template(
        'profile/widget/partials/_modal',
        $data
    );

    // return the frame
    return $response
        ->setError(false)
        ->addHeader('Access-Control-Allow-Origin', '*')
        ->setContent($frame);
});

/**
 * Render the job posts search.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/plugins/widget/post/search', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true, 'Not Found');
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);
    
    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();
    
    // decode meta
    $widget['widget_meta'] = @json_decode($widget['widget_meta'], true);

    // set widget data to stage
    $request->setStage('item', $widget);

    // get filter
    $metaFilter = [];

    // get meta filters
    if (isset($widget['widget_meta']['filter'])) {
        $metaFilter = $widget['widget_meta']['filter'];
    }

    // filter by post type
    $request->setStage('filter', 'post_type', 'poster');

    // NOT SURE ABOUT THIS
    if (isset($widget['widget_meta']['school'])
    && !empty($widget['widget_meta']['school'])) {
        $request->removeStage('filter', 'post_type');

        // if not all jobs
        if (in_array('all_jobs', $metaFilter)) {
            $request->setStage(
                'json_nullable_filter',
                'post_tags',
                [$widget['widget_meta']['school']]
            );
        } else {
            $request->setStage(
                'json_filter',
                'post_tags',
                [$widget['widget_meta']['school']]
            );
        }
    }

    // if filter by tags?
    if (in_array('by_tags', $metaFilter)
     && isset($widget['widget_meta']['tags'])
    ) {
        // get default tags
        $tags = $widget['widget_meta']['tags'];

        // if tags from schooll is set
        if (is_array($request->getStage('json_filter', 'post_tags'))) {
            // merge it with our tags
            $tags = array_merge($tags, $request->getStage('json_filter', 'post_tags'));
        }

        // set tags not nullable filter
        $request->setStage('json_filter', 'post_tags', $tags);
    }

    // my jobs?
    if (in_array('my_jobs', $metaFilter) || $widget['widget_type'] == 'career_widget') {
        $request->setStage('filter', 'profile_id', $widget['profile_id']);
    }

    // all jobs?
    if (in_array('all_jobs', $metaFilter)) {
        $request->removeStage('json_filter', 'post_tags');
        $request->removeStage('filter', 'profile_id');
    }

    // default range
    $range = 5;

    // if career page
    if ($widget['widget_type'] == 'career_page') {
        // set range
        $range = 10;
    }

    // if start is set
    if ($request->hasStage('start')) {
        // auto calculate start
        $start = $request->getStage('start') * $range;

        // set start
        $request->setStage('start', $start);
    }

    // set range
    $request->setStage('range', $range);
    // order by post created
    $request->setStage('order', 'post_created', 'DESC');

    // trigger post search
    cradle()->trigger('post-search', $request, $response);

    // if there is an error
    if ($response->isError()) {
        // handle error
        return $response->setError(true)->setContent('<code>Invalid Request</code>');
    }

    // get the results
    $results = $response->getResults('rows');
    // get total rows
    $request->setStage('total', $response->getResults('total'));

    // render raw results?
    if ($request->getStage('template') === 'false') {
        return $response->setResults([
            'rows' => $results,
            'total' => $request->getStage('total')
        ]);
    }

    // if results is empty
    if (empty($results)) {
        // return empty template
        return $response->setError(false)->setResults(['template' => null]);
    }

    // set results on stage
    $request->setStage('item', 'rows', $results);

    // parse the post template
    $template = cradle('/app/www')->template(
        'profile/widget/partials/_post',
        $request->getStage()
    );

    return $response->setError(false)->setResults(['template' => $template]);
});

/**
 * Process the job apply form.
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/plugins/widget/post/detail/:post_id', function ($request, $response) {
    // check required parameters
    if (!$request->hasStage('widget_key')) {
        return $response->setError(true, 'Not Found');
    }

    // get post data
    $request->setStage('profile', $request->getPost('profile'));

    // csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            sprintf(
                '/plugins/widget/post/detail/%s',
                $request->getStage('post_id')
            ),
            $request,
            $response
        );
    }

    // validate widget
    cradle()->trigger('widget-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // get the widget
    $widget = $response->getResults();

    // decode meta
    $widget['widget_meta'] = @json_decode($widget['widget_meta'], true);

    // get meta filter
    $metaFilter = [];

    // get meta filter
    if (isset($widget['widget_meta']['filter'])) {
        $metaFilter = $widget['widget_meta']['filter'];
    }

    // get the post
    $post = WidgetService::get('sql')
        ->getResource()
        ->search('post')
        ->innerJoinUsing('post_profile', 'post_id')
        ->innerJoinUsing('profile', 'profile_id')
        ->addFilter('post_id = %s', $request->getStage('post_id'));

    // user posting?
    if (!in_array('all_jobs', $metaFilter) || in_array('my_jobs', $metaFilter)) {
        // filter by profile id
        $post->addFilter('profile_id = %s', $widget['profile_id']);
    }

    // get post data
    $post = $post->getRow();
    
    // post does not exists?
    if (empty($post)) {
        return $response->setError(true)->setContent('<code>Not Found</code>');
    }

    // extract profile package
    if (isset($post['profile_package'])) {
        // extract profile package
        $post['profile_package'] = @json_decode($post['profile_package'], true);

        // is the user has widget package?
        if (!in_array('career-widget', $post['profile_package'])) {
            // set widget branding flag
            $post['widget_branding'] = 1;
        }
    }

    // set post to stage
    $request->setStage($post);

    // check if profile exists
    $profile = WidgetService::get('sql')
        ->getResource()
        ->search('profile')
        ->addFilter('profile_email = %s', $request->getStage('profile', 'profile_email'))
        ->getRow();

    // if profile is empty
    if (empty($profile)) {
        // create request
        $createRequest  = Cradle\Http\Request::i();
        // create response
        $createResponse = Cradle\Http\Response::i();

        // get the stage data
        $createRequest->setStage($request->getStage('profile'));

        // trigger profile create
        cradle()->trigger('profile-create', $createRequest, $createResponse);

        // is there an error?
        if ($createResponse->isError()) {
            // set error to response
            $response
                ->setError(true, 'Invalid Parameters')
                ->set('json', 'validation', $createResponse->getValidation());

            return cradle()->triggerRoute(
                'get',
                sprintf(
                    '/plugins/widget/post/detail/%s',
                    $request->getStage('post_id')
                ),
                $request,
                $response
            );
        }

        // get the profile
        $profile = $createRequest->getStage();
        // set profile type
        $profile['profile_create_flag'] = 'create';
    }

    // set profile to stage
    $request->setStage('profile', $profile);

    // check if profile is already interested
    $interested = WidgetService::get('sql')
        ->getResource()
        ->search('post_liked')
        ->addFilter('post_id = %s', $post['post_id'])
        ->addFilter('profile_id = %s', $profile['profile_id'])
        ->getRow();

    // if already interested
    // if($interested) {
    //     // show flash
    //     cradle('global')->flash('You are already interested in this job post.', 'error');

    //     // trigger route
    //     return cradle('global')->redirect('/plugins/widget/career-widget?' . http_build_query($request->get('get')));
    // }

    // get the file
    $file = $request->getFiles('profile_resume');

    // let us simplify the process, go with old school :)
    if (!empty($file) && !empty($file['tmp_name'])) {
        // set stage
        $request->setStage('upload', 'file', $file);

        // trigger file upload
        cradle()->trigger('file-upload', $request, $response);

        // is there an error?
        if ($response->isError()) {
            // set error to response
            $response
                ->setError(true, 'An error occured while uploading your resume.');

            return cradle()->triggerRoute(
                'get',
                sprintf(
                    '/plugins/widget/post/detail/%s',
                    $request->getStage('post_id')
                ),
                $request,
                $response
            );
        }

        // create request
        $createRequest  = Cradle\Http\Request::i();
        // create response
        $createResponse = Cradle\Http\Response::i();

        // set resume link
        $createRequest->setStage('resume_link', $response->getResults('resume_link'));
        // set resume position
        $createRequest->setStage('resume_position', $post['post_position']);
        // set profile id
        $createRequest->setStage('profile_id', $profile['profile_id']);
        // set post id
        $createRequest->setStage('post_id', $post['post_id']);

        // create the resume
        cradle()->trigger('resume-create', $createRequest, $createResponse);

        // is there an error?
        if ($createResponse->isError()) {
            $response
                ->setError(true, 'An error occured while uploading your resume.');

            return cradle()->triggerRoute(
                'get',
                sprintf(
                    '/plugins/widget/post/detail/%s',
                    $request->getStage('post_id')
                ),
                $request,
                $response
            );
        }

        // set resume link on stage
        $request->setStage('resume_link', $response->getResults('resume_link'));
        $request->setStage('resume_id', $createResponse->getResults('resume_id'));
    }

    // if not yet interested
    if (!$interested) {
        // add profile to interested
        PostService::get('sql')->addLike($post['post_id'], $profile['profile_id']);
    }

    // set profile_flag if not set
    if (!isset($profile['profile_flag'])) {
        $profile['profile_flag'] = '';
    }

    // set profile_type if not set
    if (!isset($profile['profile_type'])) {
        $profile['profile_type'] = '';
    }

    // should we claim profile?
    if (($profile['profile_flag'] !== 1
    && $profile['profile_type'] !== 'claim'
    && $profile['profile_type'] !== null)
    || $profile['profile_type'] == 'claim') {
        // update profile
        WidgetService::get('sql')
            ->getResource()
            ->model([
                'profile_id'    => $profile['profile_id'],
                'profile_flag'  => 1,
                'profile_type'  => 'claim'
            ])
            ->save('profile');

        // set claim flag
        $request->setStage('profile_claim', 1);
    }

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    // set host to stage
    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));

    // set the profile id
    $request->setStage('profile_id', $profile['profile_id']);

    // get profile slug from profile detail
    cradle()->trigger('profile-detail', $request, $response);

    // set profile slug
    $request->setStage('profile', 'profile_slug', $response->getResults('profile_slug'));

    // trigger notify profile
    cradle()->trigger('widget-notify-profile', $request, $response);

    // trigger notify company
    cradle()->trigger('widget-notify-company', $request, $response);
    
    // set flash
    cradle('global')->flash('Application successfully created, please check your email for updates.', 'success');

    $redirect = sprintf(
        '/plugins/widget/post/detail/%s?',
        $request->getStage('post_id')
    ) . http_build_query($request->get('get'));

    // trigger route
    return cradle('global')->redirect($redirect);
});
