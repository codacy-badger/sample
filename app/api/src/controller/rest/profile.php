<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Profile detail is accessable by all
 * It will get both auth and profile ID
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/profile/detail/:profile_id', 'auth-profile-detail');

/**
 * Profile search is accessable by all
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/profile/search', 'profile-search');
/**
 * Profile create is accessable by all
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/profile/create', 'profile-create');
/**
 * Profile update myself
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/profile/update/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);

    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();
    if ($request->hasStage('profile_image') && !$request->getStage('profile_image')) {
        $request->setStage('profile_image', null);
    }

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

    //if profile_detail has no value make it null
    if ($request->hasStage('profile_detail') && !$request->getStage('profile_detail')) {
        $request->setStage('profile_detail', null);
    }

    //if profile_job has no value make it null
    if ($request->hasStage('profile_job') && !$request->getStage('profile_job')) {
        $request->setStage('profile_job', null);
    }

    //if profile_gender has no value use the default value
    if ($request->hasStage('profile_gender') && !$request->getStage('profile_gender')) {
        $request->setStage('profile_gender', 'unknown');
    }

    //if profile_birth has no value make it null
    if ($request->hasStage('profile_birth') && !$request->getStage('profile_birth')) {
        $request->setStage('profile_birth', null);
    }

    //if profile_website has no value make it null
    if ($request->hasStage('profile_website') && !$request->getStage('profile_website')) {
        $request->setStage('profile_website', null);
    }

    //if profile_facebook has no value make it null
    if ($request->hasStage('profile_facebook') && !$request->getStage('profile_facebook')) {
        $request->setStage('profile_facebook', null);
    }

    //if profile_linkedin has no value make it null
    if ($request->hasStage('profile_linkedin') && !$request->getStage('profile_linkedin')) {
        $request->setStage('profile_linkedin', null);
    }

    //if profile_twitter has no value make it null
    if ($request->hasStage('profile_twitter') && !$request->getStage('profile_twitter')) {
        $request->setStage('profile_twitter', null);
    }

    //if profile_google has no value make it null
    if ($request->hasStage('profile_google') && !$request->getStage('profile_google')) {
        $request->setStage('profile_google', null);
    }
    //----------------------------//
    // 3. Process Request
    if (isset($data['profile_type']) &&
        ($data['profile_type'] == 'marketer' || $data['profile_type'] == 'agent')) {
        cradle()->trigger('auth-profile-search', $request, $response);
        $request->setStage('auth_id', $response->getResults('auth_id'));
        cradle()->trigger('auth-update', $request, $response);
        if ($response->isError()) {
            return $response->setContent(json_encode([
                'error'      => true,
                'message'    => 'There are some errors in the form',
                'validation' => $response->getValidation(),
            ]));
        } else {
            return $response->setContent(json_encode([
                'error'      => false,
                'message'    => 'successfully updated your account',
            ]));
        }
    } else {
        cradle()->trigger('profile-update', $request, $response);
        $results = $response->getResults();
    }

    // Gets the file
    $file = $request->getFiles();
    //profile_type is disallowed
    $request->removeStage('profile_type');
    //profile_flag is disallowed
    $request->removeStage('profile_flag');
    $resumeUrl = '';
    $file = $request->getFiles();
    // Checks for file and resume_link
    if (!empty($request->getFiles()) || ($request->hasStage('resume_link')
        && !empty($request->getStage('resume_link')))) {
        // Checks if we have a file
        if (!empty($file)) {
            // Sets the file
            $request->setStage('upload', $file);
            // Processes the resume to be uploaded
            cradle()->trigger('file-upload', $request, $response);
            // Checks for errors on creation
            if ($response->isError()) {
                //it was good Set json Content
                return $response->setContent(json_encode([
                    'error'      => true,
                    'message'    => $response->getValidation()['error']
                ]));
            }
        } else if ($request->hasStage('resume_link')) {
            // At this point we have a link instead of a file
            // Processes the resume to be uploaded
            cradle()->trigger('link-upload', $request, $response);
            // Checks for errors on creation
            if ($response->isError()) {
                //it was good Set json Content
                return $response->setContent(json_encode([
                    'error'      => true,
                    'message'    => $response->getValidation()['error']
                ]));
            }
        }
        // get file
        $file = $response->getResults();
        // Forces a unset of resume_id to prevent any updates
        $request->removeStage('resume_id');
        // Sets the following linking parameters
        $request->setStage('resume_link', $file['resume_link']);
        $request->setStage('profile_id', $results['profile_id']);
        // Checks if a post_id was set
        if (isset($results['post_id'])) {
            // Updates the corresponding post
            $request->setStage('post_id', $results['post_id']);
            cradle()->trigger('post-detail', $request, $response);
            $request->setStage('resume_position', $response->getResults()['post_position']);
        }
        // save resume
        cradle()->trigger('resume-create', $request, $response);
        // Checks for error on creation
        if ($response->isError()) {
            //it was good Set json Content
            return $response->setContent(json_encode([
                'error'      => true,
                'message'    => 'There are some errors in the form',
                'validation' => $response->getValidation(),
            ]));
        }
        // Resume was successfully uploaded
        $resumeUrl = $response->getResults()['resume_link'];
    }
    // Checks if a resume was uploaded
    if (!empty($resumeUrl)) {
        $results['resume_link'] = $resumeUrl;
        unset($results['upload']);
        if (isset($results['original']['upload'])) {
            unset($results['original']['upload']);
        }
    }
    // Returns the update
    return $response->setContent(json_encode([
        'error'      => false,
        'message'    => $results
    ]));
});

/**
 * Process resume upload
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/profile/upload/resume', function ($request, $response) {
    /*
        Required post data for uploading a resume
            profile_id
            resume_position
            file
    */
    //only if permitted
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }
    /* Comment this section out for now
    Need verification if links are allowed
    // Checks for data type being a link
    if (isset($data['type']) && $data['type'] == 'link') {
        // link resume
        cradle()->trigger('resume-link-post', $request, $response);
        return $response->setContent(json_encode([
            'error'      => false,
            'message'    => 'Resume Was Uploaded',
        ]));
    }
    */
    $file = $request->getFiles();
    // Checks for file and resume_link
    if (empty($request->getFiles()) && !$request->hasStage('resume_link')) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'Missing Resume to upload',
        ]));
    }
    // Checks if we have a file
    if (!empty($file)) {
        // Sets the file
        $request->setStage('upload', $file);
        // Processes the resume to be uploaded
        cradle()->trigger('file-upload', $request, $response);
        // Checks for errors on creation
        if ($response->isError()) {
            //it was good Set json Content
            return $response->setContent(json_encode([
                'error'      => true,
                'message'    => $response->getValidation()['error']
            ]));
        }
    } else if ($request->hasStage('resume_link')
        && !empty($request->getStage('resume_link'))) {
        // At this point we have a link instead of a file
        // Processes the resume to be uploaded
        cradle()->trigger('link-upload', $request, $response);
        // Checks for errors on creation
        if ($response->isError()) {
            //it was good Set json Content
            return $response->setContent(json_encode([
                'error'      => true,
                'message'    => $response->getValidation()['error']
            ]));
        }
    }
    // get file
    $file = $response->getResults();
    // Forces a unset of resume_id to prevent any updates
    $request->removeStage('resume_id');
    // Sets the following linking parameters
    $request->setStage('resume_link', $file['resume_link']);
    $request->setStage('profile_id', $data['profile_id']);
    // Checks if a post_id was set
    if (isset($data['post_id'])) {
        // Updates the corresponding post
        $request->setStage('post_id', $data['post_id']);
        cradle()->trigger('post-detail', $request, $response);
        $request->setStage('resume_position', $response->getResults()['post_position']);
    }
    // save resume
    cradle()->trigger('resume-create', $request, $response);
    // Checks for error on creation
    if ($response->isError()) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $response->getValidation(),
        ]));
    }
    // Resume was successfully uploaded
    $results = $response->getResults();
    // Return the resume link
    return $response->setContent(json_encode([
        'error'       => false,
        'message'     => 'File was Created',
        'resume_link' => $results['resume_link']
    ]));
});

/**
 * Profile remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/profile/remove/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to prepare
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-remove', $request, $response);
});
/**
 * Profile restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/rest/profile/restore/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only if permitted
    $request->setStage('role', 'profile');
    cradle()->trigger('rest-permitted', $request, $response);
    if ($response->isError()) {
        return;
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('profile-restore', $request, $response);
});
