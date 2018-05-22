<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Answer\Service as AnswerService;
use Cradle\Module\Tracking\Answer\Validator as AnswerValidator;

/**
 * Answer Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AnswerValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['answer_choices'])) {
        $data['answer_choices'] = json_encode($data['answer_choices']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    $questionSql = Cradle\Module\Tracking\Question\Service::get('sql');

    //save answer to database
    $results = $answerSql->create($data);

    //link profile
    if(isset($data['question_id'])) {
        $questionSql->linkAnswer($data['question_id'], $results['answer_id']);
    }

    //index answer
    // $answerElastic->create($results['answer_id']);

    //invalidate cache
    $answerRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Answer Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['answer_id'])) {
        $id = $data['answer_id'];
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
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $answerRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $answerElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $answerSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $answerRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Answer Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the answer detail
    $this->trigger('answer-detail', $request, $response);

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
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    //save to database
    $results = $answerSql->update([
        'answer_id' => $data['answer_id'],
        'answer_active' => 0
    ]);

    //remove from index
    // $answerElastic->remove($data['answer_id']);

    //invalidate cache
    $answerRedis->removeDetail($data['answer_id']);
    $answerRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Answer Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the answer detail
    $this->trigger('answer-detail', $request, $response);

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
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    //save to database
    $results = $answerSql->update([
        'answer_id' => $data['answer_id'],
        'answer_active' => 1
    ]);

    //create index
    // $answerElastic->create($data['answer_id']);

    //invalidate cache
    $answerRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Answer Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-search', function ($request, $response) {
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
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $answerRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $answerElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $answerSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $answerRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Answer Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('answer-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the answer detail
    $this->trigger('answer-detail', $request, $response);

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
    $errors = AnswerValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['answer_choices'])) {
        $data['answer_choices'] = json_encode($data['answer_choices']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $answerSql = AnswerService::get('sql');
    $answerRedis = AnswerService::get('redis');
    // $answerElastic = AnswerService::get('elastic');

    //save answer to database
    $results = $answerSql->update($data);

    //index answer
    // $answerElastic->update($response->getResults('answer_id'));

    //invalidate cache
    $answerRedis->removeDetail($response->getResults('answer_id'));
    $answerRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
