<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Interview\Interview_setting\Service as InterviewSettingService;
use Cradle\Module\Interview\Interview_setting\Validator as InterviewSettingValidator;

/**
 * InterviewSetting Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = InterviewSettingValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['interview_setting_date'])) {
        $data['interview_setting_date'] = date('Y-m-d', strtotime($data['interview_setting_date']));
    }

    if(isset($data['interview_setting_start_time'])) {
        $data['interview_setting_start_time'] = date('H:i:s', strtotime($data['interview_setting_start_time']));
    }

    if(isset($data['interview_setting_end_time'])) {
        $data['interview_setting_end_time'] = date('H:i:s', strtotime($data['interview_setting_end_time']));
    }

    if(isset($data['interview_setting_meta'])) {
        $data['interview_setting_meta'] = json_encode($data['interview_setting_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    //save interview_setting to database
    $results = $interview_settingSql->create($data);
    //link profile
    if(isset($data['profile_id'])) {
        $interview_settingSql->linkProfile($results['interview_setting_id'], $data['profile_id']);
    }

    //index interview_setting
    // $interview_settingElastic->create($results['interview_setting_id']);

    //invalidate cache
    $interview_settingRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * InterviewSetting Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['interview_setting_id'])) {
        $id = $data['interview_setting_id'];
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
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $interview_settingRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $interview_settingElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $interview_settingSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $interview_settingRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
    }

    $response->setError(false)->setResults($results);
});

/**
 * InterviewSetting Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_setting detail
    $this->trigger('interview-setting-detail', $request, $response);

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
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    //save to database
    $results = $interview_settingSql->update([
        'interview_setting_id' => $data['interview_setting_id'],
        'interview_setting_active' => 0
    ]);

    //remove from index
    // $interview_settingElastic->remove($data['interview_setting_id']);

    //invalidate cache
    $interview_settingRedis->removeDetail($data['interview_setting_id']);
    $interview_settingRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * InterviewSetting Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_setting detail
    $this->trigger('interview-setting-detail', $request, $response);

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
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    //save to database
    $results = $interview_settingSql->update([
        'interview_setting_id' => $data['interview_setting_id'],
        'interview_setting_active' => 1
    ]);

    //create index
    // $interview_settingElastic->create($data['interview_setting_id']);

    //invalidate cache
    $interview_settingRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * InterviewSetting Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-search', function ($request, $response) {
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
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $interview_settingRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $interview_settingElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $interview_settingSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $interview_settingRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * InterviewSetting Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-setting-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_setting detail
    $this->trigger('interview-setting-detail', $request, $response);

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
    $errors = InterviewSettingValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['interview_setting_date'])) {
        $data['interview_setting_date'] = date('Y-m-d', strtotime($data['interview_setting_date']));
    }

    if(isset($data['interview_setting_start_time'])) {
        $data['interview_setting_start_time'] = date('H:i:s', strtotime($data['interview_setting_start_time']));
    }

    if(isset($data['interview_setting_end_time'])) {
        $data['interview_setting_end_time'] = date('H:i:s', strtotime($data['interview_setting_end_time']));
    }

    if(isset($data['interview_setting_meta'])) {
        $data['interview_setting_meta'] = json_encode($data['interview_setting_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $interview_settingSql = InterviewSettingService::get('sql');
    $interview_settingRedis = InterviewSettingService::get('redis');
    // $interview_settingElastic = InterviewSettingService::get('elastic');

    //save interview_setting to database
    $results = $interview_settingSql->update($data);

    //index interview_setting
    // $interview_settingElastic->update($response->getResults('interview_setting_id'));

    //invalidate cache
    $interview_settingRedis->removeDetail($response->getResults('interview_setting_id'));
    $interview_settingRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
