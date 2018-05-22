<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Render the Website Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/website/search', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    //Prepare body
    $workerRequest = new Request();
    $workerResponse = new Response();
    $this->trigger('worker-search', $workerRequest, $workerResponse->load());
    $workers = $workerResponse->getResults('rows');

    $this->trigger('website-search', $request, $response);
    $data = $response->getResults();
    $data['workers'] = $workers;
    $data['range'] = 50;

    if ($request->hasStage()) {
        $data = array_merge($data, $request->getStage());
    }

    //Render body
    $class = 'page-crawler-website-search';
    $title = cradle('global')->translate('Web Sites');
    $body = cradle('/app/crawler')->template('website/search', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-crawler-page');

/**
* Render the Website Search Page
*
* @param Request $request
* @param Response $response
*/
$cradle->get('/crawler/website/create', function ($request, $response) {
   //for logged in
    cradle('global')->requireLogin('admin');

   //Prepare body
    $data = ['item' => $request->getPost()];

    if (!isset($data['item']['website_crop'])) {
        $data['item']['website_crop'] = '0,0,0,0';
    }

    if ($request->getStage('action') === 'test' && $response->hasResults()) {
        $data['test_results'] = json_encode($response->getResults(), JSON_PRETTY_PRINT);
        $data['test_command'] = $response->get('phantom');
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

   //Render body
    $class = 'page-crawler-website-create';
    $title = cradle('global')->translate('New Website');
    $data['title'] = $title;
    $body = cradle('/app/crawler')->template('website/form', $data);

   //Set Content
    $response
       ->setPage('title', $title)
       ->setPage('class', $class)
       ->setContent($body);

   //Render blank page
}, 'render-crawler-page');

/**
* Render the Website Search Page
*
* @param Request $request
* @param Response $response
*/
$cradle->get('/crawler/website/update/:website_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    //Prepare body
    $data = ['item' => $request->getPost()];

    if ($request->getStage('action') === 'test' && $response->hasResults()) {
        $data['test_results'] = json_encode($response->getResults(), JSON_PRETTY_PRINT);
        $data['test_command'] = $response->get('phantom');
    }



    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    if (empty($data['item'])) {
        $this->trigger('website-detail', $request, $response);
        $data['item'] = $response->getResults();
    }

    //Render body
    $class = 'page-crawler-website-update';
    $title = cradle('global')->translate('Update Website');
    $data['title'] = $title;
    $body = cradle('/app/crawler')->template('website/form', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-crawler-page');

/**
 * Process the Website Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/website/remove/:website_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    cradle()->trigger('website-remove', $request, $response);

    //deal with results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Site was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/crawler/website/search');
});

/**
 * Process the Website Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/website/restore/:website_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    cradle()->trigger('website-restore', $request, $response);

    //deal with results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Site was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/crawler/website/search');
});

/**
 * Queue the first link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/website/start/:website_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    cradle()->trigger('website-detail', $request, $response);

    $link = $response->getResults('website_start');

    $response->setResults('webpage_link', $link);
    $response->setResults('webpage_type', 'search');

    cradle()->trigger('crawl-seed', $request, $response);

    //deal with results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Site is queued to crawl');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/crawler/website/search');
});

/**
 * Process the Website Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/crawler/website/create', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    if ($request->getStage('action') !== 'save') {
        $this->trigger('website-test', $request, $response);
        return $this->triggerRoute('get', '/crawler/website/create', $request, $response);
    }

    $this->trigger('website-create', $request, $response);

    if ($response->isError()) {
        return $this->triggerRoute('get', '/crawler/website/create', $request, $response);
    }

    //it was good
    //add a flash
    $_SESSION['flash'] = [
        'message' => $this->package('global')->translate('Website was Created'),
        'type' => 'success'
    ];

    //redirect
    cradle('global')->redirect('/crawler/website/search');
});

/**
 * Process the Website Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/crawler/website/update/:website_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    if ($request->getStage('action') !== 'save') {
        $this->trigger('website-test', $request, $response);
        $websiteId = $request->getStage('website_id');
        return $this->triggerRoute('get', '/crawler/website/update/' . $websiteId, $request, $response);
    }

    $this->trigger('website-update', $request, $response);

    if ($response->isError()) {
        $websiteId = $request->getStage('website_id');
        return $this->triggerRoute('get', '/crawler/website/update/' . $websiteId, $request, $response);
    }

    //it was good
    //add a flash
    cradle('global')->flash($this->package('global')->translate('Website was Updated'), 'success');

    //redirect
    cradle('global')->redirect('/crawler/website/search');
});

/**
 * Removes a worker
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/worker/remove/:worker_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    $this->trigger('worker-remove', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        $message = cradle('global')->translate('Worker was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/crawler/website/search';
    if ($request->hasStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
