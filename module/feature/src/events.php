<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Feature\Service as FeatureService;
use Cradle\Module\Feature\Validator as FeatureValidator;

use Cradle\Module\Utility\File;

/**
 * Feature Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //set name equal to title
    if (isset($data['feature_title'])) {
        $data['feature_name'] = $data['feature_title'];
    }

    //----------------------------//
    // 2. Validate Data
    $errors = FeatureValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['feature_keywords'])) {
        $data['feature_keywords'] = json_encode($data['feature_keywords']);
    }

    if(isset($data['feature_links'])) {
        $data['feature_links'] = json_encode($data['feature_links']);
    }

    if(isset($data['feature_meta'])) {
        $data['feature_meta'] = json_encode($data['feature_meta']);
    }

    //if there is an image
    if (isset($data['feature_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['feature_image'] = File::base64ToS3($data['feature_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['feature_image'] = File::base64ToUpload($data['feature_image'], $upload);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    //save feature to database
    $results = $featureSql->create($data);

    //index feature
    // $featureElastic->create($results['feature_id']);

    //invalidate cache
    $featureRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Feature Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['feature_id'])) {
        $id = $data['feature_id'];
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
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $featureRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $featureElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $featureSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $featureRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Feature Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-detail-slug', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $slug = null;
    if (isset($data['feature_slug'])) {
        $slug = $data['feature_slug'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$slug) {
        return $response->setError(true, 'Invalid Slug');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $featureRedis->getDetail($slug);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $featureElastic->get($slug);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $featureSql->getSlug($slug);
        }

        if ($results) {
            //cache it from database or index
            $featureRedis->createDetail($slug, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Feature Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the feature detail
    $this->trigger('feature-detail', $request, $response);

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
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    //save to database
    $results = $featureSql->update([
        'feature_id' => $data['feature_id'],
        'feature_active' => 0
    ]);

    //remove from index
    // $featureElastic->remove($data['feature_id']);

    //invalidate cache
    $featureRedis->removeDetail($data['feature_id']);
    $featureRedis->removeDetail($data['feature_slug']);
    $featureRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Feature Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the feature detail
    $this->trigger('feature-detail', $request, $response);

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
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    //save to database
    $results = $featureSql->update([
        'feature_id' => $data['feature_id'],
        'feature_active' => 1
    ]);

    //create index
    // $featureElastic->create($data['feature_id']);

    //invalidate cache
    $featureRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Feature Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-search', function ($request, $response) {
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
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $featureRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $featureElastic->search($data);
        }

        //if no results
        if (!$results) {
            //order by name ascending
            $data['order']['feature_title'] = 'ASC';

            //get it from database
            $results = $featureSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $featureRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Feature Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('feature-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the feature detail
    $this->trigger('feature-detail', $request, $response);

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
    $errors = FeatureValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['feature_keywords'])) {
        $data['feature_keywords'] = json_encode($data['feature_keywords']);
    }

    if(isset($data['feature_links'])) {
        $data['feature_links'] = json_encode($data['feature_links']);
    }

    if(isset($data['feature_meta'])) {
        $data['feature_meta'] = json_encode($data['feature_meta']);
    }

    //if there is an image
    if (isset($data['feature_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['feature_image'] = File::base64ToS3($data['feature_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['feature_image'] = File::base64ToUpload($data['feature_image'], $upload);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $featureSql = FeatureService::get('sql');
    $featureRedis = FeatureService::get('redis');
    // $featureElastic = FeatureService::get('elastic');

    //save feature to database
    $results = $featureSql->update($data);

    //index feature
    // $featureElastic->update($response->getResults('feature_id'));

    //invalidate cache
    $featureRedis->removeDetail($response->getResults('feature_id'));
    $featureRedis->removeDetail($response->getResults('feature_slug'));
    $featureRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
