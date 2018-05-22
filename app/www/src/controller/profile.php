<?php //-->

/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;

use Cradle\Curl\CurlHandler as Curl;
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Validator as UtilityValidator;
use Cradle\Module\Widget\Service as WidgetService;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * Render the Account Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/account**', function ($request, $response) {
    //Need to be logged in
    cradle('global')->requireLogin();

    //Prepare body
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //add CDN
    $config             = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //If no post
    if (!$request->hasPost('profile_name')) {
        //set default data
        $data['item'] = $request->getSession('me');
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    // set tempate
    $template = 'profile/account/search';

    // if get has information
    if ($request->getRoute('variables', 1) == 'information') {
        $template = 'profile/account/form';
    }

    // if get has password
    if ($request->getRoute('variables', 1) == 'password') {
        $template = 'profile/account/password';
    }

    //Render body
    $class = 'page-auth-account branding';
    $title = cradle('global')->translate('Jobayan - Account Settings');
    $body  = cradle('/app/www')->template(
        $template,
        $data,
        [
            'profile_menu',
            'profile_alert',
            '_modal-profile-completeness'
        ]
    );

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Checkout Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/credit/checkout', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $data['profile_address'] = false;
    if ($request->getSession('me', 'profile_address_street') ||
        $request->getSession('me', 'profile_address_city') ||
        $request->getSession('me', 'profile_address_state') ||
        $request->getSession('me', 'profile_address_country') ||
        $request->getSession('me', 'profile_address_postal')) {
        $data['profile_address'] = true;
    }

    //Render body
    $class = 'page-profile-credit-checkout branding';
    $title = cradle('global')->translate('Jobayan - Buy Credits');
    $body  = cradle('/app/www')->template(
        'profile/credit/checkout',
        $data,
        [
            'profile_menu'
        ]
    );

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Credit Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/credit/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $data = [];
    $date = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $request->setStage('order', 'service_id', 'DESC');

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'date',
            'service_active',
            'service_name'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->setStage('filter', $key);
            }
        }
    }

    // Checks for date filter
    if ($request->hasStage('date')) {
        $date = $request->getStage('filter', 'date');
        $request->removeStage('filter', 'date');
    }

    // if ($request->getStage('year')) {
    //     $date = $request->getStage('year');
    //     $date = date('Y', mktime(null, null, null, null, null, date($date)+1));
    // }
    //
    // if ($request->getStage('month')) {
    //     if (isset($date)) {
    //         $date .= '-'.$request->getStage('month');
    //         $date =  date('Y-m', strtotime($date)) . '-%';
    //     } else {
    //         $date = '%-'.date('m', mktime(null, null, null, $request->getStage('month'))).'-%';
    //     }
    // }
    //
    // if (!$request->getStage('month') && $request->getStage('year')) {
    //     $date = $date . '-%'. '-%';
    // }

    // if (isset($date)) {
    //     $request->setStage('groupDate', ['service_created' => $date]);
    // }

    $request->setStage('filter', 'profile_id', $request->getSession('me', 'profile_id'));

    if ($request->getStage('date', 'type') != 'range') {
        $request->removeStage('date', 'start');
        $request->removeStage('date', 'end');
    }

    //trigger job
    if ($request->hasStage('export')) {
        $request->setStage('start', 0);
        $request->setStage('range', 0);
    }

    //get total credits spent
    $creditSpent = 0;
    cradle()->trigger('credit-spent', $request, $response);
    $item = $response->getResults('rows');

    foreach ($item as $i => $value) {
        $creditSpent += $item[$i]['service_credits'];
    }

    $request->setStage('credit_spent', $creditSpent);

    if ($request->getStage('date', 'type') != 'range') {
        $request->removeStage('date', 'start');
        $request->removeStage('date', 'end');
    }

    //trigger job

    if ($request->hasStage('export')) {
        $request->setStage('start', 0);
        $request->setStage('range', 0);
    }

    //get total credits spent
    cradle()->trigger('credit-spent', $request, $response);
    $item        = $response->getResults('rows');
    $creditSpent = 0;

    foreach ($item as $i => $value) {
        $creditSpent += $item[$i]['service_credits'];
    }

    $request->setStage('credit_spent', $creditSpent);

    // Checks for date range
    if (!empty($date)) {
        $request->setStage('filter', 'date', $date);
    }

    //search in database
    cradle()->trigger('service-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());
    //If no post
    if (!$request->hasPost('profile_name')) {
        //set default data
        $data['item'] = $request->getSession('me');
    }

    // Export CSV
    if ($request->hasStage('export')) {
        //Set CSV header
        $header = [
            'service_created' => 'Date',
            'service_credits' => 'Credits Used',
            'service_name'    => 'Service',
            'service_id'      => 'Reference'
        ];

        //Set Filename
        $request->setStage('filename', 'Services-' . date("Y-m-d") . ".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-service-search branding';
    $data['title'] = cradle('global')->translate('Jobayan - Billing Information');
    $body          = cradle('/app/www')->template(
        'profile/credit/search',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Profile Match Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/match/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $profileId = $request->getSession('me', 'profile_id');

    $postRequest = new Request();

    $postRequest->setStage('range', 25);

    $postRequest->removeStage('order');
    $postRequest->setStage('order', 'post_id', 'DESC');

    $postRequest->removeStage('filter');

    // filter by specific position | location
    if ($request->hasStage('post_position')
        && $request->hasStage('post_location')) {
        $postRequest->setStage('filter', 'post_position', $request->getStage('post_position'));
        $postRequest->setStage('filter', 'post_location', $request->getStage('post_location'));

        $type = 'poster';
        if ($request->getSession('me', 'profile_company')) {
            $type = 'seeker';
        }

        $postRequest->setStage('filter', 'post_type', $type);
        $postRequest->setGet('noindex', true);
        cradle()->trigger('post-search', $postRequest, $response);

        $data = $response->getResults();
    } else {
        $postRequest->setStage('filter', 'profile_id', $profileId);

        //trigger job
        $postRequest->setGet('noindex', true);
        cradle()->trigger('post-search', $postRequest, $response);
        $posts = $response->getResults();

        $matching = [];
        foreach ($posts['rows'] as $post) {
            $matching[$post['post_position'] . ' - ' . $post['post_location']] = [
                'post_position' => $post['post_position'],
                'post_location' => $post['post_location']
            ];
        }

        if (!$request->hasStage('range')) {
            $request->setStage('range', 10);
        }

        $data = array_merge($request->getStage(), [
            'rows'  => [],
            'total' => 0
        ]);
        //only show results if there are matches
        if (!empty($matching)) {
            $request->setStage('not_filter', 'profile_id', $profileId);
            $request->setStage('matching', array_values($matching));

            $type = 'poster';
            if ($request->getSession('me', 'profile_company')) {
                $type = 'seeker';
            }

            $request->setStage('filter', 'post_type', $type);

            //filter possible sorting options
            //we do this to prevent SQL injections
            if (is_array($request->getStage('order'))) {
                $sortable = [
                    'post_experience',
                    'post_expires',
                    'post_id'
                ];

                foreach ($request->getStage('order') as $key => $direction) {
                    if (!in_array($key, $sortable)) {
                        $request->removeStage('order', $key);
                    } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                        $request->removeStage('order', $key);
                    }
                }
            }

            //filter possible filter options
            //we do this to prevent SQL injections
            if (is_array($request->getStage('filter'))) {
                $filterable = [
                    'post_active',
                    'post_location',
                    'post_position',
                    'post_experience',
                    'post_type',
                    'post_expires'
                ];

                foreach ($request->getStage('filter') as $key => $value) {
                    if (!in_array($key, $filterable)) {
                        $request->removeStage('filter', $key);
                    }
                }
            }

            if (!$request->hasStage('order')) {
                //sort desc
                $request->setStage('order', 'post_id', 'DESC');
            }

            $request->setStage('order', 'post_id', 'DESC');
            if ($request->hasStage('post_position')
                && $request->hasStage('post_location')) {
                $request->setStage('filter', 'post_position', $request->getStage('post_position'));
                $request->setStage('filter', 'post_location', $request->getStage('post_location'));
            }

            $request->setGet('noindex', true);
            cradle()->trigger('post-search', $request, $response);
            $data = array_merge($request->getStage(), $response->getResults());
        }
    }

    // get the information
    if ($data['rows'] && !empty($data['rows'])) {
        foreach ($data['rows'] as $r => $row) {
            $request->setStage('profile_id', $row['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_information'] = $response->getResults();
            }

            // trigger the job
            cradle()->trigger('profile-resume', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_resume'] = $response->getResults();
            }
        }
    }

    //----------------------------//
    // 3. Render Template
    $class                = 'page-profile-match-search branding';
    $data['title']        = cradle('global')->translate('Jobayan - Matches');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body                 = cradle('/app/www')->template(
        'profile/match/search',
        $data,
        [
            'profile_menu',
            'profile_alert',
            'profile_message',
            '_modal-profile-completeness'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Profile Match Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/likes/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $profileId = $request->getSession('me', 'profile_id');
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $data = array_merge($request->getStage(), [
        'rows'  => [],
        'total' => 0
    ]);

    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_name',
            'profile_company'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    // pull likers
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('likers', true);

    $request->setStage('order', ['post_created' => 'DESC']);
    cradle()->trigger('post-likes', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    // get the information
    if ($data['rows'] && !empty($data['rows'])) {
        foreach ($data['rows'] as $r => $row) {
            $request->setStage('profile_id', $row['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_information'] = $response->getResults();
            }

            // trigger the job
            cradle()->trigger('profile-resume', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_resume'] = $response->getResults();
            }
        }
    }

    //----------------------------//
    // 3. Render Template
    $class                = 'page-profile-like-search branding';
    $data['title']        = cradle('global')->translate('Jobayan - Interested');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $data['active']       = $request->getStage('type');
    $body                 = cradle('/app/www')->template(
        'profile/match/like',
        $data,
        [
            'profile_menu',
            'profile_alert',
            'profile_message',
            '_modal-profile-completeness'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Post Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/post/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'post_active', '1');
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_experience',
            'post_expires',
            'post_id',
            'post_position'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_active',
            'post_location',
            'post_experience',
            'post_type',
            'post_id'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    if (!$request->hasStage('order')) {
        //sort desc
        $request->setStage('order', 'post_id', 'DESC');
    }

    $request->setStage('order', 'post_id', 'DESC');
    $request->setStage('withMatches', true);
    $request->setStage('withForm', true);

    //trigger job
    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //for Post that will expire soon
    foreach ($data['rows'] as &$row) {
        $expire_days = strtotime($row['post_expires']) - time();
        $dayDiff     = round($expire_days / (60 * 60 * 24));
        if (($expire_days) < (7 * 24 * 60 * 60)) {
            $row['expire_soon'] = 1;
            $row['days_left']   = $dayDiff;
        } else {
            $row['expire_soon'] = 0;
        }
    }

    //if not unlimited post
    if ($request->getSession('me')) {
        //update profile session
        cradle()->trigger('profile-session', $request, $response);

        $request->setStage('post_action', 'restore');
        $request->setGet('noindex', true);
        $request->setStage('filter', 'profile_id', $request->getSession('me', 'profile_id'));

        cradle()->trigger('post-get-credit', $request, $response);

        //if not unlimited post
        if (!$request->hasSession('me', 'profile_package')
            || !in_array('unlimited-post', $request->getSession('me', 'profile_package'))) {
            $data['post_count'] = $response->getResults('total');
            $data['cost']       = cradle('global')->config('credits', 'extra-post');
            $data['cost']       = pow(2, $data['post_count'] - 4) * ($data['cost'] / 2);
        }
    }

    // Checks for export action
    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }

    //Export CSV
    if ($request->hasStage('export') && !empty($data['rows'])) {
        //Set CSV header
        $header = [
            'post_position' => 'Job Title',
            'post_location' => 'Location',
            'post_phone'    => 'Contact Details',
            'post_created'  => 'Date Posted',
            'post_expires'  => 'Expiration Date',
            'services'      => 'Services',
        ];

        // prepare services
        foreach ($data['rows'] as $key => $value) {
            $data['rows'][$key]['services'] = [];
            if (!empty($value['post_flag']) && $value['post_flag'] == '1') {
                $data['rows'][$key]['services'][] = "Promoted Post";
            }
            if (!empty($value['profile_package'])) {
                // $profilePackage = json_encode($value['profile_package']);
                $profilePackage = $value['profile_package'];
                if (in_array('interview-scheduler', $profilePackage)) {
                    $data['rows'][$key]['services'][] = "Interview Scheduler";
                }
                if (in_array('ats', $profilePackage)) {
                    $data['rows'][$key]['services'][] = "Applicant Tracking";
                }
                if (in_array('sms-interest', $profilePackage)) {
                    $data['rows'][$key]['services'][] = "SMS Interested";
                }
                if (in_array('sms-match', $profilePackage)) {
                    $data['rows'][$key]['services'][] = "SMS Match";
                }
            }
            if (!empty($value['post_notify'])) {
                $postNotify = $value['post_notify'];
                if (in_array('sms-interest', $postNotify)) {
                    $data['rows'][$key]['services'][] = "SMS Interested";
                }
                if (in_array('sms-match', $postNotify)) {
                    $data['rows'][$key]['services'][] = "SMS Match";
                }
            }
            // remove duplicated sms match or sms interested and prepare services for export
            $data['rows'][$key]['services'] = array_unique($data['rows'][$key]['services']);
            $data['rows'][$key]['services'] = json_encode($data['rows'][$key]['services']);
            $data['rows'][$key]['services'] = substr($data['rows'][$key]['services'], 1, -1);
        }

        //Set Filename
        $request->setStage('filename', 'Profiles-' . date("Y-m-d") . ".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class                = 'page-profile-post-search branding';
    $data['title']        = cradle('global')->translate('Jobayan - Posts');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body                 = cradle('/app/www')->template(
        'profile/post/search',
        $data,
        [
            'profile_menu',
            'profile_alert',
            'profile_confirm',
            'profile_renew',
            'profile_boost',
            'profile_expiredRenew',
            'post/modal_credit',
            '_modal-profile-completeness'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    cradle()->triggerRoute('get', '/post/renew', $request, $response);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Post Restore
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/post/restore/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data    = ['item' => $request->getPost()];
    $post_id = $request->getStage('post_id');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    if ($request->getSession('me')) {
        //update profile session
        cradle()->trigger('profile-session', $request, $response);

        $request->setStage('post_action', 'restore');
        $request->setGet('noindex', true);
        $request->setStage('filter', 'profile_id', $request->getSession('me', 'profile_id'));

        cradle()->trigger('post-get-credit', $request, $response);

        //if not unlimited post
        if (!$request->hasSession('me', 'profile_package')
            || !in_array('unlimited-post', $request->getSession('me', 'profile_package'))) {
            $data['post_count'] = $response->getResults('total');

            // at this point all validations are done
            // get all the list of currencies and their symbols
            cradle()->trigger('currency-search', $request, $response);
            $data['currency'] = $response->getResults();
        }
    }

    //----------------------------//
    // 3. Process Request
    //restore posts if unlimited post and if posts < 5 (for non-unlimited post)
    if (!isset($data['post_count']) || (isset($data['post_count']) && $data['post_count'] < 5)) {
        cradle()->trigger('post-restore', $request, $response);

        //add a flash
        $message = cradle('global')->translate('Post was Restored');
        cradle('global')->flash($message, 'success');
    } elseif (isset($data['post_count']) && $data['post_count'] >= 5) {
        $cost = cradle('global')->config('credits', 'extra-post');
        $cost = pow(2, $data['post_count'] - 4) * ($cost / 2);

        if ($cost > $request->getSession('me', 'profile_credits')) {
            $response
                ->setError(true, 'Credits Required')
                ->set('json', 'validation', [
                    'error'            => true,
                    'credits_required' => 'Not enough credits to proceed'
                ]);
        }

        $request
            ->setStage('profile_id', $request->getSession('me', 'profile_id'))
            ->setStage('service_name', 'Extra Post')
            ->setStage('service_meta', [
                'post_id' => $post_id
            ])
            ->setStage('service_credits', $cost);

        cradle()->trigger('post-restore', $request, $response);
        cradle()->trigger('service-create', $request, $response);

        if (!$response->isError()) {
            $request->setSession(
                'me',
                'profile_credits',
                $request->getSession('me', 'profile_credits') - $cost
            );
        }
    }

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    }

    //redirect
    $redirect = '/profile/post/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Render the Seeker Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/seeker/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_experience',
            'post_expires',
            'post_id'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_active',
            'post_location',
            'post_experience',
            'post_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    $profileId = $request->getSession('me', 'profile_id');
    $request->setStage('not_filter', 'profile_id', $profileId);

    //post_type
    $request->setStage('filter', 'post_type', 'seeker');
    //post_expires
    $request->setStage('post_expires', '-1 year');

    //sort desc
    $request->setStage('order', 'post_created', 'DESC');

    //trigger job
    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    // get profile information
    if (isset($data['rows']) && !empty($data['rows'])) {
        foreach ($data['rows'] as $r => $row) {
            $request->setStage('profile_id', $row['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_information'] = $response->getResults();
            }

            // trigger the job
            cradle()->trigger('profile-resume', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_resume'] = $response->getResults();
            }
        }

        // check if has resume
        if ($request->hasGet('has_resume') &&
            $request->getGet('has_resume') == 1) {
            foreach ($data['rows'] as $r => $row) {
                // do not include if progress is less than 40%
                if (!isset($row['profile_resume'])) {
                    if (isset($row['profile_information'])
                        && $row['profile_information']['information_progress'] < 40) {
                        unset($data['rows'][$r]);
                    }
                }
            }
        }
    }

    //----------------------------//
    // 3. Render Template
    $class                = 'page-profile-seeker-search branding';
    $data['title']        = cradle('global')->translate('Jobayan - Job Seeker Search');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body                 = cradle('/app/www')->template(
        'profile/seeker/search',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Transaction Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/transaction/detail/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Filter by transaction id
    $request->setStage('filter', 'transaction_id', $request->getStage('transaction_id'));

    //trigger job
    cradle()->trigger('transaction-detail', $request, $response);
    $data['transaction'] = $response->getResults();

    //Prepare form
    $data['item'] = $request->getPost();

    //add CDN
    $config             = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //If no post
    if (!$request->hasPost('profile_name')) {
        //set default data
        $data['item'] = $request->getSession('me');
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-transaction-detail branding';
    $data['title'] = cradle('global')->translate('Jobayan - Billing Detail');
    $body          = cradle('/app/www')->template(
        'profile/transaction/detail',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Transaction Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/transaction/search**', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $request->setStage('order', 'transaction_id', 'DESC');

    if ($request->getStage('year')) {
        $date = $request->getStage('year');
        $date = date('Y', mktime(null, null, null, null, null, date($date) + 1));
    }

    if ($request->getStage('month')) {
        if (isset($date)) {
            $date .= '-' . $request->getStage('month');
            $date = date('Y-m', strtotime($date)) . '-%';
        } else {
            $date = '%-' . date('m', mktime(null, null, null, $request->getStage('month'))) . '-%';
        }
    }

    if (!$request->getStage('month') && $request->getStage('year')) {
        $date = $date . '-%' . '-%';
    }

    if (isset($date)) {
        $request->setStage('groupDate', ['transaction_created' => $date]);
    }

    //trigger job
    cradle()->trigger('transaction-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //Prepare form
    $data['item'] = $request->getPost();

    //add CDN
    $config             = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //If no post
    if (!$request->hasPost('profile_name')) {
        //set default data
        $data['item'] = $request->getSession('me');
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }
    // set tempate
    $template = 'profile/transaction/search';

    // if get has update
    if ($request->getRoute('variables', 1) == 'update') {
        $template = 'profile/transaction/form';
    }

    // export
    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }

    if (empty($data['rows']) && $request->hasStage('date')) {
        cradle('global')->flash('No available Billing history!', 'danger');

        return cradle('global')->redirect('/profile/transaction/search');
    }

    //Export CSV
    if ($request->hasStage('export') && !empty($data['rows'])) {
        //Set CSV header
        $header = [
            'transaction_created'           => 'Date',
            'transaction_statement'         => 'Statement',
            'transaction_credits'           => 'Credits',
            'transaction_total'             => 'Amount',
            'interview_schedule_end_time'   => 'transaction_status',
            'transaction_payment_reference' => 'Reference',
        ];

        //Set Filename
        $request->setStage('filename', 'Billing_history' . date("Y-m-d") . ".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-transaction-search branding';
    $data['title'] = cradle('global')->translate('Jobayan - Billing Information');
    $body          = cradle('/app/www')->template(
        $template,
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Resume Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/resume/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    // set stage profile id
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/post/search');
    }

    // if there's information
    if ($response->getResults()) {
        return cradle('global')->redirect('/profile/information');
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'resume_position',
            'resume_id'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }


    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'resume_position',
            'resume_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //sort desc
    $request->setStage('order', 'resume_created', 'DESC');

    //trigger job
    cradle()->trigger('resume-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/post/search');
    }
    // set the area to data
    $data['area'] = $response->getResults();

    //----------------------------//
    // 3. Render Template
    $class                = 'page-profile-resume-search branding';
    $data['title']        = cradle('global')->translate('Jobayan - My Resume');
    $data['redirect_uri'] = urlencode($request->getServer('REQUEST_URI'));
    $body                 = cradle('/app/www')->template(
        'profile/resume/search',
        $data,
        [
            'profile_alert',
            'profile_confirm',
            'profile_menu',
            'profile/information_information'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Post Remove
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/resume/remove/:resume_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $request->setStage('permission', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('resume-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Resume was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/profile/resume/search');
});

/**
 * Render the Career Widget Settings Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/widget/settings', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    // is there an error?
    if ($response->isError()) {
        // set error
        $response->setFlash($response->getMessage(), 'danger');
        // get validation
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 2. Prepare Data
    // get the user profile
    $profile = WidgetService::get('sql')
        ->getResource()
        ->search('profile')
        ->addFilter(
            'profile.profile_id = %s',
            $request->getSession('me', 'profile_id')
        )
        ->getRow();

    // if we don't have data yet
    if (!$request->getStage()) {
        // pull the widget data
        $widget = WidgetService::get('sql')
            ->getResource()
            ->search('widget_profile')
            ->innerJoinUsing('widget', 'widget_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->addFilter(
                'widget_profile.profile_id = %s',
                $request->getSession('me', 'profile_id')
            )
            ->addFilter('widget_type = %s', 'career_widget')
            ->getRow();
    } else {
        // else get the submitted data
        $widget = $request->getStage();
    }

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set widget data
    if (!empty($widget)) {
        // set widget data
        $data['item'] = array_merge($widget, $profile);

        // set domain
        $data['item']['widget_root'] = $domain;

        // career widget code
        $widgetCode = cradle('/app/www')->template(
            'profile/widget/_code',
            array_merge(
                [
                    'widget_type' => 'career_widget'
                ],
                $data['item']
            )
        );

        // career widget code
        $data['item']['widget_code'] = htmlspecialchars($widgetCode);

        // decode profile packages
        $data['item']['profile_package'] = json_decode(
            $data['item']['profile_package'],
            true
        );

        // has branding?
        if (!in_array(
            'career_widget',
            $data['item']['profile_package']
        )
        ) {
            // set widget branding flag
            $data['item']['widget_branding'] = 1;
        }
    } else {
        // no data
        $data['item'] = [
            'widget_branding' => 1
        ];
    }

    // widget preview flag
    $data['item']['widget_preview'] = 1;
    // add widget type
    $data['item']['widget_type'] = 'career_widget';

    // sample data
    $data['item']['rows'] = [
        [
            'post_position'    => 'Senior Software Developer',
            'profile_company'  => 'Jobayan',
            'post_arrangement' => 'Full Time',
            'post_location'    => 'Makati City',
            'post_experience'  => 3,
            'post_created'     => date('Y-m-d H:i:s')
        ],
        [
            'post_position'    => 'HR Manager',
            'profile_company'  => 'Jobayan',
            'post_arrangement' => 'Part Time',
            'post_location'    => 'Makati City',
            'post_experience'  => 1,
            'post_created'     => date('Y-m-d H:i:s')
        ],
        [
            'post_position'    => 'Accounting Staff',
            'profile_company'  => 'Jobayan',
            'post_arrangement' => 'Part Time',
            'post_location'    => 'Makati City',
            'post_experience'  => 1,
            'post_created'     => date('Y-m-d H:i:s')
        ],
        [
            'post_position'    => 'Web Developer',
            'profile_company'  => 'Jobayan',
            'post_arrangement' => 'Full Time',
            'post_location'    => 'Makati City',
            'post_experience'  => 1,
            'post_created'     => date('Y-m-d H:i:s')
        ],
        [
            'post_position'    => 'Operations Manager',
            'profile_company'  => 'Jobayan',
            'post_arrangement' => 'Full Time',
            'post_location'    => 'Makati City',
            'post_experience'  => 3,
            'post_created'     => date('Y-m-d H:i:s')
        ]
    ];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-widget-form branding';
    $data['title'] = cradle('global')->translate('Jobayan - Career Widget Settings');

    $body = cradle('/app/www')->template(
        'profile/widget/form',
        $data,
        [
            'profile/widget/partials_post',
            'profile/widget/partials_widget',
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');

/**
 * Render the Career Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/widget/page/settings', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    // is there an error?
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 2. Prepare Data
    // get the user profile
    $profile = WidgetService::get('sql')
        ->getResource()
        ->search('profile')
        ->addFilter(
            'profile.profile_id = %s',
            $request->getSession('me', 'profile_id')
        )
        ->getRow();

    // if we don't have data yet
    if (!$request->getStage()) {
        // pull the widget data
        $widget = WidgetService::get('sql')
            ->getResource()
            ->search('widget_profile')
            ->innerJoinUsing('widget', 'widget_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->addFilter(
                'widget_profile.profile_id = %s',
                $request->getSession('me', 'profile_id')
            )
            ->addFilter('widget_type = %s', 'career_page')
            ->getRow();
    } else {
        // else get the submitted data
        $widget = $request->getStage();
    }

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set widget data
    if (!empty($widget)) {
        // decode meta
        if (!is_array($widget['widget_meta'])) {
            try {
                $widget['widget_meta'] = json_decode($widget['widget_meta'], true);
            } catch (\Exception $e) {
            }
        }

        // set widget tags
        if (isset($widget['widget_meta']['tags'])) {
            $widget['post_tags'] = $widget['widget_meta']['tags'];
        }

        // set widget data
        $data['item'] = array_merge($widget, $profile);

        // set domain
        $data['item']['widget_root'] = $domain;

        // career widget code
        $widgetCode = cradle('/app/www')->template(
            'profile/widget/_code',
            array_merge(
                [
                    'widget_type' => 'career_page'
                ],
                $data['item']
            )
        );

        // career widget code
        $data['item']['widget_code'] = htmlspecialchars($widgetCode);

        // decode profile packages
        $data['item']['profile_package'] = json_decode(
            $data['item']['profile_package'],
            true
        );

        // has branding?
        if (!in_array(
            'career_page',
            $data['item']['profile_package']
        )
        ) {
            // set widget branding flag
            $data['item']['widget_branding'] = 1;
        }
    } else {
        // no data
        $data['item'] = [];
    }

    // widget preview flag
    $data['item']['widget_preview'] = 1;
    // add widget type
    $data['item']['widget_type'] = 'career_page';

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-career-page branding';
    $data['title'] = cradle('global')->translate('Jobayan - Career Page');

    $body = cradle('/app/www')->template(
        'profile/widget/page/form',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');

/**
 * Render the Widget Preview for both
 * Career Widget and Career Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/widget/preview', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    // is there an error?
    if ($response->isError()) {
        // set error
        $response->setFlash($response->getMessage(), 'danger');
        // get validation
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 2. Prepare Data
    // manually check widget
    $widget = WidgetService::get('sql')
        ->getResource()
        ->search('widget_profile')
        ->innerJoinUsing('widget', 'widget_id')
        ->innerJoinUsing('profile', 'profile_id')
        ->addFilter(
            'widget_profile.profile_id = %s',
            $request->getSession('me', 'profile_id')
        )
        ->addFilter('widget_type = %s ', 'career_widget')
        ->getRow();

    // check widget page
    $page = WidgetService::get('sql')
        ->getResource()
        ->search('widget_profile')
        ->innerJoinUsing('widget', 'widget_id')
        ->innerJoinUsing('profile', 'profile_id')
        ->addFilter(
            'widget_profile.profile_id = %s',
            $request->getSession('me', 'profile_id')
        )
        ->addFilter('widget_type = %s ', 'career_page')
        ->getRow();

    // get the server name
    $domain = $request->getServer('SERVER_NAME');
    // get the port
    $port = $request->getServer('SERVER_PORT');

    // not the usual port?
    if ($port != 443 && $port != 80) {
        // set the port
        $domain = $domain . ':' . $port;
    }

    // set the career widget data
    if ($widget) {
        $data['item']['career_widget']                = $widget;
        $data['item']['career_widget']['widget_root'] = $domain;
    }

    // set the career page data
    if ($page) {
        $data['item']['career_page']                = $page;
        $data['item']['career_page']['widget_root'] = $domain;
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-widget-preview branding';
    $data['title'] = cradle('global')->translate('Jobayan - Career Widget Preview');

    $body = cradle('/app/www')->template(
        'profile/widget/preview',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');

/**
 * Render the Profile Information Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/information', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    // remove modal_flag in session
    $request->removeSession('me', 'modal_flag');

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // get the data
    $data['information'] = $response->getResults();

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/post/search');
    }

    // check if there's information
    if (!$response->getResults()) {
        // trigger the job
        cradle()->trigger('resume-search', $request, $response);

        // check for error
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');

            return cradle('global')->redirect('/profile/information');
        }

        // check if there's resume
        if ($response->getResults('rows')) {
            return cradle('global')->redirect('/profile/resume/search');
        }
    }

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // remove stage profile id
    $request->removeStage('profile_id');

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/post/search');
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/post/search');
    }

    // set the area to data
    $data['industry'] = $response->getResults();

    //----------------------------//
    // 3. Render Template
    $class         = 'page-profile-information-account branding';
    $data['title'] = cradle('global')->translate('Jobayan - My Profile');
    $body          = cradle('/app/www')->template(
        'profile/information/account',
        $data,
        [
            'profile_alert',
            'profile_menu',
            'profile/information_accomplishment',
            'profile/information_confirm',
            'profile/information_education',
            'profile/information_experience',
            'profile/information_information',
            'profile/information_skills',
            'profile/information_update',
            'profile/information_view',
            '_modal-profile-completeness'
        ]
    );

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    // Render www page
}, 'render-www-page');

/**
 * Information Resume Download
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/information/resume/download', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/profile/information');
    }

    // set the information to data
    $data['information'] = $response->getResults();

    // check if there's information
    if (!$data['information']) {
        //add a flash
        cradle('global')->flash('No information found', 'danger');

        return cradle('global')->redirect('/profile/information');
    }

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host         = $protocol . '://' . $request->getServer('HTTP_HOST');
    $data['logo'] = $host . '/images/logo-gray.png';

    // get the template
    $body = cradle('/app/www')->template('profile/information/_resume', $data);

    try {
        $html2pdf = new Html2Pdf('P', 'A4', 'en');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->pdf->SetTitle('Jobayan - ' . $data['information']['profile_name'] . ' Resume');
        $html2pdf->writeHTML($body);
        $html2pdf->output($data['information']['profile_name'] . '_Resume_Jobayan.pdf');
    } catch (Html2PdfException $e) {
        $html2pdf->clean();
        $formatter = new ExceptionFormatter($e);
        cradle('global')->flash($formatter->getHtmlMessage(), 'danger');

        return cradle('global')->redirect('/profile/information');
    }
});

/**
 * Process the Account Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/account', function ($request, $response) {
    //need to be online
    cradle('global')->requireLogin();

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        if ($request->getStage('redirect')) {
            return cradle()->triggerRoute(
                'get',
                $request->getStage('redirect'),
                $request,
                $response
            );
        } else {
            return cradle()->triggerRoute(
                'get',
                '/profile/account',
                $request,
                $response
            );
        }
    }

    //set the auth_id and profile_id
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('permission', $request->getSession('me', 'profile_id'));

    //if profile_website has no value make it null
    if ($request->hasStage('profile_website') &&
        !$request->getStage('profile_website')) {
        $request->setStage('profile_website', null);
    }

    //if profile_detail has no value make it null
    if ($request->hasStage('profile_detail') &&
        !$request->getStage('profile_detail')) {
        $request->setStage('profile_detail', null);
    }

    //trigger the job
    cradle()->trigger('auth-update', $request, $response);
    if ($response->isError()) {
        if ($request->getStage('redirect')) {
            return cradle()->triggerRoute(
                'get',
                $request->getStage('redirect'),
                $request,
                $response
            );
        } else {
            return cradle()->triggerRoute(
                'get',
                '/profile/account',
                $request,
                $response
            );
        }
    }

    //it was good
    //update the session
    cradle()->trigger('auth-detail', $request, $response);
    cradle()->trigger('profile-session', $request, $response);

    //add a flash
    cradle('global')->flash('Update Successful', 'success');

    if ($request->getSession('me', 'profile_company') != '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'profile_update');
        $request->setStage('profile_experience', $experience);
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned ' . $experience . ' experience points';

        //add a flash
        cradle('global')->flash($message, 'info');
    }

    //redirect
    $redirect = '/profile/account';
    if ($request->hasGet('redirect')) {
        $redirect = $request->getGet('redirect');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Profile Cover Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/banner', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $profileId = $request->getSession('me', 'profile_id');

    $request->setStage(
        'permission',
        $profileId
    );

    $request->setStage(
        'profile_id',
        $profileId
    );

    cradle()->trigger('profile-detail', $request, $response);

    $slug = $response->getResults('profile_slug');

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/post/search');
    }


    //disallowed
    $request->removeStage('profile_name');
    $request->removeStage('profile_email');
    $request->removeStage('profile_slug');
    $request->removeStage('profile_active');
    $request->removeStage('profile_type');

    cradle()->trigger('profile-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        cradle('global')->flash('Banner Image was Updated', 'success');
    }

    //redirect
    $redirect = '/' . $slug . '/profile-post';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Profile Cover Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/background', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $profileId = $request->getSession('me', 'profile_id');

    $request->setStage(
        'permission',
        $profileId
    );

    $request->setStage(
        'profile_id',
        $profileId
    );

    cradle()->trigger('profile-detail', $request, $response);

    $slug = $response->getResults('profile_slug');

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');

        return cradle('global')->redirect('/post/search');
    }


    //disallowed
    $request->removeStage('profile_name');
    $request->removeStage('profile_email');
    $request->removeStage('profile_slug');
    $request->removeStage('profile_active');
    $request->removeStage('profile_type');

    cradle()->trigger('profile-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        cradle('global')->flash('Background Color was Updated', 'success');
    }

    //redirect
    $redirect = '/' . $slug . '/profile-post';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Checkout Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/credit/checkout', function ($request, $response) {
    //need to be online
    cradle('global')->requireLogin();

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    //get data
    $config  = cradle('global')->config('services', 'magpie');
    $profile = $request->getSession('me');

    //validate data
    $errors = [];

    // check for name
    if (!$request->getStage('name') && empty($request->getStage('name'))) {
        $errors['name'] = 'Name is Required';
    }

    //fix formatting
    $number = str_replace([' ', '-'], '', $request->getStage('number'));
    $request->setStage('number', $number);

    $exp = str_replace(' ', '', $request->getStage('exp'));
    $request->setStage('exp', $exp);

    if (!is_numeric($request->getStage('amount'))
        || $request->getStage('amount') < 100
        || $request->getStage('amount') > 999999
    ) {
        $errors['amount'] = 'Invalid Amount';
    }

    if (!UtilityValidator::isCreditCard($request->getStage('number'))) {
        $errors['number'] = 'Invalid Credit Card Format';
    }

    $exp = $request->getStage('exp_month') . '/' . $request->getStage('exp_year');
    $exp = $request->setStage('exp', $exp);

    if (!preg_match('/^[0-9]{2}\/[0-9]{4}$/is', $request->getStage('exp'))) {
        $errors['exp'] = 'Invalid Date Format';
    }

    if (!is_numeric($request->getStage('cvc'))) {
        $errors['cvc'] = 'Invalid CVC Format';
    }

    if (!empty($errors)) {
        $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);

        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    list($month, $year) = explode('/', $request->getStage('exp'));

    $card = [
        'name'      => $request->getStage('name'),
        'number'    => $request->getStage('number'),
        'exp_month' => $month,
        'exp_year'  => $year,
        'cvc'       => $request->getStage('cvc')
    ];

    if (isset($profile['profile_billing_name'])
        && trim($profile['profile_billing_name'])
    ) {
        $card['name'] = trim($profile['profile_billing_name']);
    }

    if (trim($profile['profile_address_street'])) {
        $card['address_line1'] = trim($profile['profile_address_street']);
    }

    if (trim($profile['profile_address_city'])) {
        $card['address_city'] = trim($profile['profile_address_city']);
    }

    if (trim($profile['profile_address_state'])) {
        $card['address_state'] = trim($profile['profile_address_state']);
    }

    if (trim($profile['profile_address_country'])) {
        $card['address_country'] = trim($profile['profile_address_country']);
    }

    if (trim($profile['profile_address_postal'])) {
        $card['address_zip'] = trim($profile['profile_address_postal']);
    }

    $authorization = base64_encode($config['key'] . ':');

    $results = Curl::i()
        ->setUrl($config['token_endpoint'])
        ->setHeader(false)
        ->setPost(true)
        ->setPostFields(json_encode(['card' => $card]))
        ->setHttpheader([
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $authorization
        ])
        ->getJsonResponse();

    if (isset($results['error'], $results['message'])) {
        $response->setError(true, $results['message']);

        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    if (!isset($results['id'])) {
        $response->setError(
            true,
            'The payment could not be processed at this ' .
            'time. Please try again later.'
        );

        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    $authorization = base64_encode($config['secret'] . ':');

    $results = Curl::i()
        ->setUrl($config['charge_endpoint'])
        ->setHeader(false)
        ->setPost(true)
        ->setPostFields(json_encode([
            'amount'               => $request->getStage('amount'),
            'currency'             => 'php',
            'source'               => $results['id'],
            'description'          => $config['statement'],
            'statement_descriptor' => $config['statement'],
            'capture'              => 'true'
        ]))
        ->setHttpheader([
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . $authorization
        ])
        ->getJsonResponse();

    if (isset($results['error'], $results['message'])) {
        $response->setError(true, $results['message']);

        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    if (!isset($results['id'])) {
        $response->setError(
            true,
            'The payment could not be processed at this time. ' .
            'Please try again later.'
        );

        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    $credits = $request->getStage('amount');

    $request->setStage([
        'profile_id'                    => $profile['profile_id'],
        'transaction_status'            => 'complete',
        'transaction_payment_method'    => 'magpie',
        'transaction_payment_reference' => $results['id'],
        'transaction_meta'              => $results,
        'transaction_statement'         => $config['statement'],
        'transaction_currency'          => 'PHP',
        'transaction_total'             => $request->getStage('amount'),
        'transaction_credits'           => $credits
    ]);

    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/profile/credit/checkout',
            $request,
            $response
        );
    }

    $total = $request->getSession('me', 'profile_credits');

    $request->setSession('me', 'profile_credits', $total + $credits);

    cradle()->trigger('transaction-create', $request, $response);

    cradle('global')->flash('Transaction Successful', 'success');

    if ($request->getSession('me', 'profile_id')) {
        // add create post experience
        $experience = cradle('global')->config('experience', 'credit_purchase');
        $experience = $experience * $credits;
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned ' . $experience . ' experience point';

        //add a experience flash
        cradle('global')->flash($message, 'info');
    }

    // if they have a redirect from a page
    if ($request->getStage('redirect_uri')) {
        cradle('global')
            ->redirect($request->getStage('redirect_uri') . '?credits=true');
    }
    cradle('global')->redirect('/profile/transaction/search#history');
});

/**
 * Process the Tranaction Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/transaction/search', function ($request, $response) {
    //need to be online
    cradle('global')->requireLogin();

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        if ($request->getStage('redirect')) {
            return cradle()->triggerRoute(
                'get',
                $request->getStage('redirect'),
                $request,
                $response
            );
        } else {
            return cradle()->triggerRoute(
                'get',
                '/profile/transaction/search',
                $request,
                $response
            );
        }
    }

    //set the auth_id and profile_id
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('permission', $request->getSession('me', 'profile_id'));

    //remove email, because that is an auth thing
    if (!$request->getStage('profile_email')) {
        $request->removeStage('profile_email');
    }

    //trigger the job
    cradle()->trigger('profile-update', $request, $response);

    if ($response->isError()) {
        if ($request->getStage('redirect')) {
            $response->setFlash($response->getMessage(), 'danger');
            $request->setStage('errors', $response->getValidation());

            return cradle()->triggerRoute(
                'get',
                $request->getStage('redirect'),
                $request,
                $response
            );
        } else {
            $response->setFlash($response->getMessage(), 'danger');
            $request->setStage('errors', $response->getValidation());

            return cradle()->triggerRoute(
                'get',
                '/profile/transaction/search',
                $request,
                $response
            );
        }
    }

    //it was good
    //update the session
    cradle()->trigger('auth-detail', $request, $response);
    $_SESSION['me'] = $response->getResults();

    //add a flash
    cradle('global')->flash('Update Successful', 'success');

    //redirect
    $redirect = '/profile/transaction/search';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Career Widget Settings
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/widget/settings', function ($request, $response) {
    // require login
    cradle('global')->requireLogin();

    // csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/profile/widget/settings',
            $request,
            $response
        );
    }

    // get id
    $id = $request->getStage('widget_id');

    // if there is no widget id
    if (!$id || is_nan($id)) {
        // remove widget id
        $request->removeStage('widget_id');
        // set profile id
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        // set widget key
        $request->setStage('widget_key', md5(time() . time() . uniqid()));

        // if widget meta is not set
        if (!$request->getStage('widget_meta')) {
            $request->setStage('widget_meta', '[]');
        }

        // create new widget
        cradle()->trigger('widget-create', $request, $response);
    } else {
        // manually check widget
        $widget = WidgetService::get('sql')
            ->getResource()
            ->search('widget_profile')
            ->innerJoinUsing('widget', 'widget_id')
            ->addFilter(
                'widget_profile.profile_id = %s',
                $request->getSession('me', 'profile_id')
            )
            ->addFilter('widget_type = %s ', 'career_widget')
            ->getRow();

        // if widget is empty
        if (empty($widget)) {
            // set error
            $response->setError(true, 'Invalid Request');

            // trigger route
            return cradle()->triggerRoute(
                'get',
                '/profile/widget/settings',
                $request,
                $response
            );
        }

        // update the widget
        cradle()->trigger('widget-update', $request, $response);
    }

    // is there an error?
    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/profile/widget/settings',
            $request,
            $response
        );
    }

    //add a flash
    cradle('global')->flash('Career widget has been successully updated', 'success');
    // redirect back
    cradle('global')->redirect('/profile/widget/settings');
});

/**
 * Process the Career Page Settings
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/profile/widget/page/settings', function ($request, $response) {
    // require login
    cradle('global')->requireLogin();

    // csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    // is there an error?
    if ($response->isError()) {
        // trigger route
        return cradle()->triggerRoute(
            'get',
            '/profile/widget/page/settings',
            $request,
            $response
        );
    }

    // if school is set
    if ($request->getStage('widget_meta', 'school')) {
        // get the widget school
        $school = $request->getStage('widget_meta', 'school');
        // replace hyphens
        $school = str_replace(' ', '-', $school);
        // replace special characters
        $school = preg_replace('/[^ \w]+/', ' ', $school);

        // update school
        $request->setStage('widget_meta', 'school', $school);
    }

    // if we have widget tags
    if ($request->getStage('post_tags')) {
        // get the widget tags
        $tags = $request->getStage('post_tags');
        // map and lower all the tags
        $tags = array_map('strtolower', $tags);

        // remove empty tags
        $tags = array_filter($tags, function ($tag) {
            return strlen($tag);
        });

        // replace special characters
        $tags = preg_replace('/[^ \w]+/', ' ', $tags);

        // update tags
        $request->setStage('widget_meta', 'tags', $tags);
    }

    // get data
    $data = $request->getStage();

    // if we don't have post tags
    if (empty($data['post_tags'])) {
        // set empty tags
        $data['post_tags'] = [];
    }

    // is there an error?
    if ($response->isError()) {
        // trigger route
        return cradle()->triggerRoute(
            'get',
            '/profile/widget/page/settings',
            $request,
            $response
        );
    }

    // get id
    $id = $request->getStage('widget_id');

    // if there is no widget id
    if (!$id || is_nan($id)) {
        // remove widget id
        $request->removeStage('widget_id');
        // set profile id
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        // set widget key
        $request->setStage('widget_key', md5(time() . time() . uniqid()));

        // encode meta
        if ($request->hasStage('widget_meta')) {
            $request->setStage('widget_meta', json_encode(
                $request->getStage('widget_meta')
            ));
        }

        // create new widget
        cradle()->trigger('widget-create', $request, $response);
    } else {
        // manually check widget
        $widget = WidgetService::get('sql')
            ->getResource()
            ->search('widget_profile')
            ->innerJoinUsing('widget', 'widget_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->addFilter(
                'widget_profile.profile_id = %s',
                $request->getSession('me', 'profile_id')
            )
            ->addFilter('widget_type = %s ', 'career_page')
            ->getRow();

        // if widget is empty
        if (empty($widget)) {
            // set error
            $response->setError(true, 'Invalid widget');

            // trigger route
            return cradle()->triggerRoute(
                'get',
                '/profile/widget/page/settings',
                $request,
                $response
            );
        }

        // encode meta
        if ($request->hasStage('widget_meta')) {
            $request->setStage('widget_meta', json_encode(
                $request->getStage('widget_meta')
            ));
        }

        // update the widget
        cradle()->trigger('widget-update', $request, $response);
    }

    // $this->inspect($response);exit;

    // if there is an error
    if ($response->isError()) {
        // trigger route
        return cradle()->triggerRoute(
            'get',
            '/profile/widget/page/settings',
            $request,
            $response
        );
    }

    //add a flash
    cradle('global')->flash('Career Page Successfully Saved', 'success');
    // redirect back
    cradle('global')->redirect('/profile/widget/page/settings');
});

/**
 * Process the Widget Settings
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/profile/message/:profile_slug', function ($request, $response) {
    // require login
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Checks for a profile slig
    if (isset($data['profile_slug'])) {
        $request->setStage('filter', 'profile_slug', $data['profile_slug']);
        $request->setGet('noindex', true);
    }

    // Search for the profile
    // Based on the profile_slug
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults();

    // Checks if there was no profiel returned
    if (!$results['total']) {
        // Return an error message
        cradle('global')->flash('Not Found', 'danger');

        // Redirect back to the front page
        cradle('global')->redirect('/');
    }

    // Pass the profile to the template
    $data['profile'] = $results['rows'][0];

    $title = cradle('global')->translate('Jobayan - Message');
    $class = 'page-profile-message branding';
    $body  = cradle('/app/www')->template(
        'profile/message',
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');
