<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\Date;
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Validator as UtilityValidator;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * Render the AJAX Post Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/search', function ($request, $response) {
    if (!$request->hasStage('range')) {
        $request->setStage('range', 45);
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

    //post_tags
    if ($request->getStage('tag')) {
        $request->setStage(
            'post_tags',
            $request->getStage('tag')
        );
    }

    // industry
    if ($request->getStage('industry')) {
        switch ($request->getStage('industry')) {
            case 'agriculture-mining':
                $industries = ['Agriculture and Mining'];
                break;

            case 'bpo':
                $industries = ['BPO and Call Center'];
                break;

            case 'agriculture-mining':
                $industries = ['Construction'];
                break;

            case 'finance-insurance':
                $industries = ['Finance and Insurance'];
                break;

            case 'healthcare':
                $industries = ['Health Care'];
                break;

            case 'hospitality':
                $industries = ['Hospitality'];
                break;

            case 'human-resourcing':
                $industries = ['Human Resourcing'];
                break;

            case 'warehouse-logistics':
                $industries = ['Logistics'];
                break;

            case 'poea':
                $industries = ['Work Abroad and POEA'];
                break;

            case 'real-estate':
                $industries = ['Real Estate'];
                break;

            case 'restaurants':
                $industries = ['Restaurant'];
                break;

            case 'retail':
                $industries = ['Retail'];
                break;

            case 'startup':
                $industries = ['Start Up'];
                break;

            case 'tech':
                $industries = ['Technology'];
                break;

            default:
                $industries = $this->package('global')->config('industries');
                break;
        }

        $request->setStage(
            'post_tags',
            $industries
        );
    }

    //sort shortcuts
    switch ($request->getStage('sort')) {
        case 'popular':
            $request->setStage('order', 'post_like_count', 'DESC');
            break;
        default:
            $request->setStage('order', 'post_id', 'DESC');
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
            'post_download_count'
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
            'post_industry',
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

    // set the data
    $data = $request->getStage();

    // get the unpromoted post
    // set the range to 45
    $request->setStage('range', 45);
    // set the post flag
    $request->setStage('filter', 'post_flag', [0,3]);
    // trigger post search
    cradle()->trigger('post-search', $request, $response);
    // set the unpromoted post
    $unpromotedPost = $response->getResults('rows');

    // get the sponsored post
    // set the start for sponsored
    $request->setStage('start', $request->getStage('start_sponsored'));
    // set the sponsored range 5
    $request->setStage('range', 5);
    // set the post flag
    $request->setStage('filter', 'post_flag', 1);
    // trigget the post search
    cradle()->trigger('post-search', $request, $response);
    // set the promoted post
    $promotedPost = $response->getResults('rows');

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

    // // set the data
    // $data = $request->getStage();
    //
    // // set the post flag
    // $request->setStage('filter', 'post_flag', 0);
    // // trigger post search
    // cradle()->trigger('post-search', $request, $response);
    // // set the results to data rows
    // $data['rows'] = $response->getResults('rows');
    //
    // $unpromotedPost = [];
    // // If there's a row
    // if ($data['rows']) {
    //     $inc = 0;
    //     // Skip rows multiple of 10
    //     foreach ($data['rows'] as $key => $rows) {
    //         if (($key + 1 + $inc)  == 11 || ($key + 1 + $inc) == 20
    //             || ($key + 1 + $inc) == 31 || ($key + 1 + $inc) == 40) {
    //             $inc++;
    //         }
    //         $unpromotedPost['rows'][$key + 1 + $inc] = $rows;
    //     }
    // } else {
    //     $unpromotedPost = $response->getResults();
    // }
    //
    // // set the start for sponsored
    // $request->setStage('start', $request->getStage('start_sponsored'));
    // // get the sponsored post
    // // set the range 5
    // $request->setStage('range', 5);
    // // set the post flag
    // $request->setStage('filter', 'post_flag', 1);
    // // trigget the post search
    // cradle()->trigger('post-search', $request, $response);
    // // set the results to data rows
    // $data['rows'] = $response->getResults('rows');
    //
    // $promotedPost = [];
    // // If there's a row
    // if ($data['rows']) {
    //     // Make rows multiple of 10
    //     $postAlternate = 0;
    //     foreach ($data['rows'] as $key => $rows) {
    //         if ($postAlternate == 0) {
    //             $promotedPost['rows'][$key * 10] = $rows;
    //             $postAlternate++;
    //         } else {
    //             $promotedPost['rows'][($key * 10) + 1] = $rows;
    //             $postAlternate = 0;
    //         }
    //     }
    // } else {
    //     $promotedPost = $response->getResults();
    // }
    //
    // $data['rows'] = $promotedPost['rows'] + $unpromotedPost['rows'];
    // ksort($data['rows']);

    // group the post if same profile id
    $previousProfileId = null;
    $previousKeyValue = null;
    foreach ($data['rows'] as $r => $row) {
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

    $request->removeStage();
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

    // set the industries
    $data['industries'] = array_column($data['filter_panel']['industry'], 'feature_name');

    $request->removeStage();

    // set list of school
    $request->setStage('range', '0');
    cradle()->trigger('school-search', $request, $response);
    $data['school'] = $response->getResults('rows');
    foreach ($data['school'] as $key => $value) {
        $data['school'][$key] = $value['school_name'];
    }
    // remove special character
    $data['school'] = array_map('strtolower', $data['school']);
    $data['school'] = preg_replace('/[^a-z0-9\s]/', '', $data['school']);

    foreach ($data['rows'] as $r => $row) {
        if ($row['post_tags']) {
            // compare industry and post tags
            $industriesCompare = array_uintersect($data['industries'], $row['post_tags'], 'strcasecmp');

            // post industry
            if ($industriesCompare) {
                $data['rows'][$r]['post_industry'] = true;
            }

            // compare post tags and school
            $schoolCompare = array_uintersect($data['school'], $row['post_tags'], 'strcasecmp');

            // if schoolCompare has value set post schoo flag
            if (!empty($schoolCompare)) {
                $data['rows'][$r]['post_school_flag'] = true;
            }
        }
        // check if location has value
        if (!empty($row['post_location'])) {
            // add post location flag
            $data['rows'][$r]['post_location_flag'] = true;
        }

        // set date today
        $today = strtotime(date("Y-m-d H:i:s"));
        // get post created date
        $postCreate = $row['post_created'];
        // add 14 days to post created
        $postCreate = strtotime(date('Y-m-d H:i:s', strtotime($postCreate. ' + 14 days')));
        // subtract 14 days after post created to date today
        $postHiredTips = $postCreate - $today;
        // if postHiredTips is less than or equal to zero
        if ($postHiredTips <= 0) {
            // add post hired flag
            $data['rows'][$r]['post_hired_flag'] = true;
        }
    }

    foreach ($data['rows'] as $key => $value) {
        if ($value['post_type'] == 'seeker') {
            // add post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement'])
            ) {
                $data['rows'][$key]['post_tips'] = true;
            }
        } else {
            // add post tips
            if (isset($value['post_location_flag']) &&
                isset($value['post_industry']) &&
                isset($value['post_school_flag']) &&
                !empty($value['post_arrangement']) &&
                $value['post_experience']) {
                $data['rows'][$key]['post_tips'] = true;
            }
        }
    }

    //Render body
    $body = cradle('/app/www')->template(
        'post/_list',
        $data,
        [
            'post_actions',
            'partial_resumedownload',
            'post_poster',
            'post_seeker',
            'post_poster-tips',
            'post_seeker-tips'
        ]
    );

    //Set Content
    $response->setContent($body);
});

