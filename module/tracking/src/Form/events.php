<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Tracking\Form\Service as FormService;
use Cradle\Module\Tracking\Form\Validator as FormValidator;
use Cradle\Module\Tracking\Question\Service as QuestionService;
use Cradle\Module\Profile\Service as ProfileService;

/**
 * Form Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = FormValidator::getCreateErrors($data);

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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    //profile
    $profileSql = Cradle\Module\Profile\Service::get('sql');

    //save form to database
    $results = $formSql->create($data);

    //link question
    if (isset($data['question_id'])) {
        $formSql->linkQuestion($results['form_id'], $data['question_id']);
    }
    
    //link form
    if (isset($data['profile_id'])) {
        $profileSql->linkForm($data['profile_id'], $results['form_id']);
    }

    //index form
    // $formElastic->create($results['form_id']);

    //invalidate cache
    $formRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Form Publish Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-publish', function ($request, $response) {
    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    //save to database
    $results = $formSql->update([
        'form_id'  => $data['form_id'],
        'form_flag'=> 1
    ]);

    //remove from index
    // $formElastic->remove($data['form_id']);

    //invalidate cache
    $formRedis->removeDetail($data['form_id']);
    $formRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Form Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-duplicate', function ($request, $response) {
    // Variable declaration
    $data = [];
    $id = null;
    $form = null;

    // Checks for staging data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Checks for form_id
    if (isset($data['form_id'])) {
        $id = $data['form_id'];
    } else {
        // Return an error message
        return $response->setError(true, 'Invalid ID');
    }

    $formSql = FormService::get('sql');
    $questionSql = QuestionService::get('sql');
    $profileSql = ProfileService::get('sql');

    // Gets the form from SQL
    // Based on form_id
    $form = $formSql->get($id);

    // Checks if a form was not returned
    if (empty($form)) {
        return $response->setError(true);
    }

    // Unset the form id / form_id
    unset($form['form_id']);

    // Sets the form_flag
    $form['form_flag'] = 0;

    // Alter the form name / form_name
    // Append duplicate string
    $form['form_name'] .= ' - Duplicate';

    // Create a new form
    $newForm = $formSql->create($form);

    // Links the form to the profile
    if (isset($data['profile_id'])) {
        $profileSql->linkForm($data['profile_id'], $newForm['form_id']);
    }

    // At this point, the form was returned
    // Based on form_id
    $searchFilter['filter']['form_id'] = $id;

    // Get the questions
    // Based on form_id
    $results = $questionSql->search($searchFilter);
    // Checks if there are questions to duplicate
    if ($results['rows']) {
        $questions = $results['rows'];

        // Loops through the questions
        foreach ($questions as $question) {
            // Unset the question id / question_id
            unset($question['question_id']);

            // Encodes the json columns
            $question['question_choices'] = json_encode($question['question_choices'], true);
            $question['question_type'] = json_encode($question['question_type'], true);

            // Saves the duplicate question to the database
            $newQuestion = $questionSql->create($question);

            // Link the question to the form
            $formSql->linkQuestion($newForm['form_id'], $newQuestion['question_id']);
        }
    }

    // Returns a success with the newly created form
    return $response->setError(false)->setResults($newForm);
});

/**
 * Form Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['form_id'])) {
        $id = $data['form_id'];
    }

    $active = null;
    if (isset($data['form_active'])) {
        $active = $data['form_active'];
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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $formRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $formElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $formSql->get($id, $active);
        }

        if ($results) {
            //cache it from database or index
            $formRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Form Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-post', function ($request, $response) {
    // Variable Declaration
    $data = [];
    $form = null;
    $post = null;
    $active = null;

    // Checks if there is staging data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Checks if form_id exists
    if (isset($data['form_id'])) {
        $form = $data['form_id'];
    }

    // Checks if post_id exists
    if (isset($data['post_id'])) {
        $post = $data['post_id'];
    }

    // Checks for form_active
    if (isset($data['form_active'])) {
        $active = $data['form_active'];
    }

    // Checks for an ID
    if (!$form && !$post) {
        return $response->setError(true, 'Invalid ID');
    }

    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache') && $form) {
        //get it from cache
        $results = $formRedis->getDetail($form);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $formElastic->get($id);
        }

        // if no results
        if (!$results) {
            // Checks for form_id
            if ($form) {
                $results = $formSql->get($form);
            } else {
                // At this point, there is only a post_id
                $results = $formSql->getPostForm($post, $active);
            }
        }

        // Checks for results
        if ($results) {
            //cache it from database or index
            $formRedis->createDetail($results['form_id'], $results);
        }
    }

    // Checks if there are no results
    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    // At this point there is no errors
    $response->setError(false)->setResults($results);
});

/**
 * Form Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the form detail
    $this->trigger('form-detail', $request, $response);

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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    //save to database
    $results = $formSql->update([
        'form_id' => $data['form_id'],
        'form_active' => 0
    ]);

    //remove from index
    // $formElastic->remove($data['form_id']);

    //invalidate cache
    $formRedis->removeDetail($data['form_id']);
    $formRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Form Permanent Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-permanent', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the form detail
    $this->trigger('form-detail', $request, $response);
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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');
    //save to database
    $results = $formSql->update([
        'form_id' => $data['form_id'],
        'form_active' => 2
    ]);
    //remove from index
    // $formElastic->remove($data['form_id']);
    //invalidate cache
    $formRedis->removeDetail($data['form_id']);
    $formRedis->removeSearch();
    $response->setError(false)->setResults($results);
});

/**
 * Form Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the form detail
    $this->trigger('form-detail', $request, $response);

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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    //save to database
    $results = $formSql->update([
        'form_id' => $data['form_id'],
        'form_active' => 1
    ]);

    //create index
    // $formElastic->create($data['form_id']);

    //invalidate cache
    $formRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Form Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-search', function ($request, $response) {
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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $formRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $formElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $formSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $formRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Form Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('form-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the form detail
    $this->trigger('form-detail', $request, $response);

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
    $errors = FormValidator::getUpdateErrors($data);

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
    $formSql = FormService::get('sql');
    $formRedis = FormService::get('redis');
    // $formElastic = FormService::get('elastic');

    //save form to database
    $results = $formSql->update($data);

    //index form
    // $formElastic->update($response->getResults('form_id'));

    //invalidate cache
    $formRedis->removeDetail($response->getResults('form_id'));
    $formRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
