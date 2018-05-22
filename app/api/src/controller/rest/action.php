<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 use Cradle\Http\Request;
 use Cradle\Http\Response;
 
/**
 * Action search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/action/search', function ($request, $response) {
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
            'action_id',
            'action_title',
            'action_event',
            'template_title',
            'action_created',
            'action_updated',
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    // filter possible filter options
    // we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'action_active',
            'action_id',
            'action_title',
            'action_event',
            'action_tags',
            'action_created',
            'action_updated',
            'template_title'
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
    cradle()->trigger('action-search', $request, $response);
});

/**
 * Action detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/action/detail/:action_id', function ($request, $response) {
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
    cradle()->trigger('action-detail', $request, $response);
});

/**
 * Action create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/action/create', function ($request, $response) {
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

    //if action_title has no value make it null
    if ($request->hasStage('action_title') && !$request->getStage('action_title')) {
        $request->setStage('action_title', 'No Title');
    }

    $data = $request->getStage();

    // if template is "no-campaign" unset template_id
    if (!is_numeric($data['template_id'])) {
        $request->removeStage('template_id');
        $request->removeStage('action_medium');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('action-create', $request, $response);
});

/**
 * Action update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/action/update/:action_id', function ($request, $response) {
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
    $data = $request->getStage();

    // if template is "no-campaign" unset template_id
    if (!is_numeric($data['template_id'])) {
        $request->removeStage('template_id');
        $request->removeStage('action_medium');
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('action-update', $request, $response);
});

/**
 * Action remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/action/remove/:action_id', function ($request, $response) {
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
    cradle()->trigger('action-remove', $request, $response);
});

/**
 * Action restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/action/restore/:action_id', function ($request, $response) {
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
    cradle()->trigger('action-restore', $request, $response);
});

/**
 * Action Bulk
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/action/bulk', function ($request, $response) {
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
    cradle()->trigger('action-bulk-action', $request, $response);
});
