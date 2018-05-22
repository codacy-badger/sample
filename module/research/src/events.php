<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Research\Service as ResearchService;
use Cradle\Module\Research\Validator as ResearchValidator;

/**
 * Research Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ResearchValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['research_position'])) {
        $data['research_position'] = json_encode($data['research_position']);
    }

    if(isset($data['research_location'])) {
        $data['research_location'] = json_encode($data['research_location']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    //save research to database
    $results = $researchSql->create($data);

    //index research
    // $researchElastic->create($results['research_id']);

    //invalidate cache
    $researchRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Research Companies
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-companies', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $companies = null;
    if (isset($data['companies'])) {
        $companies = $data['companies'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$companies) {
        return $response->setError(true, 'Invalid Companies');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    //this/these will be used a lot
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    $researchElastic = ResearchService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $researchRedis->getCompanies($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $researchElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $researchSql->getCompanies($companies);
        }

        if ($results) {
            //cache it from database or index
            $researchRedis->getCompanies($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $researchSql = ResearchService::get('sql');



    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Research Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['research_id'])) {
        $id = $data['research_id'];
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
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $researchRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $researchElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $researchSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $researchRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Research Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the research detail
    $this->trigger('research-detail', $request, $response);

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
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    //save to database
    $results = $researchSql->update([
        'research_id' => $data['research_id'],
        'research_active' => 0
    ]);

    // $researchElastic->update($response->getResults('research_id'));

    //invalidate cache
    $researchRedis->removeDetail($data['research_id']);
    $researchRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Research Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the research detail
    $this->trigger('research-detail', $request, $response);

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
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    //save to database
    $results = $researchSql->update([
        'research_id' => $data['research_id'],
        'research_active' => 1
    ]);

    //create index
    // $researchElastic->create($data['research_id']);

    //invalidate cache
    $researchRedis->removeSearch();

    $response->setError(false)->setResults($results);
});


/**
 * Research Companies
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-top-companies', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $researchSql = ResearchService::get('sql');

    //get it from database
    $results = $researchSql->getTopCompanies($data['location']);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});


/**
 * Research Top Positions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-top-positions', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $companies = null;
    if (isset($data['companies'])) {
        $companies = $data['companies'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$companies) {
        return $response->setError(true, 'Invalid Companies');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $researchSql = ResearchService::get('sql');

    //get it from database
    $results = $researchSql->getTopPositions($data);

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});
/**
 * Research Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-search', function ($request, $response) {
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
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $researchRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $researchElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $researchSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $researchRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});



/**
 * Research Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('research-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the research detail
    $this->trigger('research-detail', $request, $response);

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
    $errors = ResearchValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['research_position'])) {
        $data['research_position'] = json_encode($data['research_position']);
    }

    if(isset($data['research_location'])) {
        $data['research_location'] = json_encode($data['research_location']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $researchSql = ResearchService::get('sql');
    $researchRedis = ResearchService::get('redis');
    // $researchElastic = ResearchService::get('elastic');

    //save research to database
    $results = $researchSql->update($data);

    //index research
    // $researchElastic->update($response->getResults('research_id'));

    //invalidate cache
    $researchRedis->removeDetail($response->getResults('research_id'));
    $researchRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
