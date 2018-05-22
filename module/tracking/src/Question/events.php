<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Question\Service as QuestionService;
use Cradle\Module\Tracking\Question\Validator as QuestionValidator;

use Cradle\Module\Tracking\Form\Service as FormService;

/**
 * Question Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = QuestionValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $data['question_type'] = array();

    // Checks for question_choices
    if (isset($data['question_choices'])) {
        $data['question_choices'] = json_encode($data['question_choices']);
        $data['question_type'][] = 'choices';
    }

    // Checks for question_custom
    if (isset($data['question_custom']) && $data['question_custom']) {
        $data['question_type'][] = 'custom';
    }

    // Checks for question_file
    if (isset($data['question_file']) && $data['question_file']) {
        $data['question_type'][] = 'file';
    }

    // Implodes the array into a string for SQL
    $data['question_type'] = implode(',', $data['question_type']);

    //----------------------------//
    // 4. Process Data
    // Question services
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    // Form Services
    $formSql = FormService::get('sql');

    //save question to database
    $results = $questionSql->create($data);

    //link answer
    if (isset($data['answer_id'])) {
        $questionSql->linkAnswer($results['question_id'], $data['answer_id']);
    }

    if (isset($data['form_id'])) {
        $formSql->linkQuestion($data['form_id'], $results['question_id']);
    }

    // Checks if there are question_choices
    if ($results['question_choices']) {
        $results['question_choices'] = json_decode($results['question_choices'], true);
    } else {
        $results['question_choices'] = [];
    }

    // Explodes the question_type
    $results['question_type'] = explode(',', $results['question_type']);

    //index question
    // $questionElastic->create($results['question_id']);

    //invalidate cache
    $questionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Question Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }
    $id = null;
    if (isset($data['question_id'])) {
        $id = $data['question_id'];
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
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');
    $results = null;
    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $questionRedis->getDetail($id);
    }
    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $questionElastic->get($id);
        }
        //if no results
        if (!$results) {
            //get it from database
            $results = $questionSql->get($id);
        }
        if ($results) {
            //cache it from database or index
            $questionRedis->createDetail($id, $results);
        }
    }
    if (!$results) {
        return $response->setError(true, 'Not Found');
    }
    $response->setError(false)->setResults($results);
});

/**
 * Question Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-priority', function ($request, $response) {
    //----------------------------//
    //get the question detail
    $this->trigger('question-detail', $request, $response);

    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['question_id'])) {
        $id = $data['question_id'];
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
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    $results = null;

    //save question to database
    $results = $questionSql->update($data);

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $questionRedis->getDetail($id);
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Question Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the question detail
    $this->trigger('question-detail', $request, $response);

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
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    //save to database
    $results = $questionSql->update([
        'question_id' => $data['question_id'],
        'question_active' => 0
    ]);

    //remove from index
    // $questionElastic->remove($data['question_id']);

    //invalidate cache
    $questionRedis->removeDetail($data['question_id']);
    $questionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Question Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the question detail
    $this->trigger('question-detail', $request, $response);

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
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    //save to database
    $results = $questionSql->update([
        'question_id' => $data['question_id'],
        'question_active' => 1
    ]);

    //create index
    // $questionElastic->create($data['question_id']);

    //invalidate cache
    $questionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Question Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-search', function ($request, $response) {
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
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $questionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $questionElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $questionSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $questionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Question Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the question detail
    $this->trigger('question-detail', $request, $response);

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
    $errors = QuestionValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    $data['question_type'] = array();

    // Checks for question_choices
    if (isset($data['question_choices'])) {
        $data['question_choices'] = json_encode($data['question_choices']);
        $data['question_type'][] = 'choices';
    }

    // Checks for question_custom
    if (isset($data['question_custom']) && $data['question_custom']) {
        $data['question_type'][] = 'custom';
    }

    // Checks for question_file
    if (isset($data['question_file']) && $data['question_file']) {
        $data['question_type'][] = 'file';
    }

    // Implodes the array into a string for SQL
    $data['question_type'] = implode(',', $data['question_type']);

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    // $questionElastic = QuestionService::get('elastic');

    //save question to database
    $results = $questionSql->update($data);

    //index question
    // $questionElastic->update($response->getResults('question_id'));

    //invalidate cache
    $questionRedis->removeDetail($response->getResults('question_id'));
    $questionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Question View Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('question-view', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
     if (!$data['post_id']) {
        return $response->setError(true, 'Invalid ID');
    }
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $questionSql = QuestionService::get('sql');
    $questionRedis = QuestionService::get('redis');
    $questionElastic = QuestionService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $questionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $questionElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $questionSql->viewQuestion($data);
        }

        if ($results) {
            //cache it from database or index
            $questionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});
