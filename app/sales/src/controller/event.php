<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;

/**
 * Render the Event Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/event/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $request->setStage('sales', true);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/event/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $data = $data->get();
    if ($data['error'] && isset($data['message'])) {
        //add a flash
        cradle('global')->flash($data['message'], 'danger');
        cradle('global')->redirect('/control/business/dashboard');
    }

    $data = $data['results'] ? $data['results']: [];

    // add-remove label and make active-inactive
    $tags = $request->getStage('tags');
    $post = false;
    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk-rows')) {
        foreach ($request->getStage('bulk-rows') as $key => $value) {
            $request->setStage('event_id', $value);
            if (!empty($tags)) {
                if ('add-tag' === $request->getStage('bulk')) {
                    $request->setStage('add_tags', $tags);
                } else if ('remove-tag' === $request->getStage('bulk')) {
                    $request->setStage('remove_tags', $tags);
                }
                $profile = Rest::i($api.'/rest/event/update/'.$value);
                $post = true;
            }

            if ('restore' === $request->getStage('bulk')) {
                $profile = Rest::i($api.'/rest/event/restore/'.$value);
            } else if ('remove' === $request->getStage('bulk')) {
                $profile = Rest::i($api.'/rest/event/remove/'.$value);
            }

            $profile->set('client_id', $app['token'])
                ->set('client_secret', $app['secret']);

            if ($request->getStage()) {
                foreach ($request->getStage() as $key => $value) {
                    $profile->set($key, $value);
                }
            }

            if ($post) {
                $results = $profile->post();
            } else {
                $results = $profile->get();
            }
        } exit;
        return cradle('global')->redirect('/control/business/event/search');
    }

    //Export CSV
    if ($request->hasStage('export') && isset($data['rows'])) {
        //Set CSV header
        $header = [
            'event_id' => 'Event Id',
            'event_active' => 'Event Active',
            'event_created' => 'Event Created',
            'event_updated' => 'Event Updated',
            'event_title' => 'Event Name',
            'event_type' => 'Event Type',
            'event_start' => 'Event Start',
            'event_end' => 'Event End',
            'event_description' => 'Event Description',
            'profile_id' => 'Profile Id',
            'profile_name' => 'Agent Name'
        ];

        //Set Filename
        $request->setStage('filename', 'Events-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;

    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-sales-event-search';
    $body = cradle('/app/sales')->template('event/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Event Search - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Event Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/event/calendar', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    if ($request->getStage()) {
        $data = array_merge($request->getStage(), $data);
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-event-form';
    $body = cradle('/app/sales')->template('event/calendar', $data, ['event_form']);

    //Set Content
    $response
        ->setPage('title', 'Event Calendar - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Event Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/event/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-event-form';
    $body = cradle('/app/sales')->template('event/create', $data, ['event_form']);

    //Set Content
    $response
        ->setPage('title', 'Event Create - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Event Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/event/update/:event_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the details
        $results = Rest::i($api.'/rest/event/detail/'. $request->getStage('event_id'))
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->get();

        //process errors
        if ($results['error']) {
            $response->setFlash($results['message'], 'danger');
            $data['errors'] = $results['validation'];
        }

        $data['item'] = $results['results']; // instantiate with results
    }

    if ($request->getSession('app_session', 'results', 'profile_type') == 'agent' &&
        $data['item']['profile_id'] != $request->getSession('app_session', 'results', 'profile_id')) {
        cradle('global')->flash('Unathorized Request', 'danger');
        return cradle('global')->redirect('/control/business/event/search');
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-event-form';
    $body = cradle('/app/sales')->template('event/update', $data, ['event_form']);

    //Set Content
    $response
        ->setPage('title', 'Event Update - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Event Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/event/remove/:event_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    $results = Rest::i($api.'/rest/event/remove/'.$request->getStage('event_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        //add a flash
        cradle('global')->flash($results['message'], 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Event was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/event/search');
});

/**
 * Process the Event Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/event/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->getStage('profile_id')) {
        $profile = $request->getSession('app_session', 'results', 'profile_id');
        $request->setStage('profile_id', $profile);
    }

    //----------------------------//
    // 3. Process Request
    // create event
    $data = Rest::i($api.'/rest/event/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $results = $data->post();
    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $route = '/control/business/event/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/business/event/create';
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Event was Created', 'success');

    $redirect = '/control/business/event/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    //redirect
    cradle('global')->redirect($redirect);
});

/**
 * Process the Event Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/event/update/:event_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $profile = $request->getSession('app_session', 'results', 'profile_id');
    $request->setStage('profile_id', $profile);

    $old = Rest::i($api.'/rest/event/detail/'. $request->getStage('event_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //process errors
    if ($old['error']) {
        $response->setFlash($results['message'], 'danger');
        return cradle('global')->redirect('/control/business/event/search');
    }

    $old = $old['results']; // instantiate with results

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/event/update/'.$request->getStage('event_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $results = $data->post();

    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $route = '/control/business/event/update/' . $request->getStage('event_id');

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $action = '';

    if ($old['event_title'] != $request->getStage('event_title')) {
        $action .= ' Event Name changed from ' . $old['event_title'] .
            ' to '. $request->getStage('event_title') . "<br />";
    }

    if ($old['event_type'] != $request->getStage('event_type')) {
        $action .= $old['event_title'] . ' type changed from ' . $old['event_type'] .
            ' to '. $request->getStage('event_type'). "<br />";
    }

    if ($old['event_location'] != $request->getStage('event_location')) {
        $action .= $old['event_title'] . ' location changed from ' . $old['event_location'] .
            ' to '. $request->getStage('event_location'). "<br />";
    }

    if ($old['event_details'] != $request->getStage('event_details')) {
        $action .= $old['event_title'] . ' details changed from ' . $old['event_details'] .
            ' to '. $request->getStage('event_details'). "<br />";
    }

    if ($old['event_start'] != date('Y-m-d H:i:s', strtotime($request->getStage('event_start')))) {
        $action .= $old['event_title'] . ' start schedule changed from ' . date('M d, Y h:i a', strtotime($old['event_start'])) .
            ' to '. date('M d, Y h:i a', strtotime($request->getStage('event_start'))). "<br />";
    }

    if ($old['event_end'] != date('Y-m-d H:i:s', strtotime($request->getStage('event_end')))) {
        $action .= $old['event_title'] . ' end schedule changed from ' . date('M d, Y h:i a', strtotime($old['event_end'])) .
            ' to '. date('M d, Y h:i a', strtotime($request->getStage('event_end'))). "<br />";
    }

    //it was good
    //add a flash
    cradle('global')->flash('Event was Updated', 'success');
    $redirect = '/control/business/event/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    //redirect
    cradle('global')->redirect($redirect);
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/event/import', function ($request, $response) {
    $columns = array(
        'keys' => array(
            'event_id',
            'event_active',
            'event_created',
            'event_updated',
            'event_name',
            'event_email',
            'event_gender',
            'event_birth',
            'event_phone',
            'event_location',
            'event_school',
            'event_study',
            'event_company',
            'event_job_title',
            'event_tags',
            'event_facebook',
            'event_linkedin',
            'event_image',
            'event_campaigns',
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/business/event/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {

        if ($value['event_tags']) {
            $value['event_tags'] = explode(',', $value['event_tags']);
        }

        if ($value['event_campaigns']) {
            $value['event_campaigns'] = explode(',', $value['event_campaigns']);
        }

        if ($value['event_created']) {
            $value['event_created'] = date(
                'Y-m-d H:i:s', strtotime($value['event_created']));
        }

        if ($value['event_updated']) {
            $value['event_updated'] = date(
                'Y-m-d H:i:s', strtotime($value['event_updated']));
        }

        if (trim($value['event_active']) == '') {
            unset($value['event_active']);
        }

        $value['event_type'] = 'poster';
        $request->setStage($value);

        if (!isset($value['event_id']) || empty($value['event_id'])) {
            $request->removeStage('event_id');
            $rest = Rest::i($api.'/rest/event/create');
        } else {
            $rest = Rest::i($api.'/rest/event/update/'.$value['event_id']);
        }

        $rest->set('client_id', $app['token'])
            ->set('client_secret', $app['secret']);

        foreach ($value as $field => $val) {
            $rest->set($field, $val);
        }

        $results = $rest->post();

        //get error response
        if ($results['error']) {
            if (isset($results['validation'])) {
                $errors[] = '<br>#'. $value['event_name'] .' - '. implode(' ', $results['validation']);
                $errorCtr++;
            }
        } else {
            if (!isset($value['lead_id'])) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Event Created <br>' .' ['.
        $successUpdateCtr. '] Event Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/business/event/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
