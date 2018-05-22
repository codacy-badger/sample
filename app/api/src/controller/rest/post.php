<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Post search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/post/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter shortcuts
    $filter = array();

    //post_name
    if ($request->getStage('name')) {
        $request->setStage(
            'filter',
            'post_name',
            $request->getStage('name')
        );
    }

    //post_position
    if ($request->getStage('position')) {
        $request->setStage(
            'filter',
            'post_position',
            $request->getStage('position')
        );

        $filter[] = 'filter[post_position]='.$request->getStage('position');
    }

    //post_location
    if ($request->getStage('location')) {
        $request->setStage(
            'filter',
            'post_location',
            $request->getStage('location')
        );

        $filter[] = 'filter[post_location]='.$request->getStage('location');
    }

    //post_experience
    if ($request->getStage('experience')) {
        $request->setStage(
            'exact_filter',
            'post_experience',
            $request->getStage('experience')
        );

        $filter[] = 'exact_filter[post_experience]='.$request->getStage('experience');
    }

    //post_type
    if ($request->getStage('type')) {
        $request->setStage(
            'filter',
            'post_type',
            $request->getStage('type')
        );

        $filter[] = 'filter[post_type]='.$request->getStage('type');
    }

    //profile_id
    if ($request->getStage('profile')) {
        $request->setStage(
            'filter',
            'profile_id',
            $request->getStage('profile')
        );
    }

    //profile_company
    if ($request->getStage('company')) {
        $request->setStage(
            'filter',
            'profile_company',
            $request->getStage('company')
        );

        $filter[] = 'company='.$request->getStage('company');
    }

    //post_tags
    if ($request->getStage('tag')) {
        $request->setStage(
            'post_tags',
            $request->getStage('tag')
        );
    }

    //sort shortcuts
    switch ($request->getStage('sort')) {
        case 'popular':
            $request->setStage('order', 'post_like_count', 'DESC');
            break;
        default:
            $request->setStage('order', 'post_updated', 'DESC');
            break;
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_id',
            'post_expires',
            'post_experience',
            'post_salary',
            'post_like_count',
            'post_download_count',
            'post_created',
            'post_updated',
            'profile_company'
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
            'post_name',
            'profile_id',
            'post_location',
            'post_position',
            'post_active',
            'post_experience',
            'post_location',
            'post_salary',
            'post_link',
            'post_type',
            'profile_company'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if ($request->getStage('profile_id')) {
        $request->setStage('filter', 'profile_id', $request->getStage('profile_id'));
    }

    // Gets the server settings
    $settings = cradle('global')->config('settings');

    // Checks for host settings
    if (isset($settings['host'])) {
        $host = $settings['host'];
    } else {
        $protocol = 'http';
        if ($request->getServer('HTTP_CF_VISITOR')) {
            $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
            if ($pos !== false) {
                $protocol = 'https';
            }
        }

        //host
        $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    }

    // Base url
    $url = $host . '/post/search';

    // Checks if the filters are not empty
    if (!empty($filter)) {
        $url .= '?' . implode('&', $filter);
    }

    $request->setStage('strip_tags', true);
    $request->setGet('noindex', true);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-search', $request, $response);

    //get data
    $data = $response->getResults();
    //set url
    $data['redirect_url'] = $url;
    
    $response->setError(false)->setResults($data);
});

