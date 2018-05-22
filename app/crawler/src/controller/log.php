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
$cradle->get('/crawler/log/search', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    //Prepare body
    $workerRequest = new Request();
    $workerResponse = new Response();
    $this->trigger('worker-search', $workerRequest, $workerResponse->load());
    $workers = $workerResponse->getResults('rows');

    $request->setStage('order', 'log_id', 'DESC');

    $this->trigger('log-search', $request, $response);
    $data = $response->getResults();
    $data['workers'] = $workers;

    if ($request->hasStage()) {
        $data = array_merge($data, $request->getStage());
    }

    //Render body
    $class = 'page-crawler-log-search';
    $title = cradle('global')->translate('Crawler Logs');
    $body = cradle('/app/crawler')->template('log/search', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-crawler-page');

/**
 * Removes a log
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/crawler/log/remove/:log_id', function ($request, $response) {
    //for logged in
    cradle('global')->requireLogin('admin');

    $this->trigger('log-remove', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'error');
    } else {
        $message = cradle('global')->translate('Log was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    cradle('global')->redirect('/crawler/log/search');
});
