<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
  
/**
 * Routes
 */
$cradle->post('/rest/access', function ($request, $response) {
    //set the auth id
    $auth = $request->get('source', 'auth_id');
    $request->setStage('permission', $auth);

    //call the job
    cradle()->trigger('session-access', $request, $response);
});

/**
 * Auth search is accessable by all
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/auth/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // Nothing to prepare!
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-search', $request, $response);
});

/**
 * Auth create is accessable by all
 *
 * @param Request $request
 * @param Response $response
 */
// $cradle->post('/rest/auth/create', 'auth-create');
$cradle->post('/rest/auth/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    // remove profile_id so that it will create new profile_id upon auth creation
    $request->removeStage('profile_id');
    //----------------------------//
    // 2. Prepare Data
    // Nothing to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-create', $request, $response);
});