/**
 * Process the Post Download
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/download/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    $cost = cradle('global')->config('credits', 'resume-download');
    $available = $request->getSession('me', 'profile_credits');

    //if not unlimited download
    if (empty($request->hasSession('me', 'profile_package'))
        || !in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
        if ($available < $cost) {
            cradle('global')->flash('You just need 10 more credits to download this resume.', 'danger');
            return $response
                ->addValidation('code', 'insufficient-credits')
                ->setError(true, 'Insufficient-Credits');
        }
    }

    //----------------------------//
    // 2. Prepare Data

    $post = $request->getStage('post_id');

    $request
        ->setStage('profile_id', $request->getSession('me', 'profile_id'))
        ->setStage('service_name', 'Resume Download')
        ->setStage('service_meta', [
            'post_id' => $post
        ])
        ->setStage('service_credits', $cost);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-download', $request, $response);

    if (!$response->isError()) {
        $results = $response->getResults();

        // add create resume experience
        $experience = cradle('global')->config('experience', 'resume_download');
        $request->setStage('profile_experience', $experience);
        $this->trigger('profile-add-experience', $request, $response);

        $results['credits'] = $experience;

        $request->setStage('activity', 'downloaded');
        $this->trigger('post-get-count', $request, $response);
        $activityCount = $response->getResults();

        // 10th download badge
        if ($activityCount == 10) {
            $achievement = cradle('global')->config('achievements', 'downloaded_10');
            $request->setStage('profile_achievement', 'downloaded_10');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        // 50th download badge
        if ($activityCount == 50) {
            $achievement = cradle('global')->config('achievements', 'downloaded_50');
            $request->setStage('profile_achievement', 'downloaded_50');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        // 100th download badge
        if ($activityCount == 100) {
            $achievement = cradle('global')->config('achievements', 'downloaded_100');
            $request->setStage('profile_achievement', 'downloaded_100');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        $response->setResults($results);

        cradle()->trigger('service-create', $request, $response);
        if (!$response->isError()) {
            //if not unlimited download resume
            if (empty($request->hasSession('me', 'profile_package'))
            || !in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
                $request->setSession('me', 'profile_credits', $available - $cost);
            }

            $response->setResults($results);
        }
    }

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the Post Download
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/resume/download/:resume_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    $cost = cradle('global')->config('credits', 'resume-download');
    $available = $request->getSession('me', 'profile_credits');

    cradle()->trigger('profile-session', $request, $response);

    //if not unlimited download resume
    if (empty($request->hasSession('me', 'profile_package'))
        || !in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
        if ($available < $cost) {
            cradle('global')->flash('You just need 10 more credits to download this resume.', 'danger');
            return $response
                ->addValidation('code', 'insufficient-credits')
                ->setError(true, 'Insufficient-Credits');
        }
    }

    //----------------------------//
    // 2. Prepare Data

    $resume = $request->getStage('resume_id');

    $request
        ->setStage('profile_id', $request->getSession('me', 'profile_id'))
        ->setStage('service_name', 'Resume Download')
        ->setStage('service_meta', [
            'resume_id' => $resume
        ])
        ->setStage('service_credits', $cost);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('resume-download', $request, $response);

    if (!$response->isError()) {
        $results = $response->getResults();

        // add create post experience
        $experience = cradle('global')->config('experience', 'resume_download');
        $request->setStage('profile_experience', $experience);
        $this->trigger('profile-add-experience', $request, $response);

        $results['credits'] = $experience;

        cradle()->trigger('service-create', $request, $response);
        if (!$response->isError()) {
            //if not unlimited download resume
            if ($request->hasSession('me', 'profile_package')
                && !in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
                $request->setSession('me', 'profile_credits', $available - $cost);
            }

            $response->setResults($results);
        }
    }

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the Post Email
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/email/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-email', $request, $response);

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the Post Like
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/like/:post_id', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-like', $request, $response);

    if (!$response->isError()) {
        $results = $response->getResults();

        if (empty(trim($request->getSession('me', 'profile_company')))) {
            return $response->setError(false)->setResults($results);
        }

        // add create post experience
        $experience = cradle('global')->config('experience', 'interested');
        $request->setStage('profile_experience', $experience);
        $this->trigger('profile-add-experience', $request, $response);

        $results['credits'] = $experience;

        $request->setStage('activity', 'interested');
        $this->trigger('post-get-count', $request, $response);
        $activityCount = $response->getResults();

        // 1st post badge
        if ($activityCount == 1) {
            $achievement = cradle('global')->config('achievements', 'interested_1');
            $request->setStage('profile_achievement', 'interested_1');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        // 10th post badge
        if ($activityCount == 10) {
            $achievement = cradle('global')->config('achievements', 'interested_10');
            $request->setStage('profile_achievement', 'interested_10');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        // 50th post badge
        if ($activityCount == 50) {
            $achievement = cradle('global')->config('achievements', 'interested_50');
            $request->setStage('profile_achievement', 'interested_50');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        // 100th post badge
        if ($activityCount == 100) {
            $achievement = cradle('global')->config('achievements', 'interested_100');
            $request->setStage('profile_achievement', 'interested_100');
            $this->trigger('profile-add-achievement', $request, $response);
            $results['badge'] = [
                'image' => $achievement['image'],
                'message' => cradle('global')->translate($achievement['modal'])
            ];
        }

        return $response->setError(false)->setResults($results);
    }

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the Post Like
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/account/verify/:auth_slug', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-verify', $request, $response);

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the Post Phone
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/phone/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-phone', $request, $response);

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Process the File Create Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/ajax/file/upload', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (isset($data['type'])
        && $data['type'] == 'link') {
        // link resume
        cradle()->trigger('resume-link-post', $request, $response);
        return $response->setContent(json_encode([
            'error'      => false,
            'message'    => 'Resume Was Uploaded',
        ]));
    }

    $file = $request->getFiles();

    if (empty($file)) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'File is Invalid',
        ]));
    }

    $request->setStage('upload', $file);
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('file-upload', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => $response->getValidation()['error']
        ]));
    }

    // get file
    $file = $response->getResults();
    $request->removeStage('resume_id');
    $request->setStage('resume_link', $file['resume_link']);
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    $results = [];

    if (isset($data['post_id'])) {
        $request->setStage('post_id', $data['post_id']);
        cradle()->trigger('post-detail', $request, $response);
        $request->setStage('resume_position', $response->getResults()['post_position']);
    }

    // save resume
    cradle()->trigger('resume-create', $request, $response);

    //check error
    if ($response->isError()) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $response->getValidation(),
        ]));
    }

    //it was good Set json Content
    return $response->setContent(json_encode([
        'error'   => false,
        'message' => 'File was Created',
        'data'     => $results
    ]));
});

/**
 * Process the Profile Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/profile/resume/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();
    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('resume-post', $request, $response);

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Message Profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/profile/message', function ($request, $response) {
    // Requires the user to be logged in
    cradle('global')->requireLogin();

    // Default error message
    $message = "Couldn't send this message. Please try again later.";

    // Gets the staging data
    $data = $request->getStage();

    // Checks if the body is empty
    if (!trim($data['message'])) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => 'You cannot send an empty message'
        ]));
    }

    // Checks if the profile id matches the assigned profile slug
    // Based on the profile id / profile_id
    // Based on the profile slug / profile_slug
    cradle()->trigger('profile-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $message
        ]));
    }

    // The profile exists at this point
    $profile = $response->getResults();

    // Checks if the profile id and slug do not match
    if ($profile['profile_slug'] != $data['profile_slug']) {
        // The data has been altered, return an error message
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $message
        ]));
    }

    // Checks if the user is sending to themselves
    if ($request->getSession('me')['profile_id'] == $profile['profile_id']) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => 'You cannot message yourself'
        ]));
    }

    // Assume that there are no more errors
    // Sets the receiver's profile
    $request->setStage('receiver', $profile);

    // Gets the logged in profile id / profile_id
    $request->setStage('sender', $request->getSession('me'));

    // Trigger the event to send the message
    cradle()->trigger('profile-message', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => $message
        ]));
    }

    return $response->setContent(json_encode([
        'error'   => false,
        'message' => 'Your message was successfully sent!'
    ]));
});

/**
 * Process the Promote Post
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/promote/post', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('post-detail', $request, $response);

    //can we update ?
    if ($response->isError()) {
        //add a flash
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $response->getValidation());
    }

    // Gets the post
    $post = $response->getResults();
    $notify = null;
    $message = 'You just need [COST] credits to [FLAG]';

    //if post_promote has checked on update
    if ($request->hasStage('post_promote')
        && $request->getStage('post_promote') == 'promote') {
        $request->setStage('post_flag', 1);
        $cost = cradle('global')->config('credits', 'post-promotion');
        $available = $request->getSession('me', 'profile_credits');

        // if insufficient credit balance
        if ($available < $cost) {
            $message = str_replace('[COST]', $cost, $message);
            $message = str_replace('[FLAG]', 'promote this post', $message);
            return $response->setError(true, $message);
        }
    }

    // Checks for post_notify change
    if ($request->hasStage('post_notify')) {
        // Gets the new notify option
        $notify = $request->getStage('post_notify');

        // Checks if the notify can not be accepted
        switch ($notify) {
            case 'sms-match':
            case 'sms-interest':
                break;

            default:
                // Cannot add the notification to the post
                return $response->setError(true, 'There are some errors in the form');
        }

        //if post_notify sms-match has checked on update
        if ($notify == 'sms-match') {
            $cost = cradle('global')->config('credits', $notify);
            $available = $request->getSession('me', 'profile_credits');

            // if insufficient credit balance
            if ($available < $cost) {
                $message = str_replace('[COST]', $cost, $message);
                $message = str_replace('[FLAG]', 'post with sms notifications', $message);
                return $response->setError(true, $message);
            }
        }

        //if post_notify sms-interest has checked on update
        if ($notify == 'sms-interest') {
            $cost = cradle('global')->config('credits', $notify);
            $available = $request->getSession('me', 'profile_credits');

            // if insufficient credit balance
            if ($available < $cost) {
                $message = str_replace('[COST]', $cost, $message);
                $message = str_replace('[FLAG]', 'post with sms interest', $message);
                return $response->setError(true, $message);
            }
        }

        // Checks if the new notification is not yet in the post
        if (!in_array($notify, $post['post_notify'])) {
            $post['post_notify'][] = $notify;
            $request->setStage('post_notify', $post['post_notify']);
        }
    }

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));

    //----------------------------//
    // 3. Process Request

    //call the job
    cradle()->trigger('post-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response->setError(true, $response->getMessage());
    }

    $successMessage = null;
    if ($notify && $notify == 'sms-match') {
        $cost = cradle('global')->config('credits', 'sms-match');
        $available = $request->getSession('me', 'profile_credits');
        $results = $response->getResults();

        $request
            ->setStage('service_name', 'Sms Match')
            ->setStage('service_meta', [
                'post_id' => $results['post_id']
            ])
            ->setStage('service_credits', $cost);

        cradle()->trigger('service-create', $request, $response);

        if (!$response->isError()) {
            $request->setSession('me', 'profile_credits', $available - $cost);
        }

        $successMessage = 'SMS Match Notification Success';
    }

    if ($request->getStage('post_promote') == 'promote') {
        $cost = cradle('global')->config('credits', 'post-promotion');
        $available = $request->getSession('me', 'profile_credits');
        $results = $response->getResults();

        $request
            ->setStage('service_name', 'Post Promotion')
            ->setStage('service_meta', [
                'post_id' => $results['post_id']
            ])
            ->setStage('service_credits', $cost);

        cradle()->trigger('service-create', $request, $response);

        if (!$response->isError()) {
            $request->setSession('me', 'profile_credits', $available - $cost);
        }

        $successMessage = 'Promote Post Success';
    }

    // SMS Interest notification success message
    if ($notify && $notify == 'sms-interest') {
        $cost = cradle('global')->config('credits', 'sms-interest');
        $available = $request->getSession('me', 'profile_credits');
        $results = $response->getResults();

        $request
            ->setStage('service_name', 'Sms Interest')
            ->setStage('service_meta', [
                'post_id' => $results['post_id']
            ])
            ->setStage('service_credits', $cost);

        cradle()->trigger('service-create', $request, $response);

        if (!$response->isError()) {
            $request->setSession('me', 'profile_credits', $available - $cost);
        }

        $successMessage = 'SMS Interest Notification Success';
    }

    // add create post experience
    $experience = cradle('global')->config('experience', 'promote_post');
    $request->setStage('profile_experience', $experience);
    $this->trigger('profile-add-experience', $request, $response);

    $request->setStage('activity', 'promoted');
    $this->trigger('post-get-count', $request, $response);
    $activityCount = $response->getResults();

    // 10th promoted badge
    if ($activityCount == 10) {
        $achievement = cradle('global')->config('achievements', 'promoted_10');
        $request->setStage('profile_achievement', 'promoted_10');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = cradle('global')->translate('earned a badge from promoting 10 posts.');
        cradle('global')->setBadge($achievement['image'], cradle('global')->translate($achievement['modal']));
    }

    // 50th promoted badge
    if ($activityCount == 50) {
        $achievement = cradle('global')->config('achievements', 'promoted_50');
        $request->setStage('profile_achievement', 'promoted_50');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = cradle('global')->translate('earned a badge from promoting 50 posts.');
        cradle('global')->setBadge($achievement['image'], cradle('global')->translate($achievement['modal']));
    }

    // 100th promoted badge
    if ($activityCount == 100) {
        $achievement = cradle('global')->config('achievements', 'promoted_100');
        $request->setStage('profile_achievement', 'promoted_100');
        $this->trigger('profile-add-achievement', $request, $response);
        $message = cradle('global')->translate('earned a badge from promoting 100 posts.');
        cradle('global')->setBadge($achievement['image'], cradle('global')->translate($achievement['modal']));
    }

    //----------------------------//
    // 4. Interpret Results
    $response->setError(false)
        ->setResults([
            'message' => $successMessage,
            'credits' => $experience
        ]);
});

/**
 * Process the Post Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/post/update/:post_id', function ($request, $response) {
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
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $response->getValidation());
    }

    //----------------------------//
    // 2. Prepare Data
    //if post_arrangement has no value make it null
    if ($request->hasStage('post_arrangement') && !$request->getStage('post_arrangement')) {
        $request->setStage('post_arrangement', null);
    }

    //validate post_experience
    if ($request->hasStage('post_experience') && $request->getStage('post_experience') < 0) {
        return $response
            ->setError(true, 'Invalid years of experience');
    }

    if ($request->hasStage('post_experience') && empty($request->getStage('post_experience'))) {
        $request->setStage('post_experience', 0);
    }

    // merge post_industry and post_tags
    if ($request->hasStage('post_industry') &&
        !empty($request->getStage('post_industry'))) {
        //process industry tag
        $postIndustryTags = $request->getStage('post_industry');
        $postIndustryTags = array_map('strtolower', $postIndustryTags);
        $postIndustryTags = preg_replace('/[^a-z0-9\s]/', '', $postIndustryTags);

        // merge data
        $request->setStage('post_tags', array_merge(
            $postIndustryTags,
            $response->getResults('post_tags')
        ));
    }

    // check if hired / inactive post
    if ($request->hasStage('post_flag')
        && $request->getStage('post_flag') == '2') {
        $request->setStage('post_active', 0);
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('post-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return $response
            ->setError(true, 'Invalid years of experience')
            ->set('json', 'validation', $response->getValidation());
    }

    //----------------------------//
    // 5 Process Service
    //it was good
    // 4. Interpret Results
    $response->setError(false)
        ->setResults($response->getResults());
});

/**
 * Ajax Area Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/area/search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    // Require login
    // cradle('global')->requireLogin();

    //----------------------------//
    // 2. Validate Data
    if (!$request->hasStage()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $request->getStage();

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //----------------------------//
    // 4. Process Data
    cradle()->trigger('area-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    $response->setError(false)->setResults($data['rows']);
});

/**
 * Ajax Position Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/position/search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    // Require login
    // cradle('global')->requireLogin();

    //----------------------------//
    // 2. Validate Data
    if (!$request->hasStage()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $request->getStage();

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //----------------------------//
    // 4. Process Data
    cradle()->trigger('position-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    $response->setError(false)->setResults($data['rows']);
});

/**
 * Process the Stash Post
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/update/stash', function ($request, $response) {
    // set the error
    $errors = [];

    // Validate name
    if ($request->hasStage('post_name') && !$request->getStage('post_name')) {
        $errors['post_name'] = 'Name is required';
    }

    // Validate postion
    if ($request->hasStage('post_position') && !$request->getStage('post_position')) {
        $errors['post_position'] = 'Title is required';
    }

    // Validate location
    if ($request->hasStage('post_location') && !$request->getStage('post_location')) {
        $errors['post_location'] = 'Location is required';
    }

    // Check for errors
    if ($errors) {
        return $response
                ->setError(true, 'There are some errors in the form')
                ->set('json', 'validation', $errors);
    }

    // Check for poster stash
    if ($request->hasSession('post/create/poster/stash')) {
        $request->setSession('post/create/poster/stash', [
            'post_name' => $request->getStage('post_name'),
            'post_location' =>  $request->getStage('post_location'),
            'post_position' =>  $request->getStage('post_position'),
            'post_experience' =>  $request->getStage('post_experience'),
            'post_type' => 'poster'
        ]);
    }

    // Check for seeker stash
    if ($request->hasSession('post/create/seeker/stash')) {
        $request->setSession('post/create/seeker/stash', [
            'post_name' => $request->getStage('post_name'),
            'post_location' =>  $request->getStage('post_location'),
            'post_position' =>  $request->getStage('post_position'),
            'post_type' => 'seeker'
        ]);
    }

    $response->setError(false)
       ->setResults('Post Detail was Updated');
});

/**
 * Message Job Seeker
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/message/jobseeker', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('send-message', $request, $response);

    if (!$response->isError()) {
        $results = $response->getResults();

        if (empty(trim($request->getSession('me', 'profile_company')))) {
            return $response->setError(false)->setResults($results);
        }

        return $response->setError(false)->setResults($results);
    }

    //----------------------------//
    // 4. Interpret Results
});

/**
 * Renew Post Event
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/post/renew', function ($request, $response) {
    cradle('global')->requireLogin();

    $data = $request->getStage();

    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error'
            ]));
    }

    // Check if we want to remove this post
    if ($data['action'] == 1) {
        // Trigger the event for removing post
        cradle()->trigger('post-remove', $request, $response);

        // Check for Errors
        if ($response->isError()) {
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Error occured.'
                ]));
        }

        // Success
        $message = cradle('global')->translate('Post was Removed');
        return $response->setContent(json_encode([
                'error'   => false,
                'message' => $message,
            ]));
    }

    // Assume that we want to renew this post
    $post = $response->getResults();
    $today = strtotime('now');
    $expires = strtotime('+1 month', $today);

    if ($post['post_type'] == 'seeker') {
        $expires = strtotime('+1 month', $expires);
    } else {
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

    if (isset($data['post_type']) && $data['post_type'] == 'seeker') {
        $request->removeStage('post_banner');
    }

    // update only if unlimited post or post_count < 5
    if (!isset($data['post_count']) || (isset($data['post_count']) && $data['post_count'] < 5)) {
        $request->setStage('post_active', 1);
        $request->setStage('post_flag', 0);
        $request->setStage('post_expires', $expires);
        $request->removeStage('post_phone');
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

        $request
            ->setStage('profile_id', $request->getSession('me', 'profile_id'))
            ->setStage('post_active', 1)
            ->setStage('post_flag', 0)
            ->setStage('post_expires', $expires)
            ->setStage('service_name', 'Extra Post')
            ->setStage('service_meta', [
                'post_id' => $post['post_id']
            ])
            ->setStage('service_credits', $cost);

        //phone not required
        $request->removeStage('post_phone');

        //trigger job
        cradle()->trigger('post-update', $request, $response);
        //check post update
        if ($response->isError()) {
            return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error occured.'
            ]));
        }

        // this will trigger if type is poster
        if ($data['post_type'] == 'poster') {
            //trigger job
            cradle()->trigger('service-create', $request, $response);
        }
        //check service updat error
        if (!$response->isError()) {
            $request->setSession(
                'me',
                'profile_credits',
                $request->getSession('me', 'profile_credits') - $cost
            );
        }
    }

    // Checks for errors
    if ($response->isError()) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error occured.'
            ]));
    }

    return $response->setContent(json_encode([
        'error'   => false,
        'message' => 'Post renewed successfully!'
    ]));
});


// * Ajax routes for the Applicant Tracking System
// */

