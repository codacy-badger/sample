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
$cradle->get('/control/industry/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    $data['industries'] = $this->package('global')->config('industries');
    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-industry-search page-admin';
    $data['title'] = cradle('global')->translate('Industries');
    
    $body = cradle('/app/admin')->template('industry/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    // render admin page
}, 'render-admin-page');
