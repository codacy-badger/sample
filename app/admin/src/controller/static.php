<?php //-->
/**
 * This file is part of the Dealcha Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Url Generate Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/url/generate', function ($request, $response) {
     //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }
    
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    
    $param = '';
    if (isset($data['position'])) {
        $param .= 'position=' . $data['position'];
    }

    if (isset($data['location'])) {
        if (!empty($param)) {
            $param .= '&location=' . $data['location'];
        } else {
            $param = 'location=' . $data['location'];
        }
    }
    if (isset($data['btn_generate'])) {
        $social = '';
        if (!empty($data['btn_generate'])) {
            $social = '/'. $data['btn_generate'];
        }

        if (!empty($param)) {
            $data['generated_url'] = $host . '/login'. $social .'?redirect_uri='. urlencode('/post/search?'. $param);
        }
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-url-generate page-admin';
    $data['title'] = cradle('global')->translate('Generate URL');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body = cradle('/app/admin')->template('url/generate', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Url Generate Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/dashboard', function ($request, $response) {
     //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-dashboard page-admin';
    $data['title'] = cradle('global')->translate('Dashboard');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body = cradle('/app/admin')->template('dashboard', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');
