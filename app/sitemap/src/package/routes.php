<?php //-->

/**
 * Render the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/sitemap.xml', function ($request, $response) {
    $request->setStage('range', 1);
    $request->setStage('post_expires', 'disabled');

    //how many posts ?
    $this->trigger('post-search', $request, $response);
    $posts = $response->getResults('total');

    //blogs
    $request->setStage('blog_published', true);
    cradle()->trigger('blog-search', $request, $response);
    $blog = $response->getResults('total');

    //Prepare body
    $data = [
        'products' => $posts,
        'blog' => $blog,
    ];

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

    //Render body
    $body = cradle('/app/sitemap')->template('index', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the posts lists
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/urlset-posts-:page.xml', function ($request, $response) {
    $page = $request->getStage('page');
    $range = 5000;
    $start = ($page - 1) * $range;

    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('order', 'post_like_count', 'DESC');
    $request->setStage('order', 'post_updated', 'DESC');
    $request->setStage('post_expires', 'disabled');

    $request->setGet('noindex', true);
    $this->trigger('post-search', $request, $response);

    //Prepare body
    $data = $response->getResults();

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $data['frequency'] = 'monthly';

    //Render body
    $body = cradle('/app/sitemap')->template('urlset', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the blog static page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/urlset-articles-:page.xml', function ($request, $response) {
    $page = $request->getStage('page');
    $range = 5000;
    $start = ($page - 1) * $range;

    $request->setGet('noindex', true);
    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('blog_published', true);
    $request->setStage('filter', 'blog_type', 'post');
    $request->setStage('order', 'blog_id', 'DESC');
    //trigger job
    cradle()->trigger('blog-search', $request, $response);

    //Prepare body
    $data = $response->getResults();

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $data['frequency'] = 'monthly';

    //Render body
    $body = cradle('/app/sitemap')->template('blog', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the Job Locations List
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/urlset-job-locations-:page.xml', function ($request, $response) {
    $page = $request->getStage('page');
    $range = 5000;
    $start = ($page - 1) * $range;

    //get feature pages for positions
    $request->setGet('noindex', true);
    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('filter', 'feature_type', 'location');
    $request->setStage('columns', ['feature_name', 'feature_slug']);

    cradle()->trigger('feature-search', $request, $response);
    $data['features'] = $response->getResults();

    foreach ($data['features']['rows'] as $index => $feature) {
        $data['features']['rows'][$index]['feature_url'] = 'Job-Locations/' . $feature['feature_slug'];
    }

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $data['frequency'] = 'monthly';

    //Render body
    $body = cradle('/app/sitemap')->template('jobfeature', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the Job Industry List
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/urlset-job-industry-:page.xml', function ($request, $response) {
    $page = $request->getStage('page');
    $range = 5000;
    $start = ($page - 1) * $range;

    //get feature pages for positions
    $request->setGet('noindex', true);
    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('filter', 'feature_type', 'industry');
    $request->setStage('columns', ['feature_name', 'feature_slug']);

    cradle()->trigger('feature-search', $request, $response);
    $data['features'] = $response->getResults();

    foreach ($data['features']['rows'] as $index => $feature) {
        $data['features']['rows'][$index]['feature_url'] = 'Job-Industries/' . $feature['feature_slug'];
    }

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $data['frequency'] = 'monthly';

    //Render body
    $body = cradle('/app/sitemap')->template('jobfeature', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the Job Industry List
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/urlset-job-positions-:page.xml', function ($request, $response) {
    $page = $request->getStage('page');
    $range = 5000;
    $start = ($page - 1) * $range;

    //get feature pages for positions
    $request->setGet('noindex', true);
    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('filter', 'feature_type', 'position');
    $request->setStage('columns', ['feature_name', 'feature_slug']);

    cradle()->trigger('feature-search', $request, $response);
    $data['features'] = $response->getResults();

    foreach ($data['features']['rows'] as $index => $feature) {
        $data['features']['rows'][$index]['feature_url'] = 'Job-Positions/' . $feature['feature_slug'];
    }

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];
    $data['frequency'] = 'monthly';

    //Render body
    $body = cradle('/app/sitemap')->template('jobfeature', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/static.xml', function ($request, $response) {

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

    //Render body
    $body = cradle('/app/sitemap')->template('static', $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});

/**
 * Render the Home Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/feed/:site.xml', function ($request, $response) {
    // Allowed sites
    $allowedSites = array(
        'careerjet',
        'indeed',
        'jobtome',
        'jooble',
        'jora',
        'trovit',
    );

    if(!in_array($request->getStage('site'), $allowedSites)) {
        return $response->setError(true, 'Invalid request');
    }

    $page = $request->getStage('page');
    $range = 1000;
    $start = ($page - 1) * $range;

    $request->setStage('start', $start);
    $request->setStage('range', $range);
    $request->setStage('order', 'post_like_count', 'DESC');
    $request->setStage('order', 'post_updated', 'DESC');
    $request->setStage('filter', ['post_type' => 'poster']);
    $request->setStage('not_filter', 'profile_verified', 0);
    $request->setGet('noindex', true);
    $this->trigger('post-search', $request, $response);

    //Prepare body
    $data = $response->getResults();

    //protocol
    $protocol = 'https';
    if ($_SERVER['SERVER_PORT'] === 80) {
        $protocol = 'http';
    }

    $data['root'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

    //Render body
    $body = cradle('/app/sitemap')->template($request->getStage('site'), $data);

    //Set Content
    $response
        ->addHeader('Content-Type', 'text/xml')
        ->setContent($body);
});
