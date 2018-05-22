<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Label\Service as LabelService;
use Cradle\Module\Tracking\Label\Validator as LabelValidator;

/**
 * Label Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = LabelValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['label_custom'])) {
        $data['label_custom'] = json_encode($data['label_custom']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    $profileSql = Cradle\Module\Profile\Service::get('sql');

    //save label to database
    $results = $labelSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $profileSql->linkLabel($data['profile_id'], $results['label_id']);
    }

    //index label
    // $labelElastic->create($results['label_id']);

    //invalidate cache
    $labelRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Label Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['label_id'])) {
        $id = $data['label_id'];
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
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $labelRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $labelElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $labelSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $labelRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});


/**
 * Label Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-profile-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['profile_id'])) {
        $id = $data['profile_id'];
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
    $labelSql = LabelService::get('sql');

    $results = $labelSql->getByProfile($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});
/**
 * Label Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the label detail
    $this->trigger('label-detail', $request, $response);

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
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    //save to database
    $results = $labelSql->update([
        'label_id' => $data['label_id'],
        'label_active' => 0
    ]);

    //remove from index
    // $labelElastic->remove($data['label_id']);

    //invalidate cache
    $labelRedis->removeDetail($data['label_id']);
    $labelRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Label Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the label detail
    $this->trigger('label-detail', $request, $response);

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
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    //save to database
    $results = $labelSql->update([
        'label_id' => $data['label_id'],
        'label_active' => 1
    ]);

    //create index
    // $labelElastic->create($data['label_id']);

    //invalidate cache
    $labelRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Label Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-search', function ($request, $response) {
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
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $labelRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $labelElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $labelSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $labelRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Label Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('label-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the label detail
    $this->trigger('label-detail', $request, $response);

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
    $errors = LabelValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['label_custom'])) {
        $data['label_custom'] = json_encode($data['label_custom']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $labelSql = LabelService::get('sql');
    $labelRedis = LabelService::get('redis');
    // $labelElastic = LabelService::get('elastic');

    //save label to database
    $results = $labelSql->update($data);

    //index label
    // $labelElastic->update($response->getResults('label_id'));

    //invalidate cache
    $labelRedis->removeDetail($response->getResults('label_id'));
    $labelRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
