<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;
 
/**
 * Render the Link Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/link/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $request->setStage('listing', true);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/utm/search')
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
        cradle('global')->redirect('/control/marketing/dashboard');
    }

    $data = $data['results'] ? $data['results']: [];

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'utm_id' => 'UTM Id',
            'utm_active' => 'UTM Active',
            'utm_created' => 'UTM Created',
            'utm_updated' => 'UTM Updated',
            'utm_title' => 'UTM Title',
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'utm_detail' => 'UTM Detail',
            'utm_page' => 'UTM Page',
            'utm_image' => 'UTM Image',
            'utm_clicked' => 'UTM Clicked',
            'utm_type' => 'UTM Type',
            'utm_flag' => 'UTM Flag'
        ];

        //Set Filename
        $request->setStage('filename', 'UTM-Links-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-link-search';
    $body = cradle('/app/marketing')->template('link/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Link Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Link Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/link/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-link-create';
    $body = cradle('/app/marketing')->template('link/create', $data, ['link_form']);

    //Set Content
    $response
        ->setPage('title', 'Link Create - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Link Update Page
 *
 * @param Request $request
 * @param Response $response
 *///BRB
$cradle->get('/control/marketing/link/update/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        $app = cradle('global')->config('services', 'jobayan_app');
        $api = cradle('global')->config('settings', 'api');

        $template = Rest::i($api.'/rest/utm/detail/'.$request->getStage('utm_id'))
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret']);

        if ($request->getStage()) {
            foreach ($request->getStage() as $key => $value) {
                $template->set($key, $value);
            }
        }

        $results = $template->get();

        //process errors
        if ($results['error']) {
            $response->setFlash($results['message'], 'danger');
        }

        $data['item'] = $results['results']; // instantiate with results
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-link-update';
    $body = cradle('/app/marketing')->template('link/update', $data, ['link_form']);

    //Set Content
    $response
        ->setPage('title', 'Link Update - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Link Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/link/remove/:utm_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/utm/remove/'.$request->getStage('utm_id'))
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
        $message = cradle('global')->translate('Link was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/link/search');
});

/**
 * Render the Link Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/link/restore/:utm_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/utm/restore/'.$request->getStage('utm_id'))
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
        $message = cradle('global')->translate('Link was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/link/search');
});

/**
 * Process the Link Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/link/create', function ($request, $response) {
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

    $data = Rest::i($api.'/rest/utm/create')
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
        $route = '/control/marketing/link/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Link was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/link/search');
});

/**
 * Process the Link Update Page
 *
 * @param Request $request
 * @param Response $response
 *///BRB
$cradle->post('/control/marketing/link/update/:utm_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/utm/update/'.$request->getStage('utm_id'))
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
        $route = '/control/marketing/link/update/' . $request->getStage('utm_id');

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Link was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/link/search');
});

/**
 * Render the link Bulk Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/link/bulk', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/utm/bulk')
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

    cradle('global')->redirect('/control/marketing/link/search');
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/link/import', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $columns = array(
        'keys' => array(
            'utm_id',
            'utm_active',
            'utm_created',
            'utm_updated',
            'utm_title',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_detail',
            'utm_page',
            'utm_image',
            'utm_clicked',
            'utm_type',
            'utm_flag'
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/marketing/link/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        foreach ($value as $field => $datum) {
            if (empty($datum) && $datum != '0') {
                unset($value[$field]);
            }
        }

        if (isset($value['utm_created']) &&
            !empty($value['utm_created'])) {
            $value['utm_created'] =
                date('Y-m-d H:i:s', strtotime($value['utm_created']));
        } else {
            unset($value['utm_created']);
        }

        if (isset($value['utm_updated']) &&
            !empty($value['utm_updated'])) {
            $value['utm_updated'] =
                date('Y-m-d H:i:s', strtotime($value['utm_updated']));
        } else {
            unset($value['utm_updated']);
        }

        if (empty($value['utm_active'])) {
            unset($value['utm_active']);
        }

        if (empty($value['utm_id'])) {
            unset($value['utm_id']);
            $rest = Rest::i($api.'/rest/utm/create');
        } else {
            $rest = Rest::i($api.'/rest/utm/update/'.$value['utm_id']);
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
                $errors[] = '<br>#'.$value['utm_title'].' - '
                    .implode(' ', $results['validation']);
                $errorCtr++;
            }
        } else {
            if (!isset($value['utm_id'])) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] UTM Link Created <br>' .' ['.
        $successUpdateCtr. '] UTM Link Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/marketing/link/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
