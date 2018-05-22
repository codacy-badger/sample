<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Blog\Service as BlogService;
use Cradle\Module\Blog\Validator as BlogValidator;

use Cradle\Module\Utility\File;

/**
 * Blog Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = BlogValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['blog_keywords'])) {
        $data['blog_keywords'] = json_encode($data['blog_keywords']);
    }

    //if there is an image
    if (isset($data['blog_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_image'] = File::base64ToS3($data['blog_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_image'] = File::base64ToUpload($data['blog_image'], $upload);
    }

    if (isset($data['blog_facebook_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_facebook_image'] = File::base64ToS3($data['blog_facebook_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_facebook_image'] = File::base64ToUpload($data['blog_facebook_image'], $upload);
    }

    if (isset($data['blog_twitter_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_twitter_image'] = File::base64ToS3($data['blog_twitter_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_twitter_image'] = File::base64ToUpload($data['blog_twitter_image'], $upload);
    }

    if (isset($data['blog_tags'])) {
        // preprage blog tags
        $data['blog_tags'] = json_encode($data['blog_tags']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    //save blog to database
    $results = $blogSql->create($data);
    //link profile
    if(isset($data['profile_id'])) {
        $blogSql->linkProfile($results['blog_id'], $data['profile_id']);
    }

    //index blog
    // $blogElastic->create($results['blog_id']);

    //invalidate cache
    $blogRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Blog Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['blog_id'])) {
        $id = $data['blog_id'];
    } else {
        $id = $data['blog_slug'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $blogRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $blogElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $blogSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $blogRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    if (isset($results['blog_tags'])) {
        $results['blog_tags'] = json_decode($results['blog_tags'], true);
    }

    //if permission is provided
    $permission = $request->getStage('permission');
    if ($permission && $results['profile_id'] != $permission) {
        return $response->setError(true, 'Invalid Permissions');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Blog Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the blog detail
    $this->trigger('blog-detail', $request, $response);

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
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    //save to database
    $results = $blogSql->update([
        'blog_id' => $data['blog_id'],
        'blog_active' => 0
    ]);

    // $blogElastic->update($response->getResults('blog_id'));

    //invalidate cache
    $blogRedis->removeDetail($data['blog_id']);
    $blogRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Blog Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the blog detail
    $this->trigger('blog-detail', $request, $response);

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
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    //save to database
    $results = $blogSql->update([
        'blog_id' => $data['blog_id'],
        'blog_active' => 1
    ]);

    //create index
    // $blogElastic->create($data['blog_id']);

    //invalidate cache
    $blogRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Blog Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-search', function ($request, $response) {
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
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $blogRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $blogElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $blogSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $blogRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Blog Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the blog detail
    $this->trigger('blog-detail', $request, $response);

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
    $errors = BlogValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['blog_keywords'])) {
        $data['blog_keywords'] = json_encode($data['blog_keywords']);
    }

    //if there is an image
    if (isset($data['blog_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_image'] = File::base64ToS3($data['blog_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_image'] = File::base64ToUpload($data['blog_image'], $upload);
    }

    if (isset($data['blog_facebook_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_facebook_image'] = File::base64ToS3($data['blog_facebook_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_facebook_image'] = File::base64ToUpload($data['blog_facebook_image'], $upload);
    }

    if (isset($data['blog_twitter_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['blog_twitter_image'] = File::base64ToS3($data['blog_twitter_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['blog_twitter_image'] = File::base64ToUpload($data['blog_twitter_image'], $upload);
    }

    if (isset($data['blog_tags'])) {
        // preprage blog tags
        $data['blog_tags'] = json_encode($data['blog_tags']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    //save blog to database
    $results = $blogSql->update($data);

    //index blog
    // $blogElastic->update($response->getResults('blog_id'));

    //invalidate cache
    $blogRedis->removeDetail($response->getResults('blog_id'));
    $blogRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Blog Slug Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('blog-search-slug', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $slug = null;
    if (isset($data['blog_slug'])) {
        $slug = $data['blog_slug'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$slug) {
        return $response->setError(true, 'Invalid Link');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $blogSql = BlogService::get('sql');
    $blogRedis = BlogService::get('redis');
    // $blogElastic = BlogService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $blogRedis->getDetail($slug);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $blogElastic->getSlug($slug);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $blogSql->getSlug($slug);
        }

        if ($results) {
            //cache it from database or index
            $blogRedis->createDetail($slug, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Invalid Link');
    }

    $response->setError(false)->setResults($results);
});
