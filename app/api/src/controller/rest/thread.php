<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Thread search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/thread/search', function ($request, $response) {
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
            'thread_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('thread-search', $request, $response);
});

/**
 * Thread detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/thread/detail/:thread_id', function ($request, $response) {
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
    cradle()->trigger('thread-detail', $request, $response);
});

/**
 * Thread create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/thread/create', function ($request, $response) {
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

    //optional
    if ($request->hasStage('thread_snippet') && !$request->getStage('thread_snippet')) {
        $request->setStage('thread_snippet', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('thread-create', $request, $response);
});

/**
 * Thread update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/thread/update/:thread_id', function ($request, $response) {
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

    //thread_gmail_id is disallowed
    $request->removeStage('thread_gmail_id');

    //thread_subject is disallowed
    $request->removeStage('thread_subject');

    //thread_snippet is disallowed
    $request->removeStage('thread_snippet');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('thread-update', $request, $response);
});

/**
 * Thread remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/thread/remove/:thread_id', function ($request, $response) {
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
    cradle()->trigger('thread-remove', $request, $response);
});

/**
 * Thread restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/thread/restore/:thread_id', function ($request, $response) {
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
    cradle()->trigger('thread-restore', $request, $response);
});
