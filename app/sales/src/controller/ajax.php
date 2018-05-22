<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
use Cradle\Module\Utility\Rest;

/**
 * Render the AJAX Agent Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/sales/agent/search', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $request->setStage('auth-profile', true);
    $request->setStage('filter', ['profile_type' => 'agent']);

    $profile = Rest::i($api.'/rest/profile/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $profile->set($key, $value);
        }
    }

    $profile = $profile->get();

    if ($profile['error']) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $profile['validation'],
        ]));
    }

    $response->setError(false)->setResults($profile['results']);
});

/**
 * Render set deal status
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/sales/deal/update/status', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $request->getStage('user_history', $request->getSession('rest', 'profile_id'));
    if (!$request->getStage('deal_id')) {
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'Unknown deal to update',
            'validation' => $response->getValidation(),
        ]));
    }

    // cradle()->trigger('deal-update', $request, $response);
    $deal = Rest::i($api.'/rest/deal/update/'.$request->getStage('deal_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('user_history', $request->getSession('app_session', 'results', 'profile_id'));

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $deal->set($key, $value);
        }
    }

    $deal = $deal->post();

    if ($deal['error']) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $deal['validation'],
        ]));
    }

    return $response->setContent(json_encode([
        'error'      => false,
        'message'    => 'Updated status successfully',
    ]));
});

/**
 * Deal Amount
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/sales/deal/update', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    if (!$request->getStage('deal_id')) {
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'Unknown deal to update',
            'validation' => $response->getValidation(),
        ]));
    }

    $deal = Rest::i($api.'/rest/deal/update/'.$request->getStage('deal_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('user_history', $request->getSession('app_session', 'results', 'profile_id'));

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $deal->set($key, $value);
        }
    }

    $deal = $deal->post();

    if ($deal['error']) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $deal['validation'],
        ]));
    }

    if ($request->hasStage('deal_amount')) {
        $message = 'Amount updated successfully';
    }

    if ($request->hasStage('deal_close')) {
        $message = 'Close date updated successfully';
    }

    return $response->setContent(json_encode([
        'error'      => false,
        'message'    => $message,
        'results'    => [],
    ]));
});


/**
 * Render the AJAX Event Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/sales/event/search', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    $request->setStage('sales', true);

    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/event/search')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $data = $data->get();

    if ($data['error']) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $data['validation'],
        ]));
    }

    return $response->setContent(json_encode([
        'error'      => false,
        'results'    => $data['results'],
    ]));
});


/**
 * Process the Event Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/sales/event/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->getStage('profile_id')) {
        $profile = $request->getSession('app_session', 'results', 'profile_id');
        $request->setStage('profile_id', $profile);
    }

    //----------------------------//
    // 3. Process Request
    // create event
    $data = Rest::i($api.'/rest/event/create')
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
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $results['validation'],
        ]));
    }

    return $response->setContent(json_encode([
        'error'      => false,
        'message'    => 'Event was Created',
        'results'    => $results['results'],
    ]));
});