/**
 * Submit Applicant Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/submit/form', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    if ($request->hasSession('me')) {
        $profileId = $request->getSession('me', 'profile_id');
    } else {
        $profileId = $request->getStage('profile_id');
    }

    /// get profile id
    $request->setStage(
        'profile_id',
        $profileId
    );

    // checks if there's no answer
    if (!$request->hasStage('question')) {
        return $response->setError(true, 'Fill up the form');
    }

    // set the s3 config
    $config = $this->package('global')->service('s3-main');
    // set the upload path
    $upload = $this->package('global')->path('upload');

    $answerIds = [];
    // loop questions id then insert answer
    foreach ($request->getStage('question') as $key => $answer) {
        // check if answer is a file
        if (base64_decode($answer, true) === false) {
            // try to upload to s3
            $answer = File::base64ToS3($answer, $config);
            //try being old school
            $answer = File::base64ToUpload($answer, $upload);
        }

        // checks if answer already exists
        $request->setStage('question_id', $key);
        $request->setStage('answer_name', $answer);

        cradle()->trigger('answer-create', $request, $response);

        // get answer ids
        if (!$response->isError()) {
            $answerIds[] = $response->getResults('answer_id');
        }
    }

    $request->setStage('answer_ids', $answerIds);
    $request->removeStage('filter');
    $request->setStage(
        'filter',
        'profile_id',
        $profileId
    );

     $request->setStage(
         'filter',
         'post_id',
         $request->getStage('post_id')
     );

    // check if appliction already submitted
    cradle()->trigger('applicant-search', $request, $response);

    if ($response->getResults('total') > 0) {
        return $response->setError(true, 'Application was already submitted');
    }

    // create applicant
    // link form, profile, answers
    cradle()->trigger('applicant-create', $request, $response);

    if (!$response->isError()) {
        return $response->setError(false, 'Application was successfully submitted');
    }
});

/**
 * ATS Inform Seekers about new attached form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/post/form/inform', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    /// get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // checks if there's no answer
    if (!$request->hasStage('post_id')) {
        return $response->setError(true, 'Not Found!');
    }

    $postId = $request->getStage('post_id');

    //get profiles who are not into the application form yet

    // check if appliction already submitted
    cradle()->trigger('post-seeker-toinform', $request, $response);
    $results = $response->getResults();

    //match the people who like and people who has already have an applicant record
    foreach ($results['profileWhoLiked'] as $index => $profileId) {
        if (!empty($results['profileHasApplicant'])) {
            if (in_array($profileId, $results['profileHasApplicant'])) {
                unset($results['profileWhoLiked'][$index]);
            }
        }
    }

    $res = [];
    //create applicant to profile
    foreach ($results['profileWhoLiked'] as $index => $profileId) {
        // Check if the user is already an applicant for this post
        $request->setStage('exclude', 'form', true);
        $request->setStage('filter', 'profile_id', $profileId);
        $request->setStage('filter', 'post_id', $request->getStage('post_id'));
        $request->setStage('profile_id', $profileId);

        // Gets the applicant detail
        cradle()->trigger('applicant-search', $request, $response);
        $results = $response->getResults();
        $applicant = null;

        // Checks if the user has an applicant data
        if (!$results['total']) {
            cradle()->trigger('applicant-create', $request, $response);

            // Check error
            if (!$response->isError()) {
                // An applicant was created
                $applicant = $response->getResults();

                // Constructs the applicant data
                $applicant['applicant_created'] = new DateTime($applicant['applicant_created']);
                $applicant['applicant_created'] = date_format($applicant['applicant_created'], 'M d, Y');
            }
        } else {
            $applicant = $results['rows'][0];
        }

        // Adds the applicant data to the return
        $res[] = $applicant;

        $request->removeStage('exclude');
        cradle()->trigger('applicant-search', $request, $response);
        $form = $response->getResults();

        // Checks if there is no form
        if (!$form['total']) {
            // Link the form to the applicant
            $request->setStage('applicant_id', $applicant['applicant_id']);
            cradle()->trigger('applicant-link-form', $request, $response);
        }

        //notify profile via email
        //TODO:: queue this when ready
        $request->setStage('post_id', $postId);
        cradle()->trigger('profile-post-inform', $request, $response);
    }

    if (!$response->isError()) {
        return $response->setError(false, 'Application was successfully submitted')->setResults($res);
    }
});

$cradle->post('/ajax/post/form/inform/seeker', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check if appliction already submitted
    cradle()->trigger('profile-detail', $request, $response);
    $profile = $response->getResults();

    /// get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // checks if there's no answer
    if (!$request->hasStage('post_id')) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot notify this seeker',
            ]));
    }

    // Default success message
    $message = 'Applicant was successfully notified';
    $postId = $request->getStage('post_id');

    //create applicant to profile
    // create applicant
    // link form, profile, answers

    //TODO:: queue this when ready
    $request->setStage('profile_id', $profile['profile_id']);

    $filter = $request->getStage();
    $request->setStage('filter', $filter);
    $request->setStage('exclude', 'form', true);
    $request->removeStage('filter', 'form_id');

    // Gets the applicant detail
    cradle()->trigger('applicant-search', $request, $response);

    // Remove the exclusion
    $request->removeStage('exclude');

    // Gets the results
    $results = $response->getResults();
    $applicant = null;

    // Checks an applicant exists for this post
    if (!$results['total']) {
        // Alters the success message
        $message = 'Application was successfully submitted';

        // At this point, no applicant was returned
        cradle()->trigger('applicant-create', $request, $response);

        //check error
        if ($response->isError()) {
            // At this point, there are errors
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Cannot notify this seeker',
                ]));
        }

        // An applicant was created
        $applicant = $response->getResults();

        // Constructs the applicant data
        $applicant['applicant_created'] = new DateTime($applicant['applicant_created']);
        $applicant['applicant_created'] = date_format($applicant['applicant_created'], 'M d, Y');
    } else {
        $applicant = $results['rows'][0];
    }

    // Checks if the applicant has a form
    $request->setStage('filter', $filter);
    cradle()->trigger('applicant-search', $request, $response);
    $form = $response->getResults();

    // Checks if there is no form
    if (!$form['total']) {
        // Link the form to the applicant
        $request->setStage('applicant_id', $applicant['applicant_id']);
        $request->setStage('form_id', $filter['form_id']);
        cradle()->trigger('applicant-link-form', $request, $response);
    }

    //notify profile via email
    //TODO:: queue this when ready
    $request->setStage('post_id', $postId);
    cradle()->trigger('profile-post-inform', $request, $response);

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot notify this seeker',
            ]));
    }

    // There are no errors at this point
    // Return a success message along with the applicant data
    return $response->setContent(json_encode([
            'error'     => false,
            'message'   => $message,
            'applicant' => $applicant
        ]));
});

/**
 * Add Custom Label
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/label/create', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // checks if there's no label
    if (!isset($data['label_name'])) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error creating label',
            ]));
    }

    $labelCustom = $data['label_name'];

    //filter by profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // get label by profile
    cradle()->trigger('label-profile-detail', $request, $response);

    if (!$response->isError()
        && $response->getResults()) {
        // merge label
        $labelCustom = $response->getResults('label_custom');

        if (in_array(strtolower($data['label_name']), array_map('strtolower', $labelCustom))) {
            // At this point, there are errors
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Label already exists',
                ]));
        }

        $labelCustom[] = $data['label_name'];

        // update label
        $request->setStage('label_id', $response->getResults('label_id'));
        $request->setStage('label_custom', $labelCustom);
        cradle()->trigger('label-update', $request, $response);

        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'    => false,
                'message'  => 'Label successfully created',
                'response' => $response->getResults()
            ]));
    }

    // get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // create label
    $labelName[] = $data['label_name'];
    $request->setStage('label_custom', $labelName);
    cradle()->trigger('label-create', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'There was error creating the label',
            ]));
    }

    // There are no errors at this point
    return $response->setContent(json_encode([
            'error'    => false,
            'message'  => 'Label successfully created',
            'response' => $response->getResults()
        ]));
});

/**
 * Add Custom Label
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/label/remove', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //filter by profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // get label by profile
    cradle()->trigger('label-profile-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'There was error deleting the label',
            ]));
    }

    // merge label
    $labelCustom = $response->getResults('label_custom');

    if (!in_array(strtolower($data['label_name']), array_map('strtolower', $labelCustom))) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'There was error deleting the label',
            ]));
    }

    if (($key = array_search($data['label_name'], $labelCustom)) !== false) {
        unset($labelCustom[$key]);
    }

    // update label
    $request->setStage('label_id', $response->getResults('label_id'));
    $request->setStage('label_custom', $labelCustom);
    cradle()->trigger('label-update', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'There was error deleting the label',
            ]));
    }

    // There are no errors at this point
    // Delete the label from the applicants attached to the label being deleted
    // Based on the profile id of the user
    //filter by profile id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('applicant-remove-label', $request, $response);

    // Send a success message
    return $response->setContent(json_encode([
            'error'    => false,
            'message'  => 'Label successfully deleted',
            'response' => $response->getResults()
        ]));
});

/**
 * Attach Label
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/attach/label', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // checks if there's no answer
    if ((!isset($data['applicant_id']) && !isset($data['applicant_ids'])) || !isset($data['label_name'])) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Not found',
            ]));
    }

    /// get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $applicants = [];
    if (isset($data['applicant_ids']) && !empty($data['applicant_ids'])) {
        $applicants = $data['applicant_ids'];
    } else {
        $applicants[] = $data['applicant_id'];
    }

    $res = [];
    //update each applicant_id with new applicant_status
    foreach ($applicants as $index => $applicant) {
        $request->setGet('noindex', 1);
        $request->setGet('nocache', 1);
        $request->setStage('applicant_id', $applicant);
        cradle()->trigger('applicant-detail', $request, $response);

        $applicantData = $response->getResults();

        //action add label
        if (!in_array($data['label_name'], $applicantData['applicant_status'])) {
            $applicantData['applicant_status'][] = $data['label_name'];
        } else {
            if (isset($data['applicant_id']) || isset($data['applicant_ids'])) {
                return $response->setContent(json_encode([
                        'error'   => true,
                        'message' => 'Label already tagged',
                    ]));
            }
        }

        //trigger update applicant statuses
        $request->removeStage();
        $request->setStage('applicant_id', $applicant);
        $request->setStage('applicant_status', $applicantData['applicant_status']);
        cradle()->trigger('applicant-update', $request, $response);

        //compose results data
        $res[] = [
            'applicant_id' => $applicant,
            'applicant_status' => $applicantData['applicant_status']
        ];
    }

    if (!is_array($res)) {
        $res[] = $res;
    }

    //set results
    if ($response->isError()) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error attaching label',
            ]));
    }

    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Label was successfully attached',
            'results' => $res
        ]));
});

/**
 * Remove Label
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/remove/label', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // checks if there's no answer
    if ((!isset($data['applicant_id']) && !isset($data['applicant_ids']))
        || !isset($data['label_name'])) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Not found',
            ]));
    }

    /// get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $applicants = [];
    if (isset($data['applicant_ids']) && !empty($data['applicant_ids'])) {
        $applicants = $data['applicant_ids'];
    } else {
        $applicants[] = $data['applicant_id'];
    }

    $res = [];
    //update each applicant_id with new applicant_status
    // Loops through the applicants
    foreach ($applicants as $index => $applicant) {
        $request->setGet('noindex', 1);
        $request->setGet('nocache', 1);
        $request->setStage('applicant_id', $applicant);
        cradle()->trigger('applicant-detail', $request, $response);

        $applicantData = $response->getResults();

        // Checks if the label being removed exists
        if (($key = array_search($data['label_name'], $applicantData['applicant_status'])) !== false) {
            unset($applicantData['applicant_status'][$key]);
        } else {
            // The label you're trying to remove doesn't exist
            // Throw an error
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Error removing label',
                ]));
        }

        //trigger update applicant statuses
        $request->removeStage();
        $request->setStage('applicant_id', $applicant);
        $request->setStage('applicant_status', $applicantData['applicant_status']);
        cradle()->trigger('applicant-update', $request, $response);

        //compose results data
        $res[] = [
            'applicant_id' => $applicant,
            'applicant_status' => $applicantData['applicant_status']
        ];
    }

    //set results
    if ($response->isError()) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error removing label',
            ]));
    }

    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Label was successfully removed',
            'results' => $res
        ]));
});

/**
 * Applicant Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/applicant/remove', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // checks if there's no answer
    if (!isset($data['applicant_id']) && !isset($data['applicant_ids'])) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Not found',
            ]));
    }

    /// get profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $applicants = [];
    if (isset($data['applicant_ids']) && !empty($data['applicant_ids'])) {
        $applicants = $data['applicant_ids'];
    } else {
        $applicants[] = $data['applicant_id'];
    }

    //update each applicant_id with inactive applicant_status
    foreach ($applicants as $index => $applicant) {
        //trigger update applicant statuses
        $request->setStage('applicant_id', $applicant);
        cradle()->trigger('applicant-remove', $request, $response);
    }

    if (!is_array($applicants)) {
        $applicants[] = $applicants;
    }

    $request->removeStage();

    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot remove applicant',
            ]));
    }

    // There are no errors at this point
    return $response->setContent(json_encode([
            'error'     => true,
            'message'   => 'Applicant was successfully remove',
            'applicant' => $applicants
        ]));
});

/**
 * Process the Attach Form Credits
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/ats/enable', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 3. Process Request

    $cost = cradle('global')->config('credits', 'attach-form');
    $available = $request->getSession('me', 'profile_credits');

    //if not enable ATS
    if (empty($request->hasSession('me', 'profile_package'))
        || !in_array('ats', $request->getSession('me', 'profile_package'))) {
        if ($available < $cost) {
            cradle('global')->flash('You just need 500 credits to attach form.', 'danger');
            return $response
                ->addValidation('code', 'insufficient-credits')
                ->setError(true, 'Insufficient-Credits');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $request
        ->setStage('profile_id', $request->getSession('me', 'profile_id'))
        ->setStage('service_name', 'Attach Form')
        ->setStage('service_meta', [
            'post_id' => $data['post_id']
        ])
        ->setStage('service_credits', $cost);

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('service-create', $request, $response);

    if (!$response->isError()) {
        //if not enable ATS
        if ($request->hasSession('me', 'profile_package')
            && !in_array('ats', $request->getSession('me', 'profile_package'))) {
            $request->setSession('me', 'profile_credits', $available - $cost);
        }
    }

    // link form
    cradle()->trigger('post-link-form', $request, $response);

    if ($response->isError()) {
        return $response->setError(true, $response->getMessage());
    }

    return $response->setError(false, 'Application successfully attached');
});

/**
 * Answer Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/post/attach/form', function ($request, $response) {

    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }


    cradle()->trigger('post-link-form', $request, $response);

    if ($response->isError()) {
        return $response->setError(true, $response->getMessage());
    }

    return $response->setError(false, 'Application successfully attached');
});

/**
 * Delete Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/remove', function ($request, $response) {
    cradle('global')->requireLogin();

    // Trigger the event
    cradle()->trigger('form-detail', $request, $response);

    // Checks if there was no errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete this form',
            ]));
    }

    // There are no errors at this point
    // Trigger the event to delete the form
    cradle()->trigger('form-remove', $request, $response);

    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Form successfully deleted',
        ]));
});

/**
 * Delete Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/bulk/remove', function ($request, $response) {
    cradle('global')->requireLogin();

    // Checks for ids
    if (!$request->hasStage('form_ids') || empty($request->getStage('form_ids'))) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete these forms',
            ]));
    }

    // At this point, there are form_ids
    $temp = $request->getStage('form_ids');
    $error = false;
    $ids = array();

    // Loops through the $temp
    foreach ($temp as $value) {
        // Checks if the value is an integer
        if (is_numeric($value)) {
            // Push the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }

    // Get the valid ids left / $ids
    $temp = $ids;
    $ids = array();

    // Loops through the temp ids / $temp
    foreach ($temp as $value) {
        // Gets the form
        // Based on form_id
        // Based on form_active
        $request->setStage('form_id', $value);
        $request->setStage('form_active', 1);
        // Trigger the event
        cradle()->trigger('form-detail', $request, $response);

        // Checks if a form was returned
        if (!$response->isError()) {
            // Pass the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }

    // Checks if there are no forms to be deleted
    if (empty($ids)) {
        // At this point, there are no forms to be deleted
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete these forms',
            ]));
    }

    // There are forms to be deleted
    // Loops through the remain ids / $ids
    foreach ($ids as $id) {
        // Sets the form to be deleted
        $request->setStage('form_id', $id);

        // Deletes the form
        cradle()->trigger('form-remove', $request, $response);
    }

    // We were able to delete ids
    // Sets the success message
    $message = 'All forms were successfully deleted';

    // Checks if there were errors
    if ($error) {
        // Sets a different message
        $message = 'Some forms couldn\'t be deleted';
    }

    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => $message,
            'ids'     => $ids
        ]));
});


/**
 * Permanent Bulk Remove Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/bulk/permanent', function ($request, $response) {
    cradle('global')->requireLogin();
    // Checks for ids
    if (!$request->hasStage('form_ids') || empty($request->getStage('form_ids'))) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete these forms',
            ]));
    }
    // At this point, there are form_ids
    $temp = $request->getStage('form_ids');
    $error = false;
    $ids = array();
    // Loops through the $temp
    foreach ($temp as $value) {
        // Checks if the value is an integer
        if (is_numeric($value)) {
            // Push the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }
    // Get the valid ids left / $ids
    $temp = $ids;
    $ids = array();
    // Loops through the temp ids / $temp
    foreach ($temp as $value) {
        // Gets the form
        // Based on form_id
        // Based on form_active
        $request->setStage('form_id', $value);
        $request->setStage('form_active', 0);
        // Trigger the event
        cradle()->trigger('form-detail', $request, $response);
        // Checks if a form was returned
        if (!$response->isError()) {
            // Pass the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }
    // Checks if there are no forms to be deleted
    if (empty($ids)) {
        // At this point, there are no forms to be deleted
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete these forms',
            ]));
    }
    // There are forms to be deleted
    // Loops through the remain ids / $ids
    foreach ($ids as $id) {
        // Sets the form to be deleted
        $request->setStage('form_id', $id);
        // Deletes the form
        cradle()->trigger('form-permanent', $request, $response);
    }
    // We were able to delete ids
    // Sets the success message
    $message = 'All forms were successfully deleted';
    // Checks if there were errors
    if ($error) {
        // Sets a different message
        $message = 'Some forms couldn\'t be deleted';
    }
    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => $message,
            'ids'     => $ids
        ]));
});

/**
 * Bulk Restore Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/bulk/restore', function ($request, $response) {
    cradle('global')->requireLogin();

    // Checks for ids
    if (!$request->hasStage('form_ids') || empty($request->getStage('form_ids'))) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot restore these forms',
            ]));
    }

    // At this point, there are form_ids
    $temp = $request->getStage('form_ids');
    $error = false;
    $ids = array();

    // Loops through the $temp
    foreach ($temp as $value) {
        // Checks if the value is an integer
        if (is_numeric($value)) {
            // Push the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }

    // Get the valid ids left / $ids
    $temp = $ids;
    $ids = array();

    // Loops through the temp ids / $temp
    foreach ($temp as $value) {
        // Gets the form
        // Based on form_id
        // Based on form_active
        $request->setStage('form_id', $value);
        $request->setStage('form_active', 0);
        // Trigger the event
        cradle()->trigger('form-detail', $request, $response);

        // Checks if a form was returned
        if (!$response->isError()) {
            // Pass the value to be deleted
            $ids[] = $value;
        } else {
            $error = true;
        }
    }

    // Checks if there are no forms to be restored
    if (empty($ids)) {
        // At this point, there are no forms to be restored
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot restore these forms',
            ]));
    }

    // There are forms to be restored
    // Loops through the remain ids / $ids
    foreach ($ids as $id) {
        // Sets the form to be deleted
        $request->setStage('form_id', $id);

        // Deletes the form
        cradle()->trigger('form-restore', $request, $response);
    }

    // We were able to restored ids
    // Sets the success message
    $message = 'All forms were successfully restored';

    // Checks if there were errors
    if ($error) {
        // Sets a different message
        $message = 'Some forms couldn\'t be restored';
    }

    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => $message,
            'ids'     => $ids
        ]));
});

/**
 * Duplicate Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/duplicate', function ($request, $response) {
    cradle('global')->requireLogin();

    // Only duplicate active forms
    // Checks if the form exists
    $request->setStage('form_active', 1);
    cradle()->trigger('form-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot duplicate this form',
            ]));
    }

    // There are no errors at this point
    // Sets the staging data
    $request->setStage('profile_id', $request->getSession()['me']['profile_id']);

    // Trigger the event to duplicate the entire form and questions
    cradle()->trigger('form-duplicate', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error duplicating form',
            ]));
    }

    $results = $response->getResults();

    // Send success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Form successfully duplicated',
            'results' => $results
        ]));
});

/**
 * Create Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/create', function ($request, $response) {
    cradle('global')->requireLogin();
    // Sets the staging data
    $request->setStage('profile_id', $request->getSession()['me']['profile_id']);

    // Sets the staging filter
    $exactFilter['form_name'] = $request->getStage('form_name');

    // return error if form_name length is greater than or equal to 256
    if (strlen($exactFilter['form_name']) >= '256') {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Application Form title too long',
            ]));
    }

    $exactFilter['profile_id'] = $request->getStage('profile_id');
    $request->setStage('exact_filter', $exactFilter);

    // Search for existing forms
    // Based on filters
    cradle()->trigger('form-search', $request, $response);
    $total = $response->getResults()['total'];

    // Checks if there were results
    if ($total) {
        // At this point, the form name / form_name was already used
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Form title already exists',
            ]));
    }

    // Sets the form flag / form_flag and removes filters
    $request->setStage('form_flag', 0);
    $request->removeStage('exact_filter');

    // Trigger the event
    cradle()->trigger('form-create', $request, $response);

    // Checks if there was no errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => $response->getValidation()['form_name'],
            ]));
    }

    // The form was successfully created at this point
    $results = $response->getResults();
    return $response->setContent(json_encode([
            'error'   => false,
            'results' => $results
        ]));
});

/**
 * Publish Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/publish', function ($request, $response) {
    cradle('global')->requireLogin();

    // Checks if the form exists
    cradle()->trigger('form-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error publishing form',
            ]));
    }

    // Trigger the event
    cradle()->trigger('form-publish', $request, $response);

    // Checks if there was no errors
    if (!$response->isError()) {
        $results = $response->getResults();

        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => false,
                'message' => 'Successfully published form',
                'results' => $results
            ]));
    }

    // At this point, there are errors
    return $response->setContent(json_encode([
            'error'   => true,
            'message' => 'Error publishing form',
        ]));
});

/**
 * Edit Form
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/form/update', function ($request, $response) {
    cradle('global')->requireLogin();

    // Gets the detail of the form
    cradle()->trigger('form-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot update this form'
            ]));
    }

    // Assume that the session exists
    // Sets the profile id / profile_id
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    // At this point there are no errors
    // Updates the form
    cradle()->trigger('form-update', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        if (isset($response->getValidation()['form_name'])) {
            // At this point, there are errors
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => $response->getValidation()['form_name']
                ]));
        }

        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot update this form'
            ]));
    }

    // At this point, there are errors
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Form title successfully updated',
            'results' => $response->getResults()
        ]));
});

/**
 * Get the form and questions
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/form/:post_id', function ($request, $response) {
    // Checks for forms
    $request->setStage('form_flag', 1);
    $request->setStage('form_active', 1);
    cradle()->trigger('form-post', $request, $response);

    // Remove what we set at stage
    $request->removeStage('post_id');
    $request->removeStage('form_active');

    // Checks if there are no errors
    if (!$response->isError()) {
        // Gets the questions
        // Based on form_id
        $form = $response->getResults();
        $request->setStage('filter', 'form_id', $form['form_id']);
        $request->setStage('order', 'question_priority', 'ASC');
        cradle()->trigger('question-search', $request, $response);
        $results = $response->getResults();

        // Remove what we set at stage
        $request->removeStage('filter', 'form_id');

        // Checks if there are questions
        if (!empty($results['rows'])) {
            // Gets the questions
            $data['questions'] = $results['rows'];
            $data['form'] = $form;

            //it was good Set json Content
            return $response->setError(false)->setResults($data);
        }
    }

    //it was good Set json Content
    return $response->setError(true);
});

/**
 * Get the form and questions
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/form/preview/:form_id', function ($request, $response) {
    // Checks for forms
    cradle()->trigger('form-post', $request, $response);

    // Checks if there are no errors
    if (!$response->isError()) {
        // Gets the questions
        // Based on form_id
        $form = $response->getResults();
        $request->setStage('filter', 'form_id', $form['form_id']);
        $request->setStage('order', 'question_priority', 'ASC');
        cradle()->trigger('question-search', $request, $response);
        $results = $response->getResults();

        // Remove what we set at stage
        $request->removeStage('filter', 'form_id');

        // Checks if there are questions
        if (!empty($results['rows'])) {
            // Gets the questions
            $data['questions'] = $results['rows'];
            $data['form'] = $form;

            //it was good Set json Content
            return $response->setError(false)->setResults($data);
        }
    }

    //it was good Set json Content
    return $response->setError(true);
});

/**
 * Add the question to the form
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/question/create', function ($request, $response) {
    // Checks if the form exists
    cradle()->trigger('form-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error saving question to form',
            ]));
    }

    // Format the question data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Variable declarations
    $choices = array();

    // Checks for choices
    if (!empty($data['question_choices'])) {
        // Loops through the choices
        foreach ($data['question_choices'] as $choice) {
            // Checks if the choice is not empty
            if (trim($choice)) {
                $choices[] = $choice;
            }
        }
    }

    // Overwrite the choices
    $request->setStage('question_choices', $choices);

    // Checks for question_custom
    if (isset($data['question_custom']) && $data['question_custom'] == 'on') {
        $request->setStage('question_custom', 1);
    }

    // Checks for question_file
    if (isset($data['question_file']) && $data['question_file'] == 'on') {
        $request->setStage('question_file', 1);
    }

    // Trigger the question create event
    cradle()->trigger('question-create', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'errors'  => $response->getValidation(),
                'message' => 'There are some errors in the form',

            ]));
    }

    // There are no errors at this point
    // Send a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Question was successfully added',
            'results' => $response->getResults()
        ]));
});

/**
 * Add the question to the form
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/question/update', function ($request, $response) {
    cradle('global')->requireLogin();

    // Checks if the form exists
    cradle()->trigger('form-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error saving question to form',
            ]));
    }

    // Checks if the question exists
    cradle()->trigger('question-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error saving question to form',
            ]));
    }

    // Format the question data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Variable declarations
    $choices = array();

    // Checks for choices
    if (!empty($data['question_choices'])) {
        // Loops through the choices
        foreach ($data['question_choices'] as $choice) {
            // Checks if the choice is not empty
            if (trim($choice)) {
                $choices[] = $choice;
            }
        }
    }

    // Overwrite the choices
    $request->setStage('question_choices', $choices);

    // Checks for question_custom
    if (isset($data['question_custom']) && $data['question_custom'] == 'on') {
        $request->setStage('question_custom', 1);
    }

    // Checks for question_file
    if (isset($data['question_file']) && $data['question_file'] == 'on') {
        $request->setStage('question_file', 1);
    }

    // Trigger the question edit event
    cradle()->trigger('question-update', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'errors'  => $response->getValidation(),
                'message' => 'There are some errors in the form',

            ]));
    }

    // There are no errors at this point
    // Gets the question details
    cradle()->trigger('question-detail', $request, $response);

    // Send a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Question was successfully edited',
            'results' => $response->getResults()
        ]));
});

/**
 * Delete Question
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/question/delete', function ($request, $response) {
    cradle('global')->requireLogin();

    // Trigger the event
    cradle()->trigger('question-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete this question'
            ]));
    }

    // There are no errors at this point
    // Delete the question
    cradle()->trigger('question-remove', $request, $response);

    return $response->setContent(json_encode(['error' => false]));
});

/**
 * Get Question Detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/question/detail/:question_id', function ($request, $response) {
    cradle('global')->requireLogin();

    // Trigger the event
    cradle()->trigger('question-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot edit this question'
            ]));
    }

    // There are no errors at this point
    $question = $response->getResults();

    // Return the question
    return $response->setContent(json_encode([
            'error'   => false,
            'results' => $question
        ]));
});

/**
 * Update Priority Question
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/question/priority', function ($request, $response) {
    cradle('global')->requireLogin();

    $data = [];

    // Format the question data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Variable declarations
    $priority = array();

    if (isset($data['rows'])) {
        // Checks every id
        foreach ($data['rows'] as $key => $value) {
            if (!empty($value['question_priority'])) {
                $request->setStage('question_priority', $value['question_priority']);
                $request->setStage('question_id', $value['question_id']);

                    // There are no errors at this point
                cradle()->trigger('question-priority', $request, $response);
            }
        }
    }

    return $response->setContent(json_encode(['error' => false]));
});

/**
 * Ajax routes for the interview scheduler
 */
