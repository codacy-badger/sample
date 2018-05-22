<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Pipeline search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/pipeline/search', function ($request, $response) {
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

    /*//filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'pipeline_active'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }*/

    if (empty($request->getStage('date_start'))
        || empty($request->getStage('date_end'))) {
            $request->removeStage('date_start');
            $request->removeStage('date_end');
            $request->removeStage('date_type');
    }

    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('pipeline-search', $request, $response);
});

/**
 * Pipeline detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/pipeline/detail/:pipeline_id', function ($request, $response) {
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
    cradle()->trigger('pipeline-detail', $request, $response);
});

/**
 * Pipeline detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/pipeline/board/:pipeline_id', function ($request, $response) {
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
    cradle()->trigger('pipeline-board', $request, $response);
});

/**
 * Pipeline create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/pipeline/create', function ($request, $response) {
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
    cradle()->trigger('pipeline-create', $request, $response);
});

/**
 * Pipeline update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/pipeline/update/:pipeline_id', function ($request, $response) {
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

    //pipeline_name is disallowed
    $request->removeStage('pipeline_name');

    //pipeline_type is disallowed
    $request->removeStage('pipeline_type');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('pipeline-update', $request, $response);
});

/**
 * Pipeline remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/pipeline/remove/:pipeline_id', function ($request, $response) {
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
    cradle()->trigger('pipeline-remove', $request, $response);
});

/**
 * Pipeline restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/pipeline/restore/:pipeline_id', function ($request, $response) {
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
    cradle()->trigger('pipeline-restore', $request, $response);
});

/**
 * Pipeline Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/pipeline/bulk', function ($request, $response) {
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
    $type = $request->getStage('bulk_action');

    if ($type == 'remove' || $type == 'restore') {
        $request->setStage('bulk_field', 'active');
    }

    if ($type == 'remove') {
        $request->setStage('bulk_value', 0);
    }

    if ($type == 'restore') {
        $request->setStage('bulk_value', 1);
    }

    /*$tags = $request->getStage('tags');

    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk_rows')) {
        if ($request->getStage('bulk') == 'remove' ||
            $request->getStage('bulk') == 'restore') {
            cradle()->trigger('pipeline-bulk-action', $request, $response);
        }

        if ($request->getStage('bulk') == 'add-stage' ||
            $request->getStage('bulk') == 'remove-stage') {
            $field = str_replace('-', '_', $request->getStage('bulk')).'s';
            $tags = $request->getStage('pipeline_stages');

            foreach ($request->getStage('bulk_rows') as $id) {
                $request->setStage('pipeline_id', $id);
                $request->setStage($field, $tags);
                cradle()->trigger('pipeline-update', $request, $response);
            }
        }
        return cradle('global')->redirect('/control/business/pipeline/search');
    }*/

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('pipeline-bulk-action', $request, $response);
});
