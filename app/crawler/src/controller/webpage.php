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
 * Render the Webpage Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/webpage/search', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    //Prepare body
    $workerRequest = new Request();
    $workerResponse = new Response();
    $this->trigger('worker-search', $workerRequest, $workerResponse->load());
    $workers = $workerResponse->getResults('rows');

    $request->setStage('order', 'webpage_id', 'DESC');

    $this->trigger('webpage-search', $request, $response);
    $data = $response->getResults();
    $data['workers'] = $workers;

    if ($request->hasStage()) {
        $data = array_merge($data, $request->getStage());
    }

    //Render body
    $class = 'page-crawler-webpage-search';
    $title = cradle('global')->translate('Web Pages');
    $body = cradle('/app/crawler')->template('webpage/search', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-crawler-page');

/**
 * Disallow a webpage from being crawled
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/webpage/disallow/:webpage_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    $request->setStage('webpage_type', 'disallowed');
    $this->trigger('webpage-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        $message = cradle('global')->translate('Page was Disallowed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    cradle('global')->redirect('/crawler/webpage/search');
});

/**
 * Allow a webpage to be crawled
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/webpage/allow/:webpage_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    //we need to get the pattern
    $this->trigger('webpage-detail', $request, $response);
    $webpage = $response->getResults();

    $request->setStage('website_root', $webpage['webpage_root']);
    $this->trigger('website-detail', $request, $response);
    $website = $response->getResults();

    //profile the webpage type
    $webpageType = 'other';
    if (preg_match($website['website_settings']['search_pattern'], $webpage['webpage_link'])) {
        $webpageType = 'search';
    } else if (preg_match($website['website_settings']['detail_pattern'], $webpage['webpage_link'])) {
        $webpageType = 'detail';
    }

    $request->setStage('webpage_type', $webpageType);
    $this->trigger('webpage-update', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        $message = cradle('global')->translate('Page was Allowed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    cradle('global')->redirect('/crawler/webpage/search');
});
