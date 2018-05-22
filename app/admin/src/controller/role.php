<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Role Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:listing', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }
    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    // set role type admin
    $request->setStage('filter', 'role_type', 'admin');

    //trigger job
    cradle()->trigger('role-search', $request, $response);

    // get roles admin
    $data = $response->getResults();

    foreach ($data['rows'] as $key => $role) {
        $permissions = [];
        // if role permissions
        if ($role['role_permissions']) {
            // loop through permissions
            foreach ($role['role_permissions'] as $permission) {
                // convert string to array
                $parts = explode(':', $permission);
                if (!in_array(ucfirst($parts[1]), $permissions)) {
                    $permissions[] = ucfirst($parts[1]);
                }
            }

            // set permissions
            $data['rows'][$key]['role_permissions'] = implode(', ', $permissions);
        }
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-search page-admin';
    $data['title'] = cradle('global')->translate('Roles');
    $body = cradle('/app/admin')->template('role/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Role Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/update/:role_id', function ($request, $response) {
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permissions
        if (!cradle('global')->role('admin:role:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/role/search');
        }
    }
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    $adminRoles = cradle('global')->config('roles')['admin'];

    // trigger role detail
    cradle()->trigger('role-detail', $request, $response);

    // get role details
    $data['item'] = $response->getResults();

    if (!empty($request->getPost())) {
        // get post stored as item
        $data['item'] = $request->getPost();

        // get any errors
        $data['errors'] = $response->getValidation();

        // if not empty item
        if (isset($data['item']) && !empty($data['item'])) {
            // get permissions key
            if (isset($data['item']['role_permissions'])) {
                $data['item']['role_permissions'] = array_keys($data['item']['role_permissions']);
            }
        }
    }

    // if not set
    if (!isset($data['item']['role_permissions'])) {
        $data['item']['role_permissions'] = [];
    }

    // define group role variable
    $groups = [];

    // loop admin roles
    foreach ($adminRoles as $key => $role) {
        // get by part
        $parts = explode(':', $role);
        // set action
        $action = 'admin:'.$role;
        // collect group roles
        $groups[$parts[0]]['actions'][] = [
            'action' => $parts[1],
            'role' => $action,
            'checked' => in_array($action, $data['item']['role_permissions']) ? 1 : 0
        ];
    }

    // set grouped roles
    $data['roles'] = $groups;
    $data['page_title'] = 'Update Roles';

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-update page-role-form page-admin';
    $data['title'] = cradle('global')->translate('Roles');

    if ($request->hasStage('view')) {
        $data['view'] = true;
        $data['title'] = cradle('global')->translate($data['item']['role_name'] . ' Details');
    }
    $body = cradle('/app/admin')->template('role/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
//render page
}, 'render-admin-page');

/**
 * View the Role Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/detail/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/role/update/%s',
            $request->getStage('role_id')
        ),
        $request,
        $response
    );
});

/**
 * Process the Role Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/role/update/:role_id', function ($request, $response) {
    // get data on stage
    $data = $request->getStage();

    // get roles
    if (isset($data['role_permissions'])) {
        // return all keys of role permissions
        $data['role_permissions'] = array_keys($data['role_permissions']);
        // set to request role permissions
        $request->setStage('role_permissions', $data['role_permissions']);
    }

    $adminRoles = cradle('global')->config('roles')['admin'];

    $request->setStage('role_permissions', $request->getStage('role_permissions'));

    // trigger update role
    cradle()->trigger('role-update', $request, $response);

    // if error
    if ($response->isError()) {
        // trigger route role create template
        return cradle()->triggerRoute('get', '/control/role/update/' . $request->getStage('role_id'), $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('role_id', $response->getResults('role_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] .' updated role id ' . $response->getResults('role_id'));
    $request->setStage('history_attribute', 'role-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    cradle('global')->flash('Role Successfully Updated', 'success');
    return cradle('global')->redirect('/control/role/search');
});

/**
 * Render the Role Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }

    // check permissions
    if (!cradle('global')->role('admin:role:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    $adminRoles = cradle('global')->config('roles')['admin'];

    // get post stored as item
    $data['item'] = $request->getPost();
    // get any errors
    $data['errors'] = $response->getValidation();

    // if not empty item
    if (isset($data['item']) && !empty($data['item'])) {
        // get permissions key
        if (isset($data['item']['role_permissions'])) {
            $data['item']['role_permissions'] = array_keys($data['item']['role_permissions']);
        }
    }

    // if not set
    if (!isset($data['item']['role_permissions'])) {
        $data['item']['role_permissions'] = [];
    }

    // define group role variable
    $groups = [];

    // loop admin roles
    foreach ($adminRoles as $key => $role) {
        // get by part
        $parts = explode(':', $role);
        // set action
        $action = 'admin:'.$role;
        // collect group roles
        $groups[$parts[0]]['actions'][] = [
            'action' => $parts[1],
            'role' => $action,
            'checked' => in_array($action, $data['item']['role_permissions']) ? 1 : 0
        ];
    }

    // set grouped roles
    $data['roles'] = $groups;
    $data['page_title'] = 'Create Roles';

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-create page-role-form page-admin';
    $data['title'] = cradle('global')->translate('Roles');
    $body = cradle('/app/admin')->template('role/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
//render page
}, 'render-admin-page');

/**
 * Process the Role Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/role/create', function ($request, $response) {
    // get data on stage
    $data = $request->getStage();

    // get roles
    if (isset($data['role_permissions'])) {
        // return all keys of role permissions
        $data['role_permissions'] = array_keys($data['role_permissions']);
        // set to request role permissions
        $request->setStage('role_permissions', $data['role_permissions']);
    }

    $adminRoles = cradle('global')->config('roles')['admin'];

    $request->setStage('role_permissions', $request->getStage('role_permissions'));

    $request->setStage('role_type', 'admin');

    // trigger create role
    cradle()->trigger('role-create', $request, $response);

    // if error
    if ($response->isError()) {
        // trigger route role create template
        return cradle()->triggerRoute('get', '/control/role/create', $request, $response);
    }

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('role_id', $response->getResults('role_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created role');
    $request->setStage('history_attribute', 'role-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    cradle('global')->flash('Role Successfully Created', 'success');
    return cradle('global')->redirect('/control/role/search');
});


$cradle->get('/control/role/access', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:access', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    $adminRoles = cradle('global')->config('roles')['admin'];

    // get post stored as item
    $data['item'] = $request->getPost();
    // get any errors
    $data['errors'] = $response->getValidation();

    // if not empty item
    if (isset($data['item']) && !empty($data['item'])) {
        // get permissions key
        if (isset($data['item']['role_permissions'])) {
            $data['item']['role_permissions'] = array_keys($data['item']['role_permissions']);
        }
    }

    // if not set
    if (!isset($data['item']['role_permissions'])) {
        $data['item']['role_permissions'] = [];
    }

    // define group role variable
    $groups = [];

    // loop admin roles
    foreach ($adminRoles as $key => $role) {
        // get by part
        $parts = explode(':', $role);
        // set action
        $action = 'admin:'.$role;
        // collect group roles
        $groups[$parts[0]]['actions'][] = [
            'action' => $parts[1],
            'role' => $action,
            'checked' => in_array($action, $data['item']['role_permissions']) ? 1 : 0
        ];
    }

    // set grouped roles
    $data['roles'] = $groups;
    $data['page_title'] = 'Access';

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-role-create page-role-form page-admin';
    $data['title'] = cradle('global')->translate('Access');
    $body = cradle('/app/admin')->template('role/access/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-admin-page');

/**
 * Process the Role Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/remove/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-remove', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('role_id', $response->getResults('role_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' removed role id #' . $response->getResults('role_id'));
    $request->setStage('history_attribute', 'role-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Role was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/role/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Role Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/role/restore/:role_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:role:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/role/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('role-restore', $request, $response);

    $data = $request->getStage();
    $value = ['GET' => $request->getStage(),
              'POST' => $request->getPost(),
              'SERVER' => $_SERVER];
    $request->setStage('role_id', $response->getResults('role_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' restored role id #' . $response->getResults('role_id'));
    $request->setStage('history_attribute', 'role-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Role was Restored');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/role/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
