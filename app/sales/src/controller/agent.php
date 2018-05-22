<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;

/**
 * Render the Agent Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/agent/search', function ($request, $response) {
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

    if (empty($request->getStage('date_start'))
        || empty($request->getStage('date_end'))) {
            $request->removeStage('date_start');
            $request->removeStage('date_end');
            $request->removeStage('date_type');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_id',
            'profile_name',
            'profile_email',
            'profile_created',
            'profile_updated'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'profile_id',
            'profile_name',
            'profile_type',
            'profile_active',
            'profile_created',
            'profile_updated',
            'profile_company',
            'type'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable) ||
                (empty($value) && $value != '0')) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
    }

    // trigger the job to get all profile
    $request->setStage('filter', 'profile_type', 'agent');
    $data = Rest::i($api.'/rest/profile/search')
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

    // make active, make inactive
    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk-rows')) {
        foreach ($request->getStage('bulk-rows') as $key => $value) {
            $request->setStage('profile_id', $value);
            if ($request->getStage('bulk') === 'restore') {
                $profile = Rest::i($api.'/rest/profile/restore/'.$value);
            } else if ($request->getStage('bulk') === 'remove') {
                $profile = Rest::i($api.'/rest/profile/remove/'.$value);
            }

            $profile
                ->set('client_id', $app['token'])
                ->set('client_secret', $app['secret']);
            if ($request->getStage()) {
                foreach ($request->getStage() as $key => $value) {
                    $profile->set($key, $value);
                }
            }

            $results = $profile->get();
        }

        if ($results['error'] && isset($results['message'])) {
            //add a flash
            cradle('global')->flash($results['message'], 'danger');
        } else {
            $message = cradle('global')->translate('Bulk action successfully applied');
            cradle('global')->flash($message, 'success');
        }

        return cradle('global')->redirect('/control/business/agent/search');
    }

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'profile_active' => 'Profile Active',
            'profile_id' => 'Profile Id',
            'profile_name' => 'Profile Name',
            'profile_email' => 'Profile Email',
            'profile_created' => 'Profile Created',
            'profile_updated' => 'Profile Updated'
        ];

        //Set Filename
        $request->setStage('filename', 'Sales Agent-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-sales-agent-search';
    $body = cradle('/app/sales')->template('agent/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Sales Agent Search - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Sales Agent Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/agent/create', function ($request, $response) {
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

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $data['form'] = 'create';

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-agent-create';
    $body = cradle('/app/sales')->template('agent/create', $data, ['agent_form']);

    //Set Content
    $response
        ->setPage('title', 'Sales Agent - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Sales Agent Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/agent/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    if ($request->getSession('app_session', 'results', 'profile_type') == 'agent' &&
        $request->getStage('profile_id') != $request->getSession('app_session', 'results', 'profile_id')) {
        cradle('global')->flash('Unathorized Request', 'danger');
        return cradle('global')->redirect('/control/business/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // add CDN after this line

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the detials
        $profile = Rest::i($api.'/rest/profile/detail/'.$request->getStage('profile_id'))
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret']);

        if ($request->getStage()) {
            foreach ($request->getStage() as $key => $value) {
                $profile->set($key, $value);
            }
        }

        $results = $profile->get();

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

    $data['form'] = 'update';
    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-agent-update';
    $body = cradle('/app/sales')->template('agent/update', $data, ['agent_form']);

    //Set Content
    $response
        ->setPage('title', 'Sales Agent Update - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Sales Agent Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/agent/remove/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    // nothing to prepare!

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/profile/remove/'.$request->getStage('profile_id'))
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
        $message = cradle('global')->translate('Sales Agent was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/agent/search');
});

/**
 * Render the Sales Agent Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/agent/restore/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    // nothing to prepare!

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/profile/restore/'.$request->getStage('profile_id'))
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
        $message = cradle('global')->translate('Sales Agent was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/agent/search');
});

/**
 * Process the Sales Agent Create Page
 *
 * @param Request $request
 * @param Response $response
 *///BRB
$cradle->post('/control/business/agent/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    //if profile_image has no value make it null
    if ($request->hasStage('profile_image') && !$request->getStage('profile_image')) {
        $request->setStage('profile_image', null);
    }

    if ($request->hasStage('auth_slug') && $request->getStage('auth_slug')) {
        $request->setStage('profile_email', $request->getStage('auth_slug'));
    }

    $request->setStage('auth_active', 1);
    $request->setStage('auth_type', 'agent');
    $request->setStage('profile_type', 'agent');
    $request->setStage('auth_permissions', ['public_profile', 'personal_profile', 'business_dashboard']);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/auth/create')
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
        $route = '/control/business/agent/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Sales Agent was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/business/agent/search');
});

/**
 * Process the Sales Agent Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/agent/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->removeStage('auth_password');
        $request->removeStage('confirm');
    }

    if ($request->hasStage('auth_slug') && $request->getStage('auth_slug')) {
        $request->setStage('profile_email', $request->getStage('auth_slug'));
    }

    //----------------------------//
    // 3. Process Request
    $profile = Rest::i($api.'/rest/profile/update/'.$request->getStage('profile_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('profile_type', 'agent');

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $profile->set($key, $value);
        }
    }

    $results = $profile->post();
    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $response->setError($results['error'], $results['message'])
            ->set('json', 'validation', $results['validation']);

        $route = '/control/business/agent/update/' . $request->getStage('profile_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Sales Agent was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/business/agent/search');
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->post('/control/business/agent/import', function ($request, $response) {
    $columns = array(
        'keys' => array(
            'profile_active',
            'profile_id',
            'profile_name',
            'profile_email',
            'profile_created',
            'profile_updated'
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/business/agent/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;

    foreach ($data['csv'] as $key => $value) {

        $request->setStage('profile_type', 'agent');
        $request->setStage('auth_type', 'agent');

        foreach ($value as $index => $v) {
        if (empty($v)) {
        unset($value[$index]);
              }
          }

        if ($value['profile_created']) {
            unset($value['profile_created']);
        }

        if ($value['profile_updated']) {
            unset($value['profile_updated']);
        }

        $request->setStage($value);

        if (!isset($value['profile_id']) || empty($value['profile_id'])) {
            cradle()->trigger('profile-create', $request, $response);
        } else {
            cradle()->trigger('profile-update', $request, $response);
        }

        //get error response
        if ($response->isError()) {
            if ($response->getValidation()) {
                $errors[] = '<br>#'. $value['profile_name'] .' - '. implode(' ', $response->getValidation());
                $errorCtr++;
            }
        } else {
            if (!$value['profile_id']) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Sales Agent Created <br>' .' ['. $successUpdateCtr. '] Sales Agent Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 20000);

    //redirect
    $redirect = '/control/business/agent/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
*/
