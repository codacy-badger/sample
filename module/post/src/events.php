<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Profile\Service as ProfileService;

use Cradle\Module\Post\Service as PostService;
use Cradle\Module\Post\Validator as PostValidator;

use Cradle\Module\Term\Service as TermService;
use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Validator as UtilityValidator;
use Cradle\Module\Utility\Queue;

use Cradle\Http\Response;

use Cradle\Curl\CurlHandler as Curl;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use Cradle\Framework\Queue\Service as QueueService;

/**
 * Post Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = PostValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //declare post_tags so that we can use it for automatic tagging of work abroad
    //only applicable if post_location LIKE '%abroad%'
    if (!isset($data['post_tags']) &&
        strpos(strtolower($data['post_location']), 'abroad') !== false) {
        $data['post_tags'] = [];
    }

    //if post_location LIKE '%abroad%', automatically add 'work abroad and poea' tag
    if (isset($data['post_location']) &&
        strpos(strtolower($data['post_location']), 'abroad') !== false) {
        array_push($data['post_tags'], 'work abroad and poea');
        //add also the tags to post_industry
        $data['post_industry'] = 'work abroad and poea';
    }
    if (isset($data['post_notify'])) {
        $data['post_notify'] = json_encode($data['post_notify']);
    }
    // Default expiration days
    $data['post_expires'] = date('Y-m-d H:i:s', strtotime('+30 days'));
    //if seeker set expires to 60 days
    if ($data['post_type'] == 'seeker') {
        $data['post_expires'] = date('Y-m-d H:i:s', strtotime('+60 days'));
    }
    //if there is a post image
    if (isset($data['post_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['post_image'] = File::base64ToS3($data['post_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['post_image'] = File::base64ToUpload($data['post_image'], $upload);
    }

    //if there is a banner
    if (isset($data['post_banner'])) {
        // Checks if the banner is a link
        if (UtilityValidator::isUrl($data['post_banner'])) {
            // Gets the s3 url
            $request->setStage('column_name', 'post_banner');
            $request->setStage('post_banner', $data['post_banner']);
            cradle()->trigger('link-upload', $request, $response);

            // Checks if there are no errors
            if (!$response->isError()) {
                $data['post_banner'] = $response->getResults()['post_banner'];
            }
        } else {
            //upload files
            //try cdn if enabled
            $config = $this->package('global')->service('s3-main');
            $data['post_banner'] = File::base64ToS3($data['post_banner'], $config);
            //try being old school
            $upload = $this->package('global')->path('upload');
            $data['post_banner'] = File::base64ToUpload($data['post_banner'], $upload);
        }
    }

    //if there is a resume
    if (isset($data['post_resume'])) {
        if (UtilityValidator::isUrl($data['post_resume'])) {
            // Gets the s3 url
            $request->setStage('column_name', 'resume_link');
            $request->setStage('resume_link', $data['post_resume']);
            cradle()->trigger('link-upload', $request, $response);

            // Checks if there are no errors
            if (!$response->isError()) {
                $data['post_resume'] = $response->getResults()['resume_link'];
            }

            // Unset the resume_link
            $request->removeStage('resume_link');
        } else {
            //upload files
            //try cdn if enabled
            $config = $this->package('global')->service('s3-main');
            $data['post_resume'] = File::base64ToS3($data['post_resume'], $config);
            //try being old school
            $upload = $this->package('global')->path('upload');
            $data['post_resume'] = File::base64ToUpload($data['post_resume'], $upload);
        }
    }

    // Checks for post_tags
    if (isset($data['post_tags'])) {
        $data['post_tags'] = json_encode($data['post_tags']);
    }

    // Checks for post_package
    if (isset($data['post_package'])) {
        $data['post_package'] = json_encode($data['post_package']);
    }

    // Checks for empty post_detail
    if (isset($data['post_detail']) && !trim($data['post_detail'])) {
        $data['post_detail'] = null;
    }

    // Checks for post_location
    if (isset($data['post_location']) && !empty($data['post_location'])) {
        $this->trigger('google-geomap', $request, $response);
        $data['post_geo_location'] = $response->getResults();
        $data['post_geo_location'] = json_encode($data['post_geo_location'], true);
        $data['post_location'] = ucwords(strtolower(trim($data['post_location'])));
    }

    if (isset($data['post_position']) && !empty($data['post_position'])) {
        $data['post_position'] = ucwords(strtolower(trim($data['post_position'])));
    }
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');
    $profileSql = ProfileService::get('sql');
    $profileRedis = ProfileService::get('redis');
    $profileElastic = ProfileService::get('elastic');

    //create guest profile
    if (!isset($data['profile_id']) || empty($data['profile_id'])) {
        //check if exists
        $search = $profileSql
            ->getResource()
            ->search('profile')
            ->filterByProfileType('guest');

        $profileWebsite = null;
        if (isset($data['profile_website'])) {
            $profileWebsite = $data['profile_website'];
        }

        $profileCompany = null;
        if (isset($data['profile_company']) && trim($data['profile_company'])) {
            $profileCompany = $data['profile_company'];
        }

        $profileName = 'Anonymous';
        if (isset($data['profile_name'])) {
            $profileName = $data['profile_name'];
        }

        //the crawler will pass profile name and profile company as the same
        if($profileName === $profileCompany) {
            $search->filterByProfileCompany($profileName);
        } else {
            $search->filterByProfileName($profileName);
        }

        $profileEmail = null;
        if (isset($data['profile_email']) && trim($data['profile_email'])) {
            $profileEmail = $data['profile_email'];
            //because a crawler can pass on info@jobayan.com
            //but what if we change the profile email ?
            //then this would create a new profile,
            //which would be moot
            if ($profileEmail !== 'info@jobayan.com') {
                $search->filterByProfileEmail($profileEmail);
            }
        }

        $profilePhone = null;
        if (isset($data['profile_phone']) && trim($data['profile_phone'])) {
            $profilePhone = $data['profile_phone'];
            $search->filterByProfilePhone($profilePhone);
        }

        $profileImage = null;
        if (isset($data['profile_image']) && trim($data['profile_image'])) {
            $profileImage = $data['profile_image'];
        }

        $profile = $search->getRow();

        // Checks if no profile was returned
        if(!$profile) {
            // create guest will return a guest profile if exists or create one
            $profile = $profileSql->create([
                'profile_email' => $profileEmail,
                'profile_phone' => $profilePhone,
                'profile_website' => $profileWebsite,
                'profile_name' => $profileName,
                'profile_company' => $profileCompany,
                'profile_image' => $profileImage,
                'profile_type' => 'guest'
            ]);

            // slugify
            $profile['profile_slug'] = $profileSql->slugify($profile['profile_name'], $profile['profile_id']);

            $profileSql->update($profile);

            //index profile
            $profileElastic->create($profile['profile_id']);

            //invalidate cache
            $profileRedis->removeSearch();

            $request->setStage('profile_id', $profile['profile_id']);

            //try to queue, and if not
            if (strpos($profileImage, '/images/default-avatar.png') === false
                && !$this->package('global')->queue('profile-image-s3', $profile)
            ) {
                //we don't really need this response
                $profileResponse = new Response();
                $profileResponse->load();

                //hit the terms manually
                $this->trigger('profile-image-s3', $request, $profileResponse);
            }
        }

        $data['profile_id'] = $profile['profile_id'];

        //this is so if the profile has a unique email, lets use that
        //instead of info@jobayan.com
        if (isset($data['post_email'])
            && (!trim($data['post_email']) || $data['post_email'] === 'info@jobayan.com')
            && trim($profile['profile_email'])
        ) {
            $data['post_email'] = $profile['profile_email'];
        }

        //same for phone
        if (isset($data['post_phone'])
            && !trim($data['post_phone'])
            && trim($profile['profile_phone'])
        ) {
            $data['post_phone'] = $profile['profile_phone'];
        }
    }

    // Search data if already exist in database
    if (isset($data)) {
        // Gets the data to check
        $duplicate = $data;

        // Checks if post_notify is set
        if (isset($duplicate['post_notify'])) {
            // Unset this for the duplicate check
            unset($duplicate['post_notify']);
        }

        // Checks if post_tag is set
        if (isset($duplicate['post_tags'])) {
            // Unset this for the duplicate check
            unset($duplicate['post_tags']);
        }

        // Check if exists
        $duplicate['post_duplicate'] = true;
        $results = $postSql->search($duplicate);

        // Checks if a post like this exists
        if (!empty($results) && $results['total'] != 0) {
            // Return Error
            return $response->setError(true, 'Post already exist!');
        }
    }

    //save post to database
    $results = $postSql->create($data);

    //link profile
    if(isset($data['profile_id'])) {
        $postSql->linkProfile($results['post_id'], $data['profile_id']);
    }

    //link comment
    if(isset($data['comment_id'])) {
        $postSql->linkComment($results['post_id'], $data['comment_id']);
    }

    //index post
    $postElastic->create($results['post_id']);

    //invalidate cache
    $postRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);

    $request->setStage('post_id', $results['post_id']);

    //try to queue, and if not
    if (!$this->package('global')->queue('post-banner-s3', $results)) {
        //hit the terms manually
        $this->trigger('post-banner-s3', $request, $response);
    }

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $results['host'] = $protocol . '://' . $request->getServer('HTTP_HOST');
    $request->setStage('host', $results['host']);

    $emailRequest  = Cradle\Http\Request::i();
    $smsRequest  = Cradle\Http\Request::i();

    //seperate request
    $emailRequest->setStage($request->getStage());
    $smsRequest->setStage($request->getStage());

    // Checks if there is a post type / post_type
    if (isset($data['post_type']) && !empty($data['post_type'])) {
        // If post matches with another post, send email to both ends
        if ($data['post_type'] == 'poster') {
            if (!$this->package('global')->queue('post-notify-matches-seeker', $results)) {
                cradle()->trigger('post-notify-matches-seeker', $request, $response);
            }
        } else {
            if (!$this->package('global')->queue('post-notify-matches-poster', $results)) {
                cradle()->trigger('post-notify-matches-poster', $request, $response);
            }
        }
    }

    // Checks if there is no queue
    if (!$this->package('global')->queue('post-notify-sms-matches', $results)) {
        // Trigger the event manualy
        $this->trigger('post-notify-sms-matches', $smsRequest, $response);
    }

    $request->setStage(
        'post_slug',
        $postSql->slugify(
            $results['post_name'],
            $results['post_id']
        )
    );

    $actionData = [
        'action_event' => 'post-create',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-create');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $story = cradle('global')->config('story', 'post-create');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);
});

/**
 * Post Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['post_id'])) {
        $id = $data['post_id'];
    } else if (isset($data['post_slug'])) {
        //what-evs-p32-p12345
        $slug = explode('-', $data['post_slug']);
        $id = substr($slug[count($slug)  -1], 1);
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            // Commented out for now till the inner join tables are fixed for elastic
            // $results = $postElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    //if permission is provided
    $permission = $request->getStage('permission');

    //check profile id and profile_parent
    if ($permission
        && ($results['profile_id'] != $permission
         && $results['profile_parent'] != $permission)) {
        //if not set role
        if (!$request->getStage('role')
            || $request->getStage('role') != 'post') {
            return $response->setError(true, 'Invalid Permissions');
        }
    }

    //virtual column
    $results['post_slug'] = $postSql->slugify($results['post_position'], $results['post_id']);

    // Checks the post_type
    if ($results['post_type'] == 'poster') {
        $url = '/Company-Hiring/'.$request->getStage('post_slug');
    } else {
        $url = '/Seeking-Job/'.$request->getStage('post_slug');
    }

    // Sets the post_url
    $request->setStage('post_url', $url);

    $response->setError(false)->setResults($results);
});

/**
 * Post Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save to database
    $results = $postSql->update([
        'post_id' => $data['post_id'],
        'post_active' => 0
    ]);

    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeDetail($data['post_id']);
    $postRedis->removeDetail($data['post_slug']);
    $postRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Post Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save to database
    $results = $postSql->update([
        'post_id' => $data['post_id'],
        'post_flag' => 0,
        'post_active' => 1,
        'post_restored' => date('Y-m-d H:i:s')
    ]);

    // Updates index
    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }
    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && !isset($data['post_duplicate'])
            && $elasticSearch) {
            //get it from index
            $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //virtual column
    if (isset($results['rows'])) {
        foreach($results['rows'] as $i => $row) {
            if (!isset($row['post_position'])) {
                continue;
            }

            $results['rows'][$i]['post_slug'] = $postSql->slugify(
                $row['post_position'],
                $row['post_id']
            );
        }
    }

    //set response format
    $response->setError(false)->setResults($results);

    $priority = rand(40, 49);
    if (isset($data['q']) && is_string($data['q']) && trim($data['q']) !== '') {
        if ($request->hasStage('priority')) {
            $priority = $request->getStage('priority');
        }

        //try to queue, and if not
        if (!$this
           ->package('global')
           ->queue()
           ->setQueue('jobayan_terms')
           ->setData($data)
           ->setPriority($priority)
           ->send('post-hit-terms', false)) {
            //hit the terms manually
            $this->trigger('post-hit-terms', $request, $response);
        }
    }
});

/**
 * Post Featured Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-featured-jobs', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getFeaturedCompanyPosts($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    $response->setError(false)->setResults($results);
});

/**
 * Post Featured Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-company-featured-jobs', function ($request, $response) {
    //----------------------------//
    // 1. Initialize Defaults
    $start = $ctr = 0;
    $posts = [];
    // we only need 4 posts, but we have to kind of set window for
    // limiting duplicate company
    $limiter = 50;

    //----------------------------//
    // 2. Process Data
    // no validation needed
    //----------------------------//
    // 3. Prepare Data
    // get the last featured post_id
    // to limit the start and end of random start
    $request->setStage('range', 1);
    $request->setStage('order', ['post_id' => 'DESC']);
    $this->trigger('post-featured-jobs', $request, $response);

    $last = $response->getResults('rows');

    //----------------------------//
    // 4. Process Data
    // is there any featured job post to begin with?
    if ($last) {
        $last = $last[0];
        $start = rand(0, $last['post_id']);

        // loop until we get 4 featured posts
        // and upto only 4 loops
        while (count($posts) < 4 && $ctr < 4) {
            $request->setStage('start_id', $start);
            $request->setStage('range', $limiter);
            $request->setStage('order', ['post_like_count' => 'DESC']);
            $this->trigger('post-featured-jobs', $request, $response);
            $featured = $response->getResults('rows');

            // if empty featured,
            // let's just display starting with start post_id > 0
            if (!$featured) {
                $start = 0;
                $request->setStage('start_id', $start);
                $this->trigger('post-featured-jobs', $request, $response);
                $featured = $response->getResults('rows');

                // if still empty, ignore the featured posts
                // and break the loop
                if (!$featured) {
                    break;
                }
            }

            foreach ($featured as $post) {
                // if we already got 4 posts
                // break the forach and while loop
                if (count($posts) >= 4) {
                    break 2;
                }

                // if the post owner is already existing
                // we don't have to check the number of like
                // since we already have sorted it by post_like_count
                if (isset($posts[$post['profile_id']])) {
                    continue;
                }

                $posts[$post['profile_id']] = $post;
            }

            // start from the last post_id
            $start = $post['post_id'];

            // if the start will be greater than latest featured post's id
            // then we need to stop from here
            if ($start >= $last['post_id']) {
                break;
            }

            $ctr++;
        }
    }

    // return in default format
    $results = [
        'rows' => $posts,
        'total' => count($posts)
    ];

    $response->setError(false)->setResults($results);
});

/**
 * Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-seeker-toinform', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getPostSeekerToInform($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //virtual column
    if (isset($results['rows'])) {
        foreach($results['rows'] as $i => $row) {
            if (!isset($row['post_position'])) {
                continue;
            }

            $results['rows'][$i]['post_slug'] = $postSql->slugify(
                $row['post_position'],
                $row['post_id']
            );
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Type Total Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-type-total', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['post_seeker'])) {
        //set response format
        $response->setError(false)->setResults(0);
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getPostTypeTotal($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Position Location Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-opening-location', function ($request, $response) {
    $data = $request->getStage();

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getOpeningLocation($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    $response->setError(false)->setResults($results);
});

/**
 * Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-tracking-job-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['profile_id'])) {
        return $response->setError(true, 'Not Found');
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getTrackingJobPost($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-link-form', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['post_id']) || !isset($data['form_id'])) {
        return $response->setError(true, 'Not Found');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = $postSql->linkForm($data['post_id'], $data['form_id']);

    // Updates Post
    $results = $postElastic->update($data['post_id']);

    // Invalidates Cache
    $postRedis->removeDetail($data['post_id']);

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Fuzzy search
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-fuzzy-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postElastic = PostService::get('elastic');

    if (!isset($data['term']) || !is_string($data['term'])) {
        return $response->setError(true, 'Invalid parameter');
    }

    $results = $postElastic->fuzzy($data);
    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-totals', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->getTotals();
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getTotals();
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Featured Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-featured', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getFeaturedPost($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Featured Post Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-featured-bulk', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    if (!isset($data['filter']['positions']) || !is_array($data['filter']['positions'])) {
        return $response->setError(true, 'Invalid Position');
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getFeaturedPostBulk($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Likes Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-likes', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getPostLikes($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //virtual column
    if(isset($results['rows'])) {
        foreach($results['rows'] as $i => $row) {
            $results['rows'][$i]['post_slug'] = $postSql->slugify(
                $row['post_position'],
                $row['post_id']
            );
        }
    }

    //set response format
    $response->setError(false)->setResults($results);

    $priority = rand(40, 49);

    if ($request->hasStage('priority')) {
        $priority = $request->getStage('priority');
    }

    //try to queue, and if not
    if(!$this
       ->package('global')
       ->queue()
       ->setQueue('jobayan_terms')
       ->setData($data)
       ->setPriority($priority)
       ->send('post-hit-terms', false)) {
        //hit the terms manually
        $this->trigger('post-hit-terms', $request, $response);
    }
});

/**
 * Post Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $post = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = PostValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['post_notify'])) {
        $data['post_notify'] = json_encode($data['post_notify']);
    }

    if (isset($data['post_expires'])) {
        $data['post_expires'] = date('Y-m-d H:i:s', strtotime($data['post_expires']));
    }

    //if there is an post image
    if (isset($data['post_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['post_image'] = File::base64ToS3($data['post_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['post_image'] = File::base64ToUpload($data['post_image'], $upload);
    }

    //if there is an image
    if (isset($data['post_banner'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['post_banner'] = File::base64ToS3($data['post_banner'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['post_banner'] = File::base64ToUpload($data['post_banner'], $upload);
    }

    if (isset($data['post_tags'])) {
        $data['post_tags'] = json_encode($data['post_tags']);
    }

    // Checks for post_package
    if (isset($data['post_package'])) {
        $data['post_package'] = json_encode($data['post_package']);
    }

    if (isset($data['post_phone'])) {
        $data['post_phone'] = trim($data['post_phone']);
    }

    if (isset($data['post_type']) && $data['post_type'] == 'poster') {
        if (isset($data['post_experience']) && $data['post_experience'] < 0) {
            return $response
                ->setError(true, 'Invalid Parameters')
                ->set('json', 'validation', [
                    'post_experience' => 'Experience should not lower than zero.'
                ]);
        }
    }

    if (isset($data['post_experience']) && $data['post_experience'] > 60) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'post_experience' => 'Experience should not greater than sixty.'
            ]);
    }

    // Checks for empty post_detail
    if (isset($data['post_detail']) && !trim($data['post_detail'])) {
        $data['post_detail'] = null;
    }

    // Checks for post_location
    if (isset($data['post_location']) && !empty($data['post_location'])) {
        $this->trigger('google-geomap', $request, $response);
        $data['post_geo_location'] = $response->getResults();
        $data['post_geo_location'] = json_encode($data['post_geo_location'], true);
        $data['post_location'] = ucwords(strtolower(trim($data['post_location'])));
    }

    if (isset($data['post_position']) && !empty($data['post_position'])) {
        $data['post_position'] = ucwords(strtolower(trim($data['post_position'])));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->update($data);

    //index post
    $res = $postElastic->update($data['post_id']);

    //invalidate cache
    $postRedis->removeDetail($response->getResults('post_id'));
    $postRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);

    $request->setStage('post_id', $results['post_id']);

    //try to queue, and if not
    if (isset($data['post_banner']) &&
        !$this->package('global')->queue('post-banner-s3', $results)) {
        //hit the terms manually
        $this->trigger('post-banner-s3', $request, $response);
    }

    $actionData = [
        'action_event' => 'post-update',
        'profile_id' => $post['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-update');
    $actionRequest->setStage('profile_id', $post['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $story = cradle('global')->config('story', 'post-update');
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();
    $storyRequest->setStage('profile_id', $post['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);
});

/**
 * Post Download Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-download', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        $response->setResults([]);
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    // Checks if there are errors
    // Checks if the profile id is not set
    if (!isset($data['profile_id'])) {
        $response->setResults([]);
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'profile_id' => 'ID is required'
            ]);
    }

    //----------------------------//
    // 3. Prepare Data
    $post = $response->getResults();

    // Checks if the post was deleted
    if (!$post['post_active']) {
        $response->setResults([]);
        return $response->setError(true, 'Deleted Post');
    }

    // Checks if there was no resume associated
    if (!isset($post['resume_link'])) {
        $response->setResults([]);
        return $response->setError(true, 'Missing Resume');
    }

    // Gets the resume_link
    $resume = $post['resume_link'];

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->addDownload($data['post_id'], $data['profile_id']);

    // Checks if this was already downloaded
    if (!$results) {
        //return response format
        return $response
            ->setError(true, 'Already downloaded')
            ->addValidation('code', 'already-downloaded')
            ->setResults(['resume_link' => $resume]);
    }

    //index post
    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeDetail($response->getResults('post_id'));
    $postRedis->removeSearch();

    $actionData = [
        'action_event' => 'post-download',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-download');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-download');
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    //return response format
    $response->setError(false)->setResults(['resume_link' => $resume]);
});

/**
 * Post Email Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //if there are errors
    if (!isset($data['profile_id'])) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'profile_id' => 'ID is required'
            ]);
    }

    //----------------------------//
    // 3. Prepare Data
    $email = $response->getResults('post_email');

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->addEmail($data['post_id'], $data['profile_id']);

    if (!$results) {
        //return response format
        return $response
            ->setError(true, 'Already emailed')
            ->setResults(['post_email' => $email]);
    }

    //index post
    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeDetail($response->getResults('post_id'));
    $postRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults(['post_email' => $email]);
});

/**
 * Post Like Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-like', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        $response->setResults([]);
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //if there are errors
    if (!isset($data['profile_id'])) {
        $response->setResults([]);
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'profile_id' => 'ID is required'
            ]);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->addLike($data['post_id'], $data['profile_id']);

    if(!$results) {
        //return response format
        return $response->remove('json', 'results')->setError(true, 'You are already interested.');
    }

    //index post
    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeDetail($response->getResults('post_id'));
    $postRedis->removeSearch();

    $settings = $this->package('global')->config('settings');
    if (!isset($settings['host'])) {
        //because there's no way the CLI queue would know the host
        $protocol = 'http';
        if ($request->getServer('SERVER_PORT') === 443) {
            $protocol = 'https';
        }

        $data['host'] = $protocol . '://' . $request->getServer('HTTP_HOST');
    } else {
        $data['host'] = $settings['host'];
    }

    $request->setStage('host', $data['host']);

    //hit the terms manually
    $this->trigger('post-notify-like', $request, $response);

    // notify poster via SMS
    $this->trigger('post-notify-sms-interest', $request, $response);

    $actionData = [
        'action_event' => 'post-like',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-like');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-like');
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    $response->setResults([]);
});

/**
 * Post View Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-view', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        $response->setResults([]);
        return;
    }

    //get data from stage
    $data = $response->getResults();

    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->addView($data['post_id']);

    if (!$results) {
        return false;
    }

    // index post
    $postElastic->update($response->getResults('post_id'));

    $response->setResults([]);
});

/**
 * Post Phone Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-phone', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //if there are errors
    if (!isset($data['profile_id'])) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', [
                'profile_id' => 'ID is required'
            ]);
    }

    //----------------------------//
    // 3. Prepare Data
    $phone = $response->getResults('post_phone');

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save post to database
    $results = $postSql->addPhone($data['post_id'], $data['profile_id']);

    if(!$results) {
        //return response format
        return $response
            ->setError(true, 'Already phoned')
            ->setResults(['post_phone' => $phone]);
    }

    //index post
    $postElastic->update($response->getResults('post_id'));

    //invalidate cache
    $postRedis->removeDetail($response->getResults('post_id'));
    $postRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults(['post_phone' => $phone]);
});

/**
 * Add term hits (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-hit-terms', function ($request, $response) {
    //get data
    $data = $request->getStage();

    //this/these will be used a lot
    $termSql = TermService::get('sql');
    $termRedis = TermService::get('redis');
    $termElastic = TermService::get('elastic');

    if (isset($data['q']) && !is_array($data['q']) && trim($data['q']) !== '') {
        $termSql->addHit($data['q'], 'search');
    }

    if (isset($data['filter']['post_location']) && !is_array($data['filter']['post_location'])
        && trim($data['filter']['post_location']) !== '') {
        $termSql->addHit($data['filter']['post_location'], 'location');
    }

    if (isset($data['filter']['post_position'])
        && trim($data['filter']['post_position']) !== '') {
        $termSql->addHit($data['filter']['post_position'], 'position');
    }

    if (isset($data['filter']['post_tags'])
        && trim($data['filter']['post_tags']) !== '') {
        $termSql->addHit($data['filter']['post_tags'], 'tag');
    }
});

/**
 * Notify like (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-notify-like', function ($request, $response) {
    $email = $this->package('global')->service('mail-main');
    $config = $this->package('global')->service('ses');

    //get data
    $this->trigger('profile-detail', $request, $response);
    if ($response->isError()) {
        return;
    }

    $profile = $response->getResults();
    $this->trigger('post-detail', $request, $response);
    $notify = $response->getResults('post_notify');

    if ($response->isError() || !$notify) {
        return;
    }

    if (!in_array('likes', $notify)) {
        return;
    }

    $post = $response->getResults();

    if (!trim($post['post_email'])) {
        return;
    }

    $emailData = [];
    $emailData['from'] = $config['sender'];

    $name = $profile['profile_name'];
    if ($post['post_type'] === 'seeker' && $profile['profile_company']) {
        $name = $profile['profile_company'];
    }

    $emailData['subject'] = $this->package('global')->translate($name . ' is Interested in you! - Jobayan');

    $handlebars = $this->package('global')->handlebars();
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $link = $host . '/' . $post['post_slug'] . '/post-message-poster';

    //if someone likes a seeker post, they are a company
    if ($post['post_type'] === "poster") {
        $link = $host . '/' . $post['post_slug'] . '/post-message-seeker';
    }

    $profileLink = $host . '/' . $profile['profile_slug'] . '/profile-post';

    $contents = file_get_contents(__DIR__ . '/template/email/like/' . $post['post_type'] . '.txt');
    $textTemplate = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/like/' . $post['post_type'] . '.html');
    $htmlTemplate = $handlebars->compile($contents);

    $emailData['to'] = [];
    $emailData['to'][] = $post['post_email'];

    $data = [
        'profile_name'    => $name,
        'profile_id'      => $profile['profile_id'],
        'profile_image'   => $profile['profile_image'],
        'profile_email'   => $profile['profile_email'],
        'profile_phone'   => $profile['profile_phone'],
        'post_name'       => $post['post_name'],
        'post_position'   => $post['post_position'],
        'post_location'   => $post['post_location'],
        'post_experience' => $post['post_experience'],
        'profile_link'    => $profileLink,
        'profile_slug'    => $profile['profile_slug'],
        'host'            => $host,
        'link'            => $link
    ];

    //get resume id
    foreach ($post['likers'] as $key => $value) {
        if ($value['profile_id'] == $profile['profile_id']
            && !empty($value['post_resume']['resume_id'])) {
            $post['post_resume']['resume_id'] = $value['post_resume']['resume_id'];
        }
    }

    if (isset($post['post_resume']) && !empty($post['post_resume'])
        && isset($post['post_resume']['resume_id'])
        && !empty($post['post_resume']['resume_id'])) {
        $link = $host . '/'.$post['post_slug'] . '/post-detail';
        $data['resume_link'] = $link . '?resume_id=' . $post['post_resume']['resume_id'];
    }

    //text
    $emailData['text'] = $textTemplate($data);

    //html
    $emailData['html'] = $htmlTemplate($data);

    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);

    $actionData = [
        'action_event' => 'post-notify-like',
        'profile_id' => $post['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-notify-like');
    $actionRequest->setStage('profile_id', $post['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-notify-like');
    $storyRequest->setStage('profile_id', $post['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    $response->remove('json', 'results');
    $response->setError(false);
});

/**
 * Notify matches poster (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-notify-matches-poster', function ($request, $response) {
    $ses = $this->package('global')->service('ses');
    $email = $this->package('global')->service('mail-main');
    $sms = $this->package('global')->service('semafore-main');

    if ((!$email || !$ses) && !$sms) {
        return;
    }

    $this->trigger('post-detail', $request, $response);

    if($response->isError()) {
        return;
    }

    //get data
    $data = $response->getResults();

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $emailData = [];
    $emailData['from'] = $ses['sender'];
    $emailData['subject'] = $this->package('global')->translate('We Found a match! - Jobayan.com');

    $seekerData = [];
    $seekerData['from'] = $ses['sender'];
    $seekerData['subject'] = $this->package('global')->translate('We Found a match! - Jobayan.com');

    $handlebars = $this->package('global')->handlebars();
    $server = $request->getServer();
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $link = $host . '/' . $data['post_slug'] . '/post-detail';

    $contents = file_get_contents(__DIR__ . '/template/email/match/poster.txt');
    $textTemplate = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/match/poster.html');
    $htmlTemplate = $handlebars->compile($contents);

    $seekerContents = file_get_contents(__DIR__ . '/template/email/match/seeker.txt');
    $seekerTextTemplate = $handlebars->compile($seekerContents);

    $seekerContents = file_get_contents(__DIR__ . '/template/email/match/seeker.html');
    $seekerHtmlTemplate = $handlebars->compile($seekerContents);

    $results = $postSql->search([
        'filter' => [
            'post_position' => $data['post_position'],
            'post_location' => $data['post_location']
        ],
        'post_notify' => 'match',
        'group' => 'profile_id,post_id'
    ]);

    // check if send to seeker
    $sendSeeker = false;
    foreach ($results['rows'] as $row) {
        //if it's the same person
        if ($row['profile_id'] === $data['profile_id']) {
            $sendSeeker = true;
            break;
        }
    }

    foreach ($results['rows'] as $row) {
        //if it's the same person
        if ($row['profile_id'] === $data['profile_id']) {
            continue;
        }

        //text
        $emailData['text'] = $textTemplate([
            'profile_name'    => $data['post_name'],
            'profile_image'   => $data['profile_image'],
            'profile_id'      => $row['profile_id'],
            'post_name'       => $data['post_name'],
            'post_position'   => $data['post_position'],
            'post_location'   => $data['post_location'],
            'post_experience' => $data['post_experience'],
            'host'            => $host,
            'link'            => $link
        ]);

        //html
        $emailData['html'] = $htmlTemplate([
            'profile_name'    => $data['post_name'],
            'profile_company' => $row['profile_company'],
            'profile_image'   => $data['profile_image'],
            'profile_id'      => $row['profile_id'],
            'post_name'       => $data['post_name'],
            'post_position'   => $data['post_position'],
            'post_location'   => $data['post_location'],
            'post_experience' => $data['post_experience'],
            'host'            => $host,
            'link'            => $link
        ]);

        if ($email && trim($row['post_email'])) {
            $emailData['to'] = [];
            $emailData['to'][] = $row['post_email'];

            //send mail
            $request->setStage($emailData);
            $this->trigger('prepare-email', $request, $response);

            // increment email count
            $row['post_email_count'] += 1;

            $request->removeStage();
            $request->setStage('post_id', $row['post_id']);
            $request->setStage('post_email_count', $row['post_email_count']);

            // update email count
            $this->trigger('post-update', $request, $response);
        }

        // set seeker data
        if ($sendSeeker) {
            $posterLink = $host . '/' . $row['post_slug'] . '/post-detail';

            //text
            $seekerData['text'] = $seekerTextTemplate([
                'profile_name'    => $data['post_name'],
                'profile_image'   => $row['profile_image'],
                'profile_id'      => $row['profile_id'],
                'post_name'       => $row['post_name'],
                'post_position'   => $data['post_position'],
                'post_location'   => $data['post_location'],
                'post_experience' => $data['post_experience'],
                'host'            => $host,
                'link'            => $posterLink
            ]);

            //html
            $seekerData['html'] = $seekerHtmlTemplate([
                'profile_name'    => $data['post_name'],
                'profile_company' => $row['profile_company'],
                'profile_image'   => $row['profile_image'],
                'profile_id'      => $row['profile_id'],
                'post_name'       => $row['post_name'],
                'post_position'   => $data['post_position'],
                'post_location'   => $data['post_location'],
                'post_experience' => $data['post_experience'],
                'host'            => $host,
                'link'            => $posterLink
            ]);

            $seekerData['to'] = [];
            $seekerData['to'][] = $data['post_email'];

            //send mail
            $request->setStage($seekerData);
            $this->trigger('prepare-email', $request, $response);

            // increment email count
            $data['post_email_count'] += 1;

            $request->removeStage();
            $request->setStage('post_id', $data['post_id']);
            $request->setStage('post_email_count', $data['post_email_count']);

            // update email count
            $this->trigger('post-update', $request, $response);
        }

        //cant sms seekers because it costs money
        if ($row['post_type'] === 'seeker') {
            continue;
        }

        $actionData = [
            'action_event' => 'post-notify-matches',
            'profile_id' => $row['profile_id']
        ];

        // check action event
        // if (!$this->package('global')->queue('action-check-event', $actionData)) {
        // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'post-notify-matches');
        $actionRequest->setStage('profile_id', $row['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
        // }

        // add story
        $storyRequest  = Cradle\Http\Request::i();
        $storyResponse  = Cradle\Http\Response::i();

        $story = cradle('global')->config('story', 'post-notify-matches');
        $storyRequest->setStage('profile_id', $row['profile_id']);
        $storyRequest->setStage('add_story', [$story]);
        $this->trigger('profile-update', $storyRequest, $storyResponse);

    }
});

/**
 * Notify matches seeker (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-notify-matches-seeker', function ($request, $response) {
    $ses = $this->package('global')->service('ses');
    $email = $this->package('global')->service('mail-main');
    $sms = $this->package('global')->service('semafore-main');

    if ((!$email || !$ses) && !$sms) {
        return;
    }

    $this->trigger('post-detail', $request, $response);

    if($response->isError()) {
        return;
    }

    //get data
    $data = $response->getResults();

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $emailData = [];
    $emailData['from'] = $ses['sender'];
    $emailData['subject'] = $this->package('global')->translate('We Found a match! - Jobayan.com');

    $posterData['from'] = $ses['sender'];
    $posterData['subject'] = $this->package('global')->translate('We Found a match! - Jobayan.com');

    $handlebars = $this->package('global')->handlebars();
    $server = $request->getServer();
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $link = $host . '/'.$data['post_slug'] . '/post-detail';

    // Seeker templates
    $contents = file_get_contents(__DIR__ . '/template/email/match/seeker.txt');
    $textTemplate = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/match/seeker.html');
    $htmlTemplate = $handlebars->compile($contents);

    // Poster templates
    $posterContents = file_get_contents(__DIR__ . '/template/email/match/poster.txt');
    $posterTextTemplate = $handlebars->compile($posterContents);

    $posterContents = file_get_contents(__DIR__ . '/template/email/match/poster.html');
    $posterHtmlTemplate = $handlebars->compile($posterContents);

    $results = $postSql->search([
        'filter' => [
            'post_position' => $data['post_position'],
            'post_location' => $data['post_location']
        ],
        'post_notify' => 'match',
        'group' => 'profile_id,post_id'
    ]);

    // check if send to poster
    $sendPoster = false;
    foreach ($results['rows'] as $row) {
        //if it's the same person
        if($row['profile_id'] === $data['profile_id']) {
            $sendPoster = true;
            continue;
        }
    }

    foreach ($results['rows'] as $row) {
        //if it's the same person
        if ($row['profile_id'] === $data['profile_id']) {
            continue;
        }

        //text
        $emailData['text'] = $textTemplate([
            'profile_name'    => $row['post_name'],
            'profile_image'   => $data['profile_image'],
            'profile_id'      => $data['profile_id'],
            'post_name'       => $data['post_name'],
            'post_position'   => $row['post_position'],
            'post_location'   => $row['post_location'],
            'post_experience' => $row['post_experience'],
            'host'            => $host,
            'link'            => $link
        ]);

        //html
        $emailData['html'] = $htmlTemplate([
            'profile_name'    => $row['post_name'],
            'profile_company' => $data['profile_company'],
            'profile_image'   => $data['profile_image'],
            'profile_id'      => $data['profile_id'],
            'post_name'       => $data['post_name'],
            'post_position'   => $row['post_position'],
            'post_location'   => $row['post_location'],
            'post_experience' => $row['post_experience'],
            'host'            => $host,
            'link'            => $link
        ]);

        if ($email && trim($row['post_email'])) {
            $emailData['to'] = [];
            $emailData['to'][] = $row['post_email'];

            //send mail
            $request->setStage($emailData);
            $this->trigger('prepare-email', $request, $response);

            // increment email count
            $row['post_email_count'] += 1;

            $request->removeStage();
            $request->setStage('post_id', $row['post_id']);
            $request->setStage('post_email_count', $row['post_email_count']);

            // update email count
            $this->trigger('post-update', $request, $response);
        }

        // set poster data
        if ($sendPoster) {
            $seekerLink = $host . '/' . $row['post_slug'] . '/post-detail';

            //text
            $posterData['text'] = $posterTextTemplate([
                'profile_name'    => $row['post_name'],
                'profile_image'   => $row['profile_image'],
                'profile_id'      => $row['profile_id'],
                'post_name'       => $row['post_name'],
                'post_position'   => $data['post_position'],
                'post_location'   => $data['post_location'],
                'post_experience' => $data['post_experience'],
                'host'            => $host,
                'link'            => $seekerLink
            ]);

            //html
            $posterData['html'] = $posterHtmlTemplate([
                'profile_name'    => $row['post_name'],
                'profile_company' => $data['profile_company'],
                'profile_image'   => $row['profile_image'],
                'profile_id'      => $row['profile_id'],
                'post_name'       => $row['post_name'],
                'post_position'   => $data['post_position'],
                'post_location'   => $data['post_location'],
                'post_experience' => $data['post_experience'],
                'host'            => $host,
                'link'            => $seekerLink
            ]);

            $posterData['to'] = [];
            $posterData['to'][] = $data['post_email'];

            //send mail
            $request->setStage($posterData);
            $this->trigger('prepare-email', $request, $response);

            // increment email count
            $data['post_email_count'] += 1;

            $request->removeStage();
            $request->setStage('post_id', $data['post_id']);
            $request->setStage('post_email_count', $data['post_email_count']);

            // update email count
            $this->trigger('post-update', $request, $response);
        }

        //cant sms seekers because it costs money
        if ($row['post_type'] === 'seeker') {
            continue;
        }

        $actionData = [
            'action_event' => 'post-notify-matches',
            'profile_id' => $row['profile_id']
        ];

        // check action event
        // if (!$this->package('global')->queue('action-check-event', $actionData)) {
        // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'post-notify-matches');
        $actionRequest->setStage('profile_id', $row['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
        // }

        // add story
        $storyRequest  = Cradle\Http\Request::i();
        $storyResponse  = Cradle\Http\Response::i();

        $story = cradle('global')->config('story', 'post-notify-matches');
        $storyRequest->setStage('profile_id', $row['profile_id']);
        $storyRequest->setStage('add_story', [$story]);
        $this->trigger('profile-update', $storyRequest, $storyResponse);

    }
});

/**
 * Notify sms matches (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-notify-sms-matches', function ($request, $response) {

    $sms = $this->package('global')->service('semafore-main');

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    if (!$sms) {
        return;
    }

    $this->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        return;
    }

    // must be of type seeker
    if ($response->getResults('post_type') !== 'seeker') {
        return false;
    }

    //get data
    $data = $response->getResults();

    // setup text template
    $handlebars = $this->package('global')->handlebars();
    $contents = file_get_contents(__DIR__ . '/template/email/match/sms.txt');
    $textTemplate = $handlebars->compile($contents);

    // post mysql
    $postSql = PostService::get('sql');

    // search for match job seeker posts
    $results = $postSql->search([
        'filter' => [
            'post_type' => 'poster',
            'post_position' => $response->getResults('post_position'),
            'post_location' => $response->getResults('post_location')
        ],
        'group' => 'profile_id,post_id'
    ]);


    foreach ($results['rows'] as $row) {
        //if it's the same person
        if ($row['profile_id'] === $response->getResults('profile_id')) {
            continue;
        }

        // do not send if sms count is more than 600
        if (!isset($row['post_sms_match_count']) || $row['post_sms_match_count'] > 60) {
            continue;
        }

        // check if has sms-match post_notify
        if ((!isset($row['post_notify']) || !in_array('sms-match', $row['post_notify']))
            && (!isset($row['profile_package']) || !in_array('sms-match', $row['profile_package']))) {
            continue;
        }

        // format phone
        if (substr($row['post_phone'], 0, 1) == '0') {
            $row['post_phone'] = '+63' . substr($row['post_phone'], 1);
        }

        // format phone
        if (substr($row['post_phone'], 0, 1) === '9') {
            $row['post_phone'] = '+63' . $row['post_phone'];
        }

        // create slug
        $row['post_slug'] = $postSql->slugify($row['post_position'], $row['post_id']);

        // set link
        $link = $host . '/'. $row['post_slug'] . '/post-detail';

        $text = $textTemplate([
            'post_name' => $data['post_name'],
            'post_position' => $data['post_position'],
            'post_location' => $data['post_location'],
            'post_experience' => $data['post_experience'],
            'host' => $host,
            'link' => $link
        ]);

        //curl http://api.semaphore.co/api/v4/messages -d "apikey=0ca99eecd3db965fa02b0f03cf6645f4&number=09053376000&sendername=Jobayan&message=We found a match! Manly is a Programmer, looking for a job in Metro Manila. www.jobayan.dev/Programmer-p190/post-detail"
        $result = (new Curl())
            ->setUrl($sms['endpoint'])
            ->setPostFields([
                'sendername' => $sms['sender_name'],
                'apikey' => $sms['token'],
                'number' => trim($row['post_phone']),
                'message' => $text
            ])
            ->getJsonResponse();

        // increment sms count
        $row['post_sms_match_count'] += 1;

        $request->removeStage();
        $request->setStage('post_id', $row['post_id']);
        $request->setStage('post_sms_match_count', $row['post_sms_match_count']);

        // update sms count
        $this->trigger('post-update', $request, $response);

        $actionData = [
            'action_event' => 'post-notify-sms-matches',
            'profile_id' => $row['profile_id']
        ];

        // check action event
        // if (!$this->package('global')->queue('action-check-event', $actionData)) {
        // if no queue manually do it
        $actionRequest  = Cradle\Http\Request::i();
        $actionResponse  = Cradle\Http\Response::i();
        $actionRequest->setStage('action_event', 'post-notify-sms-matches');
        $actionRequest->setStage('profile_id', $row['profile_id']);
        $this->trigger('action-check-event', $actionRequest, $actionResponse);
        // }

        // add story
        $storyRequest  = Cradle\Http\Request::i();
        $storyResponse  = Cradle\Http\Response::i();

        $story = cradle('global')->config('story', 'post-notify-sms-matches');
        $storyRequest->setStage('profile_id', $row['profile_id']);
        $storyRequest->setStage('add_story', [$story]);
        $this->trigger('profile-update', $storyRequest, $storyResponse);
    }
});

/**
 * Update posts email related to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-update-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];

    // if has results
    if ($response->hasResults('post_id')) {
        $data['profile_id'] =  $response->getResults('profile_id');
        $data['email'] =  $response->getResults('profile_email');
    }

    // if request
    if ($request->hasStage()) {
        $data['profile_id'] = $request->getStage('profile_id');
        $data['email'] = $request->getStage('profile_email');
    }

    // set parameter
    $data['start'] = 0;
    $data['range'] = 500;

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    while (true) {
        // get profile posts id
        $results = $postSql->getPostProfile($data);

        // if empty posts
        if (empty($results)) {
            break;
        }

        // loop through data
        foreach ($results as $result) {
            //save to database
            $results = $postSql->update([
                'post_id' => $result['post_id'],
                'post_email' => $data['email']
            ]);

            //remove from index
            $postElastic->update($result['post_id']);

            //invalidate cache
            $postRedis->removeDetail($result['post_id']);
            $postRedis->removeSearch();
        }

        // increment starting point
        $data['start'] += $data['range'];

        // delay for 2 seconds
        sleep(2);
    }

    $actionData = [
        'action_event' => 'post-update-email',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-update-email');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-update-email');
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    return true;
});

/**
 * Update posts phone related to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-update-phone', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];

    // if has results
    if ($response->hasResults('post_id')) {
        $data['profile_id'] =  $response->getResults('profile_id');
        $data['phone'] =  $response->getResults('profile_phone');
    }

    // if request
    if ($request->hasStage()) {
        $data['profile_id'] = $request->getStage('profile_id');
        $data['phone'] = $request->getStage('profile_phone');
    }

    // set parameter
    $data['start'] = 0;
    $data['range'] = 500;

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    while (true) {
        // get profile posts id
        $results = $postSql->getPostProfile($data);

        // if empty posts
        if (empty($results)) {
            break;
        }

        // loop through data
        foreach ($results as $result) {
            //save to database
            $results = $postSql->update([
                'post_id' => $result['post_id'],
                'post_phone' => $data['phone']
            ]);

            //remove from index
            $postElastic->update($result['post_id']);

            //invalidate cache
            $postRedis->removeDetail($result['post_id']);
            $postRedis->removeSearch();
        }

        // increment starting point
        $data['start'] += $data['range'];

        // delay for 2 seconds
        sleep(2);
    }

    $actionData = [
        'action_event' => 'post-update-phone',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-update-phone');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-update-phone');
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

    return true;
});

/**
 * Update posts phone related to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-update-expire', function ($request, $response) {
    $this->trigger('post-detail', $request, $response);

    if ($response->isError() && !$response->getResults()) {
        return;
    }

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $result = $postSql->update([
        'post_id' => $response->getResults('post_id'),
        'post_active' => $response->getResults('post_active'),
        'post_expires' => date('Y-m-d H:i:s', strtotime('+60 days'))
    ]);

    //remove from index
    $postElastic->update($result['post_id']);

    //invalidate cache
    $postRedis->removeDetail($result['post_id']);
    $postRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($result);
});

/**
 * Move Post Banner to S3 (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-banner-s3', function ($request, $response) {
    //try cdn if enabled
    $config = $this->package('global')->service('s3-main');

    if (!$config) {
        return;
    }

    if ($response->hasResults('post_id')) {
        $request->setStage('post_id', $response->getResults('post_id'));
    }

    //get the post detail
    $this->trigger('post-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data
    $data = $response->getResults();

    //post banner
    if (trim($data['post_banner'])) {
        //is it base 64 ?
        if (strpos($data['post_banner'], 'data:') === 0) {
            $data['post_banner'] = File::base64ToS3($data['post_banner'], $config);
        } else {
            $data['post_banner'] = File::linkToS3($data['post_banner'], $config);
        }
    }

    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    //save to database
    $results = $postSql->update([
        'post_id' => $data['post_id'],
        'post_banner' => $data['post_banner']
    ]);

    //remove from index
    $postElastic->update($data['post_id']);

    //invalidate cache
    $postRedis->removeDetail($data['post_id']);
    $postRedis->removeDetail($data['post_slug']);
    $postRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Daily queue to check if Posts are about to expire
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('check-post-expiration', function ($request, $response) {
    //get the posts
    $this->trigger('check-post-expiration-date', $request, $response);
    $resultSet = $response->getResults();

    //if there's an error
    if ($response->isError()) {
        return;
    }

    // loop through the result and set: post_name and post_expires
    foreach ($resultSet['rows'] as $key => $value) {
        $request->setStage($value);

        // check if queued
        if (!$this->package('global')->queue('post-expiring-mail', $value)) {
            // run the job for setting up the email and sending
            $this->trigger('post-expiring-mail', $request, $response);
        }
    }
});

$cradle->on('check-post-expiration-date', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Gets the settings
    $settings = $this->package('global')->config('settings');

    // Default Elastic Search Settings
    $elasticSearch = 0;

    // Checks if there are elastic search settings set
    if (isset($settings['elastic-search']) && $settings['elastic-search'] == 1) {
        $elasticSearch = 1;
    }

    //----------------------------//
    // 2. Validate Data
    // no validation needed

    //----------------------------//
    // 3. Prepare Data
    // set filters, range and indicator
    $data['post_expires'] = 'soon';
    $data['range'] = 0;

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $postElastic = PostService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex') && $elasticSearch) {
            //get it from index
            // $results = $postElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->searchExpireDate($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Expiring Email
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-expiring-mail', function ($request, $response) {
    $post = $request->getStage();

    $config = $this->package('global')->service('ses');

    if (isset($config['sender'])) {
        $sender = $config['sender'];
    }

    if (!$config) {
        $config = $this->package('global')->service('mail-main');
        $sender = [$config['user'] => $config['name']];
    }

    if (!isset($post['post_name']) || !isset($post['post_position'])
        || !isset($post['post_expires']) || !isset($post['post_slug'])) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', ['post_slug' => 'invalid post']);
    }

    //form link
    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    $remove = $host . '/post/remove/'.$post['post_id'];
    $renew = $host . '/profile/post/search?filter[post_id]='.$post['post_id'];

    //prepare data
    $data = [];
    $data['from'] = $sender;

    $data['to'] = [$request->getStage('post_email')] ;

    $data['subject'] = $this->package('global')->translate('Your Post is Expiring! - Jobayan.com');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/expiring.txt');
    $template = $handlebars->compile($contents);
    $data['text'] = $template(['remove' => $remove]);
    $data['text'] = $template(['renew' => $renew]);

    if ($post['post_type'] == 'poster') {
        $data['body_message'] = "We've noticed that your post is expiring in ". "10 days!"  ." Have you fount the talent you are looking for?";
        $data['remove_message'] = "Yes, I have found someone awesome for the role.";
        $create = $host . '/post/create/poster?clear';
    } else {
        $data['body_message'] = "We've noticed that your post is expiring in ". "10 days!" ." Have you landed your desired job role yet?";
        $data['remove_message'] = "Yes, I have landed on this position.";
        $create = $host . '/post/create/seeker?clear';
    }

    $contents = file_get_contents(__DIR__ . '/template/email/expiring.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host'           => $host,
        'remove'         => $remove,
        'create'         => $create,
        'renew'          => $renew,
        'post'           => $post,
        'body_message'   => $data['body_message'],
        'remove_message' => $data['remove_message']
    ]);

    $request->setStage($data);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Update posts phone related to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-get-count', function ($request, $response) {
    if (!$request->getStage('profile_id') ||
        !$request->getStage('activity') ||
        !in_array(
            $request->getStage('activity'),
            ['interested', 'promoted', 'downloaded']
        )
    ) {
        return;
    }

    if ($request->getStage('activity') == 'promoted') {
        $request->setStage(
            'filter',
            [
                'profile_id' => $request->getStage('profile_id'),
                'post_flag' => 1
            ]);
        cradle()->trigger('post-search', $request, $response);
        return $response
                ->setError(false)
                ->setResults($response->getResults('total'));
    }

    $postSql = PostService::get('sql');

    $result = $postSql->getUserPostActivity(
        $request->getStage('profile_id'),
        $request->getStage('activity')
    );

    //return response format
    $response->setError(false)->setResults($result);
});

/**
 * Notify like (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('send-message', function ($request, $response) {
    $email = $this->package('global')->service('mail-main');
    $config = $this->package('global')->service('ses');
    $settings = $this->package('global')->config('settings');

    //get data
    $this->trigger('profile-detail', $request, $response);

    if ($response->isError()) {
        return;
    }

    $profile = $response->getResults();

    $session = $request->getSession('me');

    if ($response->isError()) {
        return;
    }

    $post_slug = $request->getStage('post_slug');
    $post_type = $request->getStage('post_type');

    $server = $request->getServer();
    $host = $settings['host'];
    $link = $host . '/' . 'Companies/' .$session['profile_slug'];

    // Setup link if post type is seeker
    if ($post_type == "seeker") {
        $link = $host . '/' . 'Job-Seekers/' .$session['profile_slug'];
    }

    $emailData = [];
    $emailData['from'] = $config['sender'];
    $companyName = $session['profile_company'];
    $name = $profile['profile_name'];
    $emailData['data'] = $request->getStage('post_detail');

    $emailData['subject'] = $this->package('global')->translate('Sent a message to you!');

    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/messageJobseeker/' . $post_type . '.txt');
    $textTemplate = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/messageJobseeker/' . $post_type .'.html');
    $htmlTemplate = $handlebars->compile($contents);

    $emailData['to'] = [];
    $emailData['to'][] = $profile['profile_email'];
    $data = [
        'profile_image'   => $session['profile_image'],
        'profile_email'   => $session['profile_email'],
        'profile_phone'   => $session['profile_phone'],
        'profile_name'    => $session['profile_name'],
        'profile_company' => $companyName,
        'post_name'       => $name,
        'post_company'    => $profile['profile_company'],
        'post_data'       => $emailData['data'],
        'post_email'      => $profile['profile_email'],
        'post_phone'      => $profile['profile_phone'],
        'profile_id'      => $session['profile_id'],
        'link'            => $link,
        'host'            => $host,
    ];

    //text
    $emailData['text'] = $textTemplate($data);

    //html
    $emailData['html'] = $htmlTemplate($data);

    $request->setStage($emailData);
    $this->trigger('prepare-email', $request, $response);

    $response->remove('json', 'results');
    $response->setError(false);
});

$cradle->on('post-geo-location', function ($request, $response) {
    // Checks for post_location
    if (!$request->hasStage('post_location')) {
        return $response->setResults([]);
    }

    $postSql = PostService::get('sql');
    $results = $postSql->getGeoLocation($request->getStage('post_location'));

    $response->setResults($results);
});

/**
 * Notify SMS interested (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-notify-sms-interest', function ($request, $response) {
    $sms = $this->package('global')->service('semafore-main');

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];
    if (!$sms) {
        return;
    }

    // trigger job for getting post
    $this->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        return;
    }

    // must be of type poster
    if ($response->getResults('post_type') !== 'poster') {
        return false;
    }

    //get data
    $data = $response->getResults();

    // setup text template
    $handlebars = $this->package('global')->handlebars();
    $contents = file_get_contents(__DIR__ . '/template/email/like/sms.txt');
    $textTemplate = $handlebars->compile($contents);

    // check if post_notify
    if ((!isset($data['post_notify']) || !in_array('sms-interest', $data['post_notify']))
        && (!isset($data['profile_package']) || !in_array('sms-interest', $data['profile_package']))) {
        // don't send and sms
        return;
    }

    // send SMS from here
    // format phone
    if (substr($data['post_phone'], 0, 1) == '0') {
        $data['post_phone'] = '+63' . substr($data['post_phone'], 1);
    }

    // format phone
    if (substr($data['post_phone'], 0, 1) === '9') {
        $data['post_phone'] = '+63' . $data['post_phone'];
    }

    $text = $textTemplate([
        'profile_name' => $request->getSession('me', 'profile_name'),
        'post_position' => $data['post_position']
    ]);

    //curl http://api.semaphore.co/api/v4/messages -d "apikey=0ca99eecd3db965fa02b0f03cf6645f4&number=09053376000&sendername=Jobayan&message=Herman Menor Jr is interested in your Coder job post. View interested job seekers here: https://tinyurl.com/ydclc3ga"
    $result = (new Curl())
        ->setUrl($sms['endpoint'])
        ->setPostFields([
            'sendername' => $sms['sender_name'],
            'apikey' => $sms['token'],
            'number' => trim($data['post_phone']),
            'message' => $text
        ])
        ->getJsonResponse();

    // increment sms count
    $data['post_sms_interested_count'] += 1;

    $request->removeStage();
    $request->setStage('post_id', $data['post_id']);
    $request->setStage('post_sms_interested_count', $data['post_sms_interested_count']);

    // update sms count
    $this->trigger('post-update', $request, $response);

    $actionData = [
        'action_event' => 'post-notify-sms-interest',
        'profile_id' => $data['profile_id']
    ];

    // check action event
    // if (!$this->package('global')->queue('action-check-event', $actionData)) {
    // if no queue manually do it
    $actionRequest  = Cradle\Http\Request::i();
    $actionResponse  = Cradle\Http\Response::i();
    $actionRequest->setStage('action_event', 'post-notify-sms-interest');
    $actionRequest->setStage('profile_id', $data['profile_id']);
    $this->trigger('action-check-event', $actionRequest, $actionResponse);
    // }

    // add story
    $storyRequest  = Cradle\Http\Request::i();
    $storyResponse  = Cradle\Http\Response::i();

    $story = cradle('global')->config('story', 'post-notify-sms-interest');
    $storyRequest->setStage('profile_id', $data['profile_id']);
    $storyRequest->setStage('add_story', [$story]);
    $this->trigger('profile-update', $storyRequest, $storyResponse);

});

/* Post Get Total, For Dashbpard Chart
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-search-chart', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');
    $postRedis = PostService::get('redis');
    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no results
        if (!$results) {
            $results = $postSql->getChart($data);
        }

        if ($results) {
            //cache it from database or index
            $postRedis->createSearch($data, $results);
        }
    }
    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Get Post Total For Credits
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-get-credit', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');

    $results = false;

    //if no results
    if (!$results) {
        //if no results
        if (!$results) {
            //get it from database
            $results = $postSql->getTotalPostCredit($data);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Post Like Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-like-detail', function ($request, $response) {
        //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$data['post_id'] || !$data['profile_email']) {
        return $response->setError(true, 'Invalid Data');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $postSql = PostService::get('sql');

    $results = $postSql->getAlreadyLiked($data['post_id'], $data['profile_email']);

    $response->setError(false)->setResults($results);
});

/**
 * Post export csv
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('post-export-csv', function ($request, $response) {
    // get data
    $data = $request->getStage();
    // get session
    $me = $data['session'];

    // date filter
    $dates = $request->getStage('date');
    $invalidDates = false;

    // date range error
    $dateError = cradle('global')->translate('Exporting of more than 1 month of records is not yet allowed for now. Please filter by date range');

    // must have date range
    if (!$dates
        || !isset($dates['start_date'])
        || !isset($dates['end_date'])
        || empty($dates['start_date'])
        || empty($dates['end_date'])) {
        $invalidDates = true;
    } else {
        $startdate = new DateTime($dates['start_date']);
        $enddate = new DateTime($dates['end_date']);

        // get date difference
        $diff = $enddate->diff($startdate)->format("%a");

        if ($diff > 31) {
            $invalidDates = true;
        }
    }

    if ($invalidDates) {
        // notify client side for errors
        return (new Queue)->setExchange('jobayan-admin-export')
            ->setData(array(
                'event' => 'export-error',
                'data' => [
                    'message' => $dateError,
                    'session_id' => $me['session_id'],
                    'type' => 'post'
                ]
            ))->publish();
    }

    // notify client side if export is in progress
    (new Queue)->setExchange('jobayan-admin-export')
        ->setData(array(
            'event' => 'export-progress',
            'data' => [
                'session_id' => $me['session_id'],
                'type' => 'post'
            ],
        ))->publish();

    // trigger profile search job
    $request->setGet('noindex', 1);
    $request->setGet('nocache', 1);
    cradle()->trigger('post-search', $request, $response);
    // set result data
    $data = array_merge($request->getStage(), $response->getResults());

    // set csv headers
    $header = [
        'post_id'             => 'Post Id',
        'post_name'           => 'Post Name',
        'post_email'          => 'Post Email',
        'post_phone'          => 'Post Phone',
        'post_position'       => 'Post Position',
        'post_location'       => 'Post Location',
        'post_experience'     => 'Post Experience',
        'post_resume'         => 'Post Resume',
        'post_detail'         => 'Post Detail',
        'post_expires'        => 'Post Expires',
        'post_banner'         => 'Post Banner',
        'post_salary_min'     => 'Post Salary Min',
        'post_salary_max'     => 'Post Salary Max',
        'post_link'           => 'Post Link',
        'post_like_count'     => 'Post Like Count',
        'post_download_count' => 'Post Download Count',
        'post_email_count'    => 'Post Email Count',
        'post_phone_count'    => 'Post Phone Count',
        'post_tags'           => 'Post Tags',
        'post_type'           => 'Post Type',
        'post_slug'           => 'Post Slug',
        'post_active'         => 'Post Active',
        'post_created'        => 'Post Created',
        'post_updated'        => 'Post Updated'
    ];

    // convert post_notify from array to
    foreach ($data['rows'] as $index => $row) {
        if (is_array($row['post_tags']) && !is_null($row['post_tags'])) {
            $data['rows'][$index]['post_tags'] = implode(', ', $row['post_tags']);
        }
    }

    // convert post_notify from array to
    foreach ($data['rows'] as $index => $row) {
        if (is_array($row['post_notify']) && !is_null($row['post_notify'])) {
            $data['rows'][$index]['post_notify'] = implode(', ', $row['post_notify']);
        }
    }

    // set csv filename, headers, and data
    $request->setStage('filename', 'Post-' . date('Y-m-d-His') . '.csv');
    $request->setStage('header', $header);
    $request->setStage('csv', $data['rows']);

    // upload csv to s3
    cradle()->trigger('csv-s3-export', $request, $response);
    // get results
    $results = $response->getResults();

    // notify client side if export is completed
    (new Queue)->setExchange('jobayan-admin-export')
        ->setData(array(
            'event' => 'export-complete',
            'data' => array_merge($results, [
                'session_id' => $me['session_id'],
                'type' => 'post'
            ])
        ))->publish();

    // set response
    return $response->setError(false)->setResults($results);
});
