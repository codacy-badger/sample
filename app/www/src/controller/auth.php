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
 * Process the Verification Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/activate/:auth_id/:hash', function ($request, $response) {
    //get the detail
    cradle()->trigger('auth-detail', $request, $response);
    //form hash
    $authId = $response->getResults('auth_id');
    $authUpdated = $response->getResults('auth_updated');
    $hash = md5($authId.$authUpdated);

    //check the verification hash
    if ($hash !== $request->getStage('hash')) {
        cradle('global')->flash('Invalid verification. Try again.', 'danger');
        return cradle('global')->redirect('/verify');
    }

    //activate
    $request->setStage('auth_active', 1);

    if ($request->hasSession('me')) {
        $request->setSession('me', 'auth_active', 1);
    }

    //trigger the job
    cradle()->trigger('auth-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash('Invalid verification. Try again.', 'danger');
        return cradle('global')->redirect('/verify');
    }

    if ($response->getResults('profile_type') == 'interested') {
        $request->removeStage();
        $request->setStage('profile_id', $response->getResults('profile_id'));
        $request->setStage('post_id', $response->getResults('profile_flag'));
        cradle()->trigger('post-like', $request, $response);
    }


    //it was good
    //add a flash
    cradle('global')->flash('Activation Successful', 'success');

    //redirect
    cradle('global')->redirect('/login');
});

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
$cradle->get('/signup', function ($request, $response) {
    //check if user is already logged in
    if ($request->hasSession('me')) {
        cradle('global')->flash('Already logged in', 'success');
        return cradle('global')->redirect('/');
    }

    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //add captcha
    cradle()->trigger('captcha-load', $request, $response);
    $data['captcha'] = $response->getResults('captcha');

    if ($response->isError()) {
        if ($response->getValidation('auth_slug')) {
            $message = $response->getValidation('auth_slug');
            $response->addValidation('profile_email', $message);
        }

        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // remove session reference for this page
    if ($request->hasSession('reference')) {
        $request->removeSession('reference');
    }

    //Render body
    $class = 'page-auth-signup';
    $title = cradle('global')->translate('Sign Up and Get Started Here - Jobayan');

    $body = cradle('/app/www')->template('signup', $data);

    //for facebook pixel tracker
    $response->setPage('page', 'signup');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->addMeta('description', 'Sign up for a free account at Jobayan. Create a Quick Job Post, submit your resume. A simple way to connect to job seekers and job posters')
        ->addMeta('keywords', strtolower(implode(',', [
            'jobs in ' . $request->getSession('country'),
            'kalibrr',
            'jobstreet',
            'work abroad',
            'best jobs'
        ])))
        ->setContent($body);

    //Render blank page
}, 'render-www-blank');

/**
 * Render the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/login', function ($request, $response) {
    //check if user is already logged in
    if ($request->hasSession('me')) {
        cradle('global')->flash('Already logged in', 'success');
        return cradle('global')->redirect('/');
    }

    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');


    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // remove session reference for this page
    if ($request->hasSession('reference')) {
        $request->removeSession('reference');
    }

    //Render body
    $class = 'page-auth-login';
    $title = cradle('global')->translate('Welcome! Log In Here - Jobayan');
    $body = cradle('/app/www')->template('login', $data);

    //for facebook pixel tracker
    $response->setPage('page', 'login');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->addMeta('description', 'Login at Jobayan. If you are not a member yet, Register for free now to Apply for Jobs, Create Post, Promote Post. Get job alerts, apply online, connect to job seekers and job posters')
        ->addMeta('keywords', strtolower(implode(',', [
            'jobs in ' . $request->getSession('country'),
            'kalibrr',
            'jobstreet',
            'work abroad',
            'best jobs'
        ])))
        ->setContent($body);

    //Render blank page
}, 'render-www-blank');

/**
 * Render the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/login/facebook', function ($request, $response) {
    //check if user is already logged in
    if ($request->hasSession('me')) {
        cradle('global')->flash('Already logged in', 'success');
        return cradle('global')->redirect('/');
    }

    $config = cradle('global')->service('facebook-graph');

    //if there's no config
    if (!$config) {
        //redirect
        return cradle('global')->redirect('/login');
    }

    //there's a config
    $facebook = new Facebook($config);
    $helper = $facebook->getRedirectLoginHelper();

    $protocol = 'http';
    if ($request->getServer('HTTP_CF_VISITOR')) {
        $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
        if ($pos !== false) {
            $protocol = 'https';
        }
    }

    //host
    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    //if there is not a code
    if (!$request->hasStage('code')) {
        if ($request->hasGet('redirect_uri')) {
            $request->setSession('redirect_uri', $request->getGet('redirect_uri'));
        }

        $redirect = $helper->getLoginUrl($host . $request->getServer('REDIRECT_URL'), ['email']);

        //redirect
        return cradle('global')->redirect($redirect);
    }

    // get redirect uri
    if ($request->getSession('redirect_uri')) {
        $request->setGet('redirect_uri', $request->getSession('redirect_uri'));
        $request->removeSession('redirect_uri');
    }

    // get claim ref
    if ($request->getSession('reference')) {
        $request->setStage('profile', $request->getSession('reference', 'profile'));
    }

    //there's a code
    try {
        $accessToken = $helper->getAccessToken($host . $request->getServer('REDIRECT_URL'));
    } catch (FacebookResponseException $e) {
        // When Graph returns an error
        cradle('global')->flash($e->getMessage(), 'danger');
        return cradle('global')->redirect('/login');
    } catch (FacebookSDKException $e) {
        cradle('global')->flash($e->getMessage(), 'danger');
        return cradle('global')->redirect('/login');
    }

    // Logged in!
    $token = (string) $accessToken;

    // Now you can redirect to another page and use the
    // access token from $token
    try {
        // Returns a `Facebook\FacebookResponse` object
        $results = $facebook->get('/me?fields=id,name,email', $token);
    } catch (FacebookResponseException $e) {
        cradle('global')->flash($e->getMessage(), 'danger');
        return cradle('global')->redirect('/login');
    } catch (FacebookSDKException $e) {
        cradle('global')->flash($e->getMessage(), 'danger');
        return cradle('global')->redirect('/login');
    }

    // fb user data
    $user = $results->getGraphUser();

    if (!isset($user['email'])
        || !$user['email']
        || !isset($user['name'])
        || !$user['name']
        || !isset($user['id'])
        || !$user['id']
    ) {
        return cradle('global')->redirect('/login');
    }

    //set some defaults
    $request->setStage('profile_email', $user['email']);
    $request->setStage('profile_name', $user['name']);
    $request->setStage('profile_facebook', 'https://www.facebook.com/' . $user['id']);

    $request->setStage(
        'profile_image',
        implode('/', [
            'https://graph.facebook.com',
            $user['id'],
            'picture?type=square'
        ])
    );

    $request->setStage('auth_facebook_token', $token);
    $request->setStage('auth_slug', $user['email']);
    $request->setStage('auth_password', $user['id']);
    $request->setStage('auth_active', 1);
    $request->setStage('confirm', $user['id']);
    $request->setStage('auth_permissions', [
        'public_product',
        'public_profile',
        'personal_profile',
        'personal_product',
        'personal_comment',
        'personal_review'
    ]);

    cradle()->trigger('auth-sso-login', $request, $response);

    if ($response->isError() && $request->hasSession('reference')) {
        $redirect = '/claim?ref=' . $request->getSession('reference', 'hash');
        $request->removeSession('reference');
        //add a flash
        $message = cradle('global')->translate('Your Facebook account has an existing profile.');
        cradle('global')->flash($message, 'danger');
        return cradle('global')->redirect($redirect);
    }

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/login', $request, $response);
    }

    //it was good

    //store to session
    $_SESSION['me'] = $response->getResults();

    // if company
    if (!empty(trim($_SESSION['me']['profile_company']))) {
        // if user have less 20 credits, flag session true
        if ($_SESSION['me']['profile_credits'] < 20) {
            $_SESSION['me']['credit_flag'] = 1;
        }
    } else {
        // add profile is seeker add modal flag
        $_SESSION['me']['modal_flag'] = 'poster';
    }

    //redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    return cradle('global')->redirect($redirect);
});

/**
 * Render the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/login/linkedin', function ($request, $response) {
    //check if user is already logged in
    if ($request->hasSession('me')) {
        cradle('global')->flash('Already logged in', 'success');
        return cradle('global')->redirect('/');
    }

    $config = cradle('global')->service('linkedin-api');

    //if there's no config
    if (!$config) {
        //redirect
        return cradle('global')->redirect('/login');
    }

    //there's a config
    $protocol = 'http';

    if ($request->getServer('HTTP_CF_VISITOR')) {
        $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
        if ($pos !== false) {
            $protocol = 'https';
        }
    }

    //host
    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    //redirect uri
    $uri = $host . $request->getServer('REQUEST_URI');

    //find position
    $findme   = '&code';
    $pos = strpos($uri, $findme);

    //if &code exist
    if ($pos !== false) {
        $uri = substr($uri, 0, $pos);
    }

    //prepare parameters
    $linkedin = new LinkedIn([
        'api_key' => $config['api_key'],
        'api_secret' => $config['api_secret'],
        'callback_url' => $uri
    ]);

    //if there is not a code
    if (!$request->hasStage('code')) {
        $redirect = $linkedin->getLoginUrl([
            LinkedIn::SCOPE_BASIC_PROFILE,
            LinkedIn::SCOPE_EMAIL_ADDRESS
            //removing this to fix invalid scope authorization
            //LinkedIn::SCOPE_NETWORK
        ]);

         // preserve the linkedin object because after redirect, we would need the
         // linkedin object. We don't want to reinitialize or find another way to
         // call an object that would config linkedin again.
         $request->setSession('linkedin', $linkedin);

         //redirect
         return cradle('global')->redirect($redirect);
    }

    // issue-522 better way to do it?
    $linkedin = $request->getSession('linkedin');
    $request->removeSession('linkedin');

    //there's a code
    $accessToken = $linkedin->getAccessToken($request->getStage('code'));

    // Logged in!
    $token = (string) $accessToken;

    // Now you can redirect to another page and use the
    // access token from $token
    $user = $linkedin->get('/people/~:(id,first-name,last-name,email-address,picture-url,site-standard-profile-request)');

    if ((!isset($user['emailAddress']) || !$user['emailAddress']) ||
        (!isset($user['firstName']) || !$user['firstName']) ||
        (!isset($user['lastName']) || !$user['lastName']) ||
        (!isset($user['id']) || !$user['id'])
    ) {
        return cradle('global')->redirect('/login');
    }
    // get claim ref
    if ($request->getSession('reference')) {
        $request->setStage('profile', $request->getSession('reference', 'profile'));
    }

    //set some defaults
    $request->setStage('profile_email', $user['emailAddress']);
    $request->setStage('profile_name', $user['firstName'] .' '.$user['lastName']);

    if (isset($user['siteStandardProfileRequest']['url'])) {
        $request->setStage('profile_linkedin', $user['siteStandardProfileRequest']['url']);
    }

    // get claim ref
    if ($request->getSession('ref')) {
        $request->setStage('ref', $request->getSession('ref'));
        $request->removeSession('ref');
    }

    if (isset($user['pictureUrl']) && $user['pictureUrl']) {
        $request->setStage('profile_image', $user['pictureUrl']);
    }

    $request->setStage('auth_linkedin_token', $token);
    $request->setStage('auth_slug', $user['emailAddress']);
    $request->setStage('auth_password', $user['id']);
    $request->setStage('auth_active', 1);
    $request->setStage('confirm', $user['id']);
    $request->setStage('auth_permissions', [
        'public_product',
        'public_profile',
        'personal_profile',
        'personal_product',
        'personal_comment',
        'personal_review'
    ]);

    cradle()->trigger('auth-sso-login', $request, $response);

    if ($response->isError() && $request->hasSession('reference')) {
        $redirect = '/claim?ref=' . $request->getSession('reference', 'hash');
        $request->removeSession('reference');
        //add a flash

        $message = cradle('global')->translate('Your Linkedin account has an existing profile.');
        cradle('global')->flash($message, 'danger');
        return cradle('global')->redirect($redirect);
    }

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/login', $request, $response);
    }

    //it was good

    //store to session
    $_SESSION['me'] = $response->getResults();

    // if company
    if (!empty(trim($_SESSION['me']['profile_company']))) {
        // if user have less 20 credits, flag session true
        if ($_SESSION['me']['profile_credits'] < 20) {
            $_SESSION['me']['credit_flag'] = 1;
        }
    } else {
        // add profile is seeker add modal flag
        $_SESSION['me']['modal_flag'] = 'poster';
    }

    //redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    return cradle('global')->redirect($redirect);
});

/**
 * Process the Logout
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/logout', function ($request, $response) {
    //TODO: Sessions for clusters
    unset($_SESSION['me']);
    unset($_SESSION['rest']);

    //add a flash
    cradle('global')->flash('Log Out Successful', 'success');
    //redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Render the Forgot Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/forgot', function ($request, $response) {
    //Prepare body
    $data = ['item' => $request->getStage()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //Render body
    $class = 'page-auth-forgot';
    $title = cradle('global')->translate('Reset your Password - Jobayan');
    $body = cradle('/app/www')->template('forgot', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->addMeta('description', 'Reset your account here and get back to
            connecting with potential job seekers or company job vacancies.')
        ->addMeta('keywords', strtolower(implode(',', [
            'jobs in ' . $request->getSession('country'),
            'kalibrr',
            'jobstreet',
            'work abroad',
            'best jobs'
        ])))
        ->setContent($body);

    //Render blank page
}, 'render-www-blank');

/**
 * Render the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/recover/:auth_id/:hash', function ($request, $response) {

    if (!$response->isError()) {
         //get the detail
        cradle()->trigger('auth-detail', $request, $response);

        //form hash
        $authId = $response->getResults('auth_id');
        $authUpdated = $response->getResults('auth_updated');
        $hash = md5($authId.$authUpdated);

        //check the verification hash
        if ($hash !== $request->getStage('hash')) {
            cradle('global')->flash('Invalid verification. Try again.', 'danger');
            return cradle('global')->redirect('/verify');
        }

        //Prepare body
        $data = ['item' => $request->getPost()];
    } else {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

     //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //Render body
    $class = 'page-auth-recover';
    $title = cradle('global')->translate('Recover Password');
    $body = cradle('/app/www')->template('recover', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-blank');

/**
 * Render the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/verify', function ($request, $response) {
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //Render body
    $class = 'page-auth-verify';
    $title = cradle('global')->translate('Verify Account');
    $body = cradle('/app/www')->template('verify', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-blank');

/**
 * Process the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/login', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/login', $request, $response);
    }

    //call the job
    cradle()->trigger('auth-login', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/login', $request, $response);
    }

    if ($request->hasStage('redirect_uri') &&
        strpos($request->getStage('redirect_uri'), 'app_access') !== false) {
        $url = urldecode($request->getStage('redirect_uri'));
        $parts = parse_url($url);
        $get = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $get);
        }

        if (isset($get['app_access']) &&
            !in_array($get['app_access'], $response->getResults('auth_permissions'))) {
            $response->setFlash('Unauthorize Access', 'danger');
            return cradle()->triggerRoute('get', '/login', $request, $response);
        }
    }

    // get the results
    $data = $response->getResults();

    $request->setStage('profile_id', $data['profile_id']);

    // trigget the job
    cradle()->trigger('profile-information', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/login', $request, $response);
    }

    $data['profile_information'] = $response->getResults();

    //it was good

    //store to session
    //TODO: Sessions for clusters
    $_SESSION['me'] = $data;

    // if company
    if (!empty(trim($_SESSION['me']['profile_company']))) {
        // if user have less 20 credits, flag session true
        if ($_SESSION['me']['profile_credits'] < 20) {
            $_SESSION['me']['credit_flag'] = 1;
        }
    } else {
        // add profile is seeker add modal flag
        $_SESSION['me']['modal_flag'] = 'poster';
    }

    //redirect
    if ($request->hasGet('redirect')) {
        return cradle('global')->redirect($request->getGet('redirect'));
    }

    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->flash('Log In Successful', 'success');

    return cradle('global')->redirect($redirect);
});

/**
 * Process the Forgot Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/forgot', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/forgot', $request, $response);
    }

    //trigger the job
    cradle()->trigger('auth-forgot', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/forgot', $request, $response);
    }

    //its good
    $response->setFlash('An email with recovery instructions will be sent in a few minutes.', 'success');
    cradle()->triggerRoute('get', '/forgot', $request, $response);
});

/**
 * Process the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/recover/:auth_id/:hash', function ($request, $response) {
    //get the detail
    cradle()->trigger('auth-detail', $request, $response);

    //form hash
    $authId = $response->getResults('auth_id');
    $authUpdated = $response->getResults('auth_updated');
    $hash = md5($authId.$authUpdated);

    //check the recovery hash
    if ($hash !== $request->getStage('hash')) {
        cradle('global')->flash('This recovery page is expired. Please try again.', 'danger');
        return cradle('global')->redirect('/forgot');
    }

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        $redirect = '/recover/' . $authId . '/' . $hash;
        return cradle()->triggerRoute('get', $redirect, $request, $response);
    }

    //trigger the job
    cradle()->trigger('auth-recover', $request, $response);

    if ($response->isError()) {
        $redirect = '/recover/' . $authId . '/' . $hash;
        return cradle()->triggerRoute('get', $redirect, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Recovery Successful', 'success');

    //redirect
    cradle('global')->redirect('/login');
});

/**
 * Process the Signup Page
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
$cradle->post('/signup', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/signup', $request, $response);
    }

    //captcha check
    cradle()->trigger('captcha-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/signup', $request, $response);
    }

    //set defaults
    if (!$request->hasStage('auth_permissions')) {
        $request->setStage('auth_permissions', [
            'public_profile',
            'personal_profile'
        ]);
    }

    // Checks for profile_type
    if ($request->hasStage('profile_type')
        && $request->getStage('profile_type') == 'interested') {
        $tempPass = uniqid();
        $request->setStage('auth_password', $tempPass);
        $request->setStage('confirm', $tempPass);
    }

    // Checks for signup_type
    if ($request->hasStage('signup_type') && $request->getStage('signup_type') == 'seeker') {
        $request->setStage('profile_company', '');
    }

    //trigger the job
    cradle()->trigger('auth-create', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/signup', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Sign Up Successful. Please check your email for verification process.', 'success');

    // add create post experience
    $experience = cradle('global')->config('experience', 'signup');
    $request->setStage('profile_experience', $experience);
    $request->setStage('profile_id', $response->getResults('profile_id'));
    $this->trigger('profile-add-experience', $request, $response);
    $message = 'You earned '.$experience. ' experience points';

    // if company, add experience flash
    if ($request->getStage('profile_company') != '') {
        cradle('global')->setExperienceFlash($message);
    }

    $request->setStage('profile_achievement', 'signup');
    $this->trigger('profile-add-achievement', $request, $response);

    // if company, add achievement badge
    if ($request->getStage('profile_company') != '') {
        $achievement = cradle('global')->config('achievements', 'signup');
        cradle('global')->setLoggedInBadge($achievement['image'], $achievement['modal']);
    }

    //redirect
    $query = http_build_query($request->get('get'));
    cradle('global')->redirect('/login?' . $query);
});

/**
 * Process the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/verify', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/verify', $request, $response);
    }

    //trigger the job
    cradle()->trigger('auth-verify', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/verify', $request, $response);
    }

    //its good
    $response->setFlash('An email with verification instructions will be sent in a few minutes.', 'success');
    cradle()->triggerRoute('get', '/verify', $request, $response);
});

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
$cradle->get('/claim', function ($request, $response) {
    // redirect to /
    if (!$request->getStage('ref')) {
        cradle('global')->redirect('/');
    }

    //Prepare body
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        if ($response->getValidation('auth_slug')) {
            $message = $response->getValidation('auth_slug');
            $response->addValidation('profile_email', $message);
        }

        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // get reference id
    $ref = $request->getStage('ref');
    // get prefix
    $prefix = substr($ref, 0, 5);
    // rearrange reference
    $ref = substr($ref, 5) . $prefix;
    // decoding via base64
    $ref = base64_decode($ref);

    // decoding via json
    $ref = json_decode($ref, true);

    // is array
    if (!is_array($ref)) {
        cradle('global')->redirect('/');
    }

    // if profile id
    if (!$ref['profile_id']) {
        cradle('global')->redirect('/');
    }

    // set id on stage
    $request->setStage('profile_id', $ref['profile_id']);

    //trigger the job
    cradle()->trigger('profile-detail', $request, $response);

    if (!$response->getResults('profile_id')) {
        cradle('global')->redirect('/');
    }

    $data['profile'] = $response->getResults();

    if (empty($data['item'])) {
        $data['item'] = [
            'profile_email' => $data['profile']['profile_email'],
            'profile_company' => $data['profile']['profile_company']
        ];
    }

    cradle()->trigger('auth-profile-search', $request, $response);

    if (!empty($response->getResults())) {
        cradle('global')->redirect('/');
    }

    // set id on stage
    $request->setStage('profile', $ref['profile_id']);
    $request->removeStage('profile_id');

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');


    if ($response->isError()) {
        if ($response->getValidation('auth_slug')) {
            $message = $response->getValidation('auth_slug');
            $response->addValidation('profile_email', $message);
        }

        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    if ($request->hasStage('profile') && $request->hasStage('ref')) {
        $request->setSession('reference', 'hash', $request->getStage('ref'));
        $request->setSession('reference', 'profile', $request->getStage('profile'));
        $data['profile_id'] = $request->getStage('profile');
    }

    //Render body
    $class = 'page-auth-claim';
    $title = cradle('global')->translate('Claim Your Profile');

    $body = cradle('/app/www')->template('claim', $data, [
        'partial_howitworks'
    ]);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Claim Page
 *
 * SIGNUP FLOW:
 * - GET /claim
 * - POST /claim
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/claim', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/claim', $request, $response);
    }

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/claim', $request, $response);
    }

    //set defaults
    if (!$request->hasStage('auth_permissions')) {
        $request->setStage('auth_permissions', [
            'public_profile',
            'personal_profile'
        ]);
    }

    //trigger the job
    cradle()->trigger('auth-create', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/claim', $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Sign Up Successful.', 'success');

    //redirect
    $query = http_build_query($request->get('get'));
    cradle('global')->redirect('/login?' . $query);
});
