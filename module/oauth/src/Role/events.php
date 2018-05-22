<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

 use Cradle\Sql\SqlFactory;

use Cradle\Module\Oauth\Role\Service as RoleService;
use Cradle\Module\Oauth\Role\Validator as RoleValidator;

/**
 * Role Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = RoleValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['role_permissions'])) {
        $data['role_permissions'] = json_encode($data['role_permissions']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    //save role to database
    $results = $roleSql->create($data);
    //link auth
    if (isset($data['auth_id'])) {
        $roleSql->linkAuth($results['role_id'], $data['auth_id']);
    }

    //index role
    // $roleElastic->create($results['role_id']);

    //invalidate cache
    $roleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Role Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['role_id'])) {
        $id = $data['role_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $roleRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $roleElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $roleSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $roleRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Role Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the role detail
    $this->trigger('role-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    //save to database
    $results = $roleSql->update([
        'role_id' => $data['role_id'],
        'role_active' => 0
    ]);

    //remove from index
    // $roleElastic->remove($data['role_id']);

    //invalidate cache
    $roleRedis->removeDetail($data['role_id']);
    $roleRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Role Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the role detail
    $this->trigger('role-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    //save to database
    $results = $roleSql->update([
        'role_id' => $data['role_id'],
        'role_active' => 1
    ]);

    //create index
    // $roleElastic->create($data['role_id']);

    //invalidate cache
    $roleRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Role Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $roleRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $roleElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $roleSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $roleRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Role Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the role detail
    $this->trigger('role-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = RoleValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['role_permissions'])) {
        $data['role_permissions'] = json_encode($data['role_permissions']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    //save role to database
    $results = $roleSql->update($data);

    //index role
    // $roleElastic->update($response->getResults('role_id'));

    //invalidate cache
    $roleRedis->removeDetail($response->getResults('role_id'));
    $roleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links Role to auth
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-link-auth', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['role_id'], $data['auth_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    $results = $roleSql->linkAuth(
        $data['role_id'],
        $data['auth_id']
    );

    //index post
    // $roleElastic->update($data['role_id']);

    //invalidate cache
    $roleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks Role from auth
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-unlink-auth', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['role_id'], $data['auth_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    $results = $roleSql->unlinkAuth(
        $data['role_id'],
        $data['auth_id']
    );

    //index post
    // $roleElastic->update($data['role_id']);

    //invalidate cache
    $roleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all Role from auth
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('role-unlinkall-auth', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['role_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $roleSql = RoleService::get('sql');
    $roleRedis = RoleService::get('redis');
    // $roleElastic = RoleService::get('elastic');

    $results = $roleSql->unlinkAllAuth($data['role_id']);

    //index post
    // $roleElastic->update($data['role_id']);

    //invalidate cache
    $roleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-detail', function ($request, $response) {
    //if the auth-detail from auth returned an error
    //----------------------------//
    // 1. Check Error
    if ($response->isError()) {
        //do nothing
        return;
    }

    //----------------------------//
    // 2. Get Response Data
    $data = $response->getResults();

    //----------------------------//
    // 3. Get Role
    // load mysql
    $database = SqlFactory::load(cradle('global')->service('sql-main'));

    $row = [];

    // if auth id
    if (isset($data['auth_id'])) {
        // get role
        $row = $database
            ->search('auth')
            ->setColumns('role.*')
            ->leftJoinUsing('role_auth', 'auth_id')
            ->leftJoinUsing('role', 'role_id')
            ->filterByAuthId($data['auth_id'])
            ->getRow();

        // decode permissions
        if ($row && $row['role_permissions']) {
            $row['role_permissions'] = json_decode($row['role_permissions'], true);
        }
    }

    //----------------------------//
    // 4. Merge Results
    // merge results
    $results = array_merge($data, $row);

    // set response results
    $response->setResults($results);
});

/**
 * Auth Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-update', function ($request, $response) {
    //if the auth-update from auth returned an error
    //----------------------------//
    // 1. Check Error
    if ($response->isError()) {
        //do nothing
        return;
    }

    //----------------------------//
    // 2. Get Response Data
    $data = $response->getResults();

    $roleSql = RoleService::get('sql');

    if (!isset($data['_role_id']) || empty($data['_role_id'])) {
        if (isset($data['role_id']) && !empty($data['role_id'])) {
            $roleSql->unlinkAuth($data['role_id'], $data['auth_id']);
        }

        return $response->setResults($data);
    }

    if (!empty($data['role_id'])) {
        if ($data['role_id'] === $data['_role_id']) {
            return $response->setResults($data);
        }

        $roleSql->unlinkAuth($data['role_id'], $data['auth_id']);
    }

    $roleSql->linkAuth($data['_role_id'], $data['auth_id']);

    // set response results
    $response->setResults($data);
});