/**
 * Get the profiles who have liked the post
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/interview/post/:post_id/likers', function ($request, $response) {
    cradle('global')->requireLogin();

    // Checks for the post
    $request->setStage(
        'filter',
        'post_id',
        $request->getStage('post_id')
    );

    // Trigger the event to search for the job
    // Use the search and not the detail
    // Search also looks if the post is active and not expired
    // Based on the post id / post_id
    cradle()->trigger('post-search', $request, $response);

    // Gets the results
    $results = $response->getResults();

    // Removes the staging data filters
    $request->removeStage('filter');

    // Checks if there were no rows returned
    if (!$results['total']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'This post has no likers',
            ]));
    }

    // There are no errors at this point
    // gets the applicants already scheduled for an interview with this post
    cradle()->trigger('interview-schedule-post', $request, $response);
    $results = $response->getResults();

    // Checks if there are results
    if (!empty($results)) {
        $notFilter['post_liked.profile_id'] = array();

        // Loops through the rows
        foreach ($results as $row) {
            $notFilter['post_liked.profile_id'][] = $row['profile_id'];
        }

        $request->setStage('not_filter', $notFilter);
    }

    // Trigger the event to get likers
    // Based on the post id / post_id
    // Based on interested
    $request->setStage('interested', true);
    $request->setStage('exact_filter', 'profile_company', '""');
    cradle()->trigger('post-likes', $request, $response);

    // Gets the results
    $results = $response->getResults();

    // Return the list of users who have liked the post
    return $response->setContent(json_encode([
            'error'    => false,
            'profiles' => $results['rows']
        ]));
});

/**
 * Returns the list of interview settings of the user
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/interview/setting/:interview_setting_id', function ($request, $response) {
    /**
     * Notes for this page
     * Only show settings for this user - based on the session profile_id
     * Only show settings that are not expired and active
     * Only show available interview dates
     * - dates greater than or equal to today
     * - dates with available slots
     */
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $request->setStage(
        'not_filter',
        'interview_setting_id',
        $data['interview_setting_id']
    );

    // Trigger the event
    // Gets the schedule settings / availabilities
    // Based on profile id / profile_id
    // Get total schedules as well
    $request->setStage('schedule_total', true);
    $request->setStage('exclude_maxed_out', true);
    $request->setStage('range', 20);
    $request->setStage('filter', 'today', true);
    $request->setStage('order', 'interview_setting_date', 'ASC');
    cradle()->trigger('interview-setting-search', $request, $response);

    $results = $response->getResults();

    foreach ($results['rows'] as $index => $row) {
        $row['interview_setting_date_format'] = date('M d, Y', strtotime($row['interview_setting_date']));
        $row['interview_setting_start_format'] = date('g:i A', strtotime($row['interview_setting_start_time']));
        $row['interview_setting_end_format'] = date('g:i A', strtotime($row['interview_setting_end_time']));
        $results['rows'][$index] = $row;
    }

    // Returns what ever rows we have
    return $response->setContent(json_encode([
            'error'   => false,
            'results' => $results['rows'],
            'total'   => $results['total']
        ]));
});

/**
 * Add Availability
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/interview/availability/list', function ($request, $response) {
    cradle('global')->requireLogin();

    // Sets the filters
    $filter['profile_id'] = $request->getSession('me', 'profile_id');
    $filter['dates']['start_date'] = date('Y-m-d', strtotime('now'));
    $filter['dates']['end_date'] = strtotime('+3 months', strtotime('now'));
    $filter['dates']['end_date'] = date('Y-m-d', $filter['dates']['end_date']);

    // Set the columns
    $columns = array(
        'profile_id',
        'interview_setting_id',
        'interview_setting_date');

    $request->setStage('filter', $filter);
    $request->setStage('columns', $columns);

    // Trigger the event to search for the job
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();

    $dates = $results['rows'];

    // Checks if the rows are not empty
    if (!empty($dates)) {
        // Loops through the dates
        foreach ($dates as $index => $date) {
            $dates[$index] = $date['interview_setting_date'];
        }
    }

    // Return the list of users who have liked the post
    return $response->setContent(json_encode([
            'error'   => false,
            'results' => $dates
        ]));
});

/**
 * Add Availability
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/availability/add', function ($request, $response) {
    cradle('global')->requireLogin();

    // Gets the data being passed
    $data = $request->getStage();
    $errors = array();

    $today = strtotime(date('d F Y', strtotime('now')));

    // Checks if the start time is greater than the end time
    if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
        $errors[] = 'Please enter a valid time range';
    }

    // Checks if the start date is empty
    if (empty($data['start_date'])) {
        $errors['start_date'] = 'Start Date cannot be empty';
    }

    // Formats the dates
    if (!empty($data['start_date'])) {
        // Checks if it is a past date
        if ($today > strtotime($data['start_date'])) {
            $errors['start_date'] = 'Please select a valid date';
        } else {
            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        }
    }

    // Checks if the end_date is not empty
    if (!empty($data['end_date']) && !isset($errors['start_date'])) {
        // Checks if the start date is greater than the end date
        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors[] = 'Please enter a valid date range';
        }

        $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
    }

    // Checks if the slots are not numbers
    if (!is_numeric($data['slots']) || $data['slots'] < 1) {
        $errors[] = 'Maximum slots should be a valid number';
    }

    // Checks if there are any errors
    if (!empty($errors)) {
        // Return an error message with the errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error adding availability',
                'errors'  => $errors
            ]));
    }

    // Sets the filters
    $filter['profile_id'] = $request->getSession('me', 'profile_id');
    $filter['dates'] = array('start_date' => $data['start_date'],
        'end_date' => $data['end_date']);

    // Sets the filters to stage
    $request->setStage('filter', $filter);

    // Adds grouping
    $request->setStage('group', 'interview_setting_date');

    // Trigger the event to search for the job
    cradle()->trigger('interview-setting-search', $request, $response);

    // Gets the results
    $results = $response->getResults();

    // Removes the staging data filters
    $request->removeStage('filter');
    $request->removeStage('group');

    // Checks if there were no rows returned
    if (!empty($results['rows'])) {
        // At this point, the dates already exist
        $dates = [];

        // Loops through the dates that exist
        foreach ($results['rows'] as $row) {
            $dates[] = date('F d, Y', strtotime($row['interview_setting_date']));
        }

        // Return an error message with the dates
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error adding availability',
                'dates'   => $dates
            ]));
    }

    // There are no errors at this point
    // All dates can then be added
    $start = new DateTime($data['start_date']);
    $end = new DateTime($data['end_date']);
    $interviews = array();

    // Checks if there is no end date
    if (empty($data['end_date'])) {
        $request->removeStage();

        // Sets the values to be added
        $insert = array();
        $insert['profile_id'] = $request->getSession('me', 'profile_id');
        $insert['interview_setting_date'] = $start->format('Y-m-d');
        $insert['interview_setting_start_time'] = $data['start_time'];
        $insert['interview_setting_end_time'] = $data['end_time'];
        $insert['interview_setting_slots'] = floor($data['slots']);
        $request->setStage($insert);

        // Creates the interview setting
        // Linked to the profile
        cradle()->trigger('interview-setting-create', $request, $response);

        // Checks if there are no errors
        if (!$response->isError()) {
            // Pass this back to the requester
            $return = array();
            $result = $response->getResults();
            $return['id']             = $result['interview_setting_id'];
            $return['date']           = $start->format('F d, Y');
            $return['start']          = $insert['interview_setting_start_time'];
            $return['start_formated'] = date('g:i A', strtotime($insert['interview_setting_start_time']));
            $return['end']            = $insert['interview_setting_end_time'];
            $return['end_formated']   = date('g:i A', strtotime($insert['interview_setting_end_time']));
            $return['slots']          = $insert['interview_setting_slots'];
            $interviews[]             = $return;
        }

        $request->removeStage();
    } else {
        // At this point, there was an end date
        // Loops through the dates
        for ($i = $start; $i <= $end; $i->modify('+1 day')) {
            $request->removeStage();

            // Sets the values to be added
            $insert = array();
            $insert['profile_id'] = $request->getSession('me', 'profile_id');
            $insert['interview_setting_date'] = $i->format('Y-m-d');
            $insert['interview_setting_start_time'] = $data['start_time'];
            $insert['interview_setting_end_time'] = $data['end_time'];
            $insert['interview_setting_slots'] = floor($data['slots']);
            $request->setStage($insert);

            // Creates the interview setting
            // Linked to the profile
            cradle()->trigger('interview-setting-create', $request, $response);

            // Checks if there are no errors
            if (!$response->isError()) {
                // Pass this back to the requester
                $return = array();
                $result = $response->getResults();
                $return['id']             = $result['interview_setting_id'];
                $return['date']           = $i->format('F d, Y');
                $return['start']          = $insert['interview_setting_start_time'];
                $return['start_formated'] = date('g:i A', strtotime($insert['interview_setting_start_time']));
                $return['end']            = $insert['interview_setting_end_time'];
                $return['end_formated']   = date('g:i A', strtotime($insert['interview_setting_end_time']));
                $return['slots']          = $insert['interview_setting_slots'];
                $interviews[]             = $return;
            }

            $request->removeStage();
        }
    }

    // Return the list of users who have liked the post
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Interview Schedules successfully added',
            'results' => $interviews
        ]));
});

/*
 * Edit Availability
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/availability/edit/:interview_setting_id', function ($request, $response) {
    cradle('global')->requireLogin();

    // Gets the data being passed
    $data = $request->getStage();
    $errors = array();

    $today = strtotime(date('d F Y', strtotime('now')));

    // Checks if the start date is empty
    if (empty($data['date'])) {
        $errors[] = 'Date cannot be empty';
    }

    // Checks if the slots are not numbers
    if (!is_numeric($data['slots']) || $data['slots'] < 1) {
        $errors[] = 'Maximum slots should be a valid number';
    }

    // Checks if the date has passed
    if ($today > strtotime($data['date'])) {
        $errors[] = 'Please select a valid date';
    }

    // Checks if the start time is empty
    if (empty($data['start_time'])) {
        $errors[] = 'Please enter a start time';
    }

    // Checks if the end time is empty
    if (empty($data['end_time'])) {
        $errors[] = 'Please enter a end time';
    }

    // Checks if the start time is greater than the end time
    if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
        $errors[] = 'Please enter a valid time range';
    }

    // Checks if there are any errors
    if (!empty($errors)) {
        // Return an error message with the errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error editing availability',
                'errors'  => $errors
            ]));
    }

    // Check if the availability exists
    cradle()->trigger('interview-setting-detail', $request, $response);

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot edit availability'
            ]));
    }

    $data['date'] = date('Y-m-d', strtotime($data['date']));

    // Sets the filters
    $filter['profile_id'] = $request->getSession('me', 'profile_id');
    $filter['interview_setting_date'] = $data['date'];

    // Sets the filters to stage
    $request->setStage('filter', $filter);
    $request->setStage(
        'not_filter',
        'interview_setting_id',
        $data['interview_setting_id']
    );

    // Adds grouping
    $request->setStage('group', 'interview_setting_date');

    // Trigger the event to search for the job
    cradle()->trigger('interview-setting-search', $request, $response);

    // Gets the results
    $results = $response->getResults();

    // Removes the staging data filters
    $request->removeStage('filter');
    $request->removeStage('not_filter');
    $request->removeStage('group');

    // Checks if there were no rows returned
    if ($results['total']) {
        // At this point, the dates already exist
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'This date has already been taken',
            ]));
    }

    // Remove the staging data
    $request->removeStage();

    // Sets the filters
    $request->setStage('filter', 'interview_setting_id', $data['interview_setting_id']);
    $request->setStage('schedule_total', true);

    // Check if the availability exists
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();

    // Checks if there are no settings
    if (!$results['total']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete this availability'
            ]));
    }

    $setting = $results['rows'][0];

    // Checks if there are interviews pending
    if ($setting['slots_taken'] > $data['slots']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot edit this availability. Not enough slots.'
            ]));
    }

    // Remove the staging data
    $request->removeStage();

    // There are no errors at this point
    // The setting can be now updated
    // Updates the setting
    $update['interview_setting_id'] = $data['interview_setting_id'];
    $update['interview_setting_date'] = $data['date'];
    $update['interview_setting_start_time'] = $data['start_time'];
    $update['interview_setting_end_time'] = $data['end_time'];
    $update['interview_setting_slots'] = floor($data['slots']);
    $request->setStage($update);

    // Updates the setting
    cradle()->trigger('interview-setting-update', $request, $response);

    $request->removeStage();

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating the availability'
            ]));
    }

    // Pass this back to the requester
    $return = array();
    $return['date'] = date('F d, Y', strtotime($data['date']));
    $return['start'] = date('g:i A', strtotime($update['interview_setting_start_time']));
    $return['end'] = date('g:i A', strtotime($update['interview_setting_end_time']));
    $return['slots'] = $update['interview_setting_slots'];

    // Return the list of users who have liked the post
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Interview Schedules successfully edited',
            'results' => $return
        ]));
});

/**
 * Edit Availability
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/availability/remove/:interview_setting_id', function ($request, $response) {
    cradle('global')->requireLogin();

    // Gets the data being passed
    $data = $request->getStage();

    // Sets the filters
    $request->setStage('filter', 'interview_setting_id', $data['interview_setting_id']);
    $request->setStage('schedule_total', true);

    // Check if the availability exists
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();

    // Checks if there are no settings
    if (!$results['total']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete this availability'
            ]));
    }

    $setting = $results['rows'][0];

    // Checks if there are interviews pending
    if ($setting['slots_taken']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot delete this availability. There are interviews for this date.'
            ]));
    }

    // Removes the date
    cradle()->trigger('interview-setting-remove', $request, $response);

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error deleting the availability'
            ]));
    }

    // Return the list of users who have liked the post
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Interview Schedules successfully removed'
        ]));
});

$cradle->post('/ajax/interview/contact/update', function ($request, $response) {
    cradle('global')->requireLogin();

    // Gets the data being passed
    $data = $request->getStage();
    $errors = array();

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    // Triggers the profile detail
    cradle()->trigger('profile-detail', $request, $response);

    // Checks if the profile detail does not exists
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot update contact details'
            ]));
    }

    // Checks for errors
    // Checks for contact_person
    if (!isset($data['contact_person']) || empty($data['contact_person'])) {
        $errors[] = 'Contact Person cannot be empty';
    }

    // Checks for contact_number
    if (!isset($data['contact_number']) || empty($data['contact_number'])) {
        $errors[] = 'Contact Number cannot be empty';
    } else if (!preg_match('/^\d+(-\d+)*$/', $data['contact_number'])) {
        $errors[] = 'Contact Number should be numeric';
    }

    // Checks for contact_email
    if (!isset($data['contact_email']) || empty($data['contact_email'])) {
        $errors[] = 'Contact Email cannot be empty';
    } else if (!UtilityValidator::isEmail($data['contact_email'])) {
        $errors[] = 'Please enter a valid email address';
    }

    // Checks for contact_address
    if (!isset($data['contact_address']) || empty($data['contact_address'])) {
        $errors[] = 'Contact Address cannot be empty';
    }

    // Checks for errors
    if (!empty($errors)) {
        // Send an error message with
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating contact details',
                'errors'  => $errors
            ]));
    }

    // At this point, there are no errors
    $update['profile_id'] = $request->getSession('me', 'profile_id');
    $update['profile_interviewer'] = array(
        'contact_person'  => $data['contact_person'],
        'contact_number'  => $data['contact_number'],
        'contact_email'   => $data['contact_email'],
        'contact_address' => $data['contact_address'],
    );

    // Sets the data to be updated
    $request->setStage($update);

    // Trigger the update
    cradle()->trigger('profile-update', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating contact details',
            ]));
    }

    // There are no errors at this point
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Successfully updated your contact details',
        ]));
});

/**
 * Get the schedule for the profile who logged in
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/schedule', function ($request, $response) {
    /**
     * Notes for this page
     * Only show posts for this user - based on the session profile_id
     * Only show posts that are not expired and active
     * Only show available interview dates
     * - dates greater than or equal to today
     * - dates with available slots
     */
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $request->setStage(
        'filter',
        'interview_setting_id',
        $data['interview_setting_id']
    );

    $request->setStage('filter', 'today', true);
    $request->setStage('schedule_total', true);

    // Triggers the event
    // Use search instead of detail
    // search auto filters for active results
    cradle()->trigger('interview-setting-search', $request, $response);

    // Remove the staging data
    $request->removeStage();

    // Get the results
    $results = $response->getResults();

    // Checks if there were no results returned
    if (empty($results['rows'])) {
        // The interview could either be expired or inactive at this point
        // Return an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error scheduling interview'
            ]));
    }

    // Get the interview setting details
    $details['settings'] = $results['rows'][0];

    // Checks if there are enough slots
    if ($details['settings']['slots_taken'] >= $details['settings']['interview_setting_slots']) {
        // There are not enough slots for this interview to be scheduled
        // Return an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Cannot schedule interview. Not enough slots.'
            ]));
    }

    // Removes the staging date
    $request->removeStage();

    // Checks if the post is active
    $request->setStage('filter', 'post_id', $data['post_id']);
    cradle()->trigger('post-search', $request, $response);

    $results = $response->getResults();

    // Checks if there was no post returned
    if (empty($results['rows'])) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error scheduling interview'
            ]));
    }

    // Gets the post
    $details['post'] = $results['rows'][0];

    // Checks if the post has a package
    if (!cradle('global')->requireProfilePackage('interview-scheduler')
        && !in_array('interview-post', $details['post']['post_package'])) {
        $message = 'This post has not been activated for scheduling interviews. '
            . 'Please enable them at the Posts Section.';
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => $message
            ]));
    }

    // Removes the staging date
    $request->removeStage();

    // Check for the profile
    $request->setStage('profile_id', $data['profile_id']);
    cradle()->trigger('profile-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error scheduling interview'
            ]));
    }

    // Gets the profile to be interviewed
    $details['profile'] = $response->getResults();

    // Removes the staging date
    $request->removeStage();

    // Checks if this user has already been interviewed before
    $request->setStage('filter', 'profile_id', $data['profile_id']);
    $request->setStage('filter', 'interview_setting_id', $data['interview_setting_id']);
    $request->setStage('filter', 'interview_schedule_status', 'Interviewed');
    cradle()->trigger('interview-schedule-search', $request, $response);

    $checker = $response->getResults();

    // Checks if a schedule is already set for this user for this setting
    $request->removeStage('filter', 'interview_schedule_status');
    $request->setStage('filter', 'post_id', $data['post_id']);
    $request->setStage('filter', 'succeeding', true);
    cradle()->trigger('interview-schedule-search', $request, $response);
    $results = $response->getResults();

    // Checks if the post exists
    if ($results['total']) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'You have already scheduled this applicant for interview'
            ]));
    }

    // Removes the staging date
    $request->removeStage();

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    cradle()->trigger('profile-detail', $request, $response);
    $details['interviewer'] = $response->getResults();

    // Checks if that user already has an interview
    // Based on interview_setting_id

    $data['interview_schedule_date'] = $details['settings']['interview_setting_date'];
    $data['interview_schedule_start_time'] = $details['settings']['interview_setting_start_time'];
    $data['interview_schedule_end_time'] = $details['settings']['interview_setting_end_time'];
    $data['interview_schedule_flag'] = 1;

    // At this point, we can now schedule the interview
    $request->setStage($data);
    $request->setStage($details);

    cradle()->trigger('interview-schedule-create', $request, $response);

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error scheduling interview'
            ]));
    }

    // Removes the staging date
    $request->removeStage('filter');

    // Sets the email data
    $emailData = array();
    $emailData['title'] = 'You have an interview!';
    $emailData['template'] = 'schedule.html';

    $request->setStage('email_data', $emailData);

    // Trigger the email
    // Assume that we have sent the email
    // No error response is sent back if the email was not sent
    cradle()->trigger('interview-schedule-email', $request, $response);

    // Removes the staging date
    $request->removeStage('filter');

    // Search for the applicant
    // Based on applicant_post
    // Based on applicant_profile
    $request->setStage('filter', 'post_id', $data['post_id']);
    $request->setStage('filter', 'profile_id', $data['profile_id']);
    $request->setStage('exclude', 'form', true);
    cradle()->trigger('applicant-search', $request, $response);
    $results = $response->getResults();

    // Checks if the applicant does not exist
    if (!$results['total']) {
        // At this point, there is no applicant
        // Remove the staging data
        $request->removeStage();

        $request->setStage('profile_id', $data['profile_id']);
        $request->setStage('post_id', $data['post_id']);

        // At this point, no applicant was returned
        cradle()->trigger('applicant-create', $request, $response);

        $applicant = $response->getResults();
    } else {
        $applicant = $results['rows'][0];
    }

    // Checks if applicant status exists
    if (!isset($applicant['applicant_status']) || empty($applicant['applicant_status'])) {
        $applicant['applicant_status'] = array();
    }

    // Checks if there were previous interviews
    if ($checker['total']) {
        // Checks if the tag is not yet added
        if (!in_array('Follow Up Interview', $applicant['applicant_status'])) {
            $applicant['applicant_status'][] = 'Follow Up Interview';
        }
    } else {
        // Checks if the tag is not yet added
        if (!in_array('For Interview', $applicant['applicant_status'])) {
            $applicant['applicant_status'][] = 'For Interview';
        }
    }

    // After the interview was created, we should update the labels at ATS
    $request->setStage('applicant_id', $applicant['applicant_id']);
    $request->setStage('applicant_status', $applicant['applicant_status']);
    cradle()->trigger('applicant-update', $request, $response);

    // Remove the staging data
    $request->removeStage();

    // Return the list of schedule
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Applicant Successfully invited for Interview'
        ]));
});

/**
 * Removes the interview schedule
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/schedule/remove/:interview_schedule_id', function ($request, $response) {
    /**
     * Notes for this page
     * Only remove schedules which have links to a post
     * Only remove schedules which have links to a profile
     * Only remove schedules which have links to a interview setting
     */
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Checks if this interview schedule exists
    cradle()->trigger('interview-schedule-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error removing interview schedule'
            ]));
    }

    // Gets the details
    $schedule = $response->getResults();

    // Check interview schedule type
    if ($schedule['interview_schedule_type'] == 'anonymous') {
        // unlink interview schedule to inteview setting
        $request->setStage('interview_schedule_id', $schedule['interview_schedule_id']);
        cradle()->trigger('interview-schedule-remove', $request, $response);

        // Remove the staging data
        $request->removeStage();

        // There are no errors at this point
        // Return a success message
        return $response->setContent(json_encode([
                'error'   => false,
                'message' => 'Interview Schedule successfully removed',
                'setting' => $schedule['interview_setting_date']
            ]));
    }

    // Checks for empty linkage
    if (empty($schedule['post_id'])) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error removing interview schedule'
            ]));
    }

    // At this point, we can assume that there are existing links to be removed
    $request->setStage($schedule);
    cradle()->trigger('interview-schedule-remove', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error removing interview schedule'
            ]));
    }

    // There are no errors at this point
    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Interview Schedule successfully removed',
            'setting' => $schedule['interview_setting_date']
        ]));
});

/**
 * Reschedules the interview
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/schedule/reschedule', function ($request, $response) {
    /**
     * Notes for this page
     * Only reschedules schedules which are valid based on IDs
     * Only reschedules schedules which have links
     */
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Checks if this interview schedule exists
    cradle()->trigger('interview-schedule-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Gets the details
    $schedule = $response->getResults();

    // Check interview schedule type
    if ($schedule['interview_schedule_type'] == 'anonymous') {
        // Prepare stage data and get interview schedule date
        $request->setStage('filter', 'interview_setting_id', $data['interview_setting_id']);
        // $request->setStage('columns', ['interview_setting_date']);
        cradle()->trigger('interview-setting-search', $request, $response);
        $interview = $response->getResults('rows', '0');

        // // Checks if interview was empty
        if (empty($interview)) {
            // return error
            return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
        }

        // prepare stage to update interview schedule
        $request->setStage('interview_schedule_id', $schedule['interview_schedule_id']);
        $request->setStage('interview_schedule_date', $interview['interview_setting_date']);
        cradle()->trigger('interview-schedule-update', $request, $response);

        // // Checks if there's are errors
        if ($response->isError()) {
            // At this point, there are errors
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Error updating interview'
                ]));
        }

        // unlink interview schedule to inteview setting
        $request->setStage('interview_schedule_id', $schedule['interview_schedule_id']);
        cradle()->trigger('interview-schedule-remove', $request, $response);

        // link interview schedule to inteview setting
        $request->setStage('interview_schedule_id', $response->getResults('interview_schedule_id'));
        $request->setStage('interview_setting_id', $interview['interview_setting_id']);
        cradle()->trigger('interview-schedule-link', $request, $response);

        // Remove the staging data
        $request->removeStage();

        // Return the list of schedule
        return $response->setContent(json_encode([
                'error'   => false,
                'message' => 'Applicant Successfully Rescheduled'
            ]));
    }

    // Checks for empty linkage
    if (empty($schedule['post_id'])) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    // Based on interview setting id / interview_setting_id
    $request->setStage('filter', 'interview_setting_id', $data['interview_setting_id']);
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();

    // Checks if no interview was returned
    if (!$results['total']) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    $interview = $results['rows'][0];
    $request->removeStage('filter');

    // Get the post
    // Based on post id / post_id
    $request->setStage('filter', 'post_id', $schedule['post_id']);
    cradle()->trigger('post-search', $request, $response);
    $results = $response->getResults();

    // Checks if no interview was returned
    if (!$results['total']) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    $post = $results['rows'][0];

    $request->removeStage('filter');

    // Get the profile
    // Based on profile id / profile_id
    $request->setStage('filter', 'profile_id', $schedule['profile_id']);
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults();

    // Checks if no interview was returned
    if (!$results['total']) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    $profile = $results['rows'][0];

    $request->removeStage('filter');

    // At this point, we can assume that there are existing links to be removed
    $request->setStage($schedule);
    cradle()->trigger('interview-schedule-remove', $request, $response);
    $request->removeStage();

    $reschedule['interview_schedule_date'] = $interview['interview_setting_date'];
    $reschedule['interview_schedule_start_time'] = $interview['interview_setting_start_time'];
    $reschedule['interview_schedule_end_time'] = $interview['interview_setting_end_time'];
    $reschedule['interview_schedule_flag'] = 1;
    $reschedule['interview_setting_id'] = $data['interview_setting_id'];
    $reschedule['post_id'] = $schedule['post_id'];
    $reschedule['profile_id'] = $schedule['profile_id'];

    $request->setStage($reschedule);
    cradle()->trigger('interview-schedule-create', $request, $response);
    $request->removeStage();

    $results = $response->getResults();

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error scheduling interview'
            ]));
    }

    $request->removeStage('filter');

    // Gets the updated interview
    $request->setStage('interview_schedule_id', $results['interview_schedule_id']);
    cradle()->trigger('interview-schedule-detail', $request, $response);
    $interview = $response->getResults();
    $request->removeStage();

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    cradle()->trigger('profile-detail', $request, $response);
    $interviewer = $response->getResults();
    $request->removeStage();

    $interview['post'] = $post;
    $interview['profile'] = $profile;
    $interview['interviewer'] = $interviewer;

    $request->setStage($interview);

    // Sets the email data
    $emailData = array();
    $emailData['title'] = 'Your interview has been rescheduled!';
    $emailData['template'] = 'reschedule.html';

    $request->setStage('email_data', $emailData);

    // Trigger the email
    // Assume that we have sent the email
    // No error response is sent back if the email was not sent
    cradle()->trigger('interview-schedule-email', $request, $response);

    // Search for the applicant
    // Based on applicant_post
    // Based on applicant_profile
    $request->setStage('filter', 'post_id', $schedule['post_id']);
    $request->setStage('filter', 'profile_id', $schedule['profile_id']);
    cradle()->trigger('applicant-search', $request, $response);
    $results = $response->getResults();

    // Checks if the applicant does not exist
    if (!$results['total']) {
        // At this point, there is no applicant
        // Remove the staging data
        $request->removeStage();

        $request->setStage('profile_id', $schedule['profile_id']);
        $request->setStage('post_id', $schedule['post_id']);

        // At this point, no applicant was returned
        cradle()->trigger('applicant-create', $request, $response);

        $applicant = $response->getResults();
    } else {
        $applicant = $results['rows'][0];
    }

    // Checks if applicant status exists
    if (!isset($applicant['applicant_status']) || empty($applicant['applicant_status'])) {
        $applicant['applicant_status'] = array();
    }

    // Checks if the tag is not yet added
    if (!in_array('Rescheduled', $applicant['applicant_status'])) {
        $applicant['applicant_status'][] = 'Rescheduled';
    }

    // Checks if there is a For Interview tag
    if (($key = array_search('For Interview', $applicant['applicant_status'])) !== false) {
        unset($applicant['applicant_status'][$key]);
    }

    // After the interview was created, we should update the labels at ATS
    $request->setStage('applicant_id', $applicant['applicant_id']);
    $request->setStage('applicant_status', $applicant['applicant_status']);
    cradle()->trigger('applicant-update', $request, $response);

    // Remove the staging data
    $request->removeStage();

    // Return the list of schedule
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Applicant Successfully Rescheduled'
        ]));
});

