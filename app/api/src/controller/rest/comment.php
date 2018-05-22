<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Comment search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/comment/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
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
            'comment_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    $profile = $request->getStage('profile_id');
    $request->setStage('filter', 'profile_id', $profile);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-search', $request, $response);
});

/**
 * Comment detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/comment/detail/:comment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-detail', $request, $response);
});

/**
 * Comment create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/comment/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-create', $request, $response);
});

/**
 * Comment update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/comment/update/:comment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-update', $request, $response);
});

/**
 * Comment remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/comment/remove/:comment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-remove', $request, $response);
});

/**
 * Comment restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/comment/restore/:comment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'business_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('comment-restore', $request, $response);
});
