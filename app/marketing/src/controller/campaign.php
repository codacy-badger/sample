<?php //-->

use Cradle\Module\Utility\Rest;
use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render the Campaign Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    //for pagination
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $request->setStage('listing', true);

    if (is_array($request->getStage('order'))) {
        $sortable = [
            'campaign_id',
            'campaign_title'
        ];

        // Loops through the orders
        foreach ($request->getStage('order') as $key => $direction) {
            // Checks if the sorting value is not in the allowed sorting
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                // Checks if the sorting
                $request->removeStage('order', $key);
            }
        }
    }

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/campaign/search')
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
        return cradle('global')->redirect('/control/marketing/dashboard');
    }

    $data = $data['results'] ? $data['results']: [];

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'campaign_active' => 'Campaign Active',
            'campaign_created' => 'Campaign Created',
            'campaign_updated' => 'Campaign Updated',
            'campaign_title' => 'Campaign Title',
            'campaign_medium' => 'Campaign Medium',
            'campaign_source' => 'Campaign Source',
            'campaign_audience' => 'Campaign Audience',
            'campaign_tags' => 'Campaign Tags',
            'campaign_queue' => 'Campaign Queue',
            'campaign_sent' => 'Campaign Sent',
            'campaign_converted' => 'Campaign Converted',
            'campaign_bounced' => 'Campaign Bounced',
            'campaign_opened' => 'Campaign Opened',
            'campaign_unopened' => 'Campaign Unopened',
            'campaign_spam' => 'Campaign Spam',
            'campaign_clicked' => 'Campaign Clicked',
            'campaign_unsubscribed' => 'Campaign Unsubscribed',
            'template_id' => 'Template Id'
        ];

        //convert campaign_tags from array to
        foreach ($data['rows'] as $index => $row) {
            if (is_array($row['campaign_tags']) && !is_null($row['campaign_tags'])) {
                $data['rows'][$index]['campaign_tags'] = implode(', ', $row['campaign_tags']);
            }
        }

        //Set Filename
        $request->setStage('filename', 'Campaigns-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-campaign-search';
    $body = cradle('/app/marketing')->template('campaign/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Campaign Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Campaign Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data

    $data['item'] = $request->getPost();

    // trigger the job that gets the templates and add to items
    $template = Rest::i($api.'/rest/template/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    $data['templates'] = $template['results'];

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-campaign-create';
    $body = cradle('/app/marketing')->template('campaign/create', $data, ['campaign_form']);

    //Set Content
    $response
        ->setPage('title', 'Campaign Create - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Campaign Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/remove/:campaign_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/campaign/remove/'.$request->getStage('campaign_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //----------------------------//
    // 4. Interpret Results
    if (isset($data['error']) && $data['error']) {
        //add a flash
        cradle('global')->flash($data['message'], 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Campaign was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/campaign/search');
});

/**
 * Render the Post Copy Page
 *
 * @param Request $request
 * @param Response $response
 *///
$cradle->get('/control/marketing/campaign/copy/:campaign_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getPost();

    // trigger the job that gets the templates and add to items
    $template = Rest::i($api.'/rest/template/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    $data['templates'] = $template['results'];

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the details
        cradle()->trigger('campaign-detail', $request, $response);

        //process errors
        if ($response->isError()) {
            $response->setFlash($response->getMessage(), 'danger');
            $data['errors'] = $response->getValidation();
        }
        $data['item'] = $response->getResults(); // instantiate with results
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-campaign-create';
    $body = cradle('/app/marketing')->template('campaign/create', $data, ['campaign_form']);

    //Set Content
    $response
        ->setPage('title', 'Campaign Copy - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 *
 */
$cradle->get('/control/marketing/campaign/results/:campaign_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getPost();

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the detials
        $results = Rest::i($api.'/rest/campaign/detail/'. $request->getStage('campaign_id'))
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

    if ($data['item']['campaign_medium'] == 'email') {
        //get ses using campaign_message_id
        $request->setStage('campaign_message_id', $data['item']['campaign_message_id']);

        cradle()->trigger('ses-detail-byMessage', $request, $response);

        //process errors
        if ($response->isError()) {
            $response->setFlash($response->getMessage(), 'danger');
            $data['errors'] = $response->getValidation();
        }
        $data['item']['ses'] = $response->getResults();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    if ($data['item']['campaign_medium'] == 'email') {
        $class = 'page-marketing-campaign-result-email';
        $body = cradle('/app/marketing')->template('campaign/result-email', $data);
    } else {
        $class = 'page-marketing-campaign-result-sms';
        $body = cradle('/app/marketing')->template('campaign/result-sms', $data);
    }

    //Set Content
    $response
        ->setPage('title', 'Campaign Results - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Campaign Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/restore/:campaign_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/campaign/restore/'.$request->getStage('campaign_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //----------------------------//
    // 4. Interpret Results
    if (isset($data['error']) && $data['error']) {
        //add a flash
        cradle('global')->flash($data['message'], 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Campaign was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/campaign/search');
});

/**
 * Process the Campaign Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/campaign/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();
    //----------------------------//
    // 3. Process Request
    $campaign = Rest::i($api.'/rest/campaign/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $campaign->set($key, $value);
        }
    }

    $campaign = $campaign->post();
    $errors = $campaign['error'];

    if ($campaign['error'] && isset($campaign['message'])) {
        //add a flash
        cradle('global')->flash($campaign['message'], 'danger');
        return cradle('global')->redirect('/control/marketing/campaign/search');
    }

    $campaign = $campaign['results'] ? $campaign['results']: [];

    if (isset($data['campaign_source'])) {
        // if we're sending it out to leads
        if ($data['campaign_source'] == 'lead' ||
            $data['campaign_source'] == 'profile') {
            $type = $data['campaign_source'];
            if ($data['campaign_source'] == 'lead') {
                $request->setStage(
                    'filter',
                    ['lead_type' => $data['campaign_audience']]
                );
            }

            if ($data['campaign_source'] == 'profile') {
                // is it a solo audience?
                if ($data['campaign_audience'] == 'solo' &&
                    $data['profile_id']) {
                    $request->setStage(
                        'filter',
                        ['profile_id' => $data['profile_id']]
                    );
                // or multiple audience of specific type?
                } else {
                    $request->setStage(
                        'filter',
                        ['type' => $data['campaign_audience']]
                    );
                }
            }

            // set tags if any
            if(isset($data['campaign_tags']) && $data['campaign_tags']) {
                $request->setStage($type.'_tags', $data['campaign_tags']);
            }

            // pull total lead of the type given
            $request->setStage('range', 1);
            // $this->trigger($type.'-search', $request, $response);
            $receiver = Rest::i($api.'/rest/'.$type.'/search')
                ->set('client_id', $app['token'])
                ->set('client_secret', $app['secret']);

            if ($request->getStage()) {
                foreach ($request->getStage() as $key => $value) {
                    $receiver->set($key, $value);
                }
            }

            $receiver = $receiver->get();
            $total = $receiver['results'] ? $receiver['results']['total']: 0;

            // now we have to update how much were queued for sending/receiving
            $receiver = Rest::i($api.'/rest/campaign/update/'.$campaign['campaign_id'])
                ->set('client_id', $app['token'])
                ->set('client_secret', $app['secret'])
                ->set('campaign_queue', $total)
                ->post();

            // pull by batch of 200 from the database
            // until all the leads under the type given
            // has been queued to receive this campaign
            $request->setStage('range', 200);
            for ($start = 0; $start < $total; $start++) {
                $request->setStage('start', $start);
                $users = Rest::i($api.'/rest/'.$type.'/search')
                    ->set('client_id', $app['token'])
                    ->set('client_secret', $app['secret']);

                if ($request->getStage()) {
                    foreach ($request->getStage() as $key => $value) {
                        $users->set($key, $value);
                    }
                }

                $users = $users->get();
                $users = $users['results'] ? $users['results']['rows']: [];

                // now we have to link each pulled user to this campaign
                // and also send thru whatever type of channel we have to send
                foreach ($users as $user) {
                    Rest::i($api.'/rest/campaign/link/'.$type)
                        ->set('client_id', $app['token'])
                        ->set('client_secret', $app['secret'])
                        ->set('campaign_id', $campaign['campaign_id'])
                        ->set('client_id', $user[$type.'_id'])
                        ->get();

                    $sendRequest = new Request();
                    $sendResponse = new Response();

                    // set peripherals for sending this campaign to this user
                    $sendRequest->setStage('campaign_id', $campaign['campaign_id']);
                    $sendRequest->setStage('template_id', $data['template_id']);
                    $sendRequest->setStage('receiver', $user[$type.'_id']);
                    $sendRequest->setStage('medium', $data['campaign_medium']);
                    $sendRequest->setStage('source', $data['campaign_source']);
                    $sendRequest->setStage('message_id', $campaign['campaign_message_id']);
                    $sendRequest->setStage('host', $api);
                    $sendData = $sendRequest->getStage();

                    // queue the send if queueing is available
                    if (!$this->package('global')->queue('campaign-send', $sendData)) {
                        // send campaign manually
                        $this->trigger('campaign-send', $sendRequest, $sendResponse);
                    }
                }

                // update start for pulling,
                // add 200 since range is 200
                $start += 200;
            }
        }
    }

    //----------------------------//
    // 4. Interpret Results
    if ($errors) {
        $route = '/control/marketing/campaign/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Campaign was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/campaign/search');
});

$cradle->post('/control/marketing/campaign/copy/:campaign_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data

    //----------------------------//
    // 3. Process Request
    // cradle()->trigger('campaign-create', $request, $response);
    $data = Rest::i($api.'/rest/campaign/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->hasStage('campaign_id')) {
            $request->removeStage('campaign_id');
        }

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $results = $data->post();

    //----------------------------//
    // 4. Interpret Results

    if ($results['error']) {
        $route = '/control/marketing/campaign/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Campaign was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/campaign/search');
});

/**
 * Process the Campaign Result SMS Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/result-sms', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data


    //----------------------------//
    // 3. Render Template
    //Render body
    //$data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-campaign-result-sms';
    $body = cradle('/app/marketing')->template('campaign/result-sms');

    //Set Content
    $response
        ->setPage('title', 'Campaign Results - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Process the Campaign Result Email Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/campaign/result-email', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data


    //----------------------------//
    // 3. Render Template
    //Render body
    //$data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-campaign-result-email';
    $body = cradle('/app/marketing')->template('campaign/result-email');

    //Set Content
    $response
        ->setPage('title', 'Campaign Results - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Process the Bulk action for Campaign Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/campaign/bulk', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/campaign/bulk')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $response = $data->post();

    //----------------------------//
    // 4. Interpret Results
    if (isset($response['error']) && $response['error']) {
        //add a flash
        cradle('global')->flash($response['message'], 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Bulk action successfully applied');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/campaign/search');
});
