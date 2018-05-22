<?php //-->

use Cradle\Module\Utility\Rest;

/**
 * Render the Template Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template/search', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/template/search')
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
            'template_id' => 'Template Id',
            'template_active' => 'Template Active',
            'template_created' => 'Template Created',
            'template_updated' => 'Template Updated',
            'template_title' => 'Template Title',
            'template_type' => 'Template Type',
            'template_html' => 'Template HTML',
            'template_text' => 'Template Text',
            'template_unsubscribe' => 'Template Unsubscribe'
        ];

        $export = isset($data['rows']) ? $data['rows'] : [];
        //Set Filename
        $request->setStage('filename', 'Templates-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $export);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-template-search';
    $body = cradle('/app/marketing')->template('template/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Template Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Template Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-template-create';
    $body = cradle('/app/marketing')->template('template/create', $data, ['template_form']);

    //Set Content
    $response
        ->setPage('title', 'Template Create - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Template Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template/update/:template_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        $app = cradle('global')->config('services', 'jobayan_app');
        $api = cradle('global')->config('settings', 'api');

        $template = Rest::i($api.'/rest/template/detail/'.$request->getStage('template_id'))
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
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-template-update';
    $body = cradle('/app/marketing')->template('template/update', $data, ['template_form']);

    //Set Content
    $response
        ->setPage('title', 'Template Update - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Template Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template/remove/:template_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/template/remove/'.$request->getStage('template_id'))
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
        $message = cradle('global')->translate('Template was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/template/search');
});

/**
 * Render the Template Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template/restore/:template_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/template/restore/'.$request->getStage('template_id'))
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
        $message = cradle('global')->translate('Template was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/template/search');
});

/**
 * Process the Template Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/template/create', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/template/create')
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
        $route = '/control/marketing/template/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Template was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/template/search');
});

/**
 * Process the Template Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/template/update/:template_id', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/template/update/'.$request->getStage('template_id'))
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
        $route = '/control/marketing/template/update/' . $request->getStage('template_id');

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Template was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/template/search');
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/template/import', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $columns = array(
        'keys' => array(
            'template_id',
            'template_active',
            'template_created',
            'template_updated',
            'template_title',
            'template_type',
            'template_html',
            'template_text',
            'template_unsubscribe'
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/marketing/template/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        if (isset($value['template_created']) &&
            !empty($value['template_created'])) {
            $value['template_created'] =
                date('Y-m-d H:i:s', strtotime($value['template_created']));
        } else {
            unset($value['template_created']);
        }

        if (isset($value['template_updated']) &&
            !empty($value['template_updated'])) {
            $value['template_updated'] =
                date('Y-m-d H:i:s', strtotime($value['template_updated']));
        } else {
            unset($value['template_updated']);
        }

        if (empty($value['template_active'])) {
            unset($value['template_active']);
        }

        if (empty($value['template_id'])) {
            unset($value['template_id']);
            $rest = Rest::i($api.'/rest/template/create');
        } else {
            $rest = Rest::i($api.'/rest/template/update/'.$value['template_id']);
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
                $errors[] = '<br>#'.$value['template_title'].' - '
                    .implode(' ', $results['validation']);
                $errorCtr++;
            }
        } else {
            if (!isset($value['template_id'])) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Template Created <br>' .' ['.
        $successUpdateCtr. '] Template Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/marketing/template/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Bulk action for Template Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/template/bulk', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/template/bulk')
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

    cradle('global')->redirect('/control/marketing/template/search');
});