/**
 * Post detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/post/detail/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-detail', $request, $response);
});

/**
 * Process the Post Create Poster Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/post/create/poster', function ($request, $response) {
    $crawler = $this->package('global')->config('crawler');
    $profileId = null;

    // Checks if the profile_id is set
    if ($request->hasStage('profile_id')) {
        $profileId = $request->getStage('profile_id');
    }

    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->removeStage('profile_id');

    // Checks if there is a profile_id set previously
    if ($profileId) {
        $request->setStage('profile_id', $profileId);
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Create Poster');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data
    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_resume is disallowed
    $request->removeStage('post_resume');

    //if post_email has no value skip process
    if ($request->hasStage('post_email') && !$request->getStage('post_email')) {
        $request->setStage('post_email', null);
        return;
    }

    //if post_phone has no value make it null
    if ($request->hasStage('post_phone') && !$request->getStage('post_phone')) {
        $request->setStage('post_phone', null);
    }

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_detail has no value make it null
    if ($request->hasStage('post_detail') && !$request->getStage('post_detail')) {
        $request->setStage('post_detail', null);
    }

    //if post_link has no value make it null
    if ($request->hasStage('post_link') && !$request->getStage('post_link')) {
        $request->setStage('post_link', null);
    }

    //if post_banner has no value make it null
    if ($request->hasStage('post_banner') && !$request->getStage('post_banner')) {
        $request->setStage('post_banner', null);
    }

    //if post_tags has no value make it null
    if ($request->hasStage('post_tags') && !$request->getStage('post_tags')) {
        $request->setStage('post_tags', null);
    }

    //if post_salary has no value make it null
    if ($request->hasStage('post_salary') && !$request->getStage('post_salary')) {
        $request->setStage('post_salary', null);
    } else {
        $max = $min = $request->getStage('post_salary');
        if (strpos($min, '-')) {
            list($min, $max) = explode('-', $min);
        }

        $min = preg_replace('#[^\d\.]#', '', $min);
        $max = preg_replace('#[^\d\.]#', '', $max);

        if ($min === $max) {
            $max = null;
        }

        if ($min) {
            $request->setStage('post_salary_min', $min);
        }

        if ($max) {
            $request->setStage('post_salary_max', $max);
        }
    }

    $request->setStage('post_type', 'poster');

    // Checks if there is a session me
    if ($request->hasSession('me')) {
        // Sets the profile_id
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    if (!$request->getStage('post_email')) {
        $request->setStage('post_email', $request->getSession('me', 'profile_email'));
    }

    if (!$request->getStage('post_phone')) {
        $request->setStage('post_phone', $request->getSession('me', 'post_phone'));
    }

    if (isset($crawler['notify_likes']) && $crawler['notify_likes']) {
        $postNotify = ['likes'];
        $request->setStage('post_notify', $postNotify);
    }

    if (isset($crawler['notify_matches']) && $crawler['notify_matches']) {
        $postNotify = isset($postNotify) ? ["likes", "matches"] : ["matches"] ;
        $request->setStage('post_notify', $postNotify);
    }

    // Checks for profile email / profile_email
    if ($request->hasStage('profile_email')) {
        // Search for the profile
        // Based on profile email / profile_email
        $request->setStage('filter', 'profile_email', $request->getStage('profile_email'));
        cradle()->trigger('profile-search', $request, $response);
        $results = $response->getResults();

        // Checks if there was a return
        if ($results['total']) {
            // Sets the profile id / profile_id
            $request->setStage('profile_id', $results['rows'][0]['profile_id']);
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-create', $request, $response);

    // Checks for errors
    if (!$response->isError()) {
        // Gets the server settings
        $settings = cradle('global')->config('settings');

        // Checks for host settings
        if (isset($settings['host'])) {
            $host = $settings['host'];
        } else {
            $protocol = 'http';
            if ($request->getServer('HTTP_CF_VISITOR')) {
                $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
                if ($pos !== false) {
                    $protocol = 'https';
                }
            }

            //host
            $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        }

        $results = $response->getResults();

        // Checks for post_url
        if (isset($results['post_url'])) {
            $results['post_url'] = $host . $results['post_url'];
        }

        // Return the resume link
        return $response->setContent([
            'error'   => false,
            'results' => $results
        ]);
    }
});

/**
 * Process the Post Create Seeker Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/post/create/seeker', function ($request, $response) {
    $profileId = null;

    // Checks if the profile_id is set
    if ($request->hasStage('profile_id')) {
        $profileId = $request->getStage('profile_id');
    }

    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->removeStage('profile_id');

    // Checks if there is a profile_id set previously
    if ($profileId) {
        $request->setStage('profile_id', $profileId);
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Create Seeker');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data
    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_link is disallowed
    $request->removeStage('post_link');

    //post_banner is disallowed
    $request->removeStage('post_banner');

    //post_tags is disallowed
    $request->removeStage('post_tags');

    //post_salary is disallowed
    $request->removeStage('post_salary');

    //if post_email has no value make it null
    if ($request->hasStage('post_email') && !$request->getStage('post_email')) {
        $request->setStage('post_email', null);
    }

    //if post_phone has no value make it null
    if ($request->hasStage('post_phone') && !$request->getStage('post_phone')) {
        $request->setStage('post_phone', null);
    }

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_resume has no value make it null
    if ($request->hasStage('post_resume') && !$request->getStage('post_resume')) {
        $request->setStage('post_resume', null);
    }

    //if post_detail has no value make it null
    if ($request->hasStage('post_detail') && !$request->getStage('post_detail')) {
        $request->setStage('post_detail', null);
    }

    $request->setStage('post_type', 'seeker');

    // Checks if there is a session me
    if ($request->hasSession('me')) {
        // Sets the profile_id
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }


    if (!$request->getStage('post_email')) {
        $request->setStage('post_email', $request->getSession('me', 'profile_email'));
    }

    if (!$request->getStage('post_phone')) {
        $request->setStage('post_phone', $request->getSession('me', 'post_phone'));
    }

    // Checks for profile email / profile_email
    if ($request->hasStage('profile_email')) {
        // Search for the profile
        // Based on profile email / profile_email
        $request->setStage('filter', 'profile_email', $request->getStage('profile_email'));
        cradle()->trigger('profile-search', $request, $response);
        $results = $response->getResults();

        // Checks if there was a return
        if ($results['total']) {
            // Sets the profile id / profile_id
            $request->setStage('profile_id', $results['rows'][0]['profile_id']);
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-create', $request, $response);

    // Checks for errors
    if (!$response->isError()) {
        // Gets the server settings
        $settings = cradle('global')->config('settings');

        // Checks for host settings
        if (isset($settings['host'])) {
            $host = $settings['host'];
        } else {
            $protocol = 'http';
            if ($request->getServer('HTTP_CF_VISITOR')) {
                $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
                if ($pos !== false) {
                    $protocol = 'https';
                }
            }

            //host
            $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        }

        $results = $response->getResults();

        // Checks for post_url
        if (isset($results['post_url'])) {
            $results['post_url'] = $host . $results['post_url'];
        }

        // Return the resume link
        return $response->setContent([
            'error'   => false,
            'results' => $results
        ]);
    }
});

/**
 * Process the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/post/update/poster/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Update Poster');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_resume is disallowed
    $request->removeStage('post_resume');

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_detail has no value make it null
    if ($request->hasStage('post_detail') && !$request->getStage('post_detail')) {
        $request->setStage('post_detail', null);
    }

    //if post_link has no value make it null
    if ($request->hasStage('post_link') && !$request->getStage('post_link')) {
        $request->setStage('post_link', null);
    }

    //if post_banner has no value make it null
    if ($request->hasStage('post_banner') && !$request->getStage('post_banner')) {
        $request->setStage('post_banner', null);
    }

    //if post_tags has no value make it null
    if ($request->hasStage('post_tags') && !$request->getStage('post_tags')) {
        $request->setStage('post_tags', null);
    }

    //if post_salary has no value make it null
    if ($request->hasStage('post_salary') && !$request->getStage('post_salary')) {
        $request->setStage('post_salary', null);
    } else {
        $max = $min = $request->getStage('post_salary');
        if (strpos($min, '-')) {
            list($min, $max) = explode('-', $min);
        }

        $min = preg_replace('#[^\d\.]#', '', $min);
        $max = preg_replace('#[^\d\.]#', '', $max);

        if ($min === $max) {
            $max = null;
        }

        if ($min) {
            $request->setStage('post_salary_min', $min);
        }

        if ($max) {
            $request->setStage('post_salary_max', $max);
        }
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);
});

/**
 * Process the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/post/update/seeker/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Create Seeker');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_link is disallowed
    $request->removeStage('post_link');

    //post_banner is disallowed
    $request->removeStage('post_banner');

    //post_tags is disallowed
    $request->removeStage('post_tags');

    //post_salary is disallowed
    $request->removeStage('post_salary');

    //if post_experience has no value make it null
    if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
        $request->setStage('post_experience', null);
    }

    //if post_resume has no value make it null
    if ($request->hasStage('post_resume') && !$request->getStage('post_resume')) {
        $request->setStage('post_resume', null);
    }

    //if post_detail has no value make it null
    if ($request->hasStage('post_detail') && !$request->getStage('post_detail')) {
        $request->setStage('post_detail', null);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);
});

/**
 * Post remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/post/remove/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Remove');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-remove', $request, $response);
});

/**
 * Post restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/post/restore/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Restore');
    cradle()->trigger('log-action', $request, $response);

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-restore', $request, $response);
});

/**
 * Process the Post Create Poster Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/post/interested/:post_id', function ($request, $response) {
    /**
     * Notes for this page
     * Param to be passed
     *   - Required Params
     *     - profile_name
     *     - profile_phone
     *     - profile_email
     *     - post_id
     *   - Optional Param
     *     - profile_company
     *
     * Only allow valid profiles to interest a post
     *   - If the profile exists based on the email, use that profile_id
     *   - If the user is a company, the basis should be email and company name
     *   - If the user does not exist, create the profile
     *
     *  Limitation
     *    - If the post_type is seeker and the user is a seeker, the post cannot be liked
     */

    // Checks if there are missing params
    $data = $request->getStage();
    $params = array(
        'post_id',
        'profile_email',
        'profile_name',
        'profile_phone',
    );

    // Loops through the required params
    foreach ($params as $param) {
        // Checks if the param does no exist
        if (!isset($data[$param])) {
            // At this point, there are errors
            return $response->setError(true, 'Missing parameter');
        }
    }

    $profileId = null;

    // Checks if the profile_id is set
    if ($request->hasStage('profile_id')) {
        $profileId = $request->getStage('profile_id');
    }

    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'post');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }

    $request->setStage('log_path', 'log-api');
    $request->setStage('log_action', 'Rest Post Interested');
    cradle()->trigger('log-action', $request, $response);

    $request->removeStage('profile_id');

    // Checks if there is a profile_id set previously
    if ($profileId) {
        $request->setStage('profile_id', $profileId);
    }

    // Checks if the post is valid
    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setError(true, 'Invalid Post ID');
    }

    // The post exists at this point
    $post = $response->getResults();

    // Checks if the post email is the user's email
    if ($data['profile_email'] == $post['post_email']
        || $data['profile_email'] == $post['profile_email']) {
        // At this point, there are errors
        // Cannot like your own post
        return $response->setError(true, 'Error liking this post');
    }

    // Checks if the post type is seeker
    // Checks if the user liking is also a seeker
    if ((!isset($data['profile_company']) || empty($data['profile_company']))
        && $post['post_type'] == 'seeker') {
        // At this point, there are errors
        return $response->setError(true, 'Error liking this post');
    }

    // Constructs the filter for profile search
    $request->setStage('filter', 'profile_email', $data['profile_email']);
    $request->setStage('filter', 'type', 'seeker');

    // Checks if the user is a company/poster
    if (isset($data['profile_company']) && !empty($data['profile_company'])) {
        $request->setStage('filter', 'profile_company', $data['profile_company']);
        $request->setStage('filter', 'type', 'poster');
    }

    // Search for the profile
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults();

    // Creates a new instance for request
    $profileRequest = new Request();

    // Checks if there are no results
    if (!$results['rows']) {
        // Create the profile for this user
        $user = array();
        $user['profile_name'] = $data['profile_name'];
        $user['profile_email'] = $data['profile_email'];
        $user['profile_phone'] = $data['profile_phone'];

        if (isset($data['profile_company']) && !empty($data['profile_company'])) {
            $user['profile_company'] = $data['profile_company'];
        }

        $profileRequest->setStage($user);

        // Creates the user
        cradle()->trigger('profile-create', $profileRequest, $response);

        // Checks if there were errors creating the profile
        if ($response->isError()) {
            // At this point, there are errors
            return $response->setError(true, 'Error liking this post');
        }

        // Assume that the profile was created
        $profile = $response->getResults();
    } else {
        // Get the profile from the results
        $profile = $results['rows'][0];
    }

    // Remove the filters
    $request->removeStage('filter');
    $profileRequest->removeStage();

    // We can now like the post
    $profileRequest->setStage('post_id', $data['post_id']);
    $profileRequest->setStage('profile_id', $profile['profile_id']);

    // Trigger the like event
    cradle()->trigger('post-like', $profileRequest, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setError(true, 'Error liking this post');
    }

    // Assume that there are no errors
    $results = $response->getResults();
    $response->remove('json', 'validation');
    $response->remove('json', 'results');

    // Checks if the user is a company/poster
    if (!isset($data['profile_company']) || empty($data['profile_company'])) {
        return $response->setError(false, 'Post successfully liked');
    }

    // Add interested experience
    $experience = cradle('global')->config('experience', 'interested');
    $request->setStage('profile_experience', $experience);
    $this->trigger('profile-add-experience', $request, $response);

    $request->setStage('activity', 'interested');
    $this->trigger('post-get-count', $request, $response);
    $activityCount = $response->getResults();
    $interested = null;

    switch ($activityCount) {
        case 1:
        case 10:
        case 50:
        case 100:
            // Add Achievement
            $interested = 'interested_' + $activityCount;
            $achievement = cradle('global')->config('achievements', $interested);
            $request->setStage('profile_achievement', $interested);
            $this->trigger('profile-add-achievement', $request, $response);
            break;

        default:
            break;
    }
    
    return $response->setError(false, 'Post successfully liked');
});
