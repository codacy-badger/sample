<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Template search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/template/search', function ($request, $response) {
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

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'template_id',
            'template_title',
            'template_type',
            'template_created',
            'template_updated'
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
            'template_active',
            'template_id',
            'template_title',
            'template_type',
            'template_created',
            'template_updated'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) ||
                (empty($value) && $value != '0')) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
    }

    //----------------------------//
    // 2. Process Request
    cradle()->trigger('template-search', $request, $response);
});

/**
 * Template detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/template/detail/:template_id', function ($request, $response) {
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
    cradle()->trigger('template-detail', $request, $response);
});

/**
 * Template create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/template/create', function ($request, $response) {
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
    if ($request->hasStage('template_title') && !$request->getStage('template_title')) {
        $request->setStage('template_title', null);
    }
    if ($request->hasStage('template_type') && !$request->getStage('template_type')) {
        $request->setStage('template_type', null);
    }
    if ($request->hasStage('template_html') && !$request->getStage('template_html')) {
        $request->setStage('template_html', null);
    }
    if ($request->hasStage('template_text') && !$request->getStage('template_text')) {
        $request->setStage('template_text', null);
    }
    if ($request->hasStage('template_unsubscribe') && !$request->getStage('template_unsubscribe')) {
        $request->setStage('template_unsubscribe', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('template-create', $request, $response);
});

/**
 * Template update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/template/update/:template_id', function ($request, $response) {
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
    cradle()->trigger('template-update', $request, $response);
});

/**
 * Template remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/template/remove/:template_id', function ($request, $response) {
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
    cradle()->trigger('template-remove', $request, $response);
});

/**
 * Template restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/template/restore/:template_id', function ($request, $response) {
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
    cradle()->trigger('template-restore', $request, $response);
});

/**
 * Template Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/template/bulk', function ($request, $response) {
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
    cradle()->trigger('template-bulk-action', $request, $response);
});
