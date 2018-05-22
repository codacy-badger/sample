<?php //-->

use Cradle\Http\Request;
use Cradle\Http\Response;

use Cradle\Module\Utility\Rest;

/**
 * Render the Action Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/action/search', function ($request, $response) {
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

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/action/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getGet()) {
        foreach ($request->getGet() as $key => $value) {
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

    // Check if it is an export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'action_id' => 'Action Id',
            'action_active' => 'Action Active',
            'action_created' => 'Action Created',
            'action_updated' => 'Action Updated',
            'action_title' => 'Action Title',
            'action_event' => 'Action Event',
            'action_when' => 'Action When',
            'add_tags' => 'Action Add Tags',
            'remove_tags' => 'Action Remove Tags'
        ];

        // loop through the records. Look only for:
        // action_when
        // action_tags
        $export = [];
        if (isset($data['rows'])) {
            foreach ($data['rows'] as $index => $row) {
                $data['rows'][$index]['add_tags'] = '';
                $data['rows'][$index]['remove_tags'] = '';

                if (isset($row['action_tags']['add'])) {
                    $data['rows'][$index]['add_tags'] = implode(',', $row['action_tags']['add']);
                }

                if (isset($row['action_tags']['remove'])) {
                    $data['rows'][$index]['remove_tags'] = implode(',', $row['action_tags']['remove']);
                }

                $when = json_encode($row['action_when']);

                // convert action_when array(s) to string using json_encode
                if (is_array($row['action_when']) && !is_null($row['action_when'])) {
                    $when_arr = array();
                    // loop thru the each conditions
                    foreach ($row['action_when'] as $action_when => $condition) {
                        $when_arr[] = json_encode($condition);
                    }

                    $data['rows'][$index]['action_when'] = implode(',', $when_arr);
                }
            }

            $export = $data['rows'];
        }

        //Set Filename
        $request->setStage('filename', 'Actions-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $export);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    if ($request->getStage()) {
        $data = array_merge($request->getStage(), $data);
    }

    $class = 'page-marketing-action-search';
    $body = cradle('/app/marketing')->template('action/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Action Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Action Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/action/create', function ($request, $response) {
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

    $data['events'] = cradle('global')->config('story');

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-action-form page-marketing-action-create';
    $body = cradle('/app/marketing')->template('action/create', $data, ['action_form']);

    //Set Content
    $response
        ->setPage('title', 'Action Create - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Action Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/action/update/:action_id', function ($request, $response) {
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
        // trigger job to get the detials
        $results = Rest::i($api.'/rest/action/detail/'. $request->getStage('action_id'))
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

    $data['events'] = cradle('global')->config('story');

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-action-form page-marketing-action-update';
    $body = cradle('/app/marketing')->template('action/update', $data, ['action_form']);

    //Set Content
    $response
        ->setPage('title', 'Action Update - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Action Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/action/remove/:action_id', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/action/remove/'.$request->getStage('action_id'))
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
        $message = cradle('global')->translate('Action was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/action/search');
});

/**
 * Render the Action Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/action/restore/:action_id', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/action/restore/'.$request->getStage('action_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->get();

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Action was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/action/search');
});

/**
 * Process the Action Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/action/create', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/action/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $response->setResults($data->post());
    
    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/marketing/action/create';
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Action was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/action/search');
});

/**
 * Process the Action Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/action/update/:action_id', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/action/update/'.$request->getStage('action_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $response->setResults($data->post());

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/marketing/action/update/' . $request->getStage('action_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Action was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/action/search');
});

/**
 * Process the list of Actions from Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/action/bulk', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/action/bulk')
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

    cradle('global')->redirect('/control/marketing/action/search');
});

/**
 * Process the CSV Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/action/import', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //prepare  the columns for import. must be the same with the database
    $columns = array(
        'keys' => array(
            'action_id',
            'action_active',
            'action_created',
            'action_updated',
            'action_title',
            'action_event',
            'action_when',
            'add_tags',
            'remove_tags'
        )
    );

    //set to stage then trigger the job
    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    //error check
    if ($response->isError()) {
        return;
    }

    $data = $response->getResults();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        foreach ($value as $field => $datum) {
            if (empty($datum) && $datum != '0') {
                unset($value[$field]);
            }
        }

        $value['action_tags'] = [];

        // if there are add tags, prep it
        if (!empty($value['add_tags'])) {
            $value['action_tags']['add'] = explode(',', $value['add_tags']);
        }

        // if there are remove tags, prep it
        if (!empty($value['remove_tags'])) {
            $value['action_tags']['remove'] = explode(',', $value['remove_tags']);
        }

        // if there's a condition, prep it
        if (!empty($value['action_when'])) {
            $value['action_when'] = '['.$value['action_when'].']';
            $value['action_when'] = json_decode($value['action_when'], true);
        }

        if (isset($value['action_created']) &&
            !empty($value['action_created'])) {
            $value['action_created'] =
                date('Y-m-d H:i:s', strtotime($value['action_created']));
        } else {
            unset($value['action_created']);
        }

        if (isset($value['action_updated']) &&
            !empty($value['action_updated'])) {
            $value['action_updated'] =
                date('Y-m-d H:i:s', strtotime($value['action_updated']));
        } else {
            unset($value['action_updated']);
        }
        
        if (empty($value['action_id'])) {
            unset($value['action_id']);
            $rest = Rest::i($api.'/rest/action/create');
        } else {
            $rest = Rest::i($api.'/rest/action/update/'.$value['action_id']);
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
                $errors[] = '<br>#'.$value['action_title'].' - '
                    .implode(' ', $results['validation']);
                $errorCtr++;
            }
        } else {
            if (!isset($value['action_id'])) {
                $successCreateCtr++;
            } else {
                $successUpdateCtr++;
            }
        }
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Action Created <br>' .' ['.
        $successUpdateCtr . '] Action Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/marketing/action/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
