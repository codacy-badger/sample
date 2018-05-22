<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Interview\Interview_schedule\Service as InterviewScheduleService;
use Cradle\Module\Interview\Interview_schedule\Validator as InterviewScheduleValidator;

/**
 * InterviewSchedule Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = InterviewScheduleValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['interview_schedule_date'])) {
        $data['interview_schedule_date'] = date('Y-m-d', strtotime($data['interview_schedule_date']));
    }

    if (isset($data['interview_schedule_start_time'])) {
        $data['interview_schedule_start_time'] = date('H:i:s', strtotime($data['interview_schedule_start_time']));
    }

    if (isset($data['interview_schedule_end_time'])) {
        $data['interview_schedule_end_time'] = date('H:i:s', strtotime($data['interview_schedule_end_time']));
    }

    if (isset($data['interview_schedule_meta'])) {
        $data['interview_schedule_meta'] = json_encode($data['interview_schedule_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    //save interview_schedule to database
    $results = $interview_scheduleSql->create($data);

    //link interview_setting
    if (isset($data['interview_setting_id'])) {
        $interview_scheduleSql->linkInterviewSetting($results['interview_schedule_id'], $data['interview_setting_id']);
    }

    //link profile
    if (isset($data['profile_id'])) {
        $interview_scheduleSql->linkProfile($results['interview_schedule_id'], $data['profile_id']);
    }
    
    //link post
    if (isset($data['post_id'])) {
        $interview_scheduleSql->linkPost($results['interview_schedule_id'], $data['post_id']);
    }

    //index interview_schedule
    // $interview_scheduleElastic->create($results['interview_schedule_id']);

    //invalidate cache
    $interview_scheduleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * InterviewSchedule Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['interview_schedule_id'])) {
        $id = $data['interview_schedule_id'];
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
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $interview_scheduleRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $interview_scheduleElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $interview_scheduleSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $interview_scheduleRedis->createDetail($id, $results);
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
 * InterviewSchedule Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_schedule detail
    $this->trigger('interview-schedule-detail', $request, $response);

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
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    // Remove linkage
    // Checks if there is a post id / post_id
    if (isset($data['post_id']) && is_numeric($data['post_id'])) {
        $interview_scheduleSql->unlinkPost($data['interview_schedule_id'], $data['post_id']);
    }

    // Checks if there is a interview setting id / interview_setting_id
    if (isset($data['interview_setting_id']) && is_numeric($data['interview_setting_id'])) {
        $interview_scheduleSql->unlinkInterviewSetting($data['interview_schedule_id'], $data['interview_setting_id']);
    }

    // Checks if there is a profile id / profile_id
    if (isset($data['profile_id']) && is_numeric($data['profile_id'])) {
        $interview_scheduleSql->unlinkProfile($data['interview_schedule_id'], $data['profile_id']);
    }

    //save to database
    $results = $interview_scheduleSql->update([
        'interview_schedule_id' => $data['interview_schedule_id'],
        'interview_schedule_flag' => 0
    ]);

    //remove from index
    // $interview_scheduleElastic->remove($data['interview_schedule_id']);

    //invalidate cache
    $interview_scheduleRedis->removeDetail($data['interview_schedule_id']);
    $interview_scheduleRedis->removeSearch();

    $response->setError(false);
});

/**
 * InterviewSchedule Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_schedule detail
    $this->trigger('interview-schedule-detail', $request, $response);

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
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    //save to database
    $results = $interview_scheduleSql->update([
        'interview_schedule_id' => $data['interview_schedule_id'],
        'interview_schedule_active' => 1
    ]);

    //create index
    // $interview_scheduleElastic->create($data['interview_schedule_id']);

    //invalidate cache
    $interview_scheduleRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * InterviewSchedule Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-search', function ($request, $response) {
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
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $interview_scheduleRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $interview_scheduleElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $interview_scheduleSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $interview_scheduleRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * InterviewSchedule Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the interview_schedule detail
    $this->trigger('interview-schedule-detail', $request, $response);

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
    $errors = InterviewScheduleValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['interview_schedule_date'])) {
        $data['interview_schedule_date'] = date('Y-m-d', strtotime($data['interview_schedule_date']));
    }

    if (isset($data['interview_schedule_start_time'])) {
        $data['interview_schedule_start_time'] = date('H:i:s', strtotime($data['interview_schedule_start_time']));
    }

    if (isset($data['interview_schedule_end_time'])) {
        $data['interview_schedule_end_time'] = date('H:i:s', strtotime($data['interview_schedule_end_time']));
    }

    if (isset($data['interview_schedule_meta'])) {
        $data['interview_schedule_meta'] = json_encode($data['interview_schedule_meta']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    //save interview_schedule to database
    $results = $interview_scheduleSql->update($data);

    //index interview_schedule
    // $interview_scheduleElastic->update($response->getResults('interview_schedule_id'));

    //invalidate cache
    $interview_scheduleRedis->removeDetail($response->getResults('interview_schedule_id'));
    $interview_scheduleRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

$cradle->on('interview-schedule-post', function ($request, $response) {
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $results = $interview_scheduleSql->getPostProfiles($data);

    $response->setError(false)->setResults($results);
});

$cradle->on('interview-schedule-email', function ($request, $response) {
    $data = $request->getStage();
    $config = $this->package('global')->service('ses');

    $data['email_data']['from'] = $config['sender'];
    $data['email_data']['subject'] = $this->package('global')
        ->translate($data['email_data']['title']);

    $handlebars = $this->package('global')->handlebars();

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];

    $contents = file_get_contents(__DIR__ . '/../template/email/'.$data['email_data']['template']);
    $htmlTemplate = $handlebars->compile($contents);

    $data['email_data']['to'] = [];
    $data['email_data']['to'][] = $data['profile']['profile_email'];

    // Constructs the default avatar
    $defaultAvatar = $host.'/images/avatar/avatar-' . ($data['profile']['profile_id'] % 5) . '.png';

    $newData['host'] = $host;
    $data['profile']['profile_avatar'] = $defaultAvatar;

    // Merges the data
    $data = array_merge($data, $newData);

    // Checks for no show template
    if ($data['email_data']['template'] == 'noshow.html') {
        $data['message_link'] = $host . '/profile/message/' . $data['interviewer']['profile_slug'];
    }

    // Constrcuts the html
    $data['email_data']['html'] = $htmlTemplate($data);

    $request->setStage($data['email_data']);
    $this->trigger('prepare-email', $request, $response);

    $results = $data;
    $response->setError(false);
});


/**
 * Interview Schedule and interview setting link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('interview-schedule-link', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = InterviewScheduleValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $interview_scheduleSql = InterviewScheduleService::get('sql');
    $interview_scheduleRedis = InterviewScheduleService::get('redis');
    // $interview_scheduleElastic = InterviewScheduleService::get('elastic');

    //save interview_schedule to database
    $results = $interview_scheduleSql->create($data);

    //link interview_setting
    if (isset($data['interview_setting_id'])) {
        $interview_scheduleSql->linkInterviewSetting($results['interview_schedule_id'], $data['interview_setting_id']);
    }

    //return response format
    $response->setError(false)->setResults($results);
});