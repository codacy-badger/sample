<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Term search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/term/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    if (!$request->hasStage('order')) {
        $request->setStage('order', 'term_hits', 'DESC');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'term_hits'
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
            'term_active',
            'term_name',
            'term_type'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-search', $request, $response);
});

/**
 * Term detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/term/detail/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'term');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-detail', $request, $response);
});

/**
 * Term create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/term/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'term');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //term_name is disallowed
    $request->removeStage('term_name');

    //term_hits is disallowed
    $request->removeStage('term_hits');

    //term_type is disallowed
    $request->removeStage('term_type');

    //term_flag is disallowed
    $request->removeStage('term_flag');

    //optional
    if ($request->hasStage('term_hits') && !$request->getStage('term_hits')) {
        $request->setStage('term_hits', '1');
    }
    if ($request->hasStage('term_type') && !$request->getStage('term_type')) {
        $request->setStage('term_type', 'search');
    }
    if ($request->hasStage('term_flag') && !$request->getStage('term_flag')) {
        $request->setStage('term_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-create', $request, $response);
});

/**
 * Term update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/term/update/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'term');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data

    //term_name is disallowed
    $request->removeStage('term_name');

    //term_hits is disallowed
    $request->removeStage('term_hits');

    //term_type is disallowed
    $request->removeStage('term_type');

    //term_flag is disallowed
    $request->removeStage('term_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-update', $request, $response);
});

/**
 * Term remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/term/remove/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'term');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-remove', $request, $response);
});

/**
 * Term restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/term/restore/:term_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'term');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('term-restore', $request, $response);
});
