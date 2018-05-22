<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Service\Service as ServiceService;
use Cradle\Module\Service\Validator as ServiceValidator;

/**
 * Service Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ServiceValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if(isset($data['service_meta'])) {
        $data['service_meta'] = json_encode($data['service_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');

    //save service to database
    $results = $serviceSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $serviceSql->linkProfile($results['service_id'], $data['profile_id']);
        //update credits
        $this->trigger('profile-update-credits', $request, $response);
    }

    //index service
    // $serviceElastic->create($results['service_id']);
    //invalidate cache
    $serviceRedis->removeSearch();
    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Service Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }
    $id = null;
    if (isset($data['service_id'])) {
        $id = $data['service_id'];
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
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');
    $results = null;
    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $serviceRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $serviceElastic->get($id);
        }
        //if no results
        if (!$results) {
            //get it from database
            $results = $serviceSql->get($id);
        }
        if ($results) {
            //cache it from database or index
            $serviceRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Service Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the service detail
    $this->trigger('service-detail', $request, $response);

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
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');

    //save to database
    $results = $serviceSql->update([
        'service_id' => $data['service_id'],
        'service_active' => 0
    ]);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);
    // $serviceElastic->update($response->getResults('profile_id'));

    //invalidate cache
    $serviceRedis->removeDetail($data['service_id']);
    $serviceRedis->removeSearch();
    $response->setError(false)->setResults($results);
});

/**
 * Service Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the service detail
    $this->trigger('service-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();
    if(isset($data['service_meta'])) {
        $data['service_meta'] = json_encode($data['service_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');

    //save to database
    $results = $serviceSql->update([
        'service_id' => $data['service_id'],
        'service_active' => 1
    ]);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);

    //create index
    // $serviceElastic->create($data['service_id']);

    //invalidate cache
    $serviceRedis->removeSearch();
    $response->setError(false)->setResults($results);
});

/**
 * Service Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-search', function ($request, $response) {
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
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');
    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $serviceRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $serviceElastic->search($data);
        }
        //if no results
        if (!$results) {
            //get it from database
            $results = $serviceSql->search($data);
        }
        if ($results) {
            //cache it from database or index
            $serviceRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});



/**
 * Service Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('service-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the service detail
    $this->trigger('service-detail', $request, $response);

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
    $errors = ServiceValidator::getUpdateErrors($data);
    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $serviceSql = ServiceService::get('sql');
    $serviceRedis = ServiceService::get('redis');
    // $serviceElastic = ServiceService::get('elastic');

    //save service to database
    $results = $serviceSql->update($data);

    //update credits
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-update-credits', $request, $response);

    //index service
    // $serviceElastic->update($response->getResults('service_id'));

    //invalidate cache
    $serviceRedis->removeDetail($response->getResults('service_id'));
    $serviceRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/* Calculate credit spent on a service
 *
 * @param Request $request
 * @param Response $response
 */

$cradle->on('credit-spent', function ($request, $response) {
    $request->setStage('range', 0);
    cradle()->trigger('service-search', $request, $response);
    $request->setStage('range', 10);
});
