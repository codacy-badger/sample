<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Event search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/event/search', function ($request, $response) {
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
            'event_active',
            'event_type',
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
    cradle()->trigger('event-search', $request, $response);
});

/**
 * Event detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/event/detail/:event_id', function ($request, $response) {
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
    cradle()->trigger('event-detail', $request, $response);
});

/**
 * Event create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/event/create', function ($request, $response) {
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
    if ($request->hasStage('event_type') && !$request->getStage('event_type')) {
        $request->setStage('event_type', 'meeting');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('event-create', $request, $response);
});

/**
 * Event update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/event/update/:event_id', function ($request, $response) {
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
    cradle()->trigger('event-update', $request, $response);
});

/**
 * Event remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/event/remove/:event_id', function ($request, $response) {
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
    cradle()->trigger('event-remove', $request, $response);
});

/**
 * Event restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/event/restore/:event_id', function ($request, $response) {
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
    cradle()->trigger('event-restore', $request, $response);
});
