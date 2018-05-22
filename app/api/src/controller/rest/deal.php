<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Deal search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/deal/search', function ($request, $response) {
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
    cradle()->trigger('deal-search', $request, $response);
});

/**
 * Deal summary
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/deal/summary', function ($request, $response) {
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

    if (empty($request->getStage('date_start'))
        || empty($request->getStage('date_end'))) {
            $request->removeStage('date_start');
            $request->removeStage('date_end');
            $request->removeStage('date_type');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('deal-summary', $request, $response);
});

/**
 * Deal detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/deal/detail/:deal_id', function ($request, $response) {
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
    cradle()->trigger('deal-detail', $request, $response);
});

/**
 * Deal create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/deal/create', function ($request, $response) {
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
    //if no deal_agent, remove
    if ($request->hasStage('deal_agent') && !$request->getStage('deal_agent')) {
        $request->removeStage('deal_agent');
    }
    //if no company, remove
    if ($request->hasStage('deal_company') && !$request->getStage('deal_company')) {
        $request->removeStage('deal_company');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('deal-create', $request, $response);
});

/**
 * Deal update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/deal/update/:deal_id', function ($request, $response) {
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
    $data = $request->getStage();

    if ($request->hasStage('deal_agent') && !$request->getStage('deal_agent')) {
        $request->removeStage('deal_agent');
    }
    //if no company, remove
    if ($request->hasStage('deal_company') && !$request->getStage('deal_company')) {
        $request->removeStage('deal_company');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('deal-update', $request, $response);
});

/**
 * Deal remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/deal/remove/:deal_id', function ($request, $response) {
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
    cradle()->trigger('deal-remove', $request, $response);
});

/**
 * Deal restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/deal/restore/:deal_id', function ($request, $response) {
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
    // no data to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('deal-restore', $request, $response);
});

/**
 * Deal Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/deal/bulk', function ($request, $response) {
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
    cradle()->trigger('deal-bulk-action', $request, $response);
});