/**
 * Tag interview as Interviewed
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interview/schedule/tag/:interview_schedule_id', function ($request, $response) {
    /**
     * Notes for this page
     * Only tag schedules which are valid based on IDs
     * Only tag schedules which have links
     */
    cradle('global')->requireLogin();

    $data = $request->getStage();

    // Checks if this interview schedule exists
    cradle()->trigger('interview-schedule-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating this interview'
            ]));
    }

    // Gets the details
    $schedule = $response->getResults();

    // Checks for empty linkage
    if (empty($schedule['post_id'])) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating this interview'
            ]));
    }

    $request->removeStage('filter');

    // Get the post
    // Based on post id / post_id
    $request->setStage('filter', 'post_id', $schedule['post_id']);
    cradle()->trigger('post-search', $request, $response);
    $results = $response->getResults();

    // Checks if no interview was returned
    if (!$results['total']) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    $post = $results['rows'][0];

    $request->removeStage('filter');

    // Get the profile
    // Based on profile id / profile_id
    $request->setStage('filter', 'profile_id', $schedule['profile_id']);
    cradle()->trigger('profile-search', $request, $response);
    $results = $response->getResults();

    // Checks if no interview was returned
    if (!$results['total']) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error rescheduling this interview'
            ]));
    }

    // Get the interview setting
    $profile = $results['rows'][0];

    $request->removeStage('filter');

    // At this point, we can update the schedule status
    $request->setStage('interview_schedule_status', $data['tag']);
    cradle()->trigger('interview-schedule-update', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating this interview'
            ]));
    }

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    cradle()->trigger('profile-detail', $request, $response);
    $interviewer = $response->getResults();
    $request->removeStage();

    $interview['post'] = $post;
    $interview['profile'] = $profile;
    $interview['interviewer'] = $interviewer;

    $request->setStage($interview);

    // Checks if the tag is No Show
    if ($data['tag'] == 'No Show') {
        // Sets the email data
        $emailData = array();
        $emailData['title'] = 'You missed your interview!';
        $emailData['template'] = 'noshow.html';

        $request->setStage('email_data', $emailData);

        // Trigger the email
        // Assume that we have sent the email
        // No error response is sent back if the email was not sent
        cradle()->trigger('interview-schedule-email', $request, $response);
    }

    $request->removeStage();

    // Search for the applicant
    // Based on applicant_post
    // Based on applicant_profile
    // Excluse the form data
    $request->setStage('filter', 'post_id', $schedule['post_id']);
    $request->setStage('filter', 'profile_id', $schedule['profile_id']);
    $request->setStage('exclude', 'form', true);
    cradle()->trigger('applicant-search', $request, $response);
    $results = $response->getResults();

    // Remove the staging data
    $request->removeStage();

    // Checks if the applicant does not exist
    if (!$results['total']) {
        // At this point, there is no applicant
        // Remove the staging data
        $request->removeStage();

        $request->setStage('profile_id', $schedule['profile_id']);
        $request->setStage('post_id', $schedule['post_id']);

        // At this point, no applicant was returned
        cradle()->trigger('applicant-create', $request, $response);

        $applicant = $response->getResults();
    } else {
        $applicant = $results['rows'][0];
    }

    // Checks if applicant status exists
    if (!isset($applicant['applicant_status']) || empty($applicant['applicant_status'])) {
        $applicant['applicant_status'] = array();
    }

    // Checks if there is a For Interview tag
    if (($key = array_search('For Interview', $applicant['applicant_status'])) !== false) {
        unset($applicant['applicant_status'][$key]);
    }

    // Checks if there is a Rescheduled tag
    if (($key = array_search('Rescheduled', $applicant['applicant_status'])) !== false) {
        unset($applicant['applicant_status'][$key]);
    }

    /* Comment out this section for now
    // Checks if there is a Follow Up Interview tag
    if (($key = array_search('Follow Up Interview', $applicant['applicant_status'])) !== false) {
        unset($applicant['applicant_status'][$key]);
    }*/

    // Checks if the tag is not yet added
    if (!in_array($data['tag'], $applicant['applicant_status'])) {
        $applicant['applicant_status'][] = $data['tag'];
    }

    // After the interview was created, we should update the labels at ATS
    $request->setStage('applicant_id', $applicant['applicant_id']);
    $request->setStage('applicant_status', $applicant['applicant_status']);
    cradle()->trigger('applicant-update', $request, $response);

    // Remove the staging data
    $request->removeStage();

    // Checks for errors
    if ($response->isError()) {
        // At this point, there is an error
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating this interview'
            ]));
    }

    $message = 'Applicant successfully tagged as ' . $data['tag'];

    // There are no errors at this point
    // Return a success message
    return $response->setContent(json_encode([
            'error'   => false,
            'message' => $message
        ]));
});

$cradle->post('/ajax/interview/post', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    $data = $request->getStage();

    $request->setStage(
        'permission',
        $request->getSession('me', 'profile_id')
    );

    cradle()->trigger('post-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Error updating this post'
            ]));
    }

    $post = $response->getResults();

    // check for post notify
    if (isset($data['post_package'])) {
        $cost = cradle('global')->config('credits', $data['post_package']);
        $available = $request->getSession('me', 'profile_credits');

        // if insufficient credit balance
        if ($available < $cost) {
            return $response->setContent(json_encode([
                    'error'   => true,
                    'message' => 'Insufficient Credits'
                ]));
        }
    }

    // At this point, there are no errors
    // Updates the post_package
    $post['post_package'][] = $data['post_package'];
    $request->setStage('post_package', $post['post_package']);

    // Updates the post
    cradle()->trigger('post-update', $request, $response);

    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));

    $request->setStage('service_name', 'Interview Post')
        ->setStage('service_meta', [
            'post_id' => $post['post_id']
        ])
        ->setStage('service_credits', $cost);

    cradle()->trigger('service-create', $request, $response);

    if (!$response->isError()) {
        $request->setSession('me', 'profile_credits', $available - $cost);
    }

    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Interview Schedule now available for this post. Please wait, reload page...'
        ]));
});

