<?php //-->

/**
 * Render the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/', function ($request, $response) {
    //Prepare body
    $data = [];

    //get totals
    cradle()->trigger('post-totals', $request, $response);
    $data['totals'] = $response->getResults();

    //get feature pages
    //iterate through each types
    $features = [];
    $feature_types = ['position', 'location', 'industry'];
    foreach ($feature_types as $type) {
        $filter['feature_type'] = $type;
        $request->setStage('filter', $filter);
        $request->setStage('columns', ['feature_name', 'feature_slug']);

        cradle()->trigger('feature-search', $request, $response);
        $features[$type] = $response->getResults();
    }
    $data['features'] = $features;

    $request->removeStage('columns');

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';

    //get popular seekers
    $request->setStage('range', 6);
    $request->setStage('filter', ['post_type' => 'seeker']);
    $request->setStage('order', ['post_like_count' => 'DESC']);
    cradle()->trigger('post-search', $request, $response);

    $data['popular_seekers'] = $response->getResults('rows');

    //get featured jobs
    // temporarily commenting this out for further testing
    // on next deployment
    cradle()->trigger('post-company-featured-jobs', $request, $response);
    // commenting for now since the above block needs to be monitored first
    // $request->setStage('range', 4);
    // $notFilter = array('post_banner' => null);
    // $request->setStage('not_filter', $notFilter);
    // $request->setStage('filter', ['post_flag' => '1']);
    // cradle()->trigger('post-search', $request, $response);
    $data['featured_jobs'] = $response->getResults('rows');

    // remove stage
    $request->removeStage();

    // set stage for blog
    $request->setStage('filter', 'blog_type', 'post');
    $request->setStage('range', 2);
    $request->setStage('order', ['blog_created' => 'DESC']);
    cradle()->trigger('blog-search', $request, $response);

    // set the blog
    $data['blogs'] = $response->getResults('rows');
    $data['blog_total'] = $response->getResults('total');

    $data['test'] = array(
        'mod_title' => 'test test'
    );

    $request->removeStage();

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
    foreach ($data['popular_seekers'] as $r => $row) {
        // compare post tags and school
        $schoolCompare = array_uintersect($data['school'], $row['post_tags'], 'strcasecmp');

        // if schoolCompare has value set post schoo flag
        if (!empty($schoolCompare)) {
            $data['popular_seekers'][$r]['post_school_flag'] = true;
        }
    }

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

    //Render body
    $class = 'page-home';
    $body = cradle('/app/www')->template('index', $data, [
        'post_seeker',
        'post_poster',
        'post_actions',
        'post_poster-tips',
        'post_seeker-tips',
        'post/modal_popup',
        'post/modal_question',
        'post/modal_completeness',
        'post/modal_resume',
        'post/modal_industry',
        'post/modal_location',
        'post/modal_school',
        'partial_howitworks',
        '_modal-profile-completeness',
        'partial_createpost',
        'partial_resumedownload'
    ]);

    // Set meta variables
    $meta['title'] = 'Jobayan | Fastest Growing Job Community, the Millennial Way to Find Jobs';
    $meta['description'] = 'Search for thousands of jobs in '. $request->getSession('country')
        .' with Jobayan, the fastest growing job community! Finds jobs. Hire Talent.';
    $meta['keywords'] = array(
        'jobs in ' . $request->getSession('country'),
        'kalibrr',
        'jobstreet',
        'work abroad',
        'best jobs');

    $meta['keywords'] = strtolower(implode(', ', $meta['keywords']));

    // Set Meta
    $response->addMeta('title', $meta['title'])
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'home');

    //Set Content
    $response
        ->setPage('title', $meta['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the About Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/About-Jobayan', function ($request, $response) {

    //get totals
    cradle()->trigger('post-totals', $request, $response);
    $data['totals'] = $response->getResults();

    //Render body
    $class = 'page-static page-about branding';
    $title = cradle('global')->translate('Everything About Jobayan - The Millennial Way to Finding Jobs');
    $body = cradle('/app/www')->template('about', $data, [
        'partial_howitworks',
        'partial_createpost',
        '_modal-profile-completeness'
    ]);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/banner/we_are_jobayan_banner.jpg';

    //Set Meta
    $meta['description'] = '​Everything​ you​ need​ to​ know​ about​ Jobayan,​ the​ easiest​ way​ to​ find​ jobs​ and​ hire talent';
    $meta['keywords'] = strtolower(implode(
        ',',
        [
            'about jobayan',
            'how jobayan',
            'jobayan team',
            'getting hired',
            'finding candidates'
        ]
    ));

    $response->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Affordable Pricing Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Affordable-Pricing', function ($request, $response) {
    $data = [];

    //Render body
    $class = 'page-static page-affordable branding';
    $title = cradle('global')->translate('Affordable Pricing, Pay As You Go, No Subscriptions, No Surprise Costs');
    $body = cradle('/app/www')->template('affordable-pricing', $data, ['_modal-profile-completeness']);

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    //Set Meta
    $response->addMeta('title', $title)
        ->addMeta('description', 'Credits are designed for start up, enterprises and everything in between. Services range from 10-500 pesos. Pay as you go, no subscriptions')
        ->addMeta('keywords', strtolower(implode(',', [
            'job ads',
            'cheap job site',
            'job promotions',
            'startup jobs',
            'job post package'
        ])))
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Terms Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Terms-And-Conditions', function ($request, $response) {
    $data = [];

    //Render body
    $class = 'page-static page-terms branding';
    $title = cradle('global')->translate('Terms and Conditions of Using Jobayan');
    $body = cradle('/app/www')->template(
        'terms',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    //Set Meta
    $meta['description'] = 'By using this site, you agree to our terms and'
        . ' conditions. Please check this page periodically for changes.';
    $response->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', strtolower(implode(',', [
            'jobayan terms',
            'jobayan usage',
            'jobayan tnc',
            'jobayan legal',
            'job listing'
        ])))
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Privacy Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Privacy-Policy', function ($request, $response) {
    $data = [];

    //Render body
    $class = 'page-static page-privacy branding';
    $title = cradle('global')->translate('Commitment to Privacy While Using Jobayan');
    $body = cradle('/app/www')->template(
        'privacy',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    //Set Meta
    $meta['description'] = 'Our policy is an extension of our commitment to'
        . ' combine quality with integrity and describes how we collect,'
        . ' use and protect data provided';
    $meta['keywords'] = strtolower(implode(
        ',',
        [
            'jobayan privacy',
            'jobayan data',
            'data privacy',
            'jobayan legal',
            'job listing'
        ]
    ));

    $response->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', $meta['keywords'])
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the FAQ Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Frequently-Asked-Questions', function ($request, $response) {
    //Render body
    $class = 'page-static page-faq branding';
    $title = cradle('global')->translate('Jobayan FAQ | Help center for your questions');

    $data = [];
    $body = cradle('/app/www')->template(
        'faq',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    // Set Title

    //Set Meta
    $response->addMeta('title', $title)
        ->addMeta('description', 'Answers to the most common questions on Jobayan, the easiest way to find jobs and hire talent')
        ->addMeta('keywords', strtolower(implode(',', [
            'jobayan faq',
            'jobayan frequent questions',
            'jobayan common questions',
            'jobayan answers',
            'job listing'
        ])))
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Contact Us Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Contact-Us', function ($request, $response) {
    //Prepare body
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //get totals
    cradle()->trigger('post-totals', $request, $response);
    $data['totals'] = $response->getResults();

     //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    //Render body
    $class = 'page-static page-contact branding';
    $title = cradle('global')->translate('How to Contact Jobayan');
    $body = cradle('/app/www')->template('contact', $data, [
        'partial_createpost',
        '_modal-profile-completeness'
    ]);

    //Set Meta
    $meta['description'] = 'How to contact Jobayan and our additional commitment'
        . ' as a customer centric platform and open recruitment community';

    $response->addMeta('title', $title)
        ->addMeta('description', $meta['description'])
        ->addMeta('keywords', strtolower(implode(',', [
            'contact jobayan',
            'jobayan email',
            'jobayan location',
            'jobayan address',
            'job listing'
        ])))
        ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

// /**
//  * Render the Contact Us Page
//  *
//  * @param Request $request
//  * @param Response $response
//  */
// $cradle->get('/template/*', function ($request, $response) {
//     $class = 'template-one';
//     $data = [];
//     if ($request->getStage()) {
//         $data = $request->getStage();
//     }
//
//     switch ($request->getRoute('variables', '0')) {
//         case 'postcard':
//             $template = 'template-one/_postcard';
//             break;
//
//         case 'postdetail':
//             $template = 'template-one/_post_detail';
//             break;
//
//         case 'components':
//             $template = 'template-one/components';
//             break;
//
//         case 'search':
//             $class= 'page-post-search';
//             $template = 'template-one/search';
//             break;
//
//         case 'header':
//             $template = 'template-one/header';
//             break;
//
//         case 'footer':
//             $template = 'template-one/footer';
//             break;
//
//         case 'howitworks':
//             $template = 'template-one/_howitworks';
//             break;
//
//         case 'home':
//             $template = 'template-one/home';
//             break;
//
//         default:
//             $template = '404';
//             break;
//     }
//
//     //Render body
//     $title = cradle('global')->translate('Template One');
//     $body = cradle('/app/www')->template($template, $data, [
//         'template-one_postcard',
//         'template-one_seekercard',
//         'post_sorts'
//     ]);
//
//     //Set Content
//     $response
//         ->setPage('title', $title)
//         ->setPage('class', $class)
//         ->setContent($body);
// });

$cradle->get('/Coming-Soon', function ($request, $response) {
    //Render body
    $class = 'page-static page-coming-soon branding';
    $title = cradle('global')->translate('Jobayan - Page Coming Soon!');
    $body = cradle('/app/www')->template('coming-soon');

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);
    //Render blank page
}, 'render-www-page');

/**
  * Render the Rank and Achievement page
  *
  * @param Request $request
  * @param Response $response
  */
$cradle->get('/Ranks-And-Achievements', function ($request, $response) {
    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    // Set Image
    $image = $host . '/images/image-jobayan-preview.png';

    $data = [];

    //Render body
    $class = 'page-static page-ranks-and-achievements branding';
    $title = cradle('global')->translate('Jobayan - Ranks and Achievements');
    $body = cradle('/app/www')->template(
        'ranks-achievements',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    //Set Meta
    $meta['description'] = 'Entice engagement with job seekers to your job
        posts with your company’s rank and achievements in Jobayan.';

    $response->addMeta('title', $title)
       ->addMeta('description', $meta['description'])
       ->addMeta('keywords', strtolower(implode(',', [
           'jobayan points',
           'job engagement',
           'job post',
           'jobayan rewards',
           'entice job seekers'
       ])))
       ->addMeta('image', $image);

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);
    //Render blank page
}, 'render-www-page');

/**
 * Process the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/', function ($request, $response) {
    //Prepare body
    $data = ['item' => $request->getStage()];

    // Checks empty type
    if (!trim($request->getStage('post_type'))) {
        $response->setError(true, 'Could not process this request. Try again.');
        return cradle()->triggerRoute('get', '/', $request, $response);
    }

    // Variable declaration
    $errors = [];

    // Checks for empty name
    if (!trim($request->getStage('post_name'))) {
        $errors['post_name'] = 'Cannot be empty.';
    }

    // Checks for empty position
    if (!trim($request->getStage('post_position'))) {
        $errors['post_position'] = 'Cannot be empty.';
    }

    // Checks for empty location
    if (!trim($request->getStage('post_location'))) {
        $errors['post_location'] = 'Cannot be empty.';
    }

    // Checks for post type poster
    if ($request->getStage('post_type') === 'poster') {
        // Checks for invalid experience
        if ($request->hasStage('post_experience')) {
            if ($request->getStage('post_experience') < 0) {
                $errors['post_experience'] = 'Experience should not lower than zero.';
            }

            if ($request->hasStage('post_experience') && !$request->getStage('post_experience')) {
                $data['item']['post_experience'] = '0';
            }
        }
    }

    // Checks for errors
    if (!empty($errors)) {
        // Default error message
        $err = 'All fields are required.';

        $response->setError(true, $err);
        $response->set('json', 'validation', $errors);

        return cradle()->triggerRoute('get', '/', $request, $response);
    }

    if ($data['item']['post_type'] === 'poster') {
        $request->setSession('post/create/poster/stash', 'post_name', $data['item']['post_name']);
        $request->setSession('post/create/poster/stash', 'post_position', $data['item']['post_position']);
        $request->setSession('post/create/poster/stash', 'post_location', $data['item']['post_location']);
        $request->setSession('post/create/poster/stash', 'post_type', 'poster');
        $request->setSession('post/create/poster/stash', 'post_experience', $data['item']['post_experience']);

        if (isset($data['item']['featured_keywords'])) {
            $request->setSession('post/create/poster/stash', 'post_tags', $data['item']['featured_keywords']);
        } else {
            $request->removeSession('post/create/poster/stash', 'post_tags');
        }

        return cradle('global')->redirect('/post/create/poster');
    }

    $request->setSession('post/create/seeker/stash', 'post_name', $data['item']['post_name']);
    $request->setSession('post/create/seeker/stash', 'post_position', $data['item']['post_position']);
    $request->setSession('post/create/seeker/stash', 'post_location', $data['item']['post_location']);

    cradle('global')->redirect('/post/create/seeker');
});

/**
 * Process Contact Us POST
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/Contact-Us', function ($request, $response) {
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);
    if ($response->isError()) {
        if ($request->getStage('redirect')) {
            return cradle()->triggerRoute('get', $request->getStage('redirect'), $request, $response);
        } else {
            return cradle()->triggerRoute('get', '/Contact-Us', $request, $response);
        }
    }

    cradle()->trigger('contact-email', $request, $response);
    if ($response->isError()) {
        return cradle()->triggerRoute('get', '/Contact-Us', $request, $response);
    }

    //add a flash
    cradle('global')->flash('Inquiry Sent!', 'success');

    //redirect
    $redirect = '/Contact-Us';
    if ($request->hasGet('redirect')) {
        $redirect = $request->getGet('redirect');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process Press Kit Page
 *
 * @param Request $request
 * @param Response $response
 *
 */

$cradle->get('/press', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];
    //Render body
    $class = 'page-static page-press branding';

    $body = cradle('/app/www')->template('press', $data, ['_modal-profile-completeness']);

    $title = cradle('global')->translate('Press Kit');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Server Location
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/server/status', function ($request, $response) {
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $postRedis = Cradle\Module\Post\Service::get('redis');
    $postElastic = Cradle\Module\Post\Service::get('elastic');

    $results = false;
    $redis = true;
    $elastic  = true;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $postRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        $redis = false;
    }

    $results = false;

    //if no flag
    if (!$request->hasGet('noindex')) {
        //get it from index
        $results = $postElastic->search(['range' => 1]);
    }

    //if no results
    if (!$results) {
        $elastic = false;
    }

    $response->setResults([
        'ip' => cradle('global')->config('settings', 'server_ip'),
        'elastic' => $elastic,
        'redis' => $redis
    ]);
});

/**
 * Jobayan Maintenance Page
 */
$cradle->get('/maintenance', function ($request, $response) {
    //get config
    $settings = cradle()->package('global')->config('settings');
    //set maintenance page
    if (isset($settings['maintenance']) && $settings['maintenance'] !== 1) {
        return cradle('global')->redirect('/');
    }

    //Render body
    $class = 'page-maintenance branding';
    $title = cradle('global')->translate('Jobayan - Maintenance!');
    $body = cradle('/app/www')->template('maintenance');

    //for facebook pixel tracker
    $response->setPage('page', 'static');

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);
    //Render blank page
}, 'render-www-page');
