<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Utm search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/utm/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
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
            'utm_active',
            'utm_title',
            'utm_medium',
            'utm_source',
            'utm_campaign',
            'utm_created',
            'utm_updated'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) ||
                (empty($value) && $value != '0')) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-search', $request, $response);
});

/**
 * Utm detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/utm/detail/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-detail', $request, $response);
});

/**
 * Utm create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/utm/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //optional
    if ($request->hasStage('utm_title') && !$request->getStage('utm_title')) {
        $request->setStage('utm_title', null);
    }
    if ($request->hasStage('utm_image') && !$request->getStage('utm_image')) {
        $request->setStage('utm_image', null);
    }
    if ($request->hasStage('utm_detail') && !$request->getStage('utm_detail')) {
        $request->setStage('utm_detail', null);
    }
    if ($request->hasStage('utm_medium') && !$request->getStage('utm_medium')) {
        $request->setStage('utm_medium', null);
    }
    if ($request->hasStage('utm_source') && !$request->getStage('utm_source')) {
        $request->setStage('utm_source', null);
    }
    if ($request->hasStage('utm_campaign') && !$request->getStage('utm_campaign')) {
        $request->setStage('utm_campaign', null);
    }
    if (!$request->hasStage('utm_page')) {
        $request->setStage('utm_page', '');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-create', $request, $response);
});

/**
 * Utm update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/utm/update/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-update', $request, $response);
});

/**
 * Utm remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/utm/remove/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-remove', $request, $response);
});

/**
 * Utm restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/utm/restore/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-restore', $request, $response);
});

/**
 * Link Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/utm/bulk', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'marketing_dashboard');
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

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('utm-bulk-action', $request, $response);
});
