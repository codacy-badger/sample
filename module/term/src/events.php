<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Term\Service as TermService;
use Cradle\Module\Term\Validator as TermValidator;

/**
 * Term Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = TermValidator::getCreateErrors($data);

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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    //save term to database
    $results = $termSql->create($data);

    //index term
    // $termElastic->create($results['term_id']);

    //invalidate cache
    $termRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Term Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['term_id'])) {
        $id = $data['term_id'];
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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $termRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $termElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $termSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $termRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Term Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the term detail
    $this->trigger('term-detail', $request, $response);

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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    //save to database
    $results = $termSql->update([
        'term_id' => $data['term_id'],
        'term_active' => 0
    ]);

    // $termElastic->update($response->getResults('term_id'));

    //invalidate cache
    $termRedis->removeDetail($data['term_id']);
    $termRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Term Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the term detail
    $this->trigger('term-detail', $request, $response);

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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    //save to database
    $results = $termSql->update([
        'term_id' => $data['term_id'],
        'term_active' => 1
    ]);

    //create index
    // $termElastic->create($data['term_id']);

    //invalidate cache
    $termRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Term Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-search', function ($request, $response) {
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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $termRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $termElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $termSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $termRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Term Fuzzy search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-fuzzy-search', function ($request, $response) {
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
    // $termElastic = TermService::get('elastic');

    if (!isset($data['term']) || !is_string($data['term'])) {
        return $response->setError(true, 'Invalid parameter');
    }

    // $results = $termElastic->fuzzy($data);
    //set response format
    $response->setError(false)->setResults($results);
});
/**
 * Term Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('term-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the term detail
    $this->trigger('term-detail', $request, $response);

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
    $errors = TermValidator::getUpdateErrors($data);

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
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    // $termElastic = TermService::get('elastic');

    //save term to database
    $results = $termSql->update($data);

    //index term
    // $termElastic->update($response->getResults('term_id'));

    //invalidate cache
    $termRedis->removeDetail($response->getResults('term_id'));
    $termRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
