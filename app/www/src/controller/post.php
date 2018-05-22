<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;
use Cradle\Module\Profile\Service as ProfileService;

/**
 * Render the Post Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 45);
    }

    // trim and remove excess whitespaces between string
    if ($request->hasStage('q')) {
        $request->setStage('q', trim(preg_replace('/\s+/', ' ', $request->getStage('q'))));
    }

    //filter shortcuts

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
    }

    //post_location
    if ($request->getStage('location')) {
        $request->setStage(
            'filter',
            'post_location',
            $request->getStage('location')
        );
    }

    //post_type
    if ($request->getStage('type')) {
        $request->setStage(
            'filter',
            'post_type',
            $request->getStage('type')
        );
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
    }

    //post_tags
    if ($request->getStage('tag')) {
        $request->setStage(
            'post_tags',
            $request->getStage('tag')
        );
    }

    //post_experience
    if ($request->getStage('experience')) {
        $request->setStage(
            'filter',
            'post_experience',
            $request->getStage('experience')
        );
    }

    // sort shortcuts
    $request->setStage('sorting', 'Sort By');

    // Checks the sorting
    switch ($request->getStage('sort')) {
        case 'popular':
            $request->setStage('sorting', 'By Popular');
            $request->setStage('order', 'post_like_count', 'DESC');
            break;
        case 'latest':
            $request->setStage('sorting', 'By Latest');
            $request->setStage('order', 'post_created', 'DESC');
            break;
        default:
            $request->setStage('order', 'post_created', 'DESC');
            // $request->setStage('order', 'post_flag', 'DESC');
            break;
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_created',
            'post_download_count',
            'post_experience',
            'post_expires',
            'post_flag',
            'post_id',
            'post_like_count',
            'post_salary',
            'post_updated',
        ];

        // Loops through the orders
        foreach ($request->getStage('order') as $key => $direction) {
            // Checks if the sorting value is not in the allowed sorting
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                // Checks if the sorting
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_active',
            'post_experience',
            'post_location',
            'post_location',
            'post_name',
            'post_position',
            'post_salary',
            'post_type',
            'profile_company',
            'profile_id',
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    // set the data
    $data = $request->getStage();

    // get the unpromoted post
    // set the range to 45
    $request->setStage('range', 45);
    // set the post flag
    $request->setStage('filter', 'post_flag', [0, 3]);
    // trigger post search
    cradle()->trigger('post-search', $request, $response);
    // set the unpromoted post
    $unpromotedPost = $response->getResults('rows');

    // get the sponsored post
    // set the sponsored range 5
    $request->setStage('range', 5);
    // set the post flag
    $request->setStage('filter', 'post_flag', 1);
    // trigget the post search
    cradle()->trigger('post-search', $request, $response);
    // set the promoted post
    $promotedPost = $response->getResults('rows');

    //unset post_flag | promoted post
    $request->removeStage('filter', 'post_flag');

    // set the data for rows
    $data['rows'] = $unpromotedPost;

    // scatter the post
    if (!empty($promotedPost)) {
        $e = count($promotedPost);
        $f = count($unpromotedPost);
        $j = $f / $e;

        $k = 0;
        $i = 0;

        $newRows = [];
        if (empty($unpromotedPost)) {
            $data['rows'] = $promotedPost;
        } else {
            foreach ($unpromotedPost as $unpromoted) {
                $newRows[] = $unpromotedPost[$i];
                if ($i % ceil($j) == 0) {
                    if (array_key_exists($k, $promotedPost)) {
                        $newRows[] = $promotedPost[$k];
                        $k++;
                    }
                }
                $i++;
            }

            $data['rows'] = $newRows;
        }
    }

    // group the post if same profile id
    $previousProfileId = null;
    $previousKeyValue = null;
    foreach ($data['rows'] as $r => $row) {
        // do not include if featured post in a group
        if ($row['post_flag'] == 1) {
            continue;
        }

        // if the same profile id group together
        // do not group if profile is Anonymous
        if ($previousProfileId == $row['profile_id'] &&
            $row['profile_id'] != '46726' &&
            $row['profile_name'] != 'Anonymous') {
            // the the group to post related
            $data['rows'][$previousKeyValue]['post_related'][] = $row;
            // unset the row
            unset($data['rows'][$r]);
            // continue to life
            continue;
        }

        // set the previous key
        $previousKeyValue = $r;
        // set the previous profile id
        $previousProfileId = $row['profile_id'];
    }

    $data['suggestion'] = [];
    if (is_string($request->getStage('q'))) {
        // fuzzy search for post_title
        $request->setStage('filter', 'post_expires', '1');
        $request->setStage('term', $request->getStage('q'));
        $request->setStage('range', 5);
        cradle()->trigger('post-fuzzy-search', $request, $response);
        $result = $response->getResults();

        if (isset($result['total']) && $result['total'] > 0) {
            // loop thru results
            foreach ($result['rows'] as $v) {
                // check if result is the same as the searched query
                if (trim(strtolower($v['post_position'])) == trim(strtolower($request->getStage('q')))) {
                    continue;
                }

                // check if result is a duplicate
                if (in_array(strtolower($v['post_position']), $data['suggestion'])) {
                    continue;
                }

                $data['suggestion'][] = strtolower($v['post_position']);
            }
        }

        // fuzzy search for post_position
        $request->setStage('field', 'post_position');
        $request->setStage('range', 5);
        cradle()->trigger('post-fuzzy-search', $request, $response);
        $result = $response->getResults();

        if (isset($result['total']) && $result['total'] > 0) {
            // loop thru results
            foreach ($result['rows'] as $v) {
                // check if result is the same as the searched query
                if (trim(strtolower($v['post_position'])) == trim(strtolower($request->getStage('q')))) {
                    continue;
                }

                // check if result is a duplicate
                if (in_array(strtolower($v['post_position']), ($data['suggestion']))) {
                    continue;
                }

                $data['suggestion'][] = strtolower($v['post_position']);
            }
        }

        // fuzzy search for post_location
        $request->setStage('field', 'post_location');
        $request->setStage('range', 5);
        cradle()->trigger('post-fuzzy-search', $request, $response);
        $result = $response->getResults();

        if (isset($result['total']) && $result['total'] > 0) {
            // loop thru results
            foreach ($result['rows'] as $v) {
                // check if result is the same as the searched query
                if (trim(strtolower($v['post_location'])) == trim(strtolower($request->getStage('q')))) {
                    continue;
                }

                // check if result is a duplicate
                if (in_array(strtolower($v['post_location']), $data['suggestion'])) {
                    continue;
                }

                $data['suggestion'][] = strtolower($v['post_location']);
            }
        }
    }

    //get suggestion total
    $data['suggestion_total'] = count($data['suggestion']);

    //----------------------------//
    // 3. Render Template
    // All job opportunities and job seekers
    $title = 'All job opportunities and job seekers';
    $keywords = [];

    // All job opportunities
    if ($request->getStage('type') === 'poster') {
        $title = 'All job opportunities';
    }

    // All job job seekers
    if ($request->getStage('type') === 'seeker') {
        $title = 'All job seekers';
    }

    // All job posts looking for Programmer
    if ($request->getStage('position')) {
        $title = 'All job posts looking for ' . ucfirst($request->getStage('position'));

        // All job opportunities for Programmer
        if ($request->getStage('type') === 'poster') {
            $title = 'All job opportunities looking for ' . ucfirst($request->getStage('position'));
        }

        // All job job seekers for Programmer
        if ($request->getStage('type') === 'seeker') {
            $title = 'All job seekers looking for ' . ucfirst($request->getStage('position'));
        }

        $keywords[] = $request->getStage('position');
    }

    // check if post tag is array
    if (is_array($request->getStage('tag'))) {
        $request->setStage(
            'tag',
            implode(', ', $request->getStage('tag'))
        );
    }

    // All job posts in Agriculture and Mining
    if ($request->getStage('tag')) {
        $title = 'All job posts in ' . ucfirst($request->getStage('tag'));

        // All job opportunities in Agriculture and Mining
        if ($request->getStage('type') === 'poster') {
            $title = 'All job opportunities in ' . ucfirst($request->getStage('tag'));
        }

        // All job seekers in Agriculture and Mining
        if ($request->getStage('type') === 'seeker') {
            $title = 'All job seekers in ' . ucfirst($request->getStage('tag'));
        }

        $keywords[] = $request->getStage('tag');
    }

    // check if post location is array
    if (is_array($request->getStage('location'))) {
        $request->setStage(
            'location',
            implode(', ', $request->getStage('location'))
        );
    }

    // All job posts in Manila
    if ($request->getStage('location')) {
        $title = 'All job posts in ' . ucfirst($request->getStage('location'));

        // All job opportunities in Manila
        if ($request->getStage('type') === 'poster') {
            $title = 'All job opportunities in ' . ucfirst($request->getStage('location'));
        }

        // All job seekers in Manila
        if ($request->getStage('type') === 'seeker') {
            $title = 'All job seekers in ' . ucfirst($request->getStage('location'));
        }

        $keywords[] = $request->getStage('location');
    }

    // All job posts matching "foobar"
    $query = '';
    if ($request->getStage('q')) {
        $title = 'All job post';

        // All job opportunities matching "foobar"
        if ($request->getStage('type') === 'poster') {
            $title = 'All job opportunities';
        }

        // All job seekers matching "foobar"
        if ($request->getStage('type') === 'seeker') {
            $title = 'All job seekers';
        }

        // All job posts in Manila matching "foobar"
        if ($request->getStage('location')) {
            $title .= ' in ' .$request->getStage('location');
        }

        if (is_array($request->getStage('q'))) {
            $title .= ' matching "' . $request->getStage('q', 0) . '"';
        } else {
            $title .= ' matching "' . $request->getStage('q') . '"';
        }
        $query = $request->getStage('q', 0);
        $keywords[] = $request->getStage('q', 0);
    }

    // check for wall title
    if ($request->hasStage('wall_title')) {
        $title = $request->getStage('wall_title');
    }

    $data['wall_title'] = $title;

    $request->removeStage('filter', 'post_expires');
    $request->setStage('range', 3);
    $request->setStage('order', ['post_like_count' => 'DESC']);

    // get recommended poster
    $request->setStage('filter', 'post_type', 'poster');
    $this->trigger('post-search', $request, $response);
    $data['recommended']['poster'] = $response->getResults('rows');

    // get recommended seeker
    $request->setStage('filter', 'post_type', 'seeker');
    $this->trigger('post-search', $request, $response);

    $data['recommended']['seeker'] = $response->getResults('rows');

    // merge recommended poster and recommended seeker
    $data['recommended']['all'] = array_merge(
        $data['recommended']['poster'],
        $data['recommended']['seeker']
    );

    $request->removeStage('filter');
    $request->removeStage('order');
    $request->setStage('range', 0);

    $data['filter_panel'] = [];
    $featureTypes = ['location', 'industry'];
    foreach ($featureTypes as $ft) {
        $filter['feature_type'] = $ft;
        $request->setStage('filter', $filter);
        $request->setStage('columns', ['feature_name', 'feature_slug']);

        cradle()->trigger('feature-search', $request, $response);
        $data['filter_panel'][$ft] = $response->getResults('rows');
    }

    $request->removeStage('filter');
    $request->removeStage('order');
    $request->removeStage('columns');

    // get most popular terms
    $request->setStage('order', ['term_hits' => 'DESC']);
    $request->setStage('range', 9);
    $request->setStage('not_url', true);
    cradle()->trigger('term-search', $request, $response);
    $data['terms'] = $response->getResults('rows');
    $request->removeStage('range');

    if ($request->getSession('me', 'profile_id') &&
        $request->getSession('me', 'profile_company') !== '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'view_search');
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned '.$experience. ' experience point';

        //add a experience flash
        cradle('global')->setExperienceFlash($message);

        // add rank badge
        cradle()->trigger('profile-add-free-credits', $request, $response);
    }

    // set the industries
    $data['industries'] = array_column($data['filter_panel']['industry'], 'feature_name');

    // remove stage oreder
    $request->removeStage('order');

    foreach ($data['rows'] as $r => $row) {
        // check profile id and post type
        if ($request->getSession('me', 'profile_id') == $row['profile_id']) {
            // set area locate to stage
            $request->setStage([
                'area_locate' => $row['post_location']
            ]);

            // trigger area-search
            cradle()->trigger('area-search', $request, $response);
            // get the results
            if ($response->getResults('total')) {
                $data['rows'][$r]['post_location_flag'] = true;
            }
        }

        // check if post type seeker
        if ($row['post_type'] == 'seeker') {
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

        // to detect if sms-match is checked before
        if (in_array('sms-match', $row['post_notify'])) {
            $data['rows'][$r]['sms_match'] = 1;
        }

        //in older versions, tags can appear as strings
        if (is_string($row['post_tags'])) {
            //lets make sure it's an array
            $row['post_tags'] = [$row['post_tags']];
        }

        if ($row['post_tags']) {
            // compare industry and post tags
            $industriesCompare = array_uintersect($data['industries'], $row['post_tags'], 'strcasecmp');

            // post industry
            if ($industriesCompare) {
                $data['rows'][$r]['post_industry'] = true;
            }
        }
    }

    // check for flags
    foreach ($data['rows'] as $r => $row) {
        // post tips flag
        if (isset($row['sms_match']) &&
            isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement'] &&
            $row['post_flag']) {
            $data['rows'][$r]['post_tips_flag'] = true;
        }

        // post tips
        if (isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement']) {
            $data['rows'][$r]['post_tips'] = true;
        }

        // post notify flag
        if (isset($row['sms_match']) &&
            $row['post_flag']) {
            $data['rows'][$r]['post_notify_flag'] = true;
        }

        // get post form
        $request->setStage('post_id', $row['post_id']);
        cradle()->trigger('form-post', $request, $response);
        if (!$response->isError()) {
            $data['rows'][$r]['post_form'] = $response->getResults();
        }
    }

    // Checks for location
    if (isset($data['location'])) {
        // Checks if the location is a string
        if (!is_array($data['location'])) {
            $data['location'] = [$data['location']];
        }

        $data['locationImplode'] = array();

        // Loops through the locations
        foreach ($data['location'] as $location) {
            $data['locationImplode'][] = $location;
        }

        // Implodates the location
        $data['locationImplode'] = implode('&location[]=', $data['locationImplode']);
    }

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';

    //determine the keywords
    $keywords = array_merge(
        [
            'job opportunities',
            'job seekers',
            'talent matching',
            'apply',
            'search talent'
        ],
        $keywords
    );

    $keywords = array_slice($keywords, 0, 5);

    // Set the meta
    $meta['title'] = 'Job seekers and job hiring opportunities in the '
        . $request->getSession('country');
    $meta['description'] = 'Easily search for job openings and job seekers.'
        . ' Be a part of the fastest growing job community in the '
        . $request->getSession('country');

    //set content
    if (!$response->getPage('title')) {
        $response->setPage('title', $data['wall_title']);
    }

    if (!$response->getMeta('title')) {
        $response->addMeta('title', $meta['title']);
    }

    if (!$response->getMeta('description')) {
        $response->addMeta('description', $meta['description']);
    }

    if (!$response->getMeta('keywords')) {
        $response->addMeta('keywords', strtolower(implode(',', $keywords)));
    }

    if (!$response->getMeta('image')) {
        $response->addMeta('image', $image);
    }

    $request->removeStage();

    // prepare list of school
    $request->setStage('range', '0');
    cradle()->trigger('school-search', $request, $response);
    $data['school'] = $response->getResults('rows');
    foreach ($data['school'] as $key => $value) {
        $data['school'][$key] = $value['school_name'];
    }
    // remove special character
    $data['school'] = array_map('strtolower', $data['school']);
    $data['school'] = preg_replace('/[^a-z0-9\s]/', '', $data['school']);

    // check for post school
    foreach ($data['rows'] as $r => $row) {
        // compare post tags and school
        $schoolCompare = array_uintersect($data['school'], $row['post_tags'], 'strcasecmp');

        // if schoolCompare has value set post schoo flag
        if (!empty($schoolCompare)) {
            $data['rows'][$r]['post_school_flag'] = true;
        }
    }

    // check if all tips have value
    foreach ($data['rows'] as $key => $value) {
        if ($value['post_type'] == 'seeker') {
            // post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement'])
            ) {
                $data['rows'][$key]['post_tips'] = true;
            }
        } else {
            // post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement']) &&
                $value['post_experience']) {
                $data['rows'][$key]['post_tips'] = true;
            }
        }

        // set date today
        $today = strtotime(date("Y-m-d H:i:s"));
        // get post created date
        $postCreate = $value['post_created'];
        // add 14 days to post created
        $postCreate = strtotime(date('Y-m-d H:i:s', strtotime($postCreate. ' + 14 days')));
        // subtract 14 days after post created to date today
        $postHiredTips = $postCreate - $today;

        // setup expiring soon
        $expiringSoon = strtotime(date('Y-m-d H:i:s', strtotime($value['post_expires']. ' - 7 days'))) - $today;

        // if postHiredTips is less than or equal to zero
        if ($postHiredTips <= 0 || $expiringSoon <= 0) {
            // add post hired flag
            $data['rows'][$key]['post_hired_flag'] = true;
        }

        // check if there's a post related
        if (isset($value['post_related'])) {
            // check post related if post is greater than or equal to 14 days
            foreach ($value['post_related'] as $post_related_key => $val) {
                // set date today
                $today = strtotime(date("Y-m-d H:i:s"));

                // get post created date
                $postCreate = $val['post_created'];

                // add 14 days to post created
                $postCreate = strtotime(date('Y-m-d H:i:s', strtotime($postCreate. ' + 14 days')));

                // subtract 14 days after post created to date today
                $postHiredTips = $postCreate - $today;

                // if postHiredTips is less than or equal to zero
                if ($postHiredTips <= 0) {
                    // add post hired flag
                    $data['rows'][$key]['post_related'][$post_related_key]['post_hired_flag'] = true;
                }
            }
        }
    }

    $class = 'page-post-search branding';
    $body = cradle('/app/www')->template('post/search', $data, [
        'post_actions',
        'partial_resumedownload',
        'post_banner',
        'post_list',
        'post_poster-tips',
        'post_poster',
        'post_profile',
        'post_seeker-tips',
        'post_seeker',
        'post_sorts',
        'post/modal_arrangement',
        'post/modal_completeness',
        'post/modal_experience',
        'post/modal_industry',
        'post/modal_location',
        'post/modal_school',
        'post/modal_resume',
        'post/modal_popup',
        'post/modal_question',
        'post/modal_remove',
        '_modal-profile-completeness'
    ]);

    //for facebook pixel tracker
    $response->setPage('page', 'search');

    $response
        ->setPage('class', $class)
        ->setPage('search', $query)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render Post Featured
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/featured', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data

    //default range
    $range = 15;

    //post_position
    if ($request->getStage('position')) {
        // check post_position if array
        if (is_array($request->getStage('position'))) {
            $request->setStage(
                'post_position',
                $request->getStage('position')
            );
        } else {
            $request->setStage(
                'filter',
                'post_position',
                $request->getStage('position')
            );
        }

        // check if filter thru get
        if ($request->hasGet('position')) {
            $request->removeStage('post_position');
            $request->setStage(
                'filter',
                'post_position',
                $request->getGet('position')
            );
        }
    }

    //post_location
    if ($request->getStage('location')) {
        $request->setStage(
            'filter',
            'post_location',
            $request->getStage('location')
        );
    }

    //post_tags
    if ($request->getStage('tag')) {
        $request->setStage(
            'post_tags',
            $request->getStage('tag')
        );
    }

    // sort shortcuts
    $request->setStage('sorting', 'Sort By');
    switch ($request->getStage('sort')) {
        case 'popular':
            $request->setStage('sorting', 'By Popular');
            $request->setStage('order', 'post_like_count', 'DESC');
            break;
        case 'latest':
            $request->setStage('sorting', 'By Latest');
            $request->setStage('order', 'post_updated', 'DESC');
            break;
        case 'seeker':
            $request->setStage('sorting', 'By Seeker');
            $request->setStage('order', 'post_type', 'DESC');
            break;
        case 'company':
            $request->setStage('sorting', 'By Company');
            $request->setStage('order', 'post_type', 'ASC');
            break;

        default:
            $request->setStage('order', 'post_flag', 'DESC');
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
            'post_flag',
            'post_type'
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
            'post_type'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    if (!$request->hasStage('geo_point')) {
        // this is temporary. error when sorting poster/seeker
        $request->setGet('noindex', true);
    }

    // set the data
    $data = $request->getStage();

    // get the unpromoted post
    $request->setStage('range', $range);
    // set the post flag
    $request->setStage('filter', 'post_flag', [0, 3]);
    // trigger post search
    cradle()->trigger('post-search', $request, $response);
    // set the unpromoted post
    $unpromotedPost = $response->getResults('rows');
    $data['total'] = $response->getResults('total');

    // get the sponsored post
    // set the sponsored range 5
    $request->setStage('range', 5);
    // set the start for sponsored
    $request->setStage(
        'start',
        $request->getStage('start_sponsored') ? $request->getStage('start_sponsored') : 0
    );
    // set the post flag
    $request->setStage('filter', 'post_flag', 1);
    // trigget the post search
    cradle()->trigger('post-search', $request, $response);
    // set the promoted post
    $promotedPost = $response->getResults('rows');

    $data['rows'] = $unpromotedPost;
    $data['range'] = $range;
    $data['start_sponsored'] = $request->getStage('start_sponsored') ?
        $request->getStage('start_sponsored') : 5;


    // scatter the post
    if (!empty($promotedPost)) {
        $e = count($promotedPost);
        $f = count($unpromotedPost);
        $j = $f / $e;

        $k = 0;
        $i = 0;

        $newRows = [];
        foreach ($unpromotedPost as $unpromoted) {
            $newRows[] = $unpromotedPost[$i];
            if ($i % $j == 0) {
                if (array_key_exists($k, $promotedPost)) {
                    $newRows[] = $promotedPost[$k];
                    $k++;
                }
            }
            $i++;
        }

        $data['rows'] = $newRows;
    }

    // group the post if same profile id
    $previousProfileId = null;
    $previousKeyValue = null;
    foreach ($data['rows'] as $r => $row) {
        // do not include if featured post in a group
        if ($row['post_flag'] == 1) {
            continue;
        }

        // the the same profile id group together
        if ($previousProfileId == $row['profile_id']) {
            // the the group to post related
            $data['rows'][$previousKeyValue]['post_related'][] = $row;
            // unset the row
            unset($data['rows'][$r]);
            // continue to life
            continue;
        }

        // set the previous key
        $previousKeyValue = $r;
        // set the previous profile id
        $previousProfileId = $row['profile_id'];
    }

    //----------------------------//
    // 3. Render Template
    $request->removeStage('filter');
    $request->removeStage('order');
    $request->removeStage('start');

    // set the industries
    $data['industries'] = $this->package('global')->config('industries');

    foreach ($data['rows'] as $r => $row) {
        // check profile id and post type
        if ($request->getSession('me', 'profile_id') == $row['profile_id'] &&
            $row['post_type'] == 'poster') {
            $request->removeStage();
            // set area locate to stage
            $request->setStage([
                'area_locate' => $row['post_location']
            ]);

            // trigger area-search
            cradle()->trigger('area-search', $request, $response);

            // get the results
            if ($response->getResults('total')) {
                $data['rows'][$r]['post_location_flag'] = true;
            }
        }

        // to detect if sms-match is checked before
        if (in_array('sms-match', $row['post_notify'])) {
            $data['rows'][$r]['sms_match'] = 1;
        }

        //in older versions, tags can appear as strings
        if (is_string($row['post_tags'])) {
            //lets make sure it's an array
            $row['post_tags'] = [$row['post_tags']];
        }

        if ($row['post_tags']) {
            // compare industry and post tags
            $industriesCompare = array_uintersect(
                $data['industries'],
                $row['post_tags'],
                'strcasecmp'
            );

            // post industry
            if ($industriesCompare) {
                $data['rows'][$r]['post_industry'] = true;
            }
        }
    }

    // check for flags
    foreach ($data['rows'] as $r => $row) {
        // post tips flag
        if (isset($row['sms_match']) &&
            isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement'] &&
            $row['post_flag']) {
            $data['rows'][$r]['post_tips_flag'] = true;
        }

        // post tips
        if (isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement']) {
            $data['rows'][$r]['post_tips'] = true;
        }

        // post notify flag
        if (isset($row['sms_match']) &&
            $row['post_flag']) {
            $data['rows'][$r]['post_notify_flag'] = true;
        }

        // check if post type seeker
        if ($row['post_type'] == 'seeker') {
            $request->setStage('profile_id', $row['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            // check for error
            if ($response->getResults()) {
                $data['rows'][$r]['profile_information'] = $response->getResults();
            }
        }
    }

    // get most popular terms
    $request->setStage('order', ['term_hits' => 'DESC']);
    $request->setStage('range', 9);
    $request->setStage('not_url', true);
    cradle()->trigger('term-search', $request, $response);
    $data['terms'] = $response->getResults('rows');
    $request->removeStage('range');

    if ($request->getSession('me', 'profile_id') &&
        $request->getSession('me', 'profile_company') !== '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'view_search');
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned '.$experience. ' experience point';

        //add a experience flash
        cradle('global')->setExperienceFlash($message);
    }


    if ($request->hasStage('featured_keywords')) {
        $featured_keywords = $request->getStage('featured_keywords');
    }

    // remove stage
    $request->removeStage();

    if (isset($featured_keywords)) {
        $request->setStage('featured_keywords', $featured_keywords);
    }

    $blogTags = isset($data['featured_industry']) ?
        $data['featured_industry'] : (isset($data['position']) ? $data['position'][0]['position_name']:
        (isset($data['location']) ? ($data['location']) : $data['featured_education']));

    // set stage for blog
    $request->setStage('blog_keywords', $blogTags);
    $request->setStage('range', 3);
    $request->setStage('order', ['blog_created' => 'DESC']);
    cradle()->trigger('blog-search', $request, $response);
    $data['range'] = 15;
    // set the blog
    $data['blogs'] = $response->getResults('rows');
    $data['page'] = 'featured-page';
    $class = 'page-post-featured branding';

    $body = cradle('/app/www')->template('post/featured', $data, [
        'post_actions',
        'partial_resumedownload',
        'post_poster',
        'post_seeker',
        'post_sorts',
        'post_poster-tips',
        'post_seeker-tips',
        'post/modal_arrangement',
        'post/modal_experience',
        'post/modal_industry',
        'post/modal_location',
        'post/modal_school',
        'post/modal_resume',
        'post/modal_popup',
        'post/modal_remove',
        'feature-head',
        'partial_seekersearch',
        'partial_postersearch',
        '_modal-profile-completeness'
    ]);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    $response
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');

/**
 * Render the Post Create Poster Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/create/poster', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    if ($request->hasStage('clear')) {
        $request->removeSession('post/create/poster/stash');
    }

    // if ($request->getSession('post/create/poster/stash', 'post_method') === 'post') {
    //     return cradle()->triggerRoute('post', '/post/create/poster', $request, $response);
    // }

    // if (isset($stash['post_method']) && $stash['post_method'] === 'post') {
    //     return cradle()->triggerRoute('post', '/post/create/poster', $request, $response);
    // }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //gets the amount of credits to be used
    if ($request->getSession('me')) {
        //update profile session
        cradle()->trigger('profile-session', $request, $response);

        $request->setStage('post_action', 'create');
        $request->setGet('noindex', true);
        $request->setStage('filter', 'profile_id', $request->getSession('me', 'profile_id'));

        cradle()->trigger('post-get-credit', $request, $response);

        //if not unlimited post
        if (!$request->hasSession('me', 'profile_package')
            || !in_array('unlimited-post', $request->getSession('me', 'profile_package'))) {
            $data['post_count'] = $response->getResults('total');
            $data['cost'] = cradle('global')->config('credits', 'extra-post');
            $data['cost'] = pow(2, $data['post_count'] - 4) * ($data['cost'] / 2);
        }
    }

    if ($request->hasStage('action')) {
        $data['action'] = $request->getStage('action');
    }

    if (!isset($data['item']['post_name'])) {
        $data['item']['post_name'] = $request->getSession(
            'post/create/poster/stash',
            'post_name'
        );
    }

    if (!isset($data['item']['post_position'])) {
        $data['item']['post_position'] = $request->getSession(
            'post/create/poster/stash',
            'post_position'
        );
    }

    if (!isset($data['item']['post_location'])) {
        $data['item']['post_location'] = $request->getSession(
            'post/create/poster/stash',
            'post_location'
        );
    }

    if (!isset($data['item']['post_experience'])) {
        $data['item']['post_experience'] = $request->getSession(
            'post/create/poster/stash',
            'post_experience'
        );
    }

    if (!isset($data['item']['post_type'])) {
        $data['item']['post_type'] = $request->getSession(
            'post/create/poster/stash',
            'post_type'
        );
    }

    if (!isset($data['item']['post_notify'])) {
        $data['item']['post_notify'] = $request->getSession(
            'post/create/poster/stash',
            'post_notify'
        );
    }

    if (!isset($data['item']['post_promote'])) {
        $data['item']['post_promote'] = $request->getSession(
            'post/create/poster/stash',
            'post_promote'
        );
    }

    if (!isset($data['item']['post_tags'])) {
        $data['item']['post_tags'] = $request->getSession(
            'post/create/poster/stash',
            'post_tags'
        );
    }


    if (!isset($data['item']['post_email']) && $request->hasSession('me', 'profile_email')) {
        $data['item']['post_email'] = $request->getSession('me', 'profile_email');
    }

    if (!isset($data['item']['post_phone']) && $request->hasSession('me', 'profile_phone')) {
        // format phone_number since database remove starting zeros
        $phoneFormatted = '0'.$request->getSession('me', 'profile_phone');
        $data['item']['post_phone'] = $phoneFormatted;
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    $auth = $request->getSession('me', 'auth_active');
    if (!$auth) {
        $data['not_verified'] = 1;
    }

    // at this point all validations are done
    // get all the list of currencies and their symbols
    cradle()->trigger('currency-search', $request, $response);
    $data['currency'] = $response->getResults();

    //if logged in & not company
    if ($request->hasSession('me')
        && empty($request->getSession('me', 'profile_company'))
        && isset($data['item']['post_name'])
        && !empty($data['item']['post_name'])) {
        // convert to company
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $request->setStage('profile_company', $data['item']['post_name']);
        cradle()->trigger('profile-update', $request, $response);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-create post-form branding';
    $title = cradle('global')->translate('Jobayan - Create a New Post');
    $data['title'] = cradle('global')->translate('Create a New Post');

    if ($request->hasSession('post/create/poster/stash')) {
        if (!$request->getStage('credits')) {
            $body = cradle('/app/www')->template(
                'post/create/poster',
                $data,
                ['post/modal_credit', 'post/modal_update']
            );
        } else {
            $body = cradle('/app/www')->template(
                'post/update/poster',
                $data,
                ['post/modal_credit', 'post/modal_update']
            );
        }
    } else {
        $body = cradle('/app/www')->template(
            'post/update/poster',
            $data,
            [
                'post/modal_credit',
                'post/modal_update',
                '_modal-profile-completeness'
            ]
        );
    }

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Post Create Seeker Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/create/seeker', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    if ($request->hasStage('clear')) {
        $request->removeSession('post/create/seeker/stash');
    }

    // if ($request->getSession('post/create/seeker/stash', 'post_method') === 'post') {
    //     $request->removeSession('post/create/seeker/stash', 'post_method');
    //     return cradle()->triggerRoute('post', '/post/create/seeker', $request, $response);
    // }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($request->hasStage('action')) {
        $data['action'] = $request->getStage('action');
    }

    if (!isset($data['item']['post_name'])) {
        $data['item']['post_name'] = $request->getSession(
            'post/create/seeker/stash',
            'post_name'
        );
    }

    if (!isset($data['item']['post_position'])) {
        $data['item']['post_position'] = $request->getSession(
            'post/create/seeker/stash',
            'post_position'
        );
    }

    if (!isset($data['item']['post_location'])) {
        $data['item']['post_location'] = $request->getSession(
            'post/create/seeker/stash',
            'post_location'
        );
    }

    if (!isset($data['item']['post_notify'])
        && $request->hasSession(
            'post/create/seeker/stash',
            'post_notify'
        )
    ) {
        $data['item']['post_notify'] = $request->getSession(
            'post/create/seeker/stash',
            'post_notify'
        );
    }

    if (!isset($data['item']['post_email']) && $request->hasSession('me', 'profile_email')) {
        $data['item']['post_email'] = $request->getSession('me', 'profile_email');
    }

    if (!isset($data['item']['post_phone']) && $request->hasSession('me', 'profile_phone')) {
        $data['item']['post_phone'] = $request->getSession('me', 'profile_phone');
    }

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    $auth = $request->getSession('me', 'auth_active');
    if (!$auth) {
        $data['not_verified'] = 1;
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-create post-form branding';
    $title = cradle('global')->translate('Jobayan - Create a New Post');
    $data['title'] = cradle('global')->translate('Create a New Post');

    if ($request->hasSession('post/create/seeker/stash')) {
        if (!$request->getStage('credits')) {
            $body = cradle('/app/www')->template(
                'post/create/seeker',
                $data,
                ['post/modal_credit', 'post/modal_update']
            );
        }
    } else {
        $body = cradle('/app/www')->template(
            'post/update/seeker',
            $data,
            [
                'post/modal_credit',
                'post/modal_update',
                '_modal-profile-completeness'
            ]
        );
    }

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Post Copy Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/copy/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('post-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/post/search');
    }

    $type = $response->getResults('post_type');

    $request
    ->setPost($response->getResults())
    ->setStage('action', '/post/create/' . $type);

    cradle()->triggerRoute('get', '/post/create/' . $type, $request, $response);
});

/**
 * Render the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/update/poster/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('post-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/post/search');
        }

        if ($response->getResults('post_type') === 'seeker') {
            return cradle('global')->redirect(
                '/post/update/seeker/' .
                $request->getStage('post_id')
            );
        }

        //----------------------------//
        // 2. Prepare Data
        $data = ['item' => $request->getPost()];

        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);

        //if no item
        if (empty($data['item'])) {
            $data['item'] = $response->getResults();
        }

        // get all the list of currencies and their symbols
        cradle()->trigger('currency-search', $request, $response);
        $data['currency'] = $response->getResults();

        // to detect if sms-match is checked before
        if (in_array('sms-match', $data['item']['post_notify'])) {
            $data['item']['sms_match'] = 1;
        }

        // to detect if sms-interest is checked before
        if (in_array('sms-interest', $data['item']['post_notify'])) {
            $data['item']['sms_interest'] = 1;
        }

        $data['item']['post_phone'] = '0'.$data['item']['post_phone'];
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    if (isset($data['post_notify'])) {
        if (in_array('sms-match', $data['post_notify'])) {
            $data['sms_match'] = 1;
        }
        if (in_array('sms-interest', $data['post_notify'])) {
            $data['sms_interest'] = 1;
        }
    }

    $data['industries'] = $this->package('global')->config('industries');

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-update post-form branding';
    $title = cradle('global')->translate('Jobayan - Updating Post');
    $data['title'] = cradle('global')->translate('Updating Post');
    $body = cradle('/app/www')->template('post/update/poster', $data, [
        'post/modal_credit']);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-www-page');

/**
 * Render the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/update/seeker/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    $data = ['item' => $request->getPost()];
    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('post-detail', $request, $response);
        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/post/search');
        }

        if ($response->getResults('post_type') === 'poster') {
            return cradle('global')->redirect(
                '/post/update/poster/' .
                $request->getStage('post_id')
            );
        }

        //----------------------------//
        // 2. Prepare Data
        $data = ['item' => $request->getPost()];

        //add CDN
        $config = $this->package('global')->service('s3-main');
        $data['cdn_config'] = File::getS3Client($config);

        //if no item
        if (empty($data['item'])) {
            $data['item'] = $response->getResults();
        }
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-update post-form branding';
    $title = cradle('global')->translate('Jobayan - Updating Post');
    $data['title'] = cradle('global')->translate('Updating Post');
    $body = cradle('/app/www')->template('post/update/seeker', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-www-page');

/**
 * Process the Post Create Poster Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/post/create/poster', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    //before continuing we need to get more data
    //are they not logged in ?
    if (!$request->hasSession('me')) {
        //stash the $data
        $stash = $request->getStage();
        $stash['post_method'] = 'post';
        $request->setSession('post/create/poster/stash', $stash);

        //which was the verify method?
        if ($request->getStage('post_verify') === 'linkedin') {
            return cradle('global')->redirect('/login/linkedin?redirect_uri=/post/create/poster');
        }

        return cradle('global')->redirect('/login/facebook?redirect_uri=/post/create/poster');
    }

    //----------------------------//
    // 2. Prepare Data
    $stash = $request->getSession('post/create/poster/stash');
    if (is_array($stash)) {
        foreach ($stash as $key => $value) {
            $request->setStage($key, $value);
        }
    }

    $errors = [];

    //validate account
    if ($request->hasSession('me') && !$request->getSession('me', 'auth_active')) {
        $errors = ['auth_active' => 'Your account is not activated.'];
    }

    if ($errors) {
        $err = 'Invalid Parameters';
        if (isset($errors['auth_active'])) {
            $err = $errors['auth_active'];
        }

        $response->setError(true, $err);
        $response->set('json', 'validation', $errors);

        return cradle()->triggerRoute('get', '/post/create/poster', $request, $response);
    }

    //delete stash
    $request->removeSession('post/create/poster/stash');

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_resume is disallowed
    $request->removeStage('post_resume');

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
    } else if ($request->hasStage('post_tags') && is_array($request->getStage('post_tags'))) {
        $post_tags_arr = $request->getStage('post_tags');
        $post_tags_arr = array_map('strtolower', $post_tags_arr);
        $post_tags_arr = preg_replace('/[^a-z0-9\s]/', '', $post_tags_arr);

        $request->setStage('post_tags', $post_tags_arr);
    }

    //if post_salary_min has no value make it null
    if ($request->hasStage('post_salary_min') && !$request->getStage('post_salary_min')) {
        $request->setStage('post_salary_min', null);
    }

    //if post_salary_max has no value make it null
    if ($request->hasStage('post_salary_max') && !$request->getStage('post_salary_max')) {
        $request->setStage('post_salary_max', null);
    }

    // Converts Min Salary
    if (!empty($request->getStage('post_salary_min'))) {
        $salMin = str_replace(',', '', $request->getStage('post_salary_min'));
        $request->setStage('post_salary_min', $salMin);
    }

    // Converts Max Salary
    if (!empty($request->getStage('post_salary_max'))) {
        $salMax = str_replace(',', '', $request->getStage('post_salary_max'));
        $request->setStage('post_salary_max', $salMax);
    }

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('post_type', 'poster');

    if (!$request->getStage('post_email')) {
        $request->setStage('post_email', $request->getSession('me', 'profile_email'));
    }

    if (!$request->getStage('post_phone')) {
        $request->setStage('post_phone', $request->getSession('me', 'post_phone'));
    }

    //update profile session
    cradle()->trigger('profile-session', $request, $response);

    $request->setStage('today', true);
    $request->setStage('filter', ['profile_id' => $request->getSession('me', 'profile_id')]);
    cradle()->trigger('post-search', $request, $response);
    $postCount = $response->getResults('total');

    // Variable declaration
    $restore = array();

    // Gets the tags and notify before we remove them from stage
    if ($request->hasStage('post_tags')) {
        $restore['post_tags'] = $request->getStage('post_tags');
    }

    // Gets the tags and notify before we remove them from stage
    if ($request->hasStage('post_notify')) {
        $restore['post_notify'] = $request->getStage('post_notify');
    }

    //get the all the total number of posts in a profile
    $request->removeStage('post_id');
    $request->removeStage('post_expires');
    $request->removeStage('today');
    $request->removeStage('post_tags');
    $request->removeStage('post_notify');
    cradle()->trigger('post-search', $request, $response);
    $postCountAll = $response->getResults('total');

    //update profile session
    cradle()->trigger('profile-session', $request, $response);

    $request->setStage('today', true);
    $request->setStage('filter', 'profile_id', $request->getSession('me', 'profile_id'));
    cradle()->trigger('post-search', $request, $response);

    //if not unlimited post
    if (!$request->hasSession('me', 'profile_package')
        || !in_array('unlimited-post', $request->getSession('me', 'profile_package'))) {
        $request->setStage('post_action', 'create');
        $request->setGet('noindex', true);
        cradle()->trigger('post-get-credit', $request, $response);

        $postCount = $response->getResults('total');

        //count post
        if ($postCount >= 5) {
            $cost = cradle('global')->config('credits', 'extra-post');
            $cost = pow(2, $postCount - 4) * ($cost / 2);

            if ($cost > $request->getSession('me', 'profile_credits')) {
                $response
                    ->setError(true, 'Credits Required')
                    ->set('json', 'validation', [
                        'error'=>true,
                        'credits_required' => 'Not enough credits to proceed'
                    ]);

                return cradle()->triggerRoute('get', '/post/create/poster', $request, $response);
            }
        }
    }


    //if logged in
    if ($request->hasSession('me')) {
        //if company enable sms-match package
        if ($request->hasSession('me', 'profile_package')
            &&in_array(
                'sms-match',
                $request->getSession('me', 'profile_package')
            )) {
            //set notify
            $notify = $request->hasStage('post_notify')
                ? array_merge($request->getStage('post_notify'), ['sms-match'])
                : ['sms-match'];

            $request->setStage('post_notify', $notify);
        }
        //if company enable sms-interest package
        if ($request->hasSession('me', 'profile_package')
            && in_array('sms-interest', $request->getSession('me', 'profile_package'))) {
            // if there's a post_notify
            $notify = $request->hasStage('post_notify')
                // merge existing content and sms-interest
                ? array_merge($request->getStage('post_notify'), ['sms-interest'])
                // else just add sms-interest
                : ['sms-interest'];
            // set new post_notify to stage
            $request->setStage('post_notify', $notify);
        }
    }

    //----------------------------//
    // 3. Process Request
    if (!empty($restore)) {
        // Loops through the restore
        foreach ($restore as $index => $value) {
            $request->setStage($index, $value);
        }
    }

    // Create the post
    cradle()->trigger('post-create', $request, $response);
    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/post/create/poster', $request, $response);
    }

    //----------------------------//
    // 5. Process Service
    //it was good
    $request->removeSession('post/create/poster/stash');

    if ($postCount >= 5) {
        $results = $response->getResults();

        $request
            ->setStage('profile_id', $request->getSession('me', 'profile_id'))
            ->setStage('service_name', 'Extra Post')
            ->setStage('service_meta', [
                'post_id' => $results['post_id']
            ])
            ->setStage('service_credits', $cost);

        cradle()->trigger('service-create', $request, $response);

        if (!$response->isError()) {
            $request->setSession(
                'me',
                'profile_credits',
                $request->getSession('me', 'profile_credits') - $cost
            );
        }
    }

    //update profile session
    cradle()->trigger('profile-session', $request, $response);

    // add create post experience
    $experience = cradle('global')->config('experience', 'create_post');
    $request->setStage('profile_experience', $experience);
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $this->trigger('profile-add-experience', $request, $response);
    $message = 'You earned '.$experience. ' experience points';

    //add a flash
    cradle('global')->flash('Post was Created.', 'success');
    cradle('global')->setExperienceFlash($message);

    // first post badge
    if ($postCountAll == 0) {
        $achievement = cradle('global')->config('achievements', 'post_1');
        $request->setStage('profile_achievement', 'post_1');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = 'earned this badge by posting your 1st job post';

        // add achievement badge
        cradle('global')->setBadge($achievement['image'], $message);
    }

    // 10th post badge
    if ($postCountAll == 9) {
        $achievement = cradle('global')->config('achievements', 'post_10');
        $request->setStage('profile_achievement', 'post_10');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = 'earned this badge by posting your 10th job post';

        // add achievement badge
        cradle('global')->setBadge($achievement['image'], $message);
    }

    // 50th post badge
    if ($postCountAll == 49) {
        $achievement = cradle('global')->config('achievements', 'post_50');
        $request->setStage('profile_achievement', 'post_50');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = 'earned this badge by posting your 50th job post';

        // add achievement badge
        cradle('global')->setBadge($achievement['image'], $message);
    }

    // 100th post badge
    if ($postCountAll == 99) {
        $achievement = cradle('global')->config('achievements', 'post_100');
        $request->setStage('profile_achievements', 'post_100');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = 'earned this badge by posting your 100th job post';

        // add achievement badge
        cradle('global')->setBadge($achievement['image'], $message);
    }

    //redirect
    cradle('global')->redirect('/post/search');
});

/**
 * Process the Post Create Seeker Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/post/create/seeker', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //before continuing we need to get more data
    //are they not logged in ?
    if (!$request->hasSession('me')) {
        //stash the $data
        $stash = $request->getStage();
        $stash['post_method'] = 'post';
        $request->setSession('post/create/seeker/stash', $stash);

        //which was the verify method?
        if ($request->getStage('post_verify') === 'linkedin') {
            return cradle('global')->redirect('/login/linkedin?redirect_uri=/post/create/seeker');
        }

        return cradle('global')->redirect('/login/facebook?redirect_uri=/post/create/seeker');
    }

    //----------------------------//
    // 2. Prepare Data
    $stash = $request->getSession('post/create/seeker/stash');
    if (is_array($stash)) {
        foreach ($stash as $key => $value) {
            $request->setStage($key, $value);
        }
    }

    $errors = [];

    //validate account
    if ($request->hasSession('me') && !$request->getSession('me', 'auth_active')) {
        $message = cradle('global')->translate('Your account is not activated.');
        $errors = ['auth_active' => $message];
    }

    //validate experience
    if ($request->getStage('post_experience') < 0) {
        $message = cradle('global')->translate('Experience should not lower than zero.');
        $errors = ['post_experience' => $message];
    }

    if ($request->getStage('post_experience') > 60) {
        $message = cradle('global')->translate('Experience should not greater than sixty.');
        $errors = ['post_experience' => $message];
    }

    // Checks for errors
    if (!empty($errors)) {
        // Default error message
        $err = cradle('global')->translate('All fields are required.');

        // Checks for experience error
        if (isset($errors['post_experience'])
            && strpos($errors['post_experience'], 'Experience') !== false) {
            $err = $errors['post_experience'];
        }

        // Checks for auth active error
        if (isset($errors['auth_active'])) {
            $err = $errors['auth_active'];
        }

        $response->setError(true, $err);
        $response->set('json', 'validation', $errors);

        // Remove session error after displaying error message
        if ($errors) {
            foreach ($errors as $key => $value) {
                $request->removeSession('post/create/seeker/stash', $key);
            }
        }

        return cradle()->triggerRoute('get', '/post/create/seeker', $request, $response);
    }

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_link is disallowed
    $request->removeStage('post_link');

    //post_banner is disallowed
    $request->removeStage('post_banner');

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
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    if (!$request->getStage('post_email')) {
        $request->setStage('post_email', $request->getSession('me', 'profile_email'));
    }

    if (!$request->getStage('post_phone')) {
        $request->setStage('post_phone', $request->getSession('me', 'post_phone'));
    }

     //if post_tags has no value make it null
    if ($request->hasStage('post_tags') && !$request->getStage('post_tags')) {
        $request->setStage('post_tags', null);
    } else if ($request->hasStage('post_tags') && is_array($request->getStage('post_tags'))) {
        $post_tags_arr = $request->getStage('post_tags');
        $post_tags_arr = array_map('strtolower', $post_tags_arr);
        $post_tags_arr = preg_replace('/[^a-z0-9\s]/', '', $post_tags_arr);

        $request->setStage('post_tags', $post_tags_arr);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-create', $request, $response);


    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/post/create/seeker', $request, $response);
    }

    //if logged in & type is poster
    if ($request->hasSession('me')
        && !empty($request->getSession('me', 'profile_company'))
        && $request->hasStage('post_name')
        && !empty($request->getStage('post_name'))) {
        // convert to seeker
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $request->setStage('profile_company', '');
        cradle()->trigger('profile-update', $request, $response);
    }

    //it was good
    $request->removeSession('post/create/seeker/stash');

    //add a flash
    cradle('global')->flash('Post was Created', 'success');

    //redirect
    cradle('global')->redirect('/post/search');
});

/**
 * Process the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/post/update/poster/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();
    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('post-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/post/search');
    }

    // check for sms match
    if (in_array('sms-match', $response->getResults('post_notify'))) {
        // set the notify variable
        $notify = $request->getStage('post_notify');
        // check if notify is empty
        if (empty($notify)) {
            $notify = [];
        }
        // push the sms match to notify variable
        array_push($notify, 'sms-match');
        // set the post notify to stage
        $request->setStage('post_notify', $notify);
    }

    // check for sms interest
    if (in_array('sms-interest', $response->getResults('post_notify'))) {
        // preserve the content
        $notify = $request->getStage('post_notify');
        // check if notify is empty
        if (empty($notify)) {
            $notify = [];
        }
        // push the sms interest to notify variable
        array_push($notify, 'sms-interest');
        // set the post notify to stage
        $request->setStage('post_notify', $notify);
    }

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
    } else if ($request->hasStage('post_tags') && is_array($request->getStage('post_tags'))) {
        $post_tags_arr = $request->getStage('post_tags');
        $post_tags_arr = array_map('strtolower', $post_tags_arr);
        $post_tags_arr = preg_replace('/[^a-z0-9\s]/', '', $post_tags_arr);

        $request->setStage('post_tags', $post_tags_arr);
    }

    //if post_salary_min has no value make it null
    if ($request->hasStage('post_salary_min') && !$request->getStage('post_salary_min')) {
        $request->setStage('post_salary_min', null);
    }

    //if post_salary_max has no value make it null
    if ($request->hasStage('post_salary_max') && !$request->getStage('post_salary_max')) {
        $request->setStage('post_salary_max', null);
    }

    //validate experience
    if ($request->hasStage('post_experience') && $request->getStage('post_experience') < 0) {
        $response
            ->setError(true, 'Invalid Data')
            ->set('json', 'validation', [
                'error'=>true,
                'post_experience' => 'Experience should not lower than zero.'
            ]);

        $route = '/post/update/poster/' . $request->getStage('post_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    if (!$request->getStage('post_email')) {
        $request->setStage('post_email', $request->getSession('me', 'profile_email'));
    }

    if (!$request->getStage('post_phone')) {
        $request->setStage('post_phone', $request->getSession('me', 'post_phone'));
    }

    // Converts Min Salary
    if (!empty($request->getStage('post_salary_min'))) {
        $salMin = str_replace(',', '', $request->getStage('post_salary_min'));
        $request->setStage('post_salary_min', $salMin);
    }

    // Converts Max Salary
    if (!empty($request->getStage('post_salary_max'))) {
        $salMax = str_replace(',', '', $request->getStage('post_salary_max'));
        $request->setStage('post_salary_max', $salMax);
    }

    //update profile session
    cradle()->trigger('profile-session', $request, $response);

    //if logged in
    if ($request->hasSession('me')) {
        //if company enable sms-match package
        if ($request->hasSession('me', 'profile_package') &&
            in_array(
                'sms-match',
                $request->getSession('me', 'profile_package')
            )) {
            //set notify
            $notify = $request->hasStage('post_notify')
                ? array_merge($request->getStage('post_notify'), ['sms-match'])
                : ['sms-match'];

            $request->setStage('post_notify', $notify);
        }
        //if company enable sms-interest package
        if ($request->hasSession('me', 'profile_package') &&
            in_array(
                'sms-interest',
                $request->getSession('me', 'profile_package')
            )) {
            //set notify
            $notify = $request->hasStage('post_notify')
                ? array_merge($request->getStage('post_notify'), ['sms-interest'])
                : ['sms-interest'];

            $request->setStage('post_notify', $notify);
        }
    }


    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/post/update/poster/' . $request->getStage('post_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 5 Process Service
    //it was good
    //add a flash
    cradle('global')->flash('Post was Updated', 'success');

    //redirect
    cradle('global')->redirect('/post/search');
});

/**
 * Process the Post Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/post/update/seeker/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be logged in
    cradle('global')->requireLogin();

    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('post-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/post/search');
    }

    //----------------------------//
    // 2. Prepare Data

    //post_expires is disallowed
    $request->removeStage('post_expires');

    //post_link is disallowed
    $request->removeStage('post_link');

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

    //if post_banner has no value make it null
    if ($request->hasStage('post_banner') && !$request->getStage('post_banner')) {
        $request->setStage('post_banner', null);
    }

    //if post_detail has no value make it null
    if ($request->hasStage('post_detail') && !$request->getStage('post_detail')) {
        $request->setStage('post_detail', null);
    }

     //if post_tags has no value make it null
    if ($request->hasStage('post_tags') && !$request->getStage('post_tags')) {
        $request->setStage('post_tags', null);
    } else if ($request->hasStage('post_tags') && is_array($request->getStage('post_tags'))) {
        $post_tags_arr = $request->getStage('post_tags');
        $post_tags_arr = array_map('strtolower', $post_tags_arr);
        $post_tags_arr = preg_replace('/[^a-z0-9\s]/', '', $post_tags_arr);

        $request->setStage('post_tags', $post_tags_arr);
    }

    //validate experience
    if ($request->hasStage('post_experience') && $request->getStage('post_experience') < 0) {
        $response
            ->setError(true, 'Invalid Data')
            ->set('json', 'validation', [
                'error'=>true,
                'post_experience' => 'Experience should not lower than zero.'
            ]);

        $route = '/post/update/seeker/' . $request->getStage('post_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/post/update/seeker/' . $request->getStage('post_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash('Post was Updated', 'success');

    //redirect
    cradle('global')->redirect('/post/search');
});

/**
 * Process the Post Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/remove/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('permission', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Post was Removed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/post/search');
});

/**
 * Process the Post Renew
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/renew/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();


    // Prepare Data
    $data = $request->getStage();
    cradle()->trigger('post-detail', $request, $response);


    // Process Request
    $post = $response->getResults();
    $today = strtotime('now');
    $expires = strtotime('+1 month', $today);

    if ($post['post_type'] == 'seeker') {
        $expires = strtotime('+1 month', $expires);
    }

    $expires = date('Y-m-d H:i:s', $expires);

    $request->setStage($post);
    $request->setStage('post_action', 'renew');
    $request->setStage('post_restored', date('Y-m-d H:i:s'));

    cradle()->trigger('post-get-credit', $request, $response);

    //if not unlimited post
    if (!$request->hasSession('me', 'profile_package')
        || !in_array('unlimited-post', $request->getSession('me', 'profile_package'))) {
        $data['post_count'] = $response->getResults('total');
        $data['cost'] = cradle('global')->config('credits', 'extra-post');
        $data['cost'] = pow(2, $data['post_count'] - 4) * ($data['cost'] / 2);
    }

    // update only if unlimited post or post_count < 5
    if (!isset($data['post_count']) || (isset($data['post_count']) && $data['post_count'] < 5)) {
        $request->setStage('post_active', 1);
        $request->setStage('post_flag', 0);
        $request->setStage('post_expires', $expires);

        cradle()->trigger('post-update', $request, $response);

        //add a flash
        $message = cradle('global')->translate('Post was Renewed');
        cradle('global')->flash($message, 'success');
    } else if (isset($data['post_count']) && $data['post_count'] >= 5) {
        $cost = cradle('global')->config('credits', 'extra-post');
        $cost = pow(2, $data['post_count'] - 4) * ($cost / 2);

        if ($cost > $request->getSession('me', 'profile_credits')) {
            $response
                ->setError(true, 'Credits Required')
                ->set('json', 'validation', [
                    'error'=>true,
                    'credits_required' => 'Not enough credits to proceed'
                ]);
        }

        $newPost = [
            'profile_id' => $request->getSession('me', 'profile_id'),
            'post_active' => 1,
            'post_flag' => 0,
            'post_expires' => $expires
        ];

        $service = [
            'profile_id' => $request->getSession('me', 'profile_id'),
            'service_name' => 'Extra Post',
            'service_meta' => [
                'post_id' => $post['post_id']
            ],
            'service_credits' => $cost
        ];

        //not required
        $request->removeStage('post_phone');
        $request->setStage($newPost);
        //trigger job
        cradle()->trigger('post-update', $request, $response);
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
        }

        $request->setStage($service);
        //updat service
        cradle()->trigger('service-create', $request, $response);
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
        }

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
    } else {
        //add a flash
        $message = cradle('global')->translate('Post was Renewed');
        cradle('global')->flash($message, 'success');
    }

    if ($request->hasStage('redirect_uri')) {
        cradle('global')->redirect($request->getStage('redirect_uri'));
    }

    cradle('global')->redirect('/profile/post/search?post_expires=-1');
});

/**
 * Render the Post Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:post_slug/post-detail**', function ($request, $response) {
    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/');
    }

    $data = array_merge($request->getStage(), $response->getResults());

    // set stage the profile id
    $request->setStage(
        'profile_id',
        $data['profile_id']
    );

    // get profile information
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    // set the profile information to data
    $data['profile_information'] = $response->getResults();

    if ($data['profile_information']) {
        $request->setStage('information_id', $data['profile_information']['information_id']);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

        // trigget the job
        cradle()->trigger('check-information-download', $request, $response);

        // check for error
        if ($response->isError()) {
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/');
        }

        // reverse the checker. check if not downloaded set to true
        if ($response->getResults() != 'downloaded') {
            $data['profile_information']['profile_downloaded'] = true;
        }

        // check if user has unlimited download package
        if (!empty($request->hasSession('me', 'profile_package'))
            && in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
            $data['profile_information']['profile_downloaded'] = true;
        }

        // remove stage information id
        $request->removeStage('information_id');
    }

    // remove stage the profile id
    $request->removeStage('profile_id');

    // add the date today to check later if post is expired
    $data['today'] = date("Y-m-d H:i:s");

    // Checks if the post is not active
    if (!$data['post_active']) {
        // Redirect to the post listing page
        cradle('global')->redirect('/post/search?type='.$data['post_type']);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-detail branding';

    //determine the title
    $title = cradle('global')->translate(
        '%s from %s is Looking for a %s',
        $data['post_name'],
        $data['post_location'],
        $data['post_position']
    );

    $wallTitle = cradle('global')->translate(
        'Job opening at %s  for %s',
        $data['post_name'],
        $data['post_position']
    );

    //determine the detail
    $detail = cradle('global')->translate(
        '%s job post for %s in %s.',
        $data['post_name'],
        $data['post_position'],
        $data['post_location']
    );

    if ($data['post_type'] === 'seeker') {
        $title = cradle('global')->translate(
            '%s is Looking for Job in %s',
            $data['post_position'],
            $data['post_location']
        );

        $detail = cradle('global')->translate(
            '%s is a %s looking for a job in %s.',
            $data['post_name'],
            $data['post_position'],
            $data['post_location']
        );
    }

    $detail .= cradle('global')->translate(' Apply now, submit resume and get hired, free city jobs board.');

    //determine the keywords
    $keywords = array_merge(
        //important
        [
            str_replace([',', '  '], ' ', $data['post_name']),
            str_replace([',', '  '], ' ', $data['post_location'])
        ],
        //populate
        explode(' ', $data['post_position']),
        //fillers
        [
            'job',
            'hired',
            'apply',
            'resume'
        ]
    );

    $keywords = array_slice($keywords, 0, 5);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';
    if ($data['post_banner']) {
        $image = $data['post_banner'];
    } else if ($data['profile_image']) {
        $image = $data['profile_image'];
    }

    // set wall title based on post type (for SEO purposes)
    if ($data['post_type'] == 'poster') {
        $data['wall_title'] = $wallTitle;
    } else {
        $data['wall_title'] = $title;
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    $data['post_is_detail'] = true;

    $data['industries'] = $this->package('global')->config('industries');

    // check profile id and post type
    if ($request->getSession('me', 'profile_id') == $data['profile_id'] &&
        $data['post_type'] == 'poster') {
        $request->removeStage();
        // set area locate to stage
        $request->setStage([
            'area_locate' => $data['post_location']
        ]);

        // trigger area-search
        cradle()->trigger('area-search', $request, $response);

        // get the results
        if ($response->getResults('total')) {
            $data['post_location_flag'] = true;
        }
    }

    // to detect if sms-match is checked before
    if (isset($data['post_notify'])) {
        if (in_array('sms-match', $data['post_notify'])) {
            $data['sms_match'] = 1;
        }
    }

    // to detect if sms-interest is checked before
    if (isset($data['post_notify'])) {
        if (in_array('sms-interest', $data['post_notify'])) {
            $data['sms_interest'] = 1;
        }
    }

    // compare industry and post tags
    $industriesCompare = array_uintersect($data['industries'], $data['post_tags'], 'strcasecmp');

    // post industry
    if ($industriesCompare) {
        $data['post_industry'] = true;
    }

    // post tips flag
    if (isset($data['sms_match']) &&
        isset($data['post_location_flag']) &&
        isset($data['post_industry']) &&
        $data['post_experience'] &&
        $data['post_arrangement'] &&
        $data['post_flag']) {
        $data['post_tips_flag'] = true;
    }

    // post tips
    if (isset($data['post_location_flag']) &&
        isset($data['post_industry']) &&
        $data['post_experience'] &&
        $data['post_arrangement']) {
        $data['post_tips'] = true;
    }

    // post notify flag
    if (isset($data['sms_match']) &&
        $data['post_flag']) {
        $data['post_notify_flag'] = true;
    }

    if ($request->getRoute('variables', 1) == 'interested') {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $request->setStage('post_id', $data['post_id']);
        cradle()->trigger('post-like', $request, $response);

        if (!$response->isError()) {
            $data['post_like_count'] += 1;
            $data['post_sms_interest_count'] += 1;
        }
    }

    // Sets the not filter variable
    $notFilter = array();
    $notFilter['post_id'] = $data['post_id'];

    // Gets the related posts
    // Based on flag
    // Exclude current post from the search
    $request->setStage('filter', ['post_type' => 'poster', 'post_flag' => 1]);
    $request->setStage('not_filter', $notFilter);
    $request->setStage('range', 2);

    cradle()->trigger('post-search', $request, $response);
    $data['sponsored'] = $response->getResults();
    $data['sponsored'] = $data['sponsored']['rows'];

    // Remove filters from stage
    $request->removeStage('filter', 'post_flag');
    $request->removeStage('not_filter');
    $request->removeStage('range');

    $notFilter['post_id'] = array();
    $notFilter['post_id'][] = $data['post_id'];

    // Loops through the sponsored post
    foreach ($data['sponsored'] as $sponsored) {
        $notFilter['post_id'][] = $sponsored['post_id'];
    }

    // Gets the related posts
    // Based on tags and post type poster
    // Exclude current post from the search
    $request->setStage('post_tags', $data['post_tags']);
    $request->setStage('not_filter', $notFilter);
    $request->setStage('range', 3);
    $request->setStage('filter', ['post_type' => 'poster']);
    cradle()->trigger('post-search', $request, $response);
    $data['related'] = $response->getResults();
    $data['related'] = $data['related']['rows'];

    // Remove filters from stage
    $request->removeStage('post_tags');
    $request->removeStage('not_filter');
    $request->removeStage('range');

    $data['view_interested'] = 0;

    // Checks if there is a user logged in
    // Checks if there are is an interested list to show
    if ($request->hasSession('me') && !empty($data['likers'])) {
        // Gets the session
        $check = $request->getSession('me');

        // Checks the auth type
        if ($check['auth_type'] == 'admin') {
            $data['view_interested'] = 1;
        }

        // Check if the user is the poster
        if ($data['profile_id'] == $check['profile_id']) {
            $data['view_interested'] = 1;
        }
    }

    // remove filter
    $request->removeStage('filter');

    // set stage post location
    $request->setStage([
        'q' => [
            $data['post_location']
        ],
        'filter' => [
            'area_type' => 'city'
        ]
    ]);

    // search for area
    $this->trigger('area-search', $request, $response);

    // check for the results
    if ($response->getResults('rows')) {
        $provinceParent = $response->getResults('rows', 0, 'area_parent');
    } else {
        $provinceParent = 25877;
    }

    // remove stage filter and keywords
    $request->removeStage('filter');
    $request->removeStage('q');

    // set stage the province parent
    $request->setStage([
        'area_id' => $provinceParent
    ]);

    // get the province
    $this->trigger('area-detail', $request, $response);

    // set the data for province
    $data['post_province'] = $response->getResults('area_name');

    // set stage the area id of provice
    $request->setStage([
        'area_id' => $response->getResults('area_parent')
    ]);

    // get the region
    $this->trigger('area-detail', $request, $response);

    // set the data for region
    $data['post_region'] = $response->getResults('area_name');

    // get post form
    $request->setStage('post_id', $data['post_id']);
    cradle()->trigger('form-post', $request, $response);
    if (!$response->isError()) {
        $data['post_form'] = $response->getResults();
    }

    // get the profile information
    if ($data['likers'] &&
        !empty($data['likers']) &&
        $data['view_interested']) {
        foreach ($data['likers'] as $p => $likes) {
            $request->setStage('profile_id', $likes['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            if ($response->getResults()) {
                // set the profile information
                $data['likers'][$p]['profile_information'] = $response->getResults();
            }
        }
    }

    // set date today
    $today = strtotime(date("Y-m-d H:i:s"));
    // get post created date
    $postCreate = $data['post_created'];
    // add 14 days to post created
    $postCreate = strtotime(date('Y-m-d H:i:s', strtotime($postCreate. ' + 14 days')));
    // subtract 14 days after post created to date today
    $postHiredTips = $postCreate - $today;
    // if postHiredTips is less than or equal to zero
    if ($postHiredTips <= 0) {
        // add post hired flag
        $data['post_hired_flag'] = true;
    }

    $body = cradle('/app/www')->template('post/detail', $data, [
        'partial/form_postform',
        'partial_howitworks',
        'partial_resumedownload',
        'partial_introbanner',
        'partial_postersearch',
        'partial_seekersearch',
        'post/modal_arrangement',
        'post/modal_experience',
        'post/modal_industry',
        'post/modal_location',
        'post/modal_school',
        'post/modal_popup',
        'post/modal_question',
        'post/modal_completeness',
        'post/modal_remove',
        'post_actions',
        'post_seeker-tips',
        'post_poster-tips',
        'post_poster',
        'post_seeker',
        '_modal-profile-completeness'
    ]);

    $request->setStage('post_id', $data['post_id']);
    // Checks if there is no queue
    if (!cradle()->package('global')->queue('post-view', $data)) {
        // Runs the job
        cradle()->trigger('post-view', $request, $response);
    }

    //set content
    if (!$response->getPage('title')) {
        $response->setPage('title', $title);
    }

    if (!$response->getMeta('description')) {
        $response->addMeta('description', $detail);
    }

    if (!$response->getMeta('keywords')) {
        $response->addMeta('keywords', strtolower(implode(',', $keywords)));
    }

    if (!$response->getMeta('image')) {
        $response->addMeta('image', $image);
    }

    if ($request->getSession('me', 'profile_id') &&
        $request->getSession('me', 'profile_company') !== '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'view_detail');
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned '.$experience.' experience point';

        //add a experience flash
        cradle('global')->setExperienceFlash($message);
    }

    $response
        ->addMeta('type', 'article')
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Post Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:post_slug/extend', function ($request, $response) {
    //Need to be logged in
    cradle('global')->requireLogin();

    //trigger job
    cradle()->trigger('post-update-expire', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/profile/post/search', $request, $response);
    }

    //add a flash
    cradle('global')->flash('Post was renewed', 'success');

    //redirect
    cradle('global')->redirect('/profile/post/search');

    //render page
}, 'render-www-page');

/**
 * Render the Post Redirect
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/redirect', function ($request, $response) {
    //Prepare body
    $postId = $request->getStage('ref');
    $request->setStage('post_id', $postId);

    //get the post
    cradle()->trigger('post-detail', $request, $response);

    $data = $response->getResults();

    //Render body
    $body = cradle('/app/www')->template('post/redirect', $data);
    $title = cradle('global')->translate(
        'Redirecting you to %s',
        $data['post_name']
    );


    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', 'page-post-redirect branding')
        ->setContent($body);

    //Render web page
}, 'render-www-page');

/**
 * Render Post Profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/post/profile/:profile_id', function ($request, $response) {
    // get the profile detail
    cradle()->trigger('profile-detail', $request, $response);

    // check for error
    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $request->setStage('profile', $request->getStage('profile_id'));

    // set request uri
    $request->setStage('redirect_uri', $request->getServer('REQUEST_URI'));

    // merge stage and results
    $data = array_merge($request->getStage(), $response->getResults());

    // get profile information
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    // set the profile information to data
    $data['profile_information'] = $response->getResults();

    // set the flag for profile informations
    if ($data['profile_information']) {
        if ($data['profile_information']['information_experience'] ||
            $data['profile_information']['information_education'] ||
            $data['profile_information']['information_accomplishment'] ||
            $data['profile_information']['information_skills']) {
            $data['profile_information_flag'] = true;
        }
    }

    // Checks for accomplishment
    // Limit to 1 accomplishment
    if (isset($data['profile_information']['information_accomplishment'])
        && !empty($data['profile_information']['information_accomplishment'])) {
        $data['profile_information']['information_accomplishment']
            = array($data['profile_information']['information_accomplishment'][0]);
    }

    // Checks for skills
    if (isset($data['profile_information']['information_accomplishment'])
        && !empty($data['profile_information']['information_accomplishment'])) {
        $data['profile_information']['information_skills']
            = array_slice($data['profile_information']['information_skills'], 0, 3);
    }

    // sort order desc
    $request->setStage('order', 'post_created', 'DESC');

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getStage('profile_id')
    );

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
            'post_flag'
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
            'post_type'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('post-search', $request, $response);
    $data = array_merge($data, $response->getResults());

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    // set the industries
    $data['industries'] = $this->package('global')->config('industries');

    $request->removeStage('order');
    $request->removeStage('filter');
    foreach ($data['rows'] as $r => $row) {
        // check profile id and post type
        if ($request->getSession('me', 'profile_id') == $row['profile_id'] &&
            $row['post_type'] == 'poster') {
            // set area locate to stage
            $request->setStage([
                'area_locate' => $row['post_location']
            ]);

            // trigger area-search
            cradle()->trigger('area-search', $request, $response);

            // check for error
            if ($response->isError()) {
                //add a flash
                cradle('global')->flash($response->getMessage(), 'danger');
                return cradle('global')->redirect('/');
            }

            // get the results
            if ($response->getResults('total')) {
                $data['rows'][$r]['post_location_flag'] = true;
            }
        }

        // check if post type seeker
        if ($row['post_type'] == 'seeker') {
            // TODO : Verify this area of code
            // Do we really need to query for profile information again?
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

        // to detect if sms-match is checked before
        if (in_array('sms-match', $row['post_notify'])) {
            $data['rows'][$r]['sms_match'] = 1;
        }

        // to detect if sms-interest is checked before
        if (in_array('sms-interest', $row['post_notify'])) {
            $data['rows'][$r]['sms_interest'] = 1;
        }

        //in older versions, tags can appear as strings
        if (is_string($row['post_tags'])) {
            //lets make sure it's an array
            $row['post_tags'] = [$row['post_tags']];
        }

        if ($row['post_tags']) {
            // compare industry and post tags
            $industriesCompare = array_uintersect($data['industries'], $row['post_tags'], 'strcasecmp');

            // post industry
            if ($industriesCompare) {
                $data['rows'][$r]['post_industry'] = true;
            }
        }

        // get post form
        $request->setStage('post_id', $row['post_id']);
        cradle()->trigger('form-post', $request, $response);
        if (!$response->isError()) {
            $data['rows'][$r]['post_form'] = $response->getResults();
        }
    }

    foreach ($data['rows'] as $key => $value) {
        // add post location flag if post location has value
        if (!empty($value['post_location'])) {
            $data['rows'][$key]['post_location_flag'] = true;
        }
    }

    // check for flags
    foreach ($data['rows'] as $r => $row) {
        // post tips flag
        if (isset($row['sms_match']) &&
            isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement'] &&
            $row['post_flag']) {
            $data['rows'][$r]['post_tips_flag'] = true;
        }

        // post tips
        if (isset($row['post_location_flag']) &&
            isset($row['post_industry']) &&
            $row['post_experience'] &&
            $row['post_arrangement']) {
            $data['rows'][$r]['post_tips'] = true;
        }

        // post notify flag
        if (isset($row['sms_match']) &&
            $row['post_flag']) {
            $data['rows'][$r]['post_notify_flag'] = true;
        }

        // post notify flag
        if (isset($row['sms_interest']) &&
            $row['post_flag']) {
            $data['rows'][$r]['post_notify_flag'] = true;
        }
    }

    // prepare list of school
    $request->setStage('range', '0');
    cradle()->trigger('school-search', $request, $response);
    $data['school'] = $response->getResults('rows');
    foreach ($data['school'] as $key => $value) {
        $data['school'][$key] = $value['school_name'];
    }
    // remove special characters
    $data['school'] = array_map('strtolower', $data['school']);
    $data['school'] = preg_replace('/[^a-z0-9\s]/', '', $data['school']);

    // check for post school
    foreach ($data['rows'] as $r => $row) {
        // compare post tags and school
        $schoolCompare = array_uintersect($data['school'], $row['post_tags'], 'strcasecmp');

        // if schoolCompare has value set post schoo flag
        if (!empty($schoolCompare)) {
            $data['rows'][$r]['post_school_flag'] = true;
        }
    }

    // check if all tips have value
    foreach ($data['rows'] as $key => $value) {
        if ($value['post_type'] == 'seeker') {
            // post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement'])
            ) {
                $data['rows'][$key]['post_tips'] = true;
            }
        } else {
            // post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement']) &&
                $value['post_experience']) {
                $data['rows'][$key]['post_tips'] = true;
            }
        }

        // set date today
        $today = strtotime(date("Y-m-d H:i:s"));
        // get post created date
        $postCreate = $value['post_created'];
        // add 14 days to post created
        $postCreate = strtotime(date('Y-m-d H:i:s', strtotime($postCreate. ' + 14 days')));
        // subtract 14 days after post created to date today
        $postHiredTips = $postCreate - $today;
        // setup expiring soon
        $expiringSoon = strtotime(date('Y-m-d H:i:s', strtotime($value['post_expires']. ' - 7 days'))) - $today;
        // if postHiredTips is less than or equal to zero
        if ($postHiredTips <= 0 || $expiringSoon <= 0) {
            // add post hired flag
            $data['rows'][$key]['post_hired_flag'] = true;
        }
    }

    //----------------------------//
    // 3. Render Template

    $class = 'page-post-profile branding';
    $body = cradle('/app/www')->template('post/profile', $data, [
        'partial_resumedownload',
        'post_actions',
        'post_banner',
        'post_poster-tips',
        'post_poster',
        'post_profile',
        'post_seeker-tips',
        'post_seeker',
        'post/modal_arrangement',
        'post/modal_completeness',
        'post/modal_experience',
        'post/modal_industry',
        'post/modal_location',
        'post/modal_school',
        'post/modal_quick-edit',
        'post/modal_resume-download',
        'post/modal_question',
        'post/modal_popup',
        'post/modal_remove',
        '_modal-profile-completeness'
    ]);

    // set content
    $response
        ->setPage('class', $class)
        ->setContent($body);

    //Render web page
}, 'render-www-page');

/**
 * Render the Message this job seeker page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:post_slug/post-message-seeker**', function ($request, $response) {
    //Required login
    if (!$request->getSession('me')) {
        $post_slug = $request->getStage('post_slug');
        $redirect = urlencode($post_slug);
        $seeker_profile_slug = $request->getStage('seeker_profile_slug');
        cradle('global')->redirect('/login?redirect_uri=' . $redirect . '/post-message-seeker?seeker_profile_slug=' . $seeker_profile_slug);
    }

    // set seeker_profile_slug
    $data['seeker_profile_slug'] = $request->getStage('seeker_profile_slug');

    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/');
    }

    $data = array_merge($request->getStage(), $response->getResults());

    // Checks if the post is not active
    if (!$data['post_active']) {
        // Redirect to the post listing page
        cradle('global')->redirect('/post/search?type='.$data['post_type']);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-message-seeker branding';

    //determine the title
    $title = $response->getResults('post_name')
    . ' from ' . $response->getResults('post_location')
    . ' is Looking for a ' . $response->getResults('post_position');

    //determine the detail
    $detail = $response->getResults('post_name')
    . ' job post for ' . $response->getResults('post_position')
    . ' in '  . $response->getResults('post_location') . '.';

    if ($response->getResults('post_type') === 'seeker') {
        $title = $response->getResults('post_position')
        . ' is Looking for Job in ' . $response->getResults('post_location');

        $detail = $response->getResults('post_name')
        . ' is a ' . $response->getResults('post_position')
        . ' looking for a job in ' . $response->getResults('post_location') . '.';
    }

    $detail .= ' Apply now, submit resume and get hired, free city jobs board.';

    //determine the keywords
    $keywords = array_merge(
        //important
        [
            str_replace([',', '  '], ' ', $response->getResults('post_name')),
            str_replace([',', '  '], ' ', $response->getResults('post_location'))
        ],
        //populate
        explode(' ', $response->getResults('post_position')),
        //fillers
        [
            'job',
            'hired',
            'apply',
            'resume'
        ]
    );

    $keywords = array_slice($keywords, 0, 5);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';
    if ($response->getResults('post_banner')) {
        $image = $response->getResults('post_banner');
    } else if ($response->getResults('profile_image')) {
        $image = $response->getResults('profile_image');
    }

    $data['wall_title'] = $title;

    $body = cradle('/app/www')->template('post/message', $data, [
        '_modal-profile-completeness'
    ]);

    $request->setStage('post_id', $data['post_id']);

    //set content
    if (!$response->getPage('title')) {
        $response->setPage('title', $data['wall_title']);
    }

    if (!$response->getMeta('description')) {
        $response->addMeta('description', $detail);
    }

    if (!$response->getMeta('keywords')) {
        $response->addMeta('keywords', strtolower(implode(',', $keywords)));
    }

    if (!$response->getMeta('image')) {
        $response->addMeta('image', $image);
    }

    if ($request->getSession('me', 'profile_id') &&
        $request->getSession('me', 'profile_company') !== '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'view_detail');
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned '.$experience.' experience point';

        //add a experience flash
        cradle('global')->setExperienceFlash($message);
    }

    $response
        ->addMeta('type', 'article')
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Message this job poster page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:post_slug/post-message-poster**', function ($request, $response) {
    //Required login
    if (!$request->getSession('me')) {
        $post_slug = $request->getStage('post_slug');
        $redirect = urlencode($post_slug);
        $poster_profile_slug = $request->getStage('poster_profile_slug');
        cradle('global')->redirect('/login?redirect_uri=' . $redirect . '/post-message-poster?poster_profile_slug=' . $poster_profile_slug);
    }

    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/');
    }

    $data = array_merge($request->getStage(), $response->getResults());

    // Checks if the post is not active
    if (!$data['post_active']) {
        // Redirect to the post listing page
        cradle('global')->redirect('/post/search?type='.$data['post_type']);
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-post-message-seeker branding';

    //determine the title
    $title = $response->getResults('post_name')
    . ' from ' . $response->getResults('post_location')
    . ' is Looking for a ' . $response->getResults('post_position');

    //determine the detail
    $detail = $response->getResults('post_name')
    . ' job post for ' . $response->getResults('post_position')
    . ' in '  . $response->getResults('post_location') . '.';

    if ($response->getResults('post_type') === 'seeker') {
        $title = $response->getResults('post_position')
        . ' is Looking for Job in ' . $response->getResults('post_location');

        $detail = $response->getResults('post_name')
        . ' is a ' . $response->getResults('post_position')
        . ' looking for a job in ' . $response->getResults('post_location') . '.';
    }

    $detail .= ' Apply now, submit resume and get hired, free city jobs board.';

    //determine the keywords
    $keywords = array_merge(
        //important
        [
            str_replace([',', '  '], ' ', $response->getResults('post_name')),
            str_replace([',', '  '], ' ', $response->getResults('post_location'))
        ],
        //populate
        explode(' ', $response->getResults('post_position')),
        //fillers
        [
            'job',
            'hired',
            'apply',
            'resume'
        ]
    );

    $keywords = array_slice($keywords, 0, 5);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';
    if ($response->getResults('post_banner')) {
        $image = $response->getResults('post_banner');
    } else if ($response->getResults('profile_image')) {
        $image = $response->getResults('profile_image');
    }

    $data['wall_title'] = $title;

    $body = cradle('/app/www')->template(
        'post/message',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    $request->setStage('post_id', $data['post_id']);

    //set content
    if (!$response->getPage('title')) {
        $response->setPage('title', $data['wall_title']);
    }

    if (!$response->getMeta('description')) {
        $response->addMeta('description', $detail);
    }

    if (!$response->getMeta('keywords')) {
        $response->addMeta('keywords', strtolower(implode(',', $keywords)));
    }

    if (!$response->getMeta('image')) {
        $response->addMeta('image', $image);
    }

    if ($request->getSession('me', 'profile_id') &&
        $request->getSession('me', 'profile_company') !== '') {
        // add create post experience
        $experience = cradle('global')->config('experience', 'view_detail');
        $request->setStage('profile_experience', $experience);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $this->trigger('profile-add-experience', $request, $response);
        $message = 'You earned '.$experience.' experience point';

        //add a experience flash
        cradle('global')->setExperienceFlash($message);
    }

    $response
        ->addMeta('type', 'article')
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');
