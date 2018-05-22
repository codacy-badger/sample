<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use LinkedIn\LinkedIn;

/**
 * Render the Signup Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
/*$cradle->get('/control/marketing/signup', function ($request, $response) {
    $api = cradle('global')->config('settings', 'api');
    $app = cradle('global')->config('services', 'jobayan_app');
    $redirect = '/control/marketing/dashboard';

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $redirect = $host.'/control/app/receive?redirect_uri='.$redirect
    $redirect = urlencode($redirect);

    return cradle()
        ->redirect($api.'/dialog/signup?jobayan=marketing&client_id='. $app['token']
        .'&client_token='. $app['secret'] .'&redirect_uri=' . $redirect);

    //Render blank page
}, 'render-www-blank');*/

/**
 * Render the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/:app/login', function ($request, $response) {
    if ($request->getStage('app') != 'marketing' &&
        $request->getStage('app') != 'business') {
        cradle('global')->flash('Invalid Request', 'danger');
        cradle('global')->redirect('/');
    }

    if ($request->getSession('me')) {
        $request->removeSession('me');
    }

    $api = cradle('global')->config('settings', 'api');
    $app = cradle('global')->config('services', 'jobayan_app');

    $redirect = '/control/'.$request->getStage('app').'/dashboard';

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $redirect = $host.'/control/app/receive?redirect_uri='.$redirect;
    $access = '&app_access='.$request->getStage('app').'_dashboard';
    $redirect = urlencode($redirect);

    return cradle('global')
        ->redirect($api.'/dialog/login?client_id='. $app['token']
        .'&client_token='. $app['secret'] .'&redirect_uri=' . $redirect
        .$access);

    //Render blank page
}, 'render-www-blank');
