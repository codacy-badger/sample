<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Action\Service as ActionService;
use Cradle\Module\Action\Validator as ActionValidator;
use Cradle\Module\Profile\Service as ProfileService;
use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Action Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ActionValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['action_tags'])) {
        $data['action_tags'] = json_encode($data['action_tags']);
    }

    if(isset($data['action_when'])) {
        foreach ($data['action_when'] as $row => $condition) {
            // if no value, remove
            if (empty($condition['value'])) {
                unset($data['action_when'][$row]);
            }
        }
        $data['action_when'] = json_encode($data['action_when']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    //save action to database
    $results = $actionSql->create($data);
    //link template
    if(isset($data['template_id'])) {
        $actionSql->linkTemplate($results['action_id'], $data['template_id']);
    }

    //index action
    // $actionElastic->create($results['action_id']);

    //invalidate cache
    $actionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Action Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['action_id'])) {
        $id = $data['action_id'];
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
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $actionRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $actionElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $actionSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $actionRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Action Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the action detail
    $this->trigger('action-detail', $request, $response);

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
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    //save to database
    $results = $actionSql->update([
        'action_id' => $data['action_id'],
        'action_active' => 0
    ]);

    //remove from index
    // $actionElastic->remove($data['action_id']);

    //invalidate cache
    $actionRedis->removeDetail($data['action_id']);
    $actionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Action Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-bulk-action', function ($request, $response) {
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
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    //save to database
    $results = $actionSql->bulkAction(
        $data['bulk_ids'],
        $data['bulk_value'],
        $data['bulk_field']
    );

    foreach ($data['bulk_ids'] as $id) {
        //remove from index
        // $actionElastic->remove($id);

        //invalidate cache
        $actionRedis->removeDetail($id);
        $actionRedis->removeSearch();
    }

    $response->setError(false)->setResults($results);
});

/**
 * Action Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the action detail
    $this->trigger('action-detail', $request, $response);

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
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    //save to database
    $results = $actionSql->update([
        'action_id' => $data['action_id'],
        'action_active' => 1
    ]);

    //create index
    // $actionElastic->create($data['action_id']);

    //invalidate cache
    $actionRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Action Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-search', function ($request, $response) {
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
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $actionRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $actionElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $actionSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $actionRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Action Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the action detail
    $this->trigger('action-detail', $request, $response);
    $action = $response->getResults();

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
    $errors = ActionValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    if (isset($data['action_tags'])) {
        $data['action_tags'] = json_encode($data['action_tags']);
    }

    if(isset($data['action_when'])) {
        foreach ($data['action_when'] as $row => $condition) {
            // if no value, remove
            if (empty($condition['value']) && $condition['value'] != '0') {
                unset($data['action_when'][$row]);
            }
        }

        $data['action_when'] = json_encode($data['action_when']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $actionSql = ActionService::get('sql');
    $actionRedis = ActionService::get('redis');
    // $actionElastic = ActionService::get('elastic');

    //save action to database
    $results = $actionSql->update($data);

    //index action
    // $actionElastic->update($response->getResults('action_id'));

    // if template is set, re-assign
    if (isset($data['template_id'])) {
        $actionSql->unlinkTemplate($action['action_id'], $action['template_id']);

        if (is_numeric($data['template_id'])) {
            $actionSql->linkTemplate($action['action_id'], $data['template_id']);
        }
    }

    //invalidate cache
    $actionRedis->removeDetail($response->getResults('action_id'));
    $actionRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});


/**
 * Action Event Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('action-check-event', function ($request, $response) {
    // get data
    $data = $request->getStage();

    // validate data
    // is event name available?
    // is atleast profile_id from profile available?
    if (!isset($data['action_event']) || !isset($data['profile_id'])) {
        // if no event available, do not proceed
        //  this is an invalid request
        return $response->setError(true, 'Invalid Request');
    }

    // if there's a valid name
    // then check against the action table
    // pull action filter by event name
    $actionRequest = new Request;
    $actionResponse = new Response;

    $actionRequest->setStage(
        'filter', ['action_event' => $data['action_event']]
    );
    $this->trigger('action-search', $actionRequest, $actionResponse);
    $actions = $actionResponse->getResults('rows');

    // is there an action?
    // if none, return no action needed
    if (!$actions) {
        return $response->setError(false, 'No Action needed');
    }

    // else if yes pull the user details
    // we will be need user details later on
    $profileRequest = new Request;
    $profileResponse = new Response;
    $profileRequest->setStage('profile_id', $data['profile_id']);

    $this->trigger('profile-detail', $profileRequest, $profileResponse);
    $profile = $profileResponse->getResults();

    // for now loop through each event
    foreach ($actions as $key => $action) {
        // check for conditions
        // set to true first so we'll not do a separate
        // check for no conditions set
        $valid = true;

        foreach ($action['action_when'] as $condition) {
            $current = false;
            $operator = $condition['operator'];
            $value1 = $profile[$condition['field']];
            $value2 = $condition['value'];

            if ($condition['field'] == 'profile_type') {
                // if looking for a seeker and is profile is seeker
                if ($value2 == 'seeker' &&
                    empty(trim($profile['profile_company']))) {
                    // set to true
                    $current = true;
                }

                // if looking for poster and profile is poster
                if ($value2 == 'poster' &&
                    !empty(trim($profile['profile_company']))) {
                    // set to true
                    $current = true;
                }

            } else {

                switch (true) {
                    case $operator == '=='   && $value1 == $value2:
                    case $operator == '==='  && $value1 === $value2:
                    case $operator == '!='   && $value1 != $value2:
                    case $operator == '!=='  && $value1 !== $value2:
                    case $operator == '<'    && $value1 < $value2:
                    case $operator == '<='   && $value1 <= $value2:
                    case $operator == '>'    && $value1 > $value2:
                    case $operator == '>='   && $value1 >= $value2:
                    case $operator == 'HAS'  && in_array($value2, $value1):
                    case $operator == 'LIKE' && strpos(
                        strtolower($value1), strtolower($value2)) !== false :
                        $current = true;
                        break;
                }
            }

            $valid = $valid && $current;

        }

        // are the conditions met?
        if (!$valid) {
            // if no, do not proceed
            return $response->setError(true, 'Conditions not met.');
        }

        // if met
        // add tags to profile_tags
        if (isset($action['action_tags']['add'])
            && $action['action_tags']['add']) {
            $request->setStage('add_tags', $action['action_tags']['add']);
        }

        // remove tags from profile_tags
        if (isset($action['action_tag']['remove']) &&
            $action['action_tag']['remove']) {
            $request->setStage('remove_tags', $action['action_tag']['remove']);
        }

        // update
        $this->trigger('profile-update', $request, $response);


        // is there a sending action
        if (!$action['template_id']) {
            # code...
            // if no, continue, we're done here
            continue;
        }

        // prep data for campaign create
        $campaign = [
            'campaign_type' => 'auto-created',
            'campaign_audience' => 'solo',
            'campaign_source' => 'profile',
            'campaign_medium' => $action['action_medium'],
            'campaign_title' => $action['template_title'],
            'template_id' => $action['template_id'],
            'profile_id' => $data['profile_id']
        ];

        $campaignRequest = new Request;
        $campaignResponse = new Response;

        // set variables
        $campaignRequest->setStage($campaign);

        // send
        cradle()->trigger('campaign-create', $campaignRequest, $campaignResponse);
    }
});
