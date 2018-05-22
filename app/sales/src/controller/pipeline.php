<?php //-->
use Cradle\Module\Utility\Rest;

/**
 * Render the Pipeline Search Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->get('/control/business/pipeline/search', function ($request, $response) {
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

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/pipeline/search')
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
        cradle('global')->redirect('/control/business/pipeline/search');
    }

    $data = $data['results'] ? $data['results'] : [];

    // add-remove stage and make active-inactive
    $tags = $request->getStage('tags');

    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk_rows')) {
        if ($request->getStage('bulk') == 'remove' ||
            $request->getStage('bulk') == 'restore') {
            cradle()->trigger('pipeline-bulk-action', $request, $response);
        }
        if ($request->getStage('bulk') == 'add-stage' ||
            $request->getStage('bulk') == 'remove-stage') {
            $field = str_replace('-', '_', $request->getStage('bulk')).'s';
            $tags = $request->getStage('pipeline_stages');

            foreach ($request->getStage('bulk_rows') as $id) {
                $request->setStage('pipeline_id', $id);
                $request->setStage($field, $tags);
                cradle()->trigger('pipeline-update', $request, $response);
            }
        }

        if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response['message'], 'danger');
        } else {
            //add a flash
            $message = cradle('global')->translate('Bulk action successfully applied');
            cradle('global')->flash($message, 'success');
        }

        return cradle('global')->redirect('/control/business/pipeline/search');
    }

    //gets the first active pipeline for Boards
    $pipeline_id = $data['rows'][0]['pipeline_id'];

    $request->setStage('pipeline_id', $pipeline_id);

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'pipeline_id' => 'Pipeline Id',
            'pipeline_active' => 'Pipeline Active',
            'pipeline_created' => 'Pipeline Created',
            'pipeline_updated' => 'Pipeline Updated',
            'pipeline_name' => 'Pipeline Name',
            'pipeline_stages' => 'Pipeline Stages',
        ];

        foreach ($data['rows'] as $index => $row) {
            // convert pipeline_stages from array to string
            if (is_array($row['pipeline_stages']) && !is_null($row['pipeline_stages'])) {
                $data['rows'][$index]['pipeline_stages'] = implode(', ', $row['pipeline_stages']);
            }
        }

        //Set Filename
        $request->setStage('filename', 'Pipelines-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;

    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-sales-pipeline-search';
    $body = cradle('/app/sales')->template('pipeline/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Pipelines - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');
*/
/**
 * Render the Pipeline Create Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->get('/control/business/pipeline/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-pipeline-create';
    $body = cradle('/app/sales')->template('pipeline/create', $data, ['pipeline_form']);

    //Set Content
    $response
        ->setPage('title', 'Pipeline Create - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');
*/
/**
 * Render the Pipeline Board Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/pipeline/:pipeline_id/board', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    // rest configs
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    // get pipeline
    $template = Rest::i($api.'/rest/pipeline/detail/'.$request->getStage('pipeline_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    $results = $template->get();

    //process errors
    if ($results['error']) {
        $response->setFlash($results['message'], 'danger');
        cradle('global')->redirect('/control/business/deal/search');
    }

    $data['pipeline'] = $results['results']; // instantiate with results

    $data['pipeline_total'] = 0;
    $data['total_stage'] = count($data['pipeline']['pipeline_stages']) - 1;

    if ($request->getStage('assignment') == 'own') {
        $request->setStage('me', $request->getSession('rest', 'profile_id'));
    }

    if ($request->getStage('filter', 'deal_type') == 'all') {
        $request->removeStage('filter', 'deal_type');
    }

    if ($request->getStage('filter_range')) {
        foreach ($request->getStage('filter_range') as $column => $range) {
            if (!$range['start'] &&
            $range['start'] != '0' &&
            !$range['end'] &&
            $range['end'] != '0') {
                $request->removeStage('filter_range', $column);
            }
        }

        if (!count($request->getStage('filter_range'))) {
            $request->removeStage('filter_range');
        }
    }

    $filter = array_merge(
        $request->getStage('filter') ? $request->getStage('filter') : [],
        ['pipeline_id' => $request->getStage('pipeline_id')]
    );

    // for displaying in the board according to deal_status
    $stages = [];
    if (!isset($data['rows'])) {
        $data['rows'] = [];
    }

    foreach ($data['pipeline']['pipeline_stages'] as $key => $stage) {
        $stages[$stage] = [
            'total' => 0,
            'total_deals' => 0,
            'deals' => []
        ];

        unset($data['pipeline']['pipeline_stages'][$key]);

        $filter = $request->getStage('filter');
        $filter['deal_status'] = $stage;
        $request->setStage('filter', $filter);

        // pull all deals under this stage
        $request->setStage('filter', $filter);
        $result = Rest::i($api.'/rest/deal/search')
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret']);

        if ($request->getStage()) {
            foreach ($request->getStage() as $key => $value) {
                $result->set($key, $value);
            }
        }

        $result = $result->get();

        if ($result['error'] && isset($result['message'])) {
            //add a flash
            cradle('global')->flash($result['message'], 'danger');
            cradle('global')->redirect('/control/business/pipeline/:pipeline_id/board');
        }

        $deals = $result['results'] ? $result['results'] : [];
        if (isset($deals['rows'])) {
            $stages[$stage]['deals'] = $deals['rows'];
            $data['rows'] = array_merge($data['rows'], $deals['rows']);
            $stages[$stage]['total'] = $deals['deals_total_amount'];
            $stages[$stage]['total_deals'] = $deals['total'];
            $data['pipeline_total'] += $deals['deals_total_amount'];
        }

    }

    $data['pipeline']['pipeline_stages'] = $stages;

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

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-pipeline-board';
    $body = cradle('/app/sales')->template('pipeline/board', $data);

    //Set Content
    $response
        ->setPage('title', 'Board - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Pipeline Table Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/pipeline/:pipeline_id/table', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    // rest configs
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    // get pipeline
    $template = Rest::i($api.'/rest/pipeline/detail/'.$request->getStage('pipeline_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    $results = $template->get();

    //process errors
    if ($results['error']) {
        $response->setFlash($results['message'], 'danger');
        cradle('global')->redirect('/control/business/deal/search');
    }

    $data['pipeline'] = $results['results']; // instantiate with results

    $data['pipeline_total'] = 0;
    $data['total_stage'] = count($data['pipeline']['pipeline_stages']) - 1;

    if ($request->getStage('assignment') == 'own') {
        $request->setStage('me', $request->getSession('rest', 'profile_id'));
    }

    if ($request->getStage('filter', 'deal_type') == 'all') {
        $request->removeStage('filter', 'deal_type');
    }

    if ($request->getStage('filter_range')) {
        foreach ($request->getStage('filter_range') as $column => $range) {
            if (!$range['start'] &&
            $range['start'] != '0' &&
            !$range['end'] &&
            $range['end'] != '0') {
                $request->removeStage('filter_range', $column);
            }
        }

        if (!count($request->getStage('filter_range'))) {
            $request->removeStage('filter_range');
        }
    }

    $filter = array_merge(
        $request->getStage('filter') ? $request->getStage('filter') : [],
        ['pipeline_id' => $request->getStage('pipeline_id')]
    );

    // pull all deals under this pipeline
    $request->setStage('filter', $filter);
    $result = Rest::i($api.'/rest/deal/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $result->set($key, $value);
        }
    }

    $result = $result->get();

    if ($result['error'] && isset($result['message'])) {
        //add a flash
        cradle('global')->flash($result['message'], 'danger');
        cradle('global')->redirect('/control/business/pipeline/:pipeline_id/board');
    }

    $deals = $result['results'] ? $result['results'] : [];

    if (isset($deals['rows'])) {
        $data['total'] = $deals['total'];
        $deals = $deals['rows'];
    }

    $data['rows'] = $deals;

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
        $request->setStage('filename', 'Clients-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-sales-pipeline-board';
    $body = cradle('/app/sales')->template('pipeline/table', $data);

    //Set Content
    $response
        ->setPage('title', 'Pipeline Update - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Pipeline Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/business/pipeline/update/:pipeline_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');

    //----------------------------//
    // 2. Prepare Data
    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        $app = cradle('global')->config('services', 'jobayan_app');
        $api = cradle('global')->config('settings', 'api');

        $template = Rest::i($api.'/rest/pipeline/detail/'.$request->getStage('pipeline_id'))
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
    $class = 'page-sales-pipeline-update';
    $body = cradle('/app/sales')->template('pipeline/update', $data, ['pipeline_form']);

    //Set Content
    $response
        ->setPage('title', 'Pipeline Update - Business Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-sales-page');

/**
 * Render the Pipeline Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->get('/control/business/pipeline/remove/:pipeline_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/pipeline/remove/'.$request->getStage('pipeline_id'))
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
        $message = cradle('global')->translate('Pipeline was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/pipeline/search');
});
*/
/**
 * Render the Pipeline Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->get('/control/business/pipeline/restore/:pipeline_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/pipeline/restore/'.$request->getStage('pipeline_id'))
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
        $message = cradle('global')->translate('Pipeline was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/business/pipeline/search');
});

/**
 * Process the Pipeline Create Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->post('/control/business/pipeline/create', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/pipeline/create')
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
        $route = '/control/business/pipeline/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Pipeline was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/business/pipeline/search');
});
*/
/**
 * Process the Pipeline Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/pipeline/update/:pipeline_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    // nothing to prep

    //----------------------------//
    // 3. Process Request

    $data = Rest::i($api.'/rest/pipeline/update/'.$request->getStage('pipeline_id'))
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
        $route = '/control/business/pipeline/update/' . $request->getStage('pipeline_id');

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Pipeline was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/business/pipeline/update/' . $request->getStage('pipeline_id'));
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->post('/control/business/pipeline/import', function ($request, $response) {
    $columns = array(
        'keys' => array(
            'pipeline_id',
            'pipeline_active',
            'pipeline_created',
            'pipeline_updated',
            'pipeline_name',
            'pipeline_stages',
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/business/pipeline/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        if ($value['pipeline_stages']) {
            $value['pipeline_stages'] = explode(',', $value['pipeline_stages']);
        }

        if ($value['pipeline_created']) {
            $value['pipeline_created'] = date(
                'Y-m-d H:i:s', strtotime($value['pipeline_created']));
        }

        if ($value['pipeline_updated']) {
            $value['pipeline_updated'] = date(
                'Y-m-d H:i:s', strtotime($value['pipeline_updated']));
        }

        if (trim($value['pipeline_active']) == '') {
            unset($value['pipeline_active']);
        }

        $request->setStage($value);

        if (!isset($value['pipeline_id']) || empty($value['pipeline_id'])) {
            $request->removeStage('pipeline_id');
            cradle()->trigger('pipeline-create', $request, $response);
        } else {
            cradle()->trigger('pipeline-update', $request, $response);
        }

        //get error response
        if ($response->isError()) {
            if ($response->getValidation()) {
                $errors[] = '<br>#'. $value['pipeline_name'] .' - '. implode(' ', $response->getValidation());
                $errorCtr++;
            }
        } else {
            if (!$value['pipeline_id']) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Pipeline Created <br>' .' ['.
        $successUpdateCtr. '] Pipeline Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/business/pipeline/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
*/
/**
 * Process the list of Actions from Search Page
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->post('/control/business/pipeline/bulk', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/pipeline/bulk')
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

    cradle('global')->redirect('/control/business/pipeline/search');
});
*/
