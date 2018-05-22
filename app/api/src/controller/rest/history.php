<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * History search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/history/search', function ($request, $response) {
    return;
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
            'history_active',
            'deal_type',
            'profile_id'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('history-search', $request, $response);
});

/**
 * History detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/history/detail/:history_id', function ($request, $response) {
    return;
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
    cradle()->trigger('history-detail', $request, $response);
});

/**
 * History create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/history/create', function ($request, $response) {
    return;
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
    if ($request->hasStage('history_type') && !$request->getStage('history_type')) {
        $request->setStage('history_type', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('history-create', $request, $response);
});

/**
 * History update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/history/update/:history_id', function ($request, $response) {
    return;
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

    //history_action is disallowed
    $request->removeStage('history_action');

    //history_type is disallowed
    $request->removeStage('history_type');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('history-update', $request, $response);
});

/**
 * History remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/history/remove/:history_id', function ($request, $response) {
    return;
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
    cradle()->trigger('history-remove', $request, $response);
});

/**
 * History restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/history/restore/:history_id', function ($request, $response) {
    return;
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
    cradle()->trigger('history-restore', $request, $response);
});
