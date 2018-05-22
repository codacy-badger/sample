<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
/**
 * Lead search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/lead/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
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
    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'lead_id',
            'lead_name',
            'lead_email',
            'lead_location',
            'lead_type',
            'lead_created',
            'lead_updated'
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
            'lead_active',
            'lead_type', //company or seeker
            'lead_id',
            'lead_name',
            'lead_email',
            'lead_phone',
            'lead_job_title',
            'lead_tags',
            'lead_campaigns',
            'lead_gender',
            'lead_location',
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
    cradle()->trigger('lead-search', $request, $response);
});
/**
 * Lead detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/lead/detail/:lead_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('lead-detail', $request, $response);
});
/**
 * Lead create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/lead/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    //if lead_image has no value make it null
    if ($request->hasStage('lead_image') && !$request->getStage('lead_image')) {
        $request->setStage('lead_image', null);
    }
    //if lead_type has no value make it null
    if ($request->hasStage('lead_type') && !$request->getStage('lead_type')) {
        $request->setStage('lead_type', 'seeker');
    }
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('lead-create', $request, $response);
});
/**
 * Lead update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/lead/update/:lead_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('lead-update', $request, $response);
});
/**
 * Lead remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/lead/remove/:lead_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('lead-remove', $request, $response);
});
/**
 * Lead restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/lead/restore/:lead_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('lead-restore', $request, $response);
});

/**
 * Lead Bulk
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->post('/rest/lead/bulk', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'lead');
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
    cradle()->trigger('lead-bulk-action', $request, $response);
});*/
