<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Campaign search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/campaign/search', function ($request, $response) {
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
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'campaign_id',
            'campaign_title',
            'campaign_created',
            'campaign_updated',
            'template_title'
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
            'campaign_active',
            'campaign_title',
            'campaign_tags',
            'campaign_medium',
            'campaign_source',
            'campaign_audience',
            'campaign_queue'
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
    // 3. Process Request
    cradle()->trigger('campaign-search', $request, $response);
});

/**
 * Campaign detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/campaign/detail/:campaign_id', function ($request, $response) {
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
    cradle()->trigger('campaign-detail', $request, $response);
});

/**
 * Campaign create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/campaign/create', function ($request, $response) {
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
    if ($request->hasStage('campaign_tags') && !$request->getStage('campaign_tags')) {
        $request->setStage('campaign_tags', null);
    }

    //if campaign_title has no value make it null
    if ($request->hasStage('campaign_title') && !$request->getStage('campaign_title')) {
        $request->setStage('campaign_title', 'No Title');
    }

    $data = $request->getStage();

    if (!is_numeric($data['template_id'])) {
        $request->removeStage('template_id');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('campaign-create', $request, $response);
});

/**
 * Campaign update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/campaign/update/:campaign_id', function ($request, $response) {
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
    cradle()->trigger('campaign-update', $request, $response);
});

/**
 * Campaign remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/campaign/remove/:campaign_id', function ($request, $response) {
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
    // no data to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('campaign-remove', $request, $response);
});

/**
 * Campaign restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/campaign/restore/:campaign_id', function ($request, $response) {
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
    cradle()->trigger('campaign-restore', $request, $response);
});

/**
 * Campaign remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/campaign/link/:link_type', function ($request, $response) {
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
    // no data to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('campaign-link-client', $request, $response);
});

/**
 * Campaign Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/campaign/bulk', function ($request, $response) {
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
    cradle()->trigger('campaign-bulk-action', $request, $response);
});
