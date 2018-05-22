<?php //-->

// This is like the 'import' in Java
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;

/**
 * Render the Lead Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/lead/search', function ($request, $response) {
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

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/lead/search')
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

    // add-remove label and make active-inactive
    $tags = $request->getStage('tags');
    $post = false;

    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk-rows')) {

        foreach ($request->getStage('bulk-rows') as $key => $value) {
            $request->setStage('lead_id', $value);
            if (!empty($tags)) {
                $request->setStage('bulk_action_active', 1);
                if ('add-tag' === $request->getStage('bulk')) {
                    $request->setStage('add_tags', $tags);
                } else if ('remove-tag' === $request->getStage('bulk')) {
                    $request->setStage('remove_tags', $tags);
                } else if ('add-campaign' === $request->getStage('bulk')) {
                    $request->setStage('add_campaigns', $tags);
                } else if ('remove-campaign' === $request->getStage('bulk')) {
                    $request->setStage('remove_campaigns', $tags);
                }

                $lead = Rest::i($api.'/rest/lead/update/'.$value);
                $post = true;
            }

            if ('restore' === $request->getStage('bulk')) {
                $lead = Rest::i($api.'/rest/lead/restore/'.$value);
            } else if ('remove' === $request->getStage('bulk')) {
                $lead = Rest::i($api.'/rest/lead/remove/'.$value);
            }

            $lead->set('client_id', $app['token'])
                ->set('client_secret', $app['secret']);
                
            if ($request->getStage()) {
                foreach ($request->getStage() as $key => $value) {
                    $lead->set($key, $value);
                }
            }

            if ($post) {
                $results = $lead->post();
            } else {
                $results = $lead->get();
            }

            if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response['message'], 'danger');
            } else {
                //add a flash
                $message = cradle('global')->translate('Bulk action successfully applied');
                cradle('global')->flash($message, 'success');
            }
            
        }
        return cradle('global')->redirect('/control/marketing/lead/search');
    }
    // exit;

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'lead_id' => 'Lead Id',
            'lead_active' => 'Lead Active',
            'lead_created' => 'Post Created',
            'lead_updated' => 'Post Updated',
            'lead_name' => 'Post Name',
            'lead_email' => 'Post Email',
            'lead_type' => 'Lead Type',
            'lead_gender' => 'Gender',
            'lead_birth' => 'Lead Birth',
            'lead_phone' => 'Lead Phone',
            'lead_location' => 'Lead Location',
            'lead_school' => 'Lead School',
            'lead_study' => 'Lead Study',
            'lead_company' => 'Lead Company',
            'lead_job_title' => 'Lead Job Title',
            'lead_tags' => 'Lead Tags',
            'lead_facebook' => 'Lead Banner',
            'lead_linkedin' => 'Lead LinkedIn',
            'lead_image' => 'Lead Image',
            'lead_campaigns' => 'Lead Campaigns',
        ];

        $export = [];
        if (isset($data['rows'])) {
            foreach ($data['rows'] as $index => $row) {
                // convert lead_tags from array to
                if (is_array($row['lead_tags']) && !is_null($row['lead_tags'])) {
                    $data['rows'][$index]['lead_tags'] = implode(', ', $row['lead_tags']);
                }

                // convert lead_campaigns from array to
                if (is_array($row['lead_campaigns']) && !is_null($row['lead_campaigns'])) {
                    $data['rows'][$index]['lead_campaigns'] = implode(', ', $row['lead_campaigns']);
                }

                // check if lead_phone have dash (-) or 63. remove it.
                if (isset($row['lead_phone'])) {
                    if (strpos($data['rows'][$index]['lead_phone'], '-')) {
                        $data['rows'][$index]['lead_phone']
                            = str_replace('-', '', $row['lead_phone']);
                    }

                    // if (strpos($data['rows'][$index]['lead_phone'], '63') == 0) {
                    //     $data['rows'][$index]['lead_phone']
                    //         = substr_replace($row['lead_phone'], '', 0, 2);
                    //         // = str_replace('-', '', $row['lead_phone']);
                    // }
                }
            }

            $export = $data['rows'];
        }
        
        //Set Filename
        $request->setStage('filename', 'Leads-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $export);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-lead-search';
    $body = cradle('/app/marketing')->template('lead/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Lead Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Lead Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/lead/create', function ($request, $response) {
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
    $class = 'page-marketing-lead-create';
    $body = cradle('/app/marketing')->template('lead/create', $data, ['lead_form']);

    //Set Content
    $response
        ->setPage('title', 'Lead Create - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Lead Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/lead/update/:lead_id', function ($request, $response) {
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

        $template = Rest::i($api.'/rest/lead/detail/'.$request->getStage('lead_id'))
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
        // count lead tags
        $data['item']['tags_count'] = count($data['item']['lead_tags']);
        $data['item']['campaigns_count'] = count($data['item']['lead_campaigns']);

        //get previous email
        $data['item']['lead_email_prev'] = $data['item']['lead_email'] ;
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-lead-update';
    $body = cradle('/app/marketing')->template('lead/update', $data, ['lead_form']);

    //Set Content
    $response
        ->setPage('title', 'Lead Update - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Lead Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/lead/remove/:lead_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/lead/remove/'.$request->getStage('lead_id'))
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
        $message = cradle('global')->translate('Lead was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/lead/search');
});

/**
 * Render the Lead Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/lead/restore/:lead_id', function ($request, $response) {
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
    $results = Rest::i($api.'/rest/lead/restore/'.$request->getStage('lead_id'))
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
        $message = cradle('global')->translate('Lead was Restored');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/control/marketing/lead/search');
});

/**
 * Process the Lead Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/lead/create', function ($request, $response) {
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
    $data = Rest::i($api.'/rest/lead/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            // replace 63 with zero, if 63 is at the start
            if ($key === 'lead_phone') {
                if (stripos($value, '63') === 0) {
                    $value = substr_replace($value, '', 0, 2);
                }
            }

            $data->set($key, $value);
        }
    }

    $results = $data->post();

    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $route = '/control/marketing/lead/create';

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Lead was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/lead/search');
});

/**
 * Process the Lead Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/lead/update/:lead_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();
    
    if (isset($data['lead_tags_count']) &&
        $data['lead_tags_count'] &&
        !isset($data['lead_tags'])) {
        $request->setStage('remove_lead_tags', true);
    }

    if (isset($data['lead_campaigns_count']) &&
        $data['lead_campaigns_count'] &&
        !isset($data['lead_campaigns'])) {
        $request->setStage('remove_lead_campaigns', true);
    }

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/lead/update/'.$request->getStage('lead_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            // replace 63 with zero if 63  is at the start
            if ($key === 'lead_phone') {
                if (stripos($value, '63') === 0) {
                    $value = substr_replace($value, '', 0, 2);
                }
            }
            $data->set($key, $value);
        }

    }

    $results = $data->post();

    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $route = '/control/marketing/lead/update/' . $request->getStage('lead_id');

        $response
            ->setError(true, $results['message'])
            ->set('json', 'validation', $results['validation']);

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Lead was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/lead/search');
});

/**
 * Process the Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/lead/import', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $columns = array(
        'keys' => array(
            'lead_id',
            'lead_active',
            'lead_created',
            'lead_updated',
            'lead_name',
            'lead_email',
            'lead_type',
            'lead_gender',
            'lead_birth',
            'lead_phone',
            'lead_location',
            'lead_school',
            'lead_study',
            'lead_company',
            'lead_job_title',
            'lead_tags',
            'lead_facebook',
            'lead_linkedin',
            'lead_image',
            'lead_campaigns',
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/marketing/lead/search');
    }

    $data = $response->getResults();

    $request->removeStage();

    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;
    foreach ($data['csv'] as $key => $value) {
        if (isset($value['lead_created']) &&
            !empty($value['lead_created'])) {
            $value['lead_created'] =
                date('Y-m-d H:i:s', strtotime($value['lead_created']));
        } else {
            unset($value['lead_created']);
        }

        if (isset($value['lead_updated']) &&
            !empty($value['lead_updated'])) {
            $value['lead_updated'] =
                date('Y-m-d H:i:s', strtotime($value['lead_updated']));
        } else {
            unset($value['lead_updated']);
        }

        if ($value['lead_tags']) {
            $value['lead_tags'] = explode(',', $value['lead_tags']);
        } else {
            $value['lead_tags'] = [];
        }

        if ($value['lead_campaigns']) {
            $value['lead_campaigns'] = explode(',', $value['lead_campaigns']);
        } else {
            $value['lead_campaigns'] = [];
        }

        // removes +63 and all the dashes (-) during import
        if ($value['lead_phone']) {
            if (strpos($value['lead_phone'], '-')) {
                $value['lead_phone'] = str_replace('-', '', $value['lead_phone']);
            }

            if (strpos($value['lead_phone'], '63') === 0) {
                $value['lead_phone']
                    = substr_replace($value['lead_phone'], '', 0, 2);
                    // = str_replace('63', '', $value['lead_phone']);
            }
        }

        if (empty($value['lead_active'])) {
            unset($value['lead_active']);
        }

        if (empty($value['lead_id'])) {
            unset($value['lead_id']);
            $rest = Rest::i($api.'/rest/lead/create');
        } else {
            $rest = Rest::i($api.'/rest/lead/update/'.$value['lead_id']);
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
                $errors[] = '<br>#'. $value['lead_name'] .' - '. implode(' ', $results['validation']);
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
    $message = ' ['. $successCreateCtr. '] Lead Created <br>' .' ['.
        $successUpdateCtr. '] Lead Updated ' . '<br>['. $errorCtr. '] Error(s) <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    $messageType = $errorCtr > 0 ? 'danger' : 'success';

    cradle('global')->flash($message, $messageType, 3000);

    //redirect
    $redirect = '/control/marketing/lead/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});