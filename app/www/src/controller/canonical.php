<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Job Seekers Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Seekers-Search', function ($request, $response) {
    $country = $request->getSession('country');
    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    // Sets the meta variables
    $meta['title'] = 'Find​ Job​ Seekers.​ Maximize​ talent​ matching​ for​ your​ next​ hire.';
    $meta['description'] = 'Job​ Seekers​ are​ waiting​ for​ you.​ Optimize​ talent​'
                         . ' matching​ by​ simply​ clicking interested.​ Jobayan​ will​ connect​ you​ to​'
                         . ' top​ job​ seekers​ in '. $country;
    $meta['keywords'] = strtolower(implode(
        ',',
        [ 'job seekers', 'talent matching', 'career', 'job listing', 'job hiring']
    ));

    $request->setStage('type', 'seeker');
    $response
        ->setPage('title', $meta['title'])
        ->setPage('search_action', '/Job-Seekers-Search')
        ->addMeta('title', $meta['title'])
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    cradle()->triggerRoute('get', '/post/search', $request, $response);
});

/**
 * Render the Keyword Version Product Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Seekers/:profile_slug', function ($request, $response) {
    $profileSlug = $request->getStage('profile_slug');

    //get profile id
    $profileId = substr($profileSlug, (strrpos($profileSlug, '-u')) + 2, strlen($profileSlug));

    $request->setStage('profile_id', $profileId);

    cradle()->trigger('profile-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $request->setStage('profile', $profileId);

    $request->setStage('profile_post_flag', true);

    // set request uri
    $request->setStage('redirect_uri', $request->getServer('REQUEST_URI'));

    $response
        ->setPage('title', cradle('global')->translate('%s is looking for Job Opportunities', $response->getResults('profile_name')))
        ->addMeta('title', cradle('global')->translate('%s is looking for Job Opportunities', $response->getResults('profile_name')))
        ->addMeta('robots', 'NOINDEX, FOLLOW');

    if (isset($company['profile_banner']) && $company['profile_banner']) {
        $response->addMeta('image', $company['profile_banner']);
    } else {
        $settings = $this->package('global')->config('settings');
        $host = $settings['host'];
        $image = $host . '/images/image-jobayan-preview.png';
        $response->addMeta('image', $image);
    }

    cradle()->triggerRoute('get', '/post/profile/'. $profileId, $request, $response);
});

/**
 * Render the Keyword Version Product Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Seeking-Job/:post_slug', function ($request, $response) {
    $slug = $request->getStage('post_slug');
    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/Job-Seekers-Search');
    }

    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    $post = $response->getResults();
    $response
        ->setPage('title', 'Seeking job opportunities for '.$post['post_position'].' in '.$post['post_location'].'')
        ->addMeta('title', 'Seeking job opportunities for '.$post['post_position'].' in '.$post['post_location'].'')
        ->addMeta('description', $post['post_name']. ' is a '. $post['post_position'].' and is Looking for Job in '.
                                 $post['post_location'].'. Connect with '.$post['post_name'].' by clicking interested.')
        ->addMeta('keywords', strtolower(implode(',', [
            $post['post_position'],
            $post['post_location'],
            'career',
            'job hiring',
            'job listing'])))
        ->addMeta('image', $image);

    cradle()->triggerRoute('get', '/'.$slug.'/post-detail', $request, $response);
});

/**
 * Render the Companies Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Search-Companies', function ($request, $response) {
    $country = $request->getSession('country');
    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    // Sets the meta variables
    $meta['title'] = 'Search​ job opportunities​ from​​ Top Companies​ in​ the​ Philippines '. $country;
    $meta['description'] = 'Connect​ with​ companies​ and​ find​ jobs.​ Get​ directly​'
                         . ' connected​ to​ decision​ makers​ from top​ companies​ in ' . $country;
    $meta['keywords'] = strtolower(implode(
        ',',
        ['job search', 'find job', 'career', 'job hiring', 'job listing']
    ));

    $response
        ->setPage('title', 'Find Job Opportunities from the Top Companies in the '. $country)
        ->setPage('search_action', '/Job-Search-Companies')
        ->addMeta('title', $meta['title'])
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    $request->setStage('type', 'poster');
    cradle()->triggerRoute('get', '/post/search', $request, $response);
});

/**
 * Render the Keyword Version Product Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Companies/:profile_slug', function ($request, $response) {
    $profileSlug = $request->getStage('profile_slug');

    //get profile id
    $profileId = substr($profileSlug, (strrpos($profileSlug, '-u')) + 2, strlen($profileSlug));
    $request->setStage('profile_id', $profileId);

    cradle()->trigger('profile-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $request->setStage('profile', $profileId);
    $request->setStage('profile_post_flag', true);

    // set request uri
    $request->setStage('redirect_uri', $request->getServer('REQUEST_URI'));
    $company = $response->getResults();

    $response
        ->setPage('title', 'Latest Job Openings from '. $company['profile_company'])
        ->addMeta('title', 'Latest Job Openings from '. $company['profile_company'])
        ->addMeta('description', $company['profile_company'].' is hiring! Search through jobs and get directly connected by clicking interested')
        ->addMeta('keywords', strtolower(implode(',', [
            $company['profile_company'],
            'job search',
            'find job',
            'job hiring',
            'job opening',
        ])));

    if (isset($company['profile_banner']) && $company['profile_banner']) {
        $response->addMeta('image', $company['profile_banner']);
    } else {
        $protocol = 'http';
        $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        $image = $host . '/images/image-jobayan-preview.png';
        $response->addMeta('image', $image);

        if ($request->getServer('SERVER_PORT') === 443) {
            $protocol = 'https';
        }
    }

    cradle()->triggerRoute('get', '/post/profile/'. $profileId, $request, $response);
});

/**
 * Render the Keyword Version Product Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Company-Hiring/:post_slug', function ($request, $response) {
    $slug = $request->getStage('post_slug');
    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/Job-Search-Companies');
    }

    $post = $response->getResults();
    $description = 'We are '.$post['post_name'].' from '.$post['post_location'].' and we are looking for a '.$post['post_position'];
    if (isset($post['post_experience']) && !empty($post['post_experience'])) {
        $description .= ' with  '.$post['post_experience'].' years of experience.';
    }

    $response
        ->setPage('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('description', $description)
        ->addMeta('keywords', strtolower(implode(',', [
            $post['post_name'] .' job opening',
            'Jobs in '.$post['post_location'],
            'find job for '.$post['post_position'],
            'job listing',
            'job hiring',
        ])));

    if (!empty($post['post_banner'])) {
        $response->addMeta('image', $post['post_banner']);
    } else if (!empty($post['profile_banner'])) {
        $response->addMeta('image', $post['profile_banner']);
    } else {
        $protocol = 'http';
        if ($request->getServer('SERVER_PORT') === 443) {
            $protocol = 'https';
        }

        $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        $image = $host . '/images/image-jobayan-preview.png';
        $response->addMeta('image', $image);
    }

    cradle()->triggerRoute('get', '/'.$slug.'/post-detail', $request, $response);
});

/**
 * Render the Company Interested
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Company-Hiring/:post_slug/*', function ($request, $response) {
    $slug = $request->getStage('post_slug');
    //trigger job
    cradle()->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/Companies');
    }

    $post = $response->getResults();
    $description = 'We are '.$post['post_name'].' from '.$post['post_location'].' and we are looking for a '.$post['post_position'];
    if (isset($post['post_experience']) && !empty($post['post_experience'])) {
        $description .= ' with  '.$post['post_experience'].' years of experience.';
    }

    $response
        ->setPage('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('description', $description)
        ->addMeta('keywords', strtolower(implode(',', [
            $post['post_name'] .' job opening',
            'Jobs in '.$post['post_location'],
            'find job for '.$post['post_position'],
            'job listing',
            'job hiring',
        ])));

    if (!empty($post['post_banner'])) {
        $response->addMeta('image', $post['post_banner']);
    } else if (!empty($post['profile_banner'])) {
        $response->addMeta('image', $post['profile_banner']);
    } else {
        $protocol = 'http';
        if ($request->getServer('SERVER_PORT') === 443) {
            $protocol = 'https';
        }

        $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        $image = $host . '/images/image-jobayan-preview.png';
        $response->addMeta('image', $image);
    }

    $route = '/'.$slug.'/post-detail/'. $request->getRoute('variables', 0);
    cradle()->triggerRoute('get', $route, $request, $response);
});

/**
 * Render the Company Interested
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Company-Hiring/:post_slug/*', function ($request, $response) {
    $slug = $request->getStage('post_slug');
    //trigger job
    cradle()->trigger('post-detail', $request, $response);
    if ($response->isError()) {
        cradle('global')->flash('Not Found', 'danger');
        cradle('global')->redirect('/Companies');
    }
    $post = $response->getResults();
    $description = 'We are '.$post['post_name'].' from '.$post['post_location'].' and we are looking for a '.$post['post_position'];
    if (isset($post['post_experience']) && !empty($post['post_experience'])) {
        $description .= ' with  '.$post['post_experience'].' years of experience.';
    }
    $response
        ->setPage('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('title', $post['post_name'].' from '.$post['post_location'].' is Looking for a '.$post['post_position'])
        ->addMeta('description', $description)
        ->addMeta('keywords', strtolower(implode(',', [
            $post['post_name'] .' job opening',
            'Jobs in '.$post['post_location'],
            'find job for '.$post['post_position'],
            'job listing',
            'job hiring',
        ])));
    if (!empty($post['post_banner'])) {
        $response->addMeta('image', $post['post_banner']);
    } else if (!empty($post['profile_banner'])) {
        $response->addMeta('image', $post['profile_banner']);
    } else {
        $protocol = 'http';
        if ($request->getServer('SERVER_PORT') === 443) {
            $protocol = 'https';
        }
        $host = $protocol . '://' . $request->getServer('HTTP_HOST');
        $image = $host . '/images/image-jobayan-preview.png';
        $response->addMeta('image', $image);
    }
    $route = '/'.$slug.'/post-detail/'. $request->getRoute('variables', 0);
    cradle()->triggerRoute('get', $route, $request, $response);
});

/**
 * Render the Keyword Version Product Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:profile_slug/Profile-Post', function ($request, $response) {
    $profileSlug = $request->getStage('profile_slug');

    //get profile id
    $profileId = substr($profileSlug, (strrpos($profileSlug, '-u')) + 2, strlen($profileSlug));

    $request->setStage('profile_id', $profileId);

    cradle()->trigger('profile-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $profile = $response->getResults();

    if (isset($profile['profile_company']) && !empty($profile['profile_company'])) {
        return cradle('global')->redirect('/Companies/'.$request->getStage('profile_slug'), 301);
    } else {
        return cradle('global')->redirect('/Job-Seekers/'.$request->getStage('profile_slug'), 301);
    }
});

/**
 * Render the Job Industries Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Industries', function ($request, $response) {
    //TEMP redirect
    return cradle('global')->redirect('/');

    $industries = $this->package('global')->config('industries');

    foreach ($industries as $key => $value) {
        $postTags['post_tags'][$key] = $value;
    }

    $request->setStage($postTags);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    $response
        ->setPage('title', 'Search by Popular Job Industries on Jobayan')
        ->addMeta('description', 'Find all job offers filtered by start up, technology, agriculture, BPO, construction, health care, hospitality, POEA, restaurants, retail')
        ->addMeta('keywords', strtolower(implode(',', [
            'job industries',
            'job search',
            'career',
            'job hiring',
            'job listings'
        ])))
        ->addMeta('image', $image);

    cradle()->triggerRoute('get', '/post/search', $request, $response);
});

/**
 * Render the Locations Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Locations', function ($request, $response) {
    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    // Sets the meta
    $meta['description'] = '​Find​ all​ job​ listings​ filtered​ by​ Manila,​ Cebu,​ CDO,​'
                         . ' Visayas,​ Luzon,​ Mindinao,​ Davao, Bicol,​ Illocos,​ Cordillera.';
    $meta['keywords'] = strtolower(implode(
        ',',
        [
            'job search',
            'career',
            'job hiring',
            'job listings'
        ]
    ));

    $title = 'Search by Popular Job Locations on Jobayan';
    $request->setStage('wall_title', 'All Popular job locations');
    $response
        ->setPage('title', $title)
        ->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);



    cradle()->triggerRoute('get', '/post/search', $request, $response);
});

/**
 * Render the Job Locations Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Locations', function ($request, $response) {
    //TEMP redirect
    return cradle('global')->redirect('/');
});


/**
 * Render the Positions Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Positions', function ($request, $response) {
    //TEMP redirect
    return cradle('global')->redirect('/');

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    $image = $host . '/images/image-jobayan-preview.png';

    // Sets the meta
    $meta['description'] = 'Find all job offers filtered by programmers,'
                          .' marketing, accounting, engineering, security, driving, domestic, office, sales';
    $meta['keywords'] = strtolower(implode(
        ',',
        [
            'job​ positions',
            'job​ search',
            'career',
            'job​ hiring',
            'job​ listings'
        ]
    ));

    $title = 'Search by Popular Job Positions on Jobayan';
    $request->setStage('wall_title', 'All Popular job positions');
    $response
        ->setPage('title', $title)
        ->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    cradle()->triggerRoute('get', '/post/search', $request, $response);
});


/**
 * Render the Job Industries Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Industries/:feature_slug', function ($request, $response) {
    // custom industries SEO
    cradle()->trigger('feature-detail-slug', $request, $response);
    $industryResult = $response->getResults();

    //404 redirection
    if (!$industryResult) {
        $body = cradle()->package('/app/www')->template('404');
        $class = 'page-404 page-error branding';
        $title = cradle('global')->translate('Jobayan - 404 Error Page');

        //Set Content
        $response
            ->setPage('title', $title)
            ->setPage('class', $class)
            ->setContent($body);

        $this->trigger('render-www-page', $request, $response);
        return true;
    }

    $request->removeStage('filter');

    $industry = $industryResult['feature_name'];

    $request->setStage($industryResult);
    $request->setStage('featured_industry', $industryResult['feature_name']);
    $request->setStage('featured_heading', 'All job opportunities in ' . $industryResult['feature_name']);
    $request->setStage('featured_title', $industryResult['feature_title']);
    $request->setStage('featured_detail', $industryResult['feature_detail']);
    $request->setStage('featured_link', $industryResult['feature_links']);
    $request->setStage('featured_color', $industryResult['feature_color']);
    $request->setStage('featured_image', $industryResult['feature_image']);

    //get the 6th to nth keyword
    $featureKeywords = array_slice($industryResult['feature_keywords'], 5);
    //get 0 - 5 if keywords does not exceed 6
    if (!$featureKeywords) {
        $featureKeywords = $industryResult['feature_keywords'];
    }
    $request->setStage('featured_keywords', $featureKeywords);

    // set range to zero
    $request->setStage('range', 0);

    // get the list of positions
    $this->trigger('position-search', $request, $response);
    $positions = $response->getResults('rows');
    // set the post tags to stage
    $request->setStage('post_tags', $featureKeywords);

    $featured['position'] = [];
    $pos = [];
    foreach ($positions as $p => $position) {
        $pos[] = $position['position_name'];
    }

    // bulk featured position
    $request->setStage('filter', 'positions', $pos);
    $this->trigger('post-featured-bulk', $request, $response);
    $featured['position'] = $response->getResults('rows');
    $featured['position_total'] = $response->getResults('total');

    // remove positions
    $request->removeStage('filter', 'positions');

    // remove filter
    $request->removeStage('filter', 'post_position');

    // get the list of locations
    $locations = array_keys($this->package('global')->config('location'));

    $featured['location'] = [];
    foreach ($locations as $i => $location) {
        // set stage the location
        $request->setStage('filter', 'post_location', $location);

        // trigger the post-featured job
        $this->trigger('post-featured', $request, $response);

        // check for total results
        if ($response->getResults('total')) {
            $featured['location'][$location] = $response->getResults('total');
            $featured['location_total'] = array_sum($featured['location']);
        }
    }

    // remove stage
    $request->removeStage('filter');

    //list of featured slugs and name
    $request->setStage('filter', 'feature_type', 'industry');

    //get all features where feature_type = industry
    cradle()->trigger('feature-search', $request, $response);
    $request->setStage('feature_industry', $response->getResults('rows'));

    $request->setStage('featured_sidebar', $featured);
    $request->removeStage('filter');

    //only first 5 keywords
    $metaKeywords = array_slice($industryResult['feature_keywords'], 0, 5);

    // Set Meta
    $response
        ->setPage('title', $industryResult['feature_meta_title'])
        ->addMeta('title', $industryResult['feature_meta_title'])
        ->addMeta('description', $industryResult['feature_meta_description'])
        ->addMeta('keywords', strtolower(implode(',', $metaKeywords)))
        ->addMeta('image', $industryResult['feature_image']);

    cradle()->triggerRoute('get', '/post/featured', $request, $response);
});


/**
 * Render the Job Industries Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Locations/:feature_slug', function ($request, $response) {
    //custom locations SEO
    cradle()->trigger('feature-detail-slug', $request, $response);
    $locationResult = $response->getResults();


    if (!isset($locationResult)
        || !isset($locationResult['feature_meta']['geo']['latitude'])
        || empty($locationResult['feature_meta']['geo']['latitude'])
    ) {
        $geoRequest  = Cradle\Http\Request::i();
        $geoResponse  = Cradle\Http\Response::i();

        $geoRequest->setStage('post_location', $locationResult['feature_title']);

        cradle()->trigger('google-geomap', $geoRequest, $geoResponse);

        $geo = $geoResponse->getResults();

        if ($geo) {
            $locationResult['feature_meta']['geo'] = [
                'latitude' => $geo['lat'],
                'longhitude' => $geo['lon']
            ];
        }
    }

    //404 redirection
    if (!$locationResult) {
        $body = cradle()->package('/app/www')->template('404');
        $class = 'page-404 page-error branding';
        $title = cradle('global')->translate('Jobayan - 404 Error Page');

        //Set Content
        $response
            ->setPage('title', $title)
            ->setPage('class', $class)
            ->setContent($body);

        $this->trigger('render-www-page', $request, $response);
        return true;
    }

    $request->setStage('geo_point', [
        'lat' => $locationResult['feature_meta']['geo']['latitude'],
        'lon' => $locationResult['feature_meta']['geo']['longhitude']
    ]);

    $location = $locationResult['feature_name'];

    // Set Stage
    $request->setStage('featured_location', $locationResult);
    $request->setStage('featured_heading', 'All job opportunities in '.$location.'');
    $request->setStage('featured_title', $locationResult['feature_title']);
    $request->setStage('featured_detail', $locationResult['feature_detail']);
    $request->setStage('featured_link', $locationResult['feature_links']);
    $request->setStage('featured_color', $locationResult['feature_color']);
    $request->setStage('featured_subcolor', $locationResult['feature_subcolor']);
    $request->setStage('featured_image', $locationResult['feature_image']);
    $request->setStage('featured_map', $locationResult['feature_map']);

    // set range to zero
    $request->setStage('range', 0);
    $request->removeStage('filter');

    // get the list of positions
    $this->trigger('position-search', $request, $response);

    // set the results for positions
    $positions = $response->getResults('rows');

    // set the post location to stage
    $request->setStage('filter', 'post_location', $location);

    $featured['position'] = [];
    $pos = [];
    foreach ($positions as $p => $position) {
        $pos[] = $position['position_name'];
    }

    // bulk featured position
    $request->setStage('filter', 'positions', $pos);
    $this->trigger('post-featured-bulk', $request, $response);
    $featured['position'] = $response->getResults('rows');
    $featured['position_total'] = $response->getResults('total');

    // remove positions
    $request->removeStage('filter', 'positions');

    // remove stage the post_position
    $request->removeStage('filter', 'post_position');

    // get the list of industries
    $industries = $this->package('global')->config('industries');

    $featured['industry'] = [];
    foreach ($industries as $i => $industry) {
        // set stage the industry
        $request->setStage('post_tags', $industry);

        // trigger the post-featured job
        $this->trigger('post-featured', $request, $response);

        // check for total results
        if ($response->getResults('total')) {
            // get the total results
            $featured['industry'][$industry] = $response->getResults('total');

            // count all the total results
            $featured['industry_total'] = array_sum($featured['industry']);
        }
    }

    // remove stage
    $request->removeStage('post_tags');
    $request->removeStage('filter');

    //list of featured slugs and name
    $request->setStage('filter', 'feature_type', 'location');

    //get all features where feature_type = location
    cradle()->trigger('feature-search', $request, $response);
    $request->setStage('feature_location', $response->getResults('rows'));

    $request->setStage('featured_sidebar', $featured);
    $request->setStage('location', $location);
    $request->removeStage('filter');

    //only first 5 keywords
    $metaKeywords = array_slice($locationResult['feature_keywords'], 0, 5);

    // Set Meta
    $response
        ->setPage('title', $locationResult['feature_meta_title'])
        ->addMeta('title', $locationResult['feature_meta_title'])
        ->addMeta('description', $locationResult['feature_meta_description'])
        ->addMeta('keywords', strtolower(implode(',', $metaKeywords)))
        ->addMeta('image', $locationResult['feature_image']);

    cradle()->triggerRoute('get', '/post/featured', $request, $response);
});

/**
 * Render the Job Positions Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Positions/:feature_slug', function ($request, $response) {
    // custom positions SEO
    cradle()->trigger('feature-detail-slug', $request, $response);
    $positionResult = $response->getResults();

    //404 redirection
    if (!$positionResult) {
        $body = cradle()->package('/app/www')->template('404');
        $class = 'page-404 page-error branding';
        $title = cradle('global')->translate('Jobayan - 404 Error Page');

        //Set Content
        $response
            ->setPage('title', $title)
            ->setPage('class', $class)
            ->setContent($body);

        $this->trigger('render-www-page', $request, $response);
        return true;
    }

    $position = $positionResult['feature_name'];

    $request->setStage($positionResult);
    $request->setStage('featured_position', $positionResult['feature_name']);
    $request->setStage('featured_heading', 'All job opportunities in ' . $positionResult['feature_title']);
    $request->setStage('featured_title', $positionResult['feature_title']);
    $request->setStage('featured_detail', $positionResult['feature_detail']);
    $request->setStage('featured_link', $positionResult['feature_links']);
    $request->setStage('featured_color', $positionResult['feature_color']);
    $request->setStage('featured_image', $positionResult['feature_image']);

    $request->removeStage('filter');

    // set range to zero
    $request->setStage('range', 0);

    $request->setStage('filter', 'position_name', $position);

    // get the list of positions
    $this->trigger('position-search', $request, $response);
    $positionParentId = $response->getResults('rows', 0, 'position_id');
    $request->removeStage('filter');
    $request->setStage('filter', 'position_parent', $positionParentId);
    $this->trigger('position-search', $request, $response);

    $positions = $response->getResults('rows');
    array_unshift($positions, ['position_name' => $position]);
    $request->removeStage('filter');

    $featured['position'] = [];
    $pos = [];
    foreach ($positions as $p => $position) {
        $pos[] = $position['position_name'];
    }

    // bulk featured position
    $request->setStage('filter', 'positions', $pos);
    $this->trigger('post-featured-bulk', $request, $response);
    $featured['position'] = $response->getResults('rows');
    $featured['position_total'] = $response->getResults('total');

    // remove positions
    $request->removeStage('filter', 'positions');
    // remove stage the filter
    $request->removeStage('filter', 'post_position');

    // set stage position
    $request->setStage('post_position', $positions);

    // get the list of locations
    $locations = array_keys($this->package('global')->config('location'));

    $featured['location'] = [];
    foreach ($locations as $p => $location) {
        // set stage the location
        $request->setStage('filter', 'post_location', $location);

        // trigger the post-featured job
        $this->trigger('post-featured', $request, $response);

        // check for total results
        if ($response->getResults('total')) {
            $featured['location'][$location] = $response->getResults('total');
            $featured['location_total'] = array_sum($featured['location']);
        }
    }

    // remove stage the filter
    $request->removeStage('filter', 'post_location');

    // get the list of industries
    $industries = $this->package('global')->config('industries');

    $featured['industry'] = [];
    foreach ($industries as $i => $industry) {
        // set stage the industry
        $request->setStage('post_tags', $industry);

        // trigger the post-featured job
        $this->trigger('post-featured', $request, $response);

        // check for total results
        if ($response->getResults('total')) {
            $featured['industry'][$industry] = $response->getResults('total');
            $featured['industry_total'] = array_sum($featured['industry']);
        }
    }

    // remove stage
    $request->removeStage('post_tags');
    $request->removeStage('filter');

    //list of featured slugs and name
    $request->setStage('filter', 'feature_type', 'position');

    //get all features where feature_type = position
    cradle()->trigger('feature-search', $request, $response);
    $request->setStage('feature_position', $response->getResults('rows'));

    $request->setStage('position', $positions);
    $request->setStage('featured_sidebar', $featured);
    $request->removeStage('filter');

    //only first 5 keywords
    $metaKeywords = array_slice($positionResult['feature_keywords'], 0, 5);

    // Set Meta
    $response
        ->setPage('title', $positionResult['feature_meta_title'])
        ->addMeta('title', $positionResult['feature_meta_title'])
        ->addMeta('description', $positionResult['feature_meta_description'])
        ->addMeta('keywords', strtolower(implode(',', $metaKeywords)))
        ->addMeta('image', $positionResult['feature_image']);

    cradle()->triggerRoute('get', '/post/featured', $request, $response);
});

/**
 * Render the Job Positions Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Job-Educations/:feature_slug', function ($request, $response) {
    // custom industries SEO
    cradle()->trigger('feature-detail-slug', $request, $response);
    $educationResult = $response->getResults();

    //404 redirection
    if (!$educationResult) {
        $body = cradle()->package('/app/www')->template('404');
        $class = 'page-404 page-error branding';
        $title = cradle('global')->translate('Jobayan - 404 Error Page');

        //Set Content
        $response
            ->setPage('title', $title)
            ->setPage('class', $class)
            ->setContent($body);

        $this->trigger('render-www-page', $request, $response);
        return true;
    }

    $request->removeStage('filter');

    $education = $educationResult['feature_name'];

    $request->setStage($educationResult);
    $request->setStage('featured_education', $educationResult['feature_name']);
    $request->setStage('featured_heading', 'All job opportunities in ' . $educationResult['feature_name']);
    $request->setStage('featured_title', $educationResult['feature_title']);
    $request->setStage('featured_detail', $educationResult['feature_detail']);
    $request->setStage('featured_link', $educationResult['feature_links']);
    $request->setStage('featured_color', $educationResult['feature_color']);
    $request->setStage('featured_image', $educationResult['feature_image']);

    //get the 6th to nth keyword
    $featureKeywords = array_slice($educationResult['feature_keywords'], 5);
    //get 0 - 5 if keywords does not exceed 6
    if (!$featureKeywords) {
        $featureKeywords = $educationResult['feature_keywords'];
    }
    $request->setStage('featured_keywords', $featureKeywords);

    // set range to zero
    $request->setStage('range', 0);

    // get the list of positions
    $this->trigger('position-search', $request, $response);
    $positions = $response->getResults('rows');
    // set the post tags to stage
    $request->setStage('post_tags', $featureKeywords);

    $featured['position'] = [];
    $pos = [];
    foreach ($positions as $p => $position) {
        $pos[] = $position['position_name'];
    }

    // bulk featured position
    $request->setStage('filter', 'positions', $pos);
    $this->trigger('post-featured-bulk', $request, $response);
    $featured['position'] = $response->getResults('rows');
    $featured['position_total'] = $response->getResults('total');

    // remove positions
    $request->removeStage('filter', 'positions');

    // remove filter
    $request->removeStage('filter', 'post_position');

    // get the list of locations
    $locations = array_keys($this->package('global')->config('location'));

    $featured['location'] = [];
    foreach ($locations as $i => $location) {
        // set stage the location
        $request->setStage('filter', 'post_location', $location);

        // trigger the post-featured job
        $this->trigger('post-featured', $request, $response);

        // check for total results
        if ($response->getResults('total')) {
            $featured['location'][$location] = $response->getResults('total');
            $featured['location_total'] = array_sum($featured['location']);
        }
    }

    // remove stage
    $request->removeStage('filter');

    //list of featured slugs and name
    $request->setStage('filter', 'feature_type', 'education');

    //get all features where feature_type = industry
    cradle()->trigger('feature-search', $request, $response);
    $request->setStage('feature_education', $response->getResults('rows'));

    $request->setStage('featured_sidebar', $featured);
    $request->removeStage('filter');

    //only first 5 keywords
    $metaKeywords = array_slice($educationResult['feature_keywords'], 0, 5);

    // Set Meta
    $response
        ->setPage('title', $educationResult['feature_meta_title'])
        ->addMeta('title', $educationResult['feature_meta_title'])
        ->addMeta('description', $educationResult['feature_meta_description'])
        ->addMeta('keywords', strtolower(implode(',', $metaKeywords)))
        ->addMeta('image', $educationResult['feature_image']);

    cradle()->triggerRoute('get', '/post/featured', $request, $response);
});

/**
 * Look for no responses so far
 * We need to make sure this route is last
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/:keyword', function ($request, $response) {
    //if there is already a response
    if ($response->hasJson() || $response->hasContent()) {
        return;
    }

    $keywords = $request->getStage('keyword');

    cradle()->triggerRoute('get', '/' . $keywords . '/q', $request, $response);
});
