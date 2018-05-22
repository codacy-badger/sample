<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Post\Service as PostService;
use Cradle\Module\Widget\Service as WidgetService;
use Cradle\Module\Widget\Validator as WidgetValidator;

/**
 * Widget Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }
    
    //----------------------------//
    // 2. Validate Data
    $errors = WidgetValidator::getCreateErrors($data);

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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    //save widget to database
    $results = $widgetSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        // link profile
        $widgetSql->linkProfile($results['widget_id'], $data['profile_id']);
    }

    //index widget
    // $widgetElastic->create($results['widget_id']);

    //invalidate cache
    $widgetRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Widget Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['widget_id'])) {
        $id = $data['widget_id'];
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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $widgetRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $widgetElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $widgetSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $widgetRedis->createDetail($id, $results);
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
 * Widget Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the widget detail
    $this->trigger('widget-detail', $request, $response);

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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    //save to database
    $results = $widgetSql->update([
        'widget_id' => $data['widget_id'],
        'widget_active' => 0
    ]);

    // $widgetElastic->update($response->getResults('widget_id'));

    //invalidate cache
    $widgetRedis->removeDetail($data['widget_id']);
    $widgetRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Widget Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the widget detail
    $this->trigger('widget-detail', $request, $response);

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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    //save to database
    $results = $widgetSql->update([
        'widget_id' => $data['widget_id'],
        'widget_active' => 1
    ]);

    //create index
    // $widgetElastic->create($data['widget_id']);

    //invalidate cache
    $widgetRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Widget Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-search', function ($request, $response) {
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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $widgetRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $widgetElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $widgetSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $widgetRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Widget Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the widget detail
    $this->trigger('widget-detail', $request, $response);

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
    $errors = WidgetValidator::getUpdateErrors($data);

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
    $widgetSql = WidgetService::get('sql');
    $widgetRedis = WidgetService::get('redis');
    // $widgetElastic = WidgetService::get('elastic');

    //save widget to database
    $results = $widgetSql->update($data);

    //index widget
    // $widgetElastic->update($response->getResults('widget_id'));

    //invalidate cache
    $widgetRedis->removeDetail($response->getResults('widget_id'));
    $widgetRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Widget Validate Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-validate', function($request, $response) {
    // get the widget
    $widget = WidgetService::get('sql')
        ->getResource()
        ->search('widget_profile')
        ->innerJoinUsing('profile', 'profile_id')
        ->innerJoinUsing('widget', 'widget_id')
        ->addFilter('widget_key = %s', $request->getStage('widget_key'));

        // if widget type is set
    if ($request->getStage('widget_type')) {
        // filter by widget type
        $widget->addFilter('widget_type = %s', $request->getStage('widget_type'));
    }

    $widget = $widget->getRow();

    // if widget not found
    if(empty($widget)) {
        return $response->setError(true, 'Not Found');
    }

    // get the widget domain scope
    $domain = $widget['widget_domain'];

    return $response
        ->addHeader('Access-Control-Allow-Origin', $domain)
        ->setError(false)
        ->setResults($widget);
});

/**
 * Widget Notify Profile Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-notify-profile', function($request, $response) {
    $config = $this->package('global')->service('ses');

    // get data
    $data = $request->getStage();

    //form link
    // get reference id
    $hash = json_encode(['profile_id' => $data['profile']['profile_id']]);
    $hash = base64_encode($hash);
    // get suffix
    $suffix = substr($hash, -5);
    // rearrange referenc
    $hash =  $suffix . substr($hash, 0, -5);

    //form link
    if (!$request->getStage('host')) {
        $response->setError(true, 'Host is required')
            ->remove('json', 'results');

        return;
    }

    $host = $request->getStage('host');
    $claimLink = $host . '/claim?ref=' . $hash;

    // set claim link
    $data['claimLink'] = $claimLink;

    // generate post slug
    $data['post_slug'] = PostService::get('sql')->slugify($data['post_position'], $data['post_id']);

    //prepare data
    $emailData = [];
    $emailData['from'] = $config['sender'];

    $emailData['to'] = [];

    if (!isset($data['profile']['profile_email'])) {
        $data['profile']['profile_email'] = '';
        $emailData['to'][] = $data['item']['profile_email'];
    }
    $emailData['to'][] = $data['profile']['profile_email'];

    $emailData['subject'] = $this->package('global')->translate('Application Successful! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/notify-profile.html');
    $template = $handlebars->compile($contents);
    $emailData['claimLink'] = $data['claimLink'];

    $emailData['html'] = $template($data);

    //send mail
    $request->setStage($emailData);
    
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Widget Notify Company Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('widget-notify-company', function($request, $response) {
    $config = $this->package('global')->service('ses');

    // get data
    $data = $request->getStage();

    //form link
    if (!$request->getStage('host')) {
        $response->setError(true, 'Host is required')
            ->remove('json', 'results');

        return;
    }
    
    // generate post slug
    $data['post_slug'] = PostService::get('sql')->slugify($data['post_position'], $data['post_id']);
    $data['seeker_profile_slug'] = $request->getStage('profile_slug');

    // set post link
    $data['post_link'] = $data['host'] . "/Company-Hiring/" . $data['post_slug']; 

    //prepare data
    $emailData = [];
    $emailData['from'] = $config['sender'];

    $emailData['to'] = [];
    $emailData['to'][] = $data['post_email'];

    $emailData['subject'] = $this->package('global')->translate('Someone Is Interested! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $host = $request->getStage('host');

    // setting seeker's details for the notification sent to poster
    $defaultAvatar = $host.'/images/avatar/avatar-' . 
                    (($data['profile']['profile_id']) % 5)  . '.png';
    $data += [
        'default_seeker_avatar' => $defaultAvatar,
        'seeker_image' => $data['profile']['profile_image'],
        'seeker_name' => $data['profile']['profile_name'],
        'seeker_email' => $data['profile']['profile_email'],
        'seeker_slug' => $data['profile']['profile_slug'],
        'seeker_profile_slug' => $data['seeker_profile_slug']
    ];

    $contents = file_get_contents(__DIR__ . '/template/email/notify-company.html');
    $template = $handlebars->compile($contents);
    $emailData['html'] = $template($data);

    //send mail
    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);
});
