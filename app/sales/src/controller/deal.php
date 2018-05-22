<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;

/**
 * Render the Deal Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/search', function ($request, $response) {
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

    $request->setStage('listing', true);

    //gets the first active pipeline for Boards
    $pipeline = Rest::i($api.'/rest/pipeline/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    $pipelineId = $pipeline['results']['rows'][0]['pipeline_id'];

    $request->setStage('filter', ['pipeline_id' => $pipelineId]);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/deal/search')
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
    $data['pipeline_id'] = $pipelineId;

    // make active-inactive
    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk_rows')) {
        if ($request->getStage('bulk') == 'remove' ||
            $request->getStage('bulk') == 'restore') {
            $bulk = Rest::i($api.'/rest/deal/bulk')
                ->set('client_id', $app['token'])
                ->set('client_secret', $app['secret']);

            if ($request->getStage()) {
                foreach ($request->getStage() as $key => $value) {
                    $bulk->set($key, $value);
                }
            }

            $bulk = $bulk->post();

            if ($bulk['error'] && isset($bulk['message'])) {
                //add a flash
                cradle('global')->flash($data['message'], 'danger');
                cradle('global')->redirect('/control/business/dashboard');
            } else {
                //add a flash
                $message = cradle('global')->translate('Bulk action successfully applied');
                cradle('global')->flash($message, 'success');
            }
        }

        return cradle('global')->redirect('/control/business/deal/search');
    }

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'deal_id' => 'Deal Id',
            'deal_active' => 'Deal Active',
            'deal_created' => 'Deal Created',
            'deal_updated' => 'Deal Updated',
            'deal_name' => 'Deal Name',
            'deal_stages' => 'Deal Stages',
        ];

        //Set Filename
        $request->setStage('filename', 'Deals-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-sales-deal-search';
    $body = cradle('/app/sales')->template('deal/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Deals - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Deal Messages Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/:deal_id/messages', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // if $data aka $request->getPost() is empty
    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the details
        $results = Rest::i($api.'/rest/deal/detail/'. $request->getStage('deal_id'))
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

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $index = array_search($data['item']['deal_status'], $data['item']['pipeline_stages']);
    foreach ($data['item']['pipeline_stages'] as $key => $value) {
        $data['item']['pipeline_stages'][$value] = $key <= $index;
        unset($data['item']['pipeline_stages'][$key]);
    }

    $data['pipeline_width'] = 100/count($data['item']['pipeline_stages']);

    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));

    if (isset($data['item']['company']['profile_email'])) {
        $request->setStage('email', $data['item']['company']['profile_email']);
    }

    if (isset($data['item']['company']['lead_email'])) {
        $request->setStage('email', $data['item']['company']['lead_email']);
    }

    if (!$request->getStage('pageToken')) {
        $_SESSION['email_token_current'] = 'start';
        $request->removeSession('email_token_current');
        $request->removeSession('email_page_current');
        $request->removeSession('email_token');
    }

    cradle()->trigger('gmail-pull-messages', $request, $response);
    $data['gmail'] = $response->getResults();

    if (!$request->getSession('email_token', $request->getStage('pageToken'))) {
        $_SESSION['email_token'][$request->getStage('pageToken')]['next'] = isset($data['gmail']['nextPageToken']) ? $data['gmail']['nextPageToken'] : null;
        if (isset($_SESSION['email_token_current']) && $_SESSION['email_token_current'] != $request->getStage('pageToken')) {
            $_SESSION['email_token'][$request->getStage('pageToken')]['previous'] = $_SESSION['email_token_current'];
        }
    }

    if ($request->hasStage('pageToken')) {
        $_SESSION['email_token_current'] = $request->getStage('pageToken');
    }

    $_SESSION['email_page_current'] = $_SESSION['email_token'][$request->getStage('pageToken')];

    $data = array_merge($request->getStage(), $data);

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-deal-thread page-sales-deal-detail';
    $body = cradle('/app/sales')->template('deal/messages', $data, ['deal_form']);

    //Set Content
    $response
        ->setPage('title', 'Deal Create - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');


/**
 * Render the Deal Thread Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/:deal_id/thread/:thread_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // if $data aka $request->getPost() is empty
    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the details
        $results = Rest::i($api.'/rest/deal/detail/'. $request->getStage('deal_id'))
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

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $index = array_search($data['item']['deal_status'], $data['item']['pipeline_stages']);
    foreach ($data['item']['pipeline_stages'] as $key => $value) {
        $data['item']['pipeline_stages'][$value] = $key <= $index;
        unset($data['item']['pipeline_stages'][$key]);
    }

    $data['pipeline_width'] = 100/count($data['item']['pipeline_stages']);

    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    cradle()->trigger('gmail-pull-thread', $request, $response);

    if ($response->isError()) {
        $message = $response->getMessage();
        if ($message == 'Not Found') {
            $message = 'Either Email is not existing or You are accessing an email you don\'t own';
        }

        cradle('global')->flash($message, 'danger');
        return cradle('global')->redirect('/control/business/deal/'.$request->getStage('deal_id').'/messages');
    }

    $data['gmail'] = $response->getResults();

     $data = array_merge($request->getStage(), $data);

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-deal-thread page-sales-deal-detail';
    $body = cradle('/app/sales')->template('deal/thread', $data, ['deal_form']);

    //Set Content
    $response
        ->setPage('title', 'Deal Create - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Deal Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/overview/:deal_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the details
        $results = Rest::i($api.'/rest/deal/detail/'. $request->getStage('deal_id'))
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

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $index = array_search($data['item']['deal_status'], $data['item']['pipeline_stages']);
    foreach ($data['item']['pipeline_stages'] as $key => $value) {
        $data['item']['pipeline_stages'][$value] = $key <= $index;
        unset($data['item']['pipeline_stages'][$key]);
    }

    $data['pipeline_width'] = 100/count($data['item']['pipeline_stages']);
    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-deal-detail';
    $body = cradle('/app/sales')->template('deal/detail', $data, ['deal_form']);

    //Set Content
    $response
        ->setPage('title', 'Deal Details - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Deal Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/remove/:deal_id', function ($request, $response) {
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
    // cradle()->trigger('deal-remove', $request, $response);

    $results = Rest::i($api.'/rest/deal/remove/'.$request->getStage('deal_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //for error
    $request->setStage('deal_status_update_only', 1);

    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        //add a flash
        cradle('global')->flash($results['message'], 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Deal was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/deal/search');
});

/**
 * Render the Deal Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/deal/restore/:deal_id', function ($request, $response) {
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
    // cradle()->trigger('deal-restore', $request, $response);
    $results = Rest::i($api.'/rest/deal/restore/'.$request->getStage('deal_id'))
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
        $message = cradle('global')->translate('Deal was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/deal/search');
});
