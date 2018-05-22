<?php //-->

/**
 * Render the Blog Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Articles', function ($request, $response) {

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //trigger job
    $request->setStage('filter', 'blog_type', 'post');
    $request->setStage('order', 'blog_published', 'DESC');
    cradle()->trigger('blog-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    //get blogs
    $data['blogs'] = $response->getResults('rows');

    $query = '';
    $title = '';
    if ($request->getStage('q')) {
        $title = $request->getStage('q');
    }

    // check for blog_search
    if ($request->hasStage('blog_search')) {
        $title = $request->getStage('blog_search');
    }
    $data['blog_search'] = $title;

    $request->removeStage();
    $request->setStage('filter', 'blog_type', 'keyword');
    cradle()->trigger('blog-search', $request, $response);

    //get keywords
    $data['keywords'] = $response->getResults('rows');

    // check for template and class
    $template = '/blog/search';
    $class = 'page-blog-search branding';
    if (!$data['blogs']) {
        $template = '/blog/search';
        $class = 'page-blog-search branding';
    }

    //determine the images
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $host = $protocol . '://' . $request->getServer('HTTP_HOST');

    $image = $host . '/images/image-jobayan-preview.png';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Blog')
        ->setPage('class', $class)
        ->addMeta('title', 'Jobayan - Blog')
        ->addMeta('image', $image)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Blog Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Articles/Categories', function ($request, $response) {
    //trigger job

    $request->setStage('filter', 'blog_type', 'keyword');
    cradle()->trigger('blog-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    //get result
    $data['keywords'] = $response->getResults('rows');

    //Render body
    $class = 'page-blog-category-search branding';
    $body = cradle('/app/www')->template('/blog/category/search', $data);

    $title = 'Blog Categories';

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Blog Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/Article/:blog_slug', function ($request, $response) {
    //trigger job
    cradle()->trigger('blog-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    //get result
    $data = $response->getResults();

    //category page
    if ($data['blog_type'] === 'keyword') {
        //get result
        $data = $response->getResults();

        $request->setStage('blog_keywords', $data['blog_keywords'][0]);
        $request->setStage('filter', 'blog_type', 'post');
        $request->setStage('order', 'blog_view_count', 'DESC');

        //trigger job
        cradle()->trigger('blog-search', $request, $response);
        $data['category'] = $response->getResults('rows');

        $title = $data['blog_title'];
        $description = $data['blog_description'];
        $keywords = strtolower(implode(', ', $data['blog_keywords']));
        $image = $data['blog_image'];
        $data['blog_created'] = date('F d, Y', strtotime($data['blog_created']));

        //Render body
        $class = 'page-blog-category-detail branding';
        $body = cradle('/app/www')->template('blog/category/detail', $data);
    //article page
    } else {
        //increase view count
        $data['blog_view_count']++;
        $request->setStage('blog_id', $data['blog_id']);
        $request->setStage('blog_view_count', $data['blog_view_count']);
        $request->setStage('blog_type', $data['blog_type']);

        // process request
        cradle()->trigger('blog-update', $request, $response);

        // getting popular articles
        $request->removeStage();
        $request->setStage('range', 3);
        $request->setStage('order', 'rand()', 'DESC');
        $request->setStage('filter', 'blog_type', 'post');

        //trigger job
        cradle()->trigger('blog-search', $request, $response);

        $title = $data['blog_title'];
        $description = $data['blog_description'];
        $keywords = strtolower(implode(', ', $data['blog_keywords']));
        $image = $data['blog_image'];
        $data['blog_published'] = date('F d, Y', strtotime($data['blog_published']));
        $data['popular'] = $response->getResults('rows');

        //Render body
        $class = 'page-blog-detail branding';
        $body = cradle('/app/www')->template('blog/detail', $data);
    }

    $keywords = array_slice($data['blog_keywords'], 0, 5);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->addMeta('title', $title)
        ->addMeta('description', $data['blog_description'])
        ->addMeta('keywords', implode(',', $keywords))
        ->addMeta('image', $data['blog_image'])
        ->setContent($body);

    //Render blank page
}, 'render-www-page');
