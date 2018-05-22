<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;

/**
 * Render the Profile Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/profile/search', function ($request, $response) {
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
            'profile_tags',
            'profile_story',
            'profile_campaigns',
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

    // trigger the job to get all profiles
    $request->setStage('filter', 'type', 'poster');
    $request->setStage('sales', true);

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

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'profile_id' => 'Profile Id',
            'profile_active' => 'Profile Active',
            'profile_image' => 'Profile Image',
            'profile_company' => 'Profile Company',
            'profile_name' => 'Profile Name',
            'profile_email' => 'Profile Email',
            'deal_amount' => 'Deal Amount',
            'deal_status' => 'Deal Status',
            'deal_close' => 'Deal Close',
            'profile_phone' => 'Profile Phone',
            'profile_facebook' => 'Profile Facebook',
            'profile_linkedin' => 'Profile LinkedIn',
            'profile_twitter' => 'Profile Twitter',
            'profile_google' => 'Profile Google',
            'profile_type' => 'Profile Type',
            'profile_created' => 'Profile Created',
            'profile_updated' => 'Profile Updated'
        ];

        //convert profile_tags from array to
        foreach ($data['rows'] as $index => $row) {
            if (is_array($row['profile_tags']) && !is_null($row['profile_tags'])) {
                $data['rows'][$index]['profile_tags'] = implode(', ', $row['profile_tags']);
            }
        }

        //convert profile_statuses from array to
        foreach ($data['rows'] as $index => $row) {
            if (is_array($row['profile_story']) && !is_null($row['profile_story'])) {
                $data['rows'][$index]['profile_story'] = implode(', ', $row['profile_story']);
            }
        }

        //convert profile_campaigns from array to
        foreach ($data['rows'] as $index => $row) {
            if (is_array($row['profile_campaigns']) && !is_null($row['profile_campaigns'])) {
                $data['rows'][$index]['profile_campaigns'] = implode(', ', $row['profile_campaigns']);
            }
        }

        //Set Filename
        $request->setStage('filename', 'Companies-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-sales-profile-search';
    $body = cradle('/app/sales')->template('profile/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Company Search - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Profile Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/profile/create', function ($request, $response) {
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

    $pipeline = Rest::i($api.'/rest/pipeline/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    $pipeline = $pipeline['results']['rows'][0]['pipeline_id'];

    $data['pipeline'] = Rest::i($api.'/rest/pipeline/detail/'.$pipeline)
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get()['results'];

    $data['form'] = 'create';

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-profile-form';
    $body = cradle('/app/sales')->template('profile/create', $data, ['profile_form']);

    //Set Content
    $response
        ->setPage('title', 'Profile Create - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Process the Profile Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/profile/create', function ($request, $response) {
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
    //if profile_type has no value make it poster
    if ($request->hasStage('profile_type') && !$request->getStage('profile_type')) {
        $request->setStage('profile_type', 'poster');
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/profile/create')
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
        $route = '/control/business/profile/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Profile was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/business/profile/search');
});

/**
 * Render the Profile Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/profile/remove/:profile_id', function ($request, $response) {
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
        $message = cradle('global')->translate('Profile was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/profile/search');
});

/**
 * Render the Profile Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/profile/restore/:profile_id', function ($request, $response) {
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
        $message = cradle('global')->translate('Profile was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/profile/search');
});

/**
 * Render the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    if ($request->getSession('app_session', 'results', 'profile_type') != 'agent' &&
        $request->getSession('app_session', 'results', 'profile_type') != 'admin_agent' &&
        $request->getSession('app_session', 'results', 'profile_id') != 1) {
        cradle('global')->flash('Unauthorized Access', 'danger');
        cradle('global')->redirect('/control/business/lead/search');
    }

    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

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

    $pipeline = Rest::i($api.'/rest/pipeline/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    $pipeline = $pipeline['results']['rows'][0]['pipeline_id'];

    $data['pipeline'] = Rest::i($api.'/rest/pipeline/detail/'.$pipeline)
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get()['results'];
        
    $data['form'] = 'update';

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-profile-form';
    $body = cradle('/app/sales')->template('profile/update', $data, ['profile_form']);

    //Set Content
    $response
        ->setPage('title', 'Company Update - Sales Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Process the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('business');
    if ($request->getSession('app_session', 'results', 'profile_type') != 'agent' &&
        $request->getSession('app_session', 'results', 'profile_type') != 'admin_agent' &&
        $request->getSession('app_session', 'results', 'profile_id') != 1) {
        cradle('global')->flash('Unauthorized Access', 'danger');
        cradle('global')->redirect('/control/business/lead/search');
    }

    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if ($request->getStage('profile_company')) {
        $request->setStage('deal_name', $request->getStage('profile_company'));
    }

    $request->setStage('user_history', $request->getSession('rest', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    $profile = Rest::i($api.'/rest/profile/update/'.$request->getStage('profile_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $profile->set($key, $value);
        }
    }

    $results = $profile->post();

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/business/profile/update/' . $request->getStage('profile_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Profile was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/business/profile/search');
});
