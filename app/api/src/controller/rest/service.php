<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Service search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/service/search', function ($request, $response) {
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

    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_name',
            'service_credits'
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
            'service_active',
            'service_name',
            'profile_id',
            'profile_name'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) || empty($value)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-search', $request, $response);
});

/**
 * Service detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/service/detail/:service_id', function ($request, $response) {
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
    cradle()->trigger('service-detail', $request, $response);
});

/**
 * Service create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/service/create', function ($request, $response) {
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

    //if service_name has no value make it null
    if ($request->hasStage('service_name') && !$request->getStage('service_name')) {
        $request->setStage('service_name', null);
    }

    //service_meta is disallowed
    $request->removeStage('service_meta');

    //service_type is disallowed
    $request->removeStage('service_type');

    //service_flag is disallowed
    $request->removeStage('service_flag');

    //optional
    if ($request->hasStage('service_name') && !$request->getStage('service_name')) {
        $request->setStage('service_name', null);
    }
    if ($request->hasStage('service_meta') && !$request->getStage('service_meta')) {
        $request->setStage('service_meta', null);
    }
    if ($request->hasStage('service_type') && !$request->getStage('service_type')) {
        $request->setStage('service_type', null);
    }
    if ($request->hasStage('service_flag') && !$request->getStage('service_flag')) {
        $request->setStage('service_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-create', $request, $response);
});

/**
 * Service update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/service/update/:service_id', function ($request, $response) {
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

    //if service_name has no value make it null
    if ($request->hasStage('service_name') && !$request->getStage('service_name')) {
        $request->setStage('service_name', null);
    }

    //service_meta is disallowed
    $request->removeStage('service_meta');

    //service_type is disallowed
    $request->removeStage('service_type');

    //service_flag is disallowed
    $request->removeStage('service_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-update', $request, $response);
});

/**
 * Service remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/service/remove/:service_id', function ($request, $response) {
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
    cradle()->trigger('service-remove', $request, $response);
});

/**
 * Service restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/service/restore/:service_id', function ($request, $response) {
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
    cradle()->trigger('service-restore', $request, $response);
});
