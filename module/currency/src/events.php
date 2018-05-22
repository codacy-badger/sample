<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Currency\Service as CurrencyService;
use Cradle\Module\Currency\Validator as CurrencyValidator;

/**
 * Currency Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = CurrencyValidator::getCreateErrors($data);

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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    //save currency to database
    $results = $currencySql->create($data);

    //index currency
    // $currencyElastic->create($results['currency_id']);

    //invalidate cache
    $currencyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Currency Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['currency_id'])) {
        $id = $data['currency_id'];
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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $currencyRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $currencyElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $currencySql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $currencyRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Currency Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the currency detail
    $this->trigger('currency-detail', $request, $response);

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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    //save to database
    $results = $currencySql->update([
        'currency_id' => $data['currency_id'],
        'currency_active' => 0
    ]);

    //remove from index
    // $currencyElastic->remove($data['currency_id']);

    //invalidate cache
    $currencyRedis->removeDetail($data['currency_id']);
    $currencyRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Currency Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the currency detail
    $this->trigger('currency-detail', $request, $response);

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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    //save to database
    $results = $currencySql->update([
        'currency_id' => $data['currency_id'],
        'currency_active' => 1
    ]);

    //create index
    // $currencyElastic->create($data['currency_id']);

    //invalidate cache
    $currencyRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Currency Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-search', function ($request, $response) {
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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $currencyRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $currencyElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $currencySql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $currencyRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Currency Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('currency-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the currency detail
    $this->trigger('currency-detail', $request, $response);

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
    $errors = CurrencyValidator::getUpdateErrors($data);

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
    $currencySql = CurrencyService::get('sql');
    $currencyRedis = CurrencyService::get('redis');
    // $currencyElastic = CurrencyService::get('elastic');

    //save currency to database
    $results = $currencySql->update($data);

    //index currency
    // $currencyElastic->update($response->getResults('currency_id'));

    //invalidate cache
    $currencyRedis->removeDetail($response->getResults('currency_id'));
    $currencyRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
