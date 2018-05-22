<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Template\Service as TemplateService;
use Cradle\Module\Template\Validator as TemplateValidator;

/**
 * Template Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = TemplateValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Input')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    //save template to database
    $results = $templateSql->create($data);

    //index template
    // $templateElastic->create($results['template_id']);

    //invalidate cache
    $templateRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Template Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['template_id'])) {
        $id = $data['template_id'];
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
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $templateRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $templateElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $templateSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $templateRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Template Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the template detail
    $this->trigger('template-detail', $request, $response);

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
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    //save to database
    $results = $templateSql->update([
        'template_id' => $data['template_id'],
        'template_active' => 0
    ]);

    //remove from index
    // $templateElastic->remove($data['template_id']);

    //invalidate cache
    $templateRedis->removeDetail($data['template_id']);
    $templateRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Template Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the template detail
    $this->trigger('template-detail', $request, $response);

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
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    //save to database
    $results = $templateSql->update([
        'template_id' => $data['template_id'],
        'template_active' => 1
    ]);

    //create index
    // $templateElastic->create($data['template_id']);

    //invalidate cache
    $templateRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Template Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-search', function ($request, $response) {
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
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $templateRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $templateElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $templateSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $templateRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Template Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the template detail
    $this->trigger('template-detail', $request, $response);

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
    $errors = TemplateValidator::getUpdateErrors($data);

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
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    //save template to database
    $results = $templateSql->update($data);

    //index template
    // $templateElastic->update($response->getResults('template_id'));

    //invalidate cache
    $templateRedis->removeDetail($response->getResults('template_id'));
    $templateRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Template Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('template-bulk-action', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();
    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $templateSql = TemplateService::get('sql');
    $templateRedis = TemplateService::get('redis');
    // $templateElastic = TemplateService::get('elastic');

    //save to database
    $results = $templateSql->bulkAction(
        $data['bulk_ids'],
        $data['bulk_value'],
        $data['bulk_field']
    );

    foreach ($data['bulk_ids'] as $id) {
        //remove from index
        // $templateElastic->remove($id);

        //invalidate cache
        $templateRedis->removeDetail($id);
        $templateRedis->removeSearch();
    }

    $response->setError(false)->setResults($results);
});
