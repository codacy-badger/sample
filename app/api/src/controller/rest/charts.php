<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Charts Dashboard
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/marketing/dashboard/charts', function ($request, $response) {
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
    //signup query
    switch ($request->getStage('chart')) {
        case 'signup':
            $request->setStage('chartFilter', 'type', $request->getStage('type'));
            $request->setStage('chartFilter', 'profile_active', 1);
            cradle()->trigger('auth-search-chart', $request, $response);
            break;
        case 'post':
            $request->setStage('chartFilter', 'post_type', $request->getStage('type'));
            cradle()->trigger('post-search-chart', $request, $response);
            break;
        case 'interested':
            $request->setStage('chartFilter', 'post_type', $request->getStage('type'));
            cradle()->trigger('post-search-chart', $request, $response);
            break;
        case 'active-post':
            $request->setStage('chartFilter', 'post_type', $request->getStage('type'));
            $request->setStage('chartFilter', 'profile_active', 1);
            $request->setStage('chartFilter', 'post_active', 1);
            cradle()->trigger('post-search-chart', $request, $response);
            break;
        case 'purchased-credits':
            $request->removeStage($request->getStage('type'));
            cradle()->trigger('transaction-search-chart', $request, $response);
            break;
        case 'sponsored-post':
            $request->setStage('chartFilter', 'post_type', $request->getStage('type'));
            $request->setStage('chartFilter', 'post_flag', 1);
            cradle()->trigger('post-search-chart', $request, $response);
            break;
        default:
            break;
    }
});

/**
 * Dashboard Export
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/marketing/dashboard/export', function ($request, $response) {
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

    if (($request->hasStage('date', 'start') && empty($request->getStage('date', 'start')))) {
        $request->removeStage('date');
    }

    if (($request->hasStage('date', 'end') && empty($request->getStage('date', 'end')))) {
        $request->removeStage('date');
    }

    if ($request->hasStage('date')) {
        $request->setStage('chartFilter', 'date', 'start', $data['date']['start']);
        $request->setStage('chartFilter', 'date', 'end', $data['date']['end']);
    }

    // Variable Declaration
    $export = array(
        'Signup'            => 0,
        'Posted'            => 0,
        'Interested'        => 0,
        'Active'            => 0,
        'Purchased Credits' => 0,
        'Sponsored Posts'   => 0
    );

    // Gets the signup
    $request->setStage('chart', 'signup');
    $request->setStage('chartFilter', 'type', $request->getStage('type'));
    cradle()->trigger('auth-search-chart', $request, $response);
    $result = $response->getResults();

    if (!empty($result)) {
        // Loops through the result
        foreach ($result as $value) {
            $export['Signup'] += $value['total'];
        }
    }

    // Unset whatever we placed at stage
    $request->removeStage('chart');
    $request->removeStage('chartFilter', 'type');

    $postSearchChart = array(
        'Posted' =>'post',
        'Interested' => 'interested',
        'Active' => 'active-post',
        'Sponsored Posts' => 'sponsored-post'
    );

    // Sets the post_type
    $request->setStage('chartFilter', 'post_type', $request->getStage('type'));

    // Loops through what needs to be fetched from the post-search-chart event
    foreach ($postSearchChart as $index => $value) {
        $request->setStage('chart', $value);

        // Checks if this is active-post
        if ($value == 'active-post') {
            $request->setStage('chartFilter', 'post_active', 1);
        }

        // Checks if this is sponsored-post
        if ($value == 'sponsored-post') {
            $request->setStage('chartFilter', 'post_flag', 1);
        }

        cradle()->trigger('post-search-chart', $request, $response);

        $result = $response->getResults();

        if (!empty($result)) {
            // Loops through the result
            foreach ($result as $value) {
                $export[$index] += $value['total'];
            }
        }

         // Unset whatever we placed at stage
        $request->removeStage('chartFilter', 'post_active');
        $request->removeStage('chartFilter', 'post_flag');
    }

    // Unset whatever we placed at stage
    $request->removeStage('chartFilter', 'post_type');

    // Gets the purchased credits
    $request->removeStage($request->getStage('type'));

    cradle()->trigger('transaction-search-chart', $request, $response);
    $result = $response->getResults();

    if (!empty($result)) {
        // Loops through the result
        foreach ($result as $value) {
            $export['Purchased Credits'] += $value['total'];
        }
    }

    $response->setResults($export);
});
