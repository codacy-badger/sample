<?php //-->
use Cradle\Module\Utility\Rest;

/**
 * Render the Profile Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/profile/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('admin');
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
        cradle('global')->redirect('/control/marketing/dashboard');
    }

    $data = $data['results'] ? $data['results']: [];

    // add label, remove label, add status, remove status, make active, make inactive
    $tags = $request->getStage('tags');
    $post = false;

    if (!empty($request->getStage('bulk')) && $request->hasStage('bulk-rows')) {
        foreach ($request->getStage('bulk-rows') as $key => $value) {
            $request->setStage('profile_id', $value);
            if (!empty($tags)) {
                if ('add-tag' === $request->getStage('bulk')) {
                    $request->setStage('add_tags', $tags);
                } else if ('remove-tag' === $request->getStage('bulk')) {
                    $request->setStage('remove_tags', $tags);
                } else if ('add-story' === $request->getStage('bulk')) {
                    $request->setStage('add_story', $tags);
                } else if ('remove-story' === $request->getStage('bulk')) {
                    $request->setStage('remove_story', $tags);
                } else if ('add-campaign' === $request->getStage('bulk')) {
                    $request->setStage('add_campaigns', $tags);
                } else if ('remove-campaign' === $request->getStage('bulk')) {
                    $request->setStage('remove_campaigns', $tags);
                }

                $profile = Rest::i($api.'/rest/profile/update/'.$value);
                $post = true;
            }

            if ($request->getStage('bulk') === 'restore') {
                $profile = Rest::i($api.'/rest/profile/restore/'.$value);
            } else if ($request->getStage('bulk') === 'remove') {
                $profile = Rest::i($api.'/rest/profile/remove/'.$value);
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
            
            if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response['message'], 'danger');
            } else {
                //add a flash
                $message = cradle('global')->translate('Bulk action successfully applied');
                cradle('global')->flash($message, 'success');
            }
        }
        return cradle('global')->redirect('/control/marketing/profile/search');
    }

    //Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'profile_id' => 'Profile Id',
            'profile_active' => 'Profile Active',
            'profile_image' => 'Profile Image',
            'profile_name' => 'Profile Name',
            'profile_company' => 'Profile Company',
            'profile_email' => 'Profile Email',
            'profile_phone' => 'Profile Phone',
            'profile_facebook' => 'Profile Facebook',
            'profile_linkedin' => 'Profile LinkedIn',
            'profile_twitter' => 'Profile Twitter',
            'profile_google' => 'Profile Google',
            'profile_type' => 'Profile Type',
            'profile_created' => 'Profile Created',
            'profile_updated' => 'Profile Updated',
            'profile_campaigns' => 'Profile Campaigns'
        ];

        // Checks if there are rows
        if (isset($data['rows']) && !empty($data['rows'])) {
            //convert profile_tags from array to
            foreach ($data['rows'] as $index => $row) {
                if (is_array($row['profile_tags']) && !is_null($row['profile_tags'])) {
                    $data['rows'][$index]['profile_tags'] = implode(', ', $row['profile_tags']);
                }
                
                if (is_array($row['profile_story']) && !is_null($row['profile_story'])) {
                    $data['rows'][$index]['profile_story'] = implode(', ', $row['profile_story']);
                }
                
                if (is_array($row['profile_campaigns']) && !is_null($row['profile_campaigns'])) {
                    $data['rows'][$index]['profile_campaigns'] = implode(', ', $row['profile_campaigns']);
                }
            }
        }

        //Set Filename
        $request->setStage('filename', 'Profiles-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body

    $data = array_merge($request->getStage(), $data);
    $class = 'page-marketing-profile-search';
    $body = cradle('/app/marketing')->template('profile/search', $data);

    //Set Content
    $response
        ->setPage('title', 'Profile Search - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // add CDN after this line

    // if $data aka $request->getPost() is empty
    if (empty($data['item'])) {
        // trigger job to get the detials
        $profile = Rest::i($api.'/rest/profile/detail/'.$request->getStage('profile_id'))
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->set('marketing', '1');

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
        // count profile tags
        $data['item']['tags_count'] = count($data['item']['profile_tags']);
        // count profile stories
        $data['item']['story_count'] = count($data['item']['profile_story']);
        // count profile campaigns
        $data['item']['campaign_count'] = count($data['item']['profile_campaigns']);
    }

    //process errors
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-profile-update';
    $body = cradle('/app/marketing')->template('profile/update', $data, ['profile_form']);

    //Set Content
    $response
        ->setPage('title', 'Profile Update - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Profile Remove Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/profile/remove/:profile_id', function ($request, $response) {
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

    cradle('global')->redirect('/control/marketing/profile/search');
});

/**
 * Render the Profile Restore Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/marketing/profile/restore/:profile_id', function ($request, $response) {
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

    cradle('global')->redirect('/control/marketing/profile/search');
});

/**
 * Process the Profile Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/marketing/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    if (isset($data['profile_tags_count']) && 
        $data['profile_tags_count'] &&
        !isset($data['profile_tags'])) {
        $request->setStage('remove_profile_tags', true);
    }

    if (isset($data['profile_story_count']) && 
        $data['profile_story_count'] &&
        !isset($data['profile_story'])) {
        $request->setStage('remove_profile_story', true);
    }

    if (isset($data['profile_campaign_count']) && 
        $data['profile_campaign_count'] &&
        !isset($data['profile_campaigns'])) {
        $request->setStage('remove_profile_campaigns', true);
    }
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
    if ($results['error']) {
        $response->setError($results['error'], $results['message'])
            ->set('json', 'validation', $results['validation']);

        $route = '/control/marketing/profile/update/' . $request->getStage('profile_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Profile was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/marketing/profile/search');
});