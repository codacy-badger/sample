<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Website\Service as WebsiteService;
use Cradle\Module\Crawler\Website\Validator as WebsiteValidator;

/**
 * Website Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = WebsiteValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['website_settings'])) {
        $data['website_settings'] = json_encode($data['website_settings']);
    }

    //----------------------------//
    // 4. Process Data
    //save website to database
    $results = WebsiteService::get('sql')->create($data);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Website Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['website_id'])) {
        $id = $data['website_id'];
    } else if (isset($data['website_root'])) {
        $id = $data['website_root'];
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
    //get it from database
    $results = WebsiteService::get('sql')->get($id);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Website Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the website detail
    $this->trigger('website-detail', $request, $response);

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
    //save to database
    $results = WebsiteService::get('sql')->update([
        'website_id' => $data['website_id'],
        'website_active' => 0
    ]);

    $response->setError(false)->setResults($results);
});

/**
 * Website Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the website detail
    $this->trigger('website-detail', $request, $response);

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
    //save to database
    $results = WebsiteService::get('sql')->update([
        'website_id' => $data['website_id'],
        'website_active' => 1
    ]);

    $response->setError(false)->setResults($results);
});

/**
 * Website Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-search', function ($request, $response) {
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
    //get it from database
    $results = WebsiteService::get('sql')->search($data);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Website Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('website-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the website detail
    $this->trigger('website-detail', $request, $response);

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
    $errors = WebsiteValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['website_settings'])) {
        $data['website_settings'] = json_encode($data['website_settings']);
    }

    //----------------------------//
    // 4. Process Data
    //save website to database
    $results = WebsiteService::get('sql')->update($data);

    //return response format
    $response->setError(false)->setResults($results);
});

/**
* Website Test Job
*
* @param Request $request
* @param Response $response
*/
$cradle->on('website-test', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }
    //----------------------------//
    // 2. Validate Data
    $errors = WebsiteValidator::getCreateErrors($data);
    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }
    //----------------------------//
    // 3. Prepare Data
    //link
    $link = $request->getStage('website_settings', 'testing_url');
    $type = $request->getStage('website_settings', 'testing_type');
    if (!$link) {
        $link = $request->getStage('website_start');
    }
    $request->setStage('webpage_link', $link);
    $response->setResults('webpage_type', $type);
    //settings
    $settings = $request->getStage('website_settings');
    $response->setResults('website_settings', $settings);
    //crop
    $crop = $request->getStage('website_crop');
    $response->setResults('website_crop', $crop);
    //----------------------------//
    // 4. Process Data
    $this->trigger('crawl-phantom', $request, $response);
});