/*
 * Ajax School Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/school/search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //----------------------------//
    // 2. Validate Data
    if (!$request->hasStage()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $request->getStage();

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //----------------------------//
    // 4. Process Data
    cradle()->trigger('school-search', $request, $response);

    // set the data
    $data = array_merge($request->getStage(), $response->getResults());

    $response->setError(false)->setResults($data['rows']);
});

/**
 * Ajax School Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/school/update/:post_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();
    $errors = [];

    // trim white spaces
    $data['item'] = array_map('trim', $data['item']);

    // post_school required
    if (!isset($data['item']['post_school']) ||
        empty($data['item']['post_school'])) {
        $errors['post_school'] = 'School is required';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

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

    //prepare post school
    $postSchool[] = preg_replace('/[^a-z0-9\s]/', '', strtolower($request->getStage('post_school')));

    // Check if post school is existing in school list
    $schoolCompare = array_uintersect($data['school'], $postSchool, 'strcasecmp');

    // if schoolCompare is empty get it from postSchool
    if (empty($schoolCompare)) {
        $schoolCompare = $postSchool;
    }

    // trigger post details
    cradle()->trigger('post-detail', $request, $response);

    // prepare post tags
    $postTags = $response->getResults('post_tags');

    // merge schoolCompare and post tags
    $postTags = array_merge($schoolCompare, $postTags);

    // set stage post tags
    $request->setStage('post_tags', $postTags);

    // trigger post update
    cradle()->trigger('post-update', $request, $response);

    //----------------------------//
    // 5 Process Service
    //it was good
    // 4. Interpret Results
    $response->setError(false)
        ->setResults($response->getResults());
});

/*
 * Ajax Degree Search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/degree/search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //----------------------------//
    // 2. Validate Data
    if (!$request->hasStage()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $request->getStage();

    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //----------------------------//
    // 4. Process Data
    cradle()->trigger('degree-search', $request, $response);

    // set the data
    $data = array_merge($request->getStage(), $response->getResults());

    $response->setError(false)->setResults($data['rows']);
});

/**
 * Information Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/information/update', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();

    $errors = [];
    // profile_name required
    if (!isset($data['item']['profile_name']) ||
        empty($data['item']['profile_name'])) {
        $errors['profile_name'] = 'Name is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_name'])) {
        $errors['profile_name'] = 'Name must be less than 255 characters';
    }

    // information_heading required
    if (!isset($data['item']['information_heading']) ||
        empty($data['item']['information_heading'])) {
        $errors['information_heading'] = 'Heading is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['information_heading'])) {
        $errors['profile_name'] = 'Heading must be less than 255 characters';
    }

    // profile_email required
    if (!isset($data['item']['profile_email']) ||
        empty($data['item']['profile_email'])) {
        $errors['profile_email'] = 'Email is required';
    } elseif (!UtilityValidator::isEmail($data['item']['profile_email'])) {
        // profile_email valid email
        $errors['profile_email'] = 'Must be a valid email';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_email'])) {
        $errors['profile_name'] = 'Email must be less than 255 characters';
    }

    // profile_gender required
    if (!isset($data['item']['profile_gender']) ||
        empty($data['item']['profile_gender'])) {
        $errors['profile_gender'] = 'Gender is required';
    }

    // profile_birth required
    if (!isset($data['item']['profile_birth']) ||
        empty($data['item']['profile_birth'])) {
        $errors['profile_birth'] = 'Date of birth is required';
    }

    // information_civil_status required
    if (!isset($data['item']['information_civil_status']) ||
        empty($data['item']['information_civil_status'])) {
        $errors['information_civil_status'] = 'Civil status is required';
    }

    // profile_phone required
    if (!isset($data['item']['profile_phone']) ||
        empty($data['item']['profile_phone'])) {
        $errors['profile_phone'] = 'Contact number is required';
    } elseif (!UtilityValidator::isInteger($data['item']['profile_phone'])) {
        // profile_phone numeric
        $errors['profile_phone'] = 'Contact number should be numeric';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_phone'])) {
        $errors['profile_name'] = 'Contact number be less than 255 characters';
    }

    // profile_address_street required
    if (!isset($data['item']['profile_address_street']) ||
        empty($data['item']['profile_address_street'])) {
        $errors['profile_address_street'] = 'Street address is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_address_street'])) {
        $errors['profile_name'] = 'Street address must be less than 255 characters';
    }

    // profile_address_city required
    if (!isset($data['item']['profile_address_city']) ||
        empty($data['item']['profile_address_city'])) {
        $errors['profile_address_city'] = 'City is required';
    }

    // profile_address_state required
    if (!isset($data['item']['profile_address_state']) ||
        empty($data['item']['profile_address_state'])) {
        $errors['profile_address_state'] = 'State is required';
    }

    // profile_address_postal required
    if (!isset($data['item']['profile_address_postal']) ||
        empty($data['item']['profile_address_postal'])) {
        $errors['profile_address_postal'] = 'Postal is required';
    } elseif (!UtilityValidator::isInteger($data['item']['profile_address_postal'])) {
        $errors['profile_address_postal'] = 'Postal should be numeric';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_address_postal'])) {
        $errors['profile_name'] = 'Postal must be less than 255 characters';
    }

    // profile_address_country required
    if (!isset($data['item']['profile_address_country']) ||
        empty($data['item']['profile_address_country'])) {
        $errors['profile_address_country'] = 'Country is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_address_country'])) {
        $errors['profile_name'] = 'Country must be less than 255 characters';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // set stage the profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if ($response->getResults()) {
        // set the information id
        $request->setStage('information_id', $response->getResults('information_id'));

        // if there's information update
        cradle()->trigger('information-update', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    } else {
        // if there's information create
        cradle()->trigger('information-create', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response to data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Information Quick Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/information/quick/update', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();

    $errors = [];
    // profile_name required
    if (!isset($data['item']['profile_name']) ||
        empty($data['item']['profile_name'])) {
        $errors['profile_name'] = 'Name is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['profile_name'])) {
        $errors['profile_name'] = 'Name must be less than 255 characters';
    }

    // information_heading required
    if (!isset($data['item']['information_heading']) ||
        empty($data['item']['information_heading'])) {
        $errors['information_heading'] = 'Heading is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['information_heading'])) {
        $errors['profile_name'] = 'Heading must be less than 255 characters';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // set stage the profile id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the information id
    $request->setStage('information_id', $response->getResults('information_id'));

    // if there's information update
    cradle()->trigger('information-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response to data
    $data['information'] = $response->getResults();

    $response
        ->setError(false)
        ->setResults($data['information']);
});

/**
 * Experience Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/experience/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    //if experience_to has no value make it null
    if ($request->hasStage('experience_to') && !$request->getStage('experience_to')) {
        $request->setStage('experience_to', null);
    }

    //if experience_description has no value make it null
    if ($request->hasStage('experience_description') && !$request->getStage('experience_description')) {
        $request->setStage('experience_description', null);
    }

    // set the data item
    $data['item'] = $request->getStage();

    $errors = [];
    // experience_title required
    if (!isset($data['item']['experience_title']) ||
        empty($data['item']['experience_title'])) {
        $errors['experience_title'] = 'Job title is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['experience_title'])) {
        $errors['profile_name'] = 'Job title must be less than 255 characters';
    }

    // experience_industry required
    if (!isset($data['item']['experience_industry']) ||
        empty($data['item']['experience_industry'])) {
        $errors['experience_industry'] = 'Industry is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['experience_industry'])) {
        $errors['profile_name'] = 'Industry must be less than 255 characters';
    }

    // experience_related required
    if (!isset($data['item']['experience_related']) ||
        empty($data['item']['experience_related'])) {
        $errors['experience_related'] = 'Related is required';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['experience_from'],
        $data['item']['experience_to']
    );

    // check for compare experience_from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['experience_from'] = $compareDate['from'];
    }

    // check if there is experience_to
    if (isset($data['item']['experience_to']) &&
        !empty($data['item']['experience_to'])) {
        // check for compare experience_to
        if (isset($compareDate['to']) && !empty($compareDate['to'])) {
            $errors['experience_to'] = $compareDate['to'];
        }
    }

    // experience_from required
    if (!isset($data['item']['experience_from']) ||
        empty($data['item']['experience_from'])) {
        $errors['experience_from'] = 'From is required';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id'),
        'profile_name' => $request->getSession('me', 'profile_name'),
        'profile_email' => $request->getSession('me', 'profile_email'),
        'profile_phone' => $request->getSession('me', 'profile_phone'),
        'profile_image' => $request->getSession('me', 'profile_image'),
    ]);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if (!$response->getResults()) {
        // if there's information create
        cradle()->trigger('information-create', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // set stage the information id
    $request->setStage('information_id', $response->getResults('information_id'));

    // trigget the job
    cradle()->trigger('experience-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Experience Detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/experience/detail/:experience_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    cradle()->trigger('experience-detail', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the results to experience
    $experience = $response->getResults();

    // conver the date
    if ($experience['experience_from']) {
        $experience['experience_from'] = date('F d, Y', strtotime($experience['experience_from']));
    }

    if ($experience['experience_to']) {
        $experience['experience_to'] = date('F d, Y', strtotime($experience['experience_to']));
    }

    $response
        ->setError(false)
        ->setResults($experience);
});

/**
 * Experience Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/experience/update/:experience_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    //if experience_to has no value make it null
    if ($request->hasStage('experience_to') && !$request->getStage('experience_to')) {
        $request->setStage('experience_to', null);
    }

    // set the data item
    $data['item'] = $request->getStage();

    $errors = [];
    // experience_title required
    if (!isset($data['item']['experience_title']) ||
        empty($data['item']['experience_title'])) {
        $errors['experience_title'] = 'Job title is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['experience_title'])) {
        $errors['profile_name'] = 'Job title must be less than 255 characters';
    }

    // experience_industry required
    if (!isset($data['item']['experience_industry']) ||
        empty($data['item']['experience_industry'])) {
        $errors['experience_industry'] = 'Industry is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['experience_industry'])) {
        $errors['profile_name'] = 'Industry must be less than 255 characters';
    }

    // experience_related required
    if (!isset($data['item']['experience_related']) ||
        empty($data['item']['experience_related'])) {
        $errors['experience_related'] = 'Related is required';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['experience_from'],
        $data['item']['experience_to']
    );

    // check for compare experience_from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['experience_from'] = $compareDate['from'];
    }

    // check if there is experience_to
    if (isset($data['item']['experience_to']) &&
        !empty($data['item']['experience_to'])) {
        // check for compare experience_to
        if (isset($compareDate['to']) && !empty($compareDate['to'])) {
            $errors['experience_to'] = $compareDate['to'];
        }
    }

    // experience_from required
    if (!isset($data['item']['experience_from']) ||
        empty($data['item']['experience_from'])) {
        $errors['experience_from'] = 'From is required';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // trigget the job
    cradle()->trigger('experience-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response to data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Experience Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/experience/remove/:experience_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    // trigget the job
    cradle()->trigger('experience-remove', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Education Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/education/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    //if education_to has no value make it null
    if ($request->hasStage('education_to') && !$request->getStage('education_to')) {
        $request->setStage('education_to', null);
    }

    // set the data item
    $data['item'] = $request->getStage();

    $errors = [];
    // education_school required
    if (!isset($data['item']['education_school']) ||
        empty($data['item']['education_school'])) {
        $errors['education_school'] = 'School is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['education_school'])) {
        $errors['profile_name'] = 'School must be less than 255 characters';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['education_from'],
        $data['item']['education_to']
    );

    // check for compare education_from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['education_from'] = $compareDate['from'];
    }

    // check if there is education_to
    if (isset($data['item']['education_to']) &&
        !empty($data['item']['education_to'])) {
        // check for compare education_to
        if (isset($compareDate['to']) && !empty($compareDate['to'])) {
            $errors['education_to'] = $compareDate['to'];
        }
    }

    // education_from required
    if (!isset($data['item']['education_from']) ||
        empty($data['item']['education_from'])) {
        $errors['education_from'] = 'From is required';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id'),
        'profile_name' => $request->getSession('me', 'profile_name'),
        'profile_email' => $request->getSession('me', 'profile_email'),
        'profile_phone' => $request->getSession('me', 'profile_phone'),
        'profile_image' => $request->getSession('me', 'profile_image'),
    ]);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if (!$response->getResults()) {
        // if there's information create
        cradle()->trigger('information-create', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // set stage the information id
    $request->setStage('information_id', $response->getResults('information_id'));

    // trigget the job
    cradle()->trigger('education-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Education Detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/education/detail/:education_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    cradle()->trigger('education-detail', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the results to education
    $education = $response->getResults();

    // conver the date
    if ($education['education_from']) {
        $education['education_from'] = date('F d, Y', strtotime($education['education_from']));
    }

    if ($education['education_to']) {
        $education['education_to'] = date('F d, Y', strtotime($education['education_to']));
    }

    $response
        ->setError(false)
        ->setResults($education);
});

/**
 * Education Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/education/update/:education_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    //if education_to has no value make it null
    if ($request->hasStage('education_to') && !$request->getStage('education_to')) {
        $request->setStage('education_to', null);
    }

    // set the data item
    $data['item'] = $request->getStage();

    $errors = [];
    // education_school required
    if (!isset($data['item']['education_school']) ||
        empty($data['item']['education_school'])) {
        $errors['education_school'] = 'School is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['education_school'])) {
        $errors['profile_name'] = 'School must be less than 255 characters';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['education_from'],
        $data['item']['education_to']
    );

    // check for compare education_from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['education_from'] = $compareDate['from'];
    }

    // check if there is education_to
    if (isset($data['item']['education_to']) &&
        !empty($data['item']['education_to'])) {
        // check for compare education_to
        if (isset($compareDate['to']) && !empty($compareDate['to'])) {
            $errors['education_to'] = $compareDate['to'];
        }
    }

    // education_from required
    if (!isset($data['item']['education_from']) ||
        empty($data['item']['education_from'])) {
        $errors['education_from'] = 'From is required';
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // trigget the job
    cradle()->trigger('education-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Education Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/education/remove/:education_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    // trigget the job
    cradle()->trigger('education-remove', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Accomplishment Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/accomplishment/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();

    $errors = [];
    // accomplishment_name required
    if (!isset($data['item']['accomplishment_name']) ||
        empty($data['item']['accomplishment_name'])) {
        $errors['accomplishment_name'] = 'Name / Title is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['accomplishment_name'])) {
        $errors['profile_name'] = 'Name / Title must be less than 255 characters';
    }

    // accomplishment_from required
    if (!isset($data['item']['accomplishment_from']) ||
        empty($data['item']['accomplishment_from'])) {
        $errors['accomplishment_from'] = 'From is required';
    }

    // accomplishment_to required
    if (!isset($data['item']['accomplishment_to']) ||
        empty($data['item']['accomplishment_to'])) {
        $errors['accomplishment_to'] = 'To is required';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['accomplishment_from'],
        $data['item']['accomplishment_to']
    );

    // check for compare from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['accomplishment_from'] = $compareDate['from'];
    }

    // check for compare to
    if (isset($compareDate['to']) && !empty($compareDate['to'])) {
        $errors['accomplishment_to'] = $compareDate['to'];
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id'),
        'profile_name' => $request->getSession('me', 'profile_name'),
        'profile_email' => $request->getSession('me', 'profile_email'),
        'profile_phone' => $request->getSession('me', 'profile_phone'),
        'profile_image' => $request->getSession('me', 'profile_image'),
    ]);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if (!$response->getResults()) {
        // if there's information create
        cradle()->trigger('information-create', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // set stage the information id
    $request->setStage('information_id', $response->getResults('information_id'));

    // trigget the job
    cradle()->trigger('accomplishment-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Accomplishment Detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/accomplishment/detail/:accomplishment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    cradle()->trigger('accomplishment-detail', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the results to accomplishment
    $accomplishment = $response->getResults();

    // conver the date
    if ($accomplishment['accomplishment_from']) {
        $accomplishment['accomplishment_from'] =
            date('F d, Y', strtotime($accomplishment['accomplishment_from']));
    }

    if ($accomplishment['accomplishment_to']) {
        $accomplishment['accomplishment_to'] =
            date('F d, Y', strtotime($accomplishment['accomplishment_to']));
    }

    $response
        ->setError(false)
        ->setResults($accomplishment);
});

/**
 * Accomplishment Update
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/accomplishment/update/:accomplishment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();

    $errors = [];
    // accomplishment_name required
    if (!isset($data['item']['accomplishment_name']) ||
        empty($data['item']['accomplishment_name'])) {
        $errors['accomplishment_name'] = 'Name / Title is required';
    } elseif (UtilityValidator::lessThanEqualToString(255, $data['item']['accomplishment_name'])) {
        $errors['profile_name'] = 'Name / Title must be less than 255 characters';
    }

    // accomplishment_from required
    if (!isset($data['item']['accomplishment_from']) ||
        empty($data['item']['accomplishment_from'])) {
        $errors['accomplishment_from'] = 'From is required';
    }

    // accomplishment_to required
    if (!isset($data['item']['accomplishment_to']) ||
        empty($data['item']['accomplishment_to'])) {
        $errors['accomplishment_to'] = 'To is required';
    }

    // compare date
    $compareDate = UtilityValidator::compareDate(
        $data['item']['accomplishment_from'],
        $data['item']['accomplishment_to']
    );

    // check for compare from
    if (isset($compareDate['from']) && !empty($compareDate['from'])) {
        $errors['accomplishment_from'] = $compareDate['from'];
    }

    // check for compare to
    if (isset($compareDate['to']) && !empty($compareDate['to'])) {
        $errors['accomplishment_to'] = $compareDate['to'];
    }

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'There are some errors in the form')
            ->set('json', 'validation', $errors);
    }

    // trigget the job
    cradle()->trigger('accomplishment-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Accomplishment Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/accomplishment/remove/:accomplishment_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    // trigget the job
    cradle()->trigger('accomplishment-remove', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // update the session
    cradle()->trigger('profile-session', $request, $response);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Skills Create
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/skills/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();

    $errors = [];
    // information_skills required
    if (!isset($data['item']['information_skills']) ||
        empty($data['item']['information_skills'])) {
            return $response
                ->setError(true, 'Skills are required');
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id'),
        'profile_name' => $request->getSession('me', 'profile_name'),
        'profile_email' => $request->getSession('me', 'profile_email'),
        'profile_phone' => $request->getSession('me', 'profile_phone'),
        'profile_image' => $request->getSession('me', 'profile_image'),
    ]);

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if (!$response->getResults()) {
        // if there's no information create
        cradle()->trigger('information-create', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    } else {
        // set stage the information id
        $request->setStage('information_id', $response->getResults('information_id'));

        // get the information detail
        cradle()->trigger('information-detail', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        // merge the current skills to added skills
        $request->setStage(
            'information_skills',
            array_merge(
                $response->getResults('information_skills'),
                $request->getStage('information_skills')
            )
        );

        // trigget the job
        cradle()->trigger('information-update', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Skills Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/skills/remove/:skill_name/:information_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');

    //----------------------------//
    // 2. Prepare Data
    $data['item'] = $request->getStage();
    // decode the skill name
    $data['item']['skill_name'] = urldecode($data['item']['skill_name']);

    // get the information detail
    cradle()->trigger('information-detail', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // get the skills
    $skills = $response->getResults('information_skills');

    // check if skill exist
    if (!in_array(
        strtolower($data['item']['skill_name']),
        array_map('strtolower', $skills)
    )) {
        return $response
            ->setError(true, 'No skills found');
    }

    // remove the skills from array
    if (($key = array_search($data['item']['skill_name'], $skills)) !== false) {
        unset($skills[$key]);
    }

    // set stage the skills
    $request->setStage('information_skills', $skills);

    // set stage the profile id
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id')
    ]);

    // trigget the job
    cradle()->trigger('information-update', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // trigger the job
    cradle()->trigger('resume-search', $request, $response);

    // get the resume
    if ($response->getResults('rows')) {
        $data['resume'] = $response->getResults('rows')[0];
    }

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Process the Post Download
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/information/resume/download/:profile_id/:information_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('poster');

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];

    // if no download parameter
    if (!$request->hasGet('download')) {
        // get the cost of resume download
        $cost = cradle('global')->config('credits', 'resume-download');

        //if not unlimited download
        if (empty($request->hasSession('me', 'profile_package'))
            || !in_array('unlimited-resume', $request->getSession('me', 'profile_package'))) {
            // check remaining credits
            if ($cost > $request->getSession('me', 'profile_credits')) {
                return $response
                    ->setError(true, 'You just need 10 more credits to download this resume');
            }
        } else {
            // set the stage profile _id
            $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

            //trigger job
            cradle()->trigger('information-experience', $request, $response);

            // check for error
            if ($response->isError()) {
                return $response
                    ->setError(true, $response->getMessage());
            }

            // set the results
            $results = $response->getResult();

            //trigger job
            cradle()->trigger('information-download', $request, $response);

            $results['url'] =  $host.'/ajax/information/resume/download/'.$data['profile_id'].'/'.$data['information_id'].'?download=true';

            return $response
                ->setError(false)
                ->setResults($results);
        }

        // set the stage profile _id
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

        //trigger job
        cradle()->trigger('information-experience', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        // set the results
        $results = $response->getResults();

        //trigger job
        cradle()->trigger('information-download', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        if ($response->getResults()  == 'downloaded') {
            $results['url'] =  $host.'/ajax/information/resume/download/'.$data['profile_id'].'/'.$data['information_id'].'?download=true';

            return $response
                ->setError(false)
                ->setResults($results);
        }

        // set the stage
        $request
            ->setStage('profile_id', $request->getSession('me', 'profile_id'))
            ->setStage('service_name', 'Resume Download')
            ->setStage('service_meta', [
                'profile_id' => $data['profile_id'],
                'information_id' => $data['information_id']
            ])
            ->setStage('service_credits', $cost);

        // trigger the job
        cradle()->trigger('service-create', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        // set the session
        $request->setSession(
            'me',
            'profile_credits',
            $request->getSession('me', 'profile_credits') - $cost
        );

        //update profile session
        cradle()->trigger('profile-session', $request, $response);
    }

    // if we need to download
    if ($request->hasGet('download') &&
        $request->getGet('download') === 'true') {
            //trigger job
            cradle()->trigger('information-detail', $request, $response);

            // check for error
        if ($response->isError()) {
            return $response
            ->setError(true, $response->getMessage());
        }

            // set the information to data
            $data['information'] = $response->getResults();

        if (!empty($data['information']['profile_image'])) {
            $image = file_get_contents($data['information']['profile_image']);
            $tempPath = tempnam(sys_get_temp_dir(), 'prefix');
            file_put_contents($tempPath, $image);
            $data['information']['profile_image'] = $tempPath;
        }

            // get the jobayan logo
            $data['logo'] = $host . '/images/logo-gray.png';

            // get the template
            $body = cradle('/app/www')->template('profile/information/_resume', $data);

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'en');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->pdf->SetTitle('Jobayan - '.$data['information']['profile_name'].' Resume');
            $html2pdf->writeHTML($body);
            $html2pdf->output($data['information']['profile_name'].'_Resume_Jobayan.pdf', 'D');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);
            cradle('global')->flash($formatter->getHtmlMessage(), 'danger');
            return cradle('global')->redirect('/');
        }
    }

    $results['url'] =  $host.'/ajax/information/resume/download/'.$data['profile_id'].'/'.$data['information_id'].'?download=true';

    $response
        ->setError(false)
        ->setResults($results);
});

/**
 * Resume Parser
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/resume/parser', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    // check if file exist
    if ($data['information_resume_link'] &&
        !empty($data['information_resume_link'])) {
        try {
            // set the filename
            $filename = sys_get_temp_dir().'/'.basename($data['information_resume_link']);

            // save the file to tmp folder
            file_put_contents($filename, file_get_contents($data['information_resume_link']));
        } catch (Exception $e) {
            return $response->setError(true, 'Invalid File');
        }

        // get the file
        $resume['tmp_name'] = $filename;
    } else {
        // get the file form post data
        $resume = $request->getFiles('information_resume');

        // check for error
        if ($resume['error']) {
            return $response->setError(true, 'Invalid File');
        }
    }

    // allowed files to be parsed
    $allowedFiles = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    // check if file is pdf / doc / docx
    if (!in_array(mime_content_type($resume['tmp_name']), $allowedFiles)) {
        return $response->setError(true, 'Invalid File');
    }

    // check if uploaded resume is pdf
    if (mime_content_type($resume['tmp_name']) === 'application/pdf') {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($resume['tmp_name']);

        // check if file is valid
        if (!$pdf) {
            return $response->setError(true, 'Invalid File');
        }

        // extract the text
        $extractText = explode(PHP_EOL, $pdf->getText());
    }

    // check if uploaded resume is doc / docx
    if (mime_content_type($resume['tmp_name']) === 'application/msword' ||
        mime_content_type($resume['tmp_name']) === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $docucmentParaser = new \Cradle\Module\Utility\DocumentParser($resume['tmp_name']);
        $doc = $docucmentParaser->convertToText();

        // check if file is valid
        if (!$doc) {
            return $response->setError(true, 'Invalid File');
        }

        // extract the text
        $extractText = explode(PHP_EOL, $doc);
    }

    // check extracted text
    if ($extractText) {
        $records = [];
        foreach ($extractText as $key => $row) {
            // skip if empty row
            if ($row == '' || empty($row)) {
                continue;
            }

            // remove special characters
            $row = preg_replace('/[^A-Za-z0-9\-\'\,]/', ' ', $row);

            // trim space
            $row  = trim($row);

            // remove multiple spaces
            $row = preg_replace('/\s\s+/', ' ', $row);

            // gender
            if (preg_match('/male|female/i', $row)) {
                // male
                if ((preg_match('/male/i', $row))) {
                    $request->setStage('profile_gender', 'male');
                }

                // female
                if ((preg_match('/female/i', $row))) {
                    $request->setStage('profile_gender', 'female');
                }
            }

            // date of birth
            $dateOfBirth = Date::findDate($row);
            if ($dateOfBirth) {
                $records['birth'][] = $dateOfBirth;
            }

            // civil status
            if (preg_match('/single|married|divorced|separated|widowed/i', $row)) {
                // single
                if ((preg_match('/single/i', $row))) {
                    $request->setStage('information_civil_status', 'single');
                }

                // married
                if ((preg_match('/married/i', $row))) {
                    $request->setStage('information_civil_status', 'married');
                }

                // divorced
                if ((preg_match('/divorced/i', $row))) {
                    $request->setStage('information_civil_status', 'divorced');
                }

                // separated
                if ((preg_match('/separated/i', $row))) {
                    $request->setStage('information_civil_status', 'separated');
                }

                // widowed
                if ((preg_match('/widowed/i', $row))) {
                    $request->setStage('information_civil_status', 'separated');
                }
            }

            // mobile number
            if (preg_match('/^[+]639|^09|^639|^9/', $row) && strlen($row) > 10) {
                // remove -
                $row = str_replace('-', '', $row);
                // remove +
                $row = str_replace('+', '', $row);
                $records['phone'][] = $row;
            }

            // address
            if (preg_match('/city/i', $row)) {
                $address = array_map('trim', explode(',', $row));
                    $records['address'][] = $address;
            }

            // education school
            if (preg_match('/academy|college|institute|school|technology|university/i', $row)) {
                // academy
                if (preg_match('/academy/i', $row)) {
                    $academy = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%academy%"')
                        ->getRows();

                    if ($academy) {
                        foreach ($academy as $a) {
                            if (preg_match('/'.$row.'/i', $a['school_name'])) {
                                $records['education']['school'][] = $a['school_name'];

                                break;
                            }
                        }
                    }
                }

                // college
                if (preg_match('/college/i', $row)) {
                    $colleges = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%college%"')
                        ->getRows();

                    if ($colleges) {
                        foreach ($colleges as $c) {
                            if (preg_match('/'.$row.'/i', $c['school_name'])) {
                                $records['education']['school'][] = $c['school_name'];

                                break;
                            }
                        }
                    }
                }

                // institute
                if (preg_match('/institute/i', $row)) {
                    $institutes = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%institute%"')
                        ->getRows();

                    if ($institutes) {
                        foreach ($institutes as $i) {
                            if (preg_match('/'.$row.'/i', $i['school_name'])) {
                                $records['education']['school'][] = $i['school_name'];

                                break;
                            }
                        }
                    }
                }

                // school
                if (preg_match('/school/i', $row)) {
                    $schools = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%school%"')
                        ->getRows();

                    if ($schools) {
                        foreach ($schools as $s) {
                            if (preg_match('/'.$row.'/i', $s['school_name'])) {
                                $records['education']['school'][] = $s['school_name'];

                                break;
                            }
                        }
                    }
                }

                // technology
                if (preg_match('/technology/i', $row)) {
                    $technology = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%technology%"')
                        ->getRows();

                    if ($technology) {
                        foreach ($technology as $t) {
                            if (preg_match('/'.$row.'/i', $t['school_name'])) {
                                $records['education']['school'][] = $t['school_name'];

                                break;
                            }
                        }
                    }
                }

                // university
                if (preg_match('/university/i', $row)) {
                    $university = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('school')
                        ->setRange(0)
                        ->addFilter('school_name LIKE "%university%"')
                        ->getRows();

                    if ($university) {
                        foreach ($university as $u) {
                            if (preg_match('/'.$row.'/i', $u['school_name'])) {
                                $records['education']['school'][] = $u['school_name'];

                                break;
                            }
                        }
                    }
                }
            }

            // education degree
            if (preg_match('/associate in|bachelor of|diploma in|master in|master of|PhD/i', $row)) {
                // get the previous key
                $lastKey = $key - 1;

                // associate
                if (preg_match('/associate in/i', $row)) {
                    $associate = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%associate in%"')
                        ->getRows();

                    if ($associate) {
                        foreach ($associate as $a) {
                            if (preg_match('/'.$row.'/i', $a['degree_name'])) {
                                $records['education']['degree'][] = $a['degree_name'];

                                break;
                            }
                        }
                    }
                }

                // bachelor
                if (preg_match('/bachelor of/i', $row)) {
                    $bachelor = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%bachelor of%"')
                        ->getRows();

                    if ($bachelor) {
                        foreach ($bachelor as $b) {
                            if (preg_match('/'.$row.'/i', $b['degree_name'])) {
                                $records['education']['degree'][] = $b['degree_name'];

                                break;
                            }
                        }
                    }
                }

                // diploma
                if (preg_match('/diploma in/i', $row)) {
                    $diploma = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%diploma in%"')
                        ->getRows();

                    if ($diploma) {
                        foreach ($diploma as $d) {
                            if (preg_match('/'.$row.'/i', $d['degree_name'])) {
                                $records['education']['degree'][] = $d['degree_name'];

                                break;
                            }
                        }
                    }
                }

                // master in
                if (preg_match('/master in/i', $row)) {
                    $masterIn = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%master in%"')
                        ->getRows();

                    if ($masterIn) {
                        foreach ($masterIn as $mI) {
                            if (preg_match('/'.$row.'/i', $mI['degree_name'])) {
                                $records['education']['degree'][] = $mI['degree_name'];

                                break;
                            }
                        }
                    }
                }

                // master of
                if (preg_match('/master of/i', $row)) {
                    $masterOf = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%master of%"')
                        ->getRows();

                    if ($masterOf) {
                        foreach ($masterOf as $mO) {
                            if (preg_match('/'.$row.'/i', $mO['degree_name'])) {
                                $records['education']['degree'][] = $mO['degree_name'];

                                break;
                            }
                        }
                    }
                }

                // PhD
                if (preg_match('/PhD/i', $row)) {
                    $phd = Cradle\Module\Degree\Service::get('sql')
                        ->getResource()
                        ->search('degree')
                        ->setRange(0)
                        ->addFilter('degree_name LIKE "%PhD%"')
                        ->getRows();

                    if ($phd) {
                        foreach ($phd as $p) {
                            if (preg_match('/'.$row.'/i', $p['degree_name'])) {
                                $records['education']['degree'][] = $p['degree_name'];

                                break;
                            }
                        }
                    }
                }
            }
        }
    } else {
        return $response
                    ->setError(true, 'Parsing of data is not available');
    }

    // set stage profile
    $request->setStage([
        'profile_id' => $request->getSession('me', 'profile_id'),
        'profile_name' => $request->getSession('me', 'profile_name'),
        'profile_email' => $request->getSession('me', 'profile_email')
    ]);

    // check for phone
    if (isset($records['phone'])) {
        $request->setStage('profile_phone', $records['phone'][0]);
    }

    // check for date of birth
    if (isset($records['birth'])) {
        foreach ($records['birth'] as $birth) {
            // get the year difference
            $yearsDiff = abs(date('Y') - date('Y', strtotime($birth)));

            // check year difference
            if ($yearsDiff >= 18 && $yearsDiff <= 60) {
                $request->setStage('profile_birth', $birth);
                break;
            }
        }
    }

    // check for address
    if (isset($records['address'])) {
        // set the address street
        $request->setStage('profile_address_street', $records['address'][0][0]);

        foreach ($records['address'][0] as $add) {
            // set address city
            if (preg_match('/city/i', $add)) {
                $request->setStage('profile_address_city', $add);

                $addressCity = Cradle\Module\School\Service::get('sql')
                    ->getResource()
                    ->search('area')
                    ->setRange(1)
                    ->addFilter('area_name LIKE %s', '%'.$add.'%')
                    ->getRow();

                if ($addressCity) {
                    $addressState = Cradle\Module\School\Service::get('sql')
                        ->getResource()
                        ->search('area')
                        ->setRange(1)
                        ->filterByAreaId($addressCity['area_parent'])
                        ->getRow();

                    if ($addressState) {
                        $request->setStage('profile_address_state', $addressState['area_name']);
                    }
                }
            }

            // set address postal
            if (is_int($add) && strlen($add) >=4) {
                $request->setStage('profile_address_postal', $add);
            }
        }

        // set the address country
        $request->setStage('profile_address_country', $request->getSession('country'));
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // check if there is information
    if (!$response->getResults()) {
        // if there's no information create
        cradle()->trigger('information-create', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    } else {
        // set stage the information id
        $request->setStage('information_id', $response->getResults('information_id'));

        // trigget the job
        cradle()->trigger('information-update', $request, $response);

        // check for error
        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }
    }

    // check for education
    if (isset($records['education'])) {
        // sort the education descending
        krsort($records['education']);

        // check if there's school
        if (isset($records['education']['school'])) {
            foreach ($records['education']['school'] as $s => $school) {
                // set stage the school name
                $request->setStage('education_school', $school);

                // check if there's degree
                if (isset($records['education']['degree'])) {
                    foreach ($records['education']['degree'] as $d => $degree) {
                        // if same key
                        if ($s == $d) {
                            // set stage the degree
                            $request->setStage('education_degree', $degree);
                        }
                    }
                }

                // trigget the job
                cradle()->trigger('education-create', $request, $response);

                // remove stage
                $request->removeStage('education_school');
                $request->removeStage('education_degree');
            }
        }
    }

    // trigger the job
    cradle()->trigger('profile-information', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the response for data
    $data['information'] = $response->getResults();

    // get the information location
    cradle()->trigger('information-location', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the area to data
    $data['area'] = $response->getResults();

    // get the information industry
    cradle()->trigger('information-industry', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set the industry to data
    $data['industry'] = $response->getResults();

    $body = cradle('/app/www')->template(
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
            'profile/information_view'
        ]
    );

    $response
        ->setError(false)
        ->setResults($body);
});

/**
 * Resume Upload
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/resume/upload', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // check for this is for seeker/poster
    cradle('global')->checkProfile('seeker');
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    // get the file form post data
    $resume = $request->getFiles('profile_resume');

    // check for error
    if ($resume['error']) {
        return $response
            ->setError(
                true,
                cradle('global')->translate('Invalid File')
            );
    }

    // allowed files to be parsed
    $allowedFiles = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];

    // check if file is pdf / doc / docx
    if (!in_array(mime_content_type($resume['tmp_name']), $allowedFiles)) {
        return $response
            ->setError(
                true,
                cradle('global')->translate('Invalid File')
            );
    }

    //----------------------------//
    // 3. Process Request
    $request->setStage([
        'upload' => [
            'file' => $resume
        ]
    ]);

    // trigger the job
    cradle()->trigger('file-upload', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(
                true,
                cradle('global')->translate($response->getMessage())
            );
    }

    // get file
    $file = $response->getResults();
    $request->setStage('resume_link', $file['resume_link']);
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('resume_position', 'Resume');

    // save resume
    cradle()->trigger('resume-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(
                true,
                cradle('global')->translate($response->getMessage())
            );
    }

    $response
        ->setError(false)
        ->setResults($response->getResults());
});

/**
 * Enable Tracer Study
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/enable/tracer', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    $profile = $request->getSession('me');

    // check for post notify
    $cost = cradle('global')->config('credits', 'tracer-study');
    $available = $request->getSession('me', 'profile_credits');

    // if insufficient credit balance
    if ($available < $cost) {
        return $response->setContent(json_encode([
                'error'   => true,
                'message' => 'Insufficient Credits'
            ]));
    }

    // At this point, there are no errors
    // Updates the profile_package
    $profile['profile_package'][] = 'tracer-study';
    $request->setStage('profile_package', $profile['profile_package']);
        // Updates the profile
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

    cradle()->trigger('profile-update', $request, $response);

    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));

    $request->setStage('service_name', 'Tracer Study')
        ->setStage('service_meta', [
            'profile_id' => $profile['profile_id']
        ])
        ->setStage('service_credits', $cost);

    cradle()->trigger('service-create', $request, $response);

    if (!$response->isError()) {
        $request->setSession('me', 'profile_credits', $available - $cost);
    }

    return $response->setContent(json_encode([
            'error'   => false,
            'message' => 'Tracer Study now available for this profile. Please wait, reload page...'
        ]));
});

/**
 * Validate Email
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/validate/email', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

     //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!isset($data['email'])) {
        return $response->setContent(json_encode([
            'error'   => true,
            'message' => 'No Email.'
        ]));
    }

    $request->setStage('profile_email', $data['email']);
    cradle()->trigger('profile-email-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        return $response
            ->setError(true, 'Invalid Email');
    }

    $profile = $response->getResults();

    if (!$profile) {
        return $response
            ->setError(true, 'Not Found.');
    }

    // get profile id
    $request->setStage('profile_id', $profile['profile_id']);

    cradle()->trigger('validate-email', $request, $response);

    if ($response->isError()) {
        // update profile email slug
        $request->setStage('profile_email_flag', -1);
        cradle()->trigger('profile-update', $request, $response);

        return $response->setContent(json_encode([
            'error'   => true,
            'message' => 'Invalid Email'
        ]));
    }

    // update profile email slug
    $request->setStage('profile_email_flag', 1);
    cradle()->trigger('profile-update', $request, $response);
    return $response->setContent(json_encode([
        'error'   => false,
        'message' => 'Valid Email'
    ]));
});


/**
 * Interested Claim Check for Errors
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interested/claim/check', function ($request, $response) {
    //----------------------------//
    // Prepare Data
    $data = $request->getPost();

    $errors = [];
    // profile name
    if (isset($data['profile_name']) && empty($data['profile_name'])) {
        $errors['profile_name'] = cradle('global')->translate('Name is required');
    }

    // profile email
    if (isset($data['profile_email']) && !UtilityValidator::isEmail($data['profile_email'])) {
        $errors['profile_email'] = cradle('global')->translate('Must be a valid email');
    }

    // profile phone
    if (isset($data['profile_phone']) &&
        !preg_match('/^\d+(-\d+)*$/', $data['profile_phone']) &&
        !empty($data['profile_phone'])) {
            $errors['profile_phone'] = cradle('global')->translate('Contact number should be numeric');
    }

    //----------------------------//
    // Check for Errors
    if ($errors) {
        return $response
            ->setError(true, cradle('global')->translate('There are some errors in the form'))
            ->set('json', 'validation', $errors);
    }

    // set response
    return $response
        ->setError(false)
        ->setResponse(true);
});

/**
 * Interested Claim
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interested/claim', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // trigger the job
    cradle()->trigger('profile-email-detail', $request, $response);

    // check for profile
    if ($response->getResults()) {
        $request->setStage('profile_id', $response->getResults('profile_id'));

        // trigger the interested job
        cradle()->trigger('post-like', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        return $response
            ->setError(false)
            ->setResults('User is being notified of your interest');
    }

    //----------------------------//
    // 2. Prepare Data
    //if profile_email has no value make it null
    if ($request->hasStage('profile_email') && !$request->getStage('profile_email')) {
        $request->setStage('profile_email', null);
    }

    //if profile_phone has no value make it null
    if ($request->hasStage('profile_phone') && !$request->getStage('profile_phone')) {
        $request->setStage('profile_phone', null);
    }

    //profile_slug is disallowed
    $request->removeStage('profile_slug');

    //if profile_verified has no value use the default value
    if ($request->hasStage('profile_verified') && !$request->getStage('profile_verified')) {
        $request->setStage('profile_verified', 0);
    }

    //profile_type is disallowed
    $request->removeStage('profile_type');

    //profile_flag is disallowed
    $request->removeStage('profile_flag');

    // trigger the job
    cradle()->trigger('profile-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage the profile id
    $request->setStage(
        'profile_id',
        $response->getResults('profile_id')
    );

    // trigger the interested job
    cradle()->trigger('post-like', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // trigger the job
    cradle()->trigger('auth-claim', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    $response
        ->setError(false)
        ->setResults(
            cradle('global')
                ->translate('Profile created. Please check your email to claim your profile')
        );
});

/**
 * Post Like Detail
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/post/like/detail/:post_id/:profile_email', function ($request, $response) {
    // trigger the job
    cradle()->trigger('post-like-detail', $request, $response);

    // check if no results
    if (!$response->getResults()) {
        return;
    }

    // set the response
    $response
        ->setError(false)
        ->setResults(
            cradle('global')->translate('User has already been notified of your interest')
        );
});

/**
 * Interested Profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ajax/interested/profile', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    // trigger the job
    cradle()->trigger('profile-email-detail', $request, $response);

    // check for profile
    if ($response->getResults()) {
        $profileId = $response->getResults('profile_id');

        $request->setStage('profile_id', $profileId);

        // trigger the interested job
        cradle()->trigger('post-like', $request, $response);

        if ($response->isError()) {
            return $response
                ->setError(true, $response->getMessage());
        }

        return $response
            ->setError(false)
            ->setResults($profileId);
    }

    //----------------------------//
    // 2. Prepare Data
    //if profile_email has no value make it null
    if ($request->hasStage('profile_email') && !$request->getStage('profile_email')) {
        $request->setStage('profile_email', null);
    }

    //if profile_phone has no value make it null
    if ($request->hasStage('profile_phone') && !$request->getStage('profile_phone')) {
        $request->setStage('profile_phone', null);
    }

    //profile_slug is disallowed
    $request->removeStage('profile_slug');

    //if profile_verified has no value use the default value
    if ($request->hasStage('profile_verified') && !$request->getStage('profile_verified')) {
        $request->setStage('profile_verified', 0);
    }

    //profile_type is disallowed
    $request->removeStage('profile_type');

    //profile_flag is disallowed
    $request->removeStage('profile_flag');

    // trigger the job
    cradle()->trigger('profile-create', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // set stage the profile id
    $request->setStage(
        'profile_id',
        $response->getResults('profile_id')
    );

    // trigger the interested job
    cradle()->trigger('post-like', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    // trigger the job
    cradle()->trigger('auth-claim', $request, $response);

    // check for error
    if ($response->isError()) {
        return $response
            ->setError(true, $response->getMessage());
    }

    $response
        ->setError(false)
        ->setResults($response->getResults('profile_id'));
});
