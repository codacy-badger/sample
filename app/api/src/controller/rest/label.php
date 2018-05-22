<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Label search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/label/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
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
            'label_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-search', $request, $response);
});

/**
 * Label detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/label/detail/:label_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-detail', $request, $response);
});

/**
 * Label create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/label/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //label_name is disallowed
    $request->removeStage('label_name');

    //label_type is disallowed
    $request->removeStage('label_type');

    //optional
    if ($request->hasStage('label_type') && !$request->getStage('label_type')) {
        $request->setStage('label_type', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-create', $request, $response);
});

/**
 * Label update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/label/update/:label_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //label_name is disallowed
    $request->removeStage('label_name');

    //label_type is disallowed
    $request->removeStage('label_type');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-update', $request, $response);
});

/**
 * Label remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/label/remove/:label_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-remove', $request, $response);
});

/**
 * Label restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/label/restore/:label_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'label');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('label-restore', $request, $response);
});
