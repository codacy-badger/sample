<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Transaction search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/transaction/search', function ($request, $response) {
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
        'transaction_active',
            'transaction_status',
            'transaction_payment_method'
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
    cradle()->trigger('transaction-search', $request, $response);
});

/**
 * Transaction detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/transaction/detail/:transaction_id', function ($request, $response) {
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
    cradle()->trigger('transaction-detail', $request, $response);
});

/**
 * Transaction create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/transaction/create', function ($request, $response) {
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

    //if transaction_status has no value use the default value
    if ($request->hasStage('transaction_status') && !$request->getStage('transaction_status')) {
        $request->setStage('transaction_status', 'pending');
    }

    //if transaction_payment_method has no value make it null
    if ($request->hasStage('transaction_payment_method') && !$request->getStage('transaction_payment_method')) {
        $request->setStage('transaction_payment_method', null);
    }

    //if transaction_payment_reference has no value make it null
    if ($request->hasStage('transaction_payment_reference') && !$request->getStage('transaction_payment_reference')) {
        $request->setStage('transaction_payment_reference', null);
    }

    //transaction_profile is disallowed
    $request->removeStage('transaction_profile');

    //transaction_meta is disallowed
    $request->removeStage('transaction_meta');

    //transaction_statement is disallowed
    $request->removeStage('transaction_statement');

    //transaction_currency is disallowed
    $request->removeStage('transaction_currency');

    //transaction_total is disallowed
    $request->removeStage('transaction_total');

    //transaction_credits is disallowed
    $request->removeStage('transaction_credits');

    //transaction_type is disallowed
    $request->removeStage('transaction_type');

    //transaction_flag is disallowed
    $request->removeStage('transaction_flag');

    //optional
    if ($request->hasStage('transaction_status') && !$request->getStage('transaction_status')) {
        $request->setStage('transaction_status', 'pending');
    }
    if ($request->hasStage('transaction_payment_method') && !$request->getStage('transaction_payment_method')) {
        $request->setStage('transaction_payment_method', null);
    }
    if ($request->hasStage('transaction_payment_reference') && !$request->getStage('transaction_payment_reference')) {
        $request->setStage('transaction_payment_reference', null);
    }
    if ($request->hasStage('transaction_meta') && !$request->getStage('transaction_meta')) {
        $request->setStage('transaction_meta', null);
    }
    if ($request->hasStage('transaction_statement') && !$request->getStage('transaction_statement')) {
        $request->setStage('transaction_statement', null);
    }
    if ($request->hasStage('transaction_type') && !$request->getStage('transaction_type')) {
        $request->setStage('transaction_type', null);
    }
    if ($request->hasStage('transaction_flag') && !$request->getStage('transaction_flag')) {
        $request->setStage('transaction_flag', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-create', $request, $response);
});

/**
 * Transaction update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/transaction/update/:transaction_id', function ($request, $response) {
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

    //if transaction_status has no value use the default value
    if ($request->hasStage('transaction_status') && !$request->getStage('transaction_status')) {
        $request->setStage('transaction_status', 'pending');
    }

    //if transaction_payment_method has no value make it null
    if ($request->hasStage('transaction_payment_method') && !$request->getStage('transaction_payment_method')) {
        $request->setStage('transaction_payment_method', null);
    }

    //if transaction_payment_reference has no value make it null
    if ($request->hasStage('transaction_payment_reference') && !$request->getStage('transaction_payment_reference')) {
        $request->setStage('transaction_payment_reference', null);
    }

    //transaction_profile is disallowed
    $request->removeStage('transaction_profile');

    //transaction_meta is disallowed
    $request->removeStage('transaction_meta');

    //transaction_statement is disallowed
    $request->removeStage('transaction_statement');

    //transaction_currency is disallowed
    $request->removeStage('transaction_currency');

    //transaction_total is disallowed
    $request->removeStage('transaction_total');

    //transaction_credits is disallowed
    $request->removeStage('transaction_credits');

    //transaction_type is disallowed
    $request->removeStage('transaction_type');

    //transaction_flag is disallowed
    $request->removeStage('transaction_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-update', $request, $response);
});

/**
 * Transaction remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/transaction/remove/:transaction_id', function ($request, $response) {
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
    cradle()->trigger('transaction-remove', $request, $response);
});

/**
 * Transaction restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/transaction/restore/:transaction_id', function ($request, $response) {
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
    cradle()->trigger('transaction-restore', $request, $response);
});
