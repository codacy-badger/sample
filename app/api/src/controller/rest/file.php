<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * File search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/file/search', function ($request, $response) {
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
            'file_active'
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
    cradle()->trigger('file-search', $request, $response);
});

/**
 * File detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/file/detail/:file_id', function ($request, $response) {
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
    cradle()->trigger('file-detail', $request, $response);
});

/**
 * File create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/file/create', function ($request, $response) {
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
    cradle()->trigger('file-create', $request, $response);
});

/**
 * File update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/file/update/:file_id', function ($request, $response) {
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
    cradle()->trigger('file-update', $request, $response);
});

/**
 * File remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/file/remove/:file_id', function ($request, $response) {
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
    cradle()->trigger('file-remove', $request, $response);
});

/**
 * File restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/file/restore/:file_id', function ($request, $response) {
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
    cradle()->trigger('file-restore', $request, $response);
});
