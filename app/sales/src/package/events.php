<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render Blank Web Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-sales-blank', function ($request, $response) {
    //deal with flash messages
    if ($request->hasSession('flash')) {
        $flash = $request->getSession('flash');
        $response->setPage('flash', $flash);
        $request->removeSession('flash');
    }

    $content = cradle('/app/www')->template('_blank', [
        'page' => $response->getPage(),
        'results' => $response->getResults(),
        'content' => $response->getContent()
    ]);

    $response->setContent($content);
});

/**
 * Render Web Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-sales-page', function ($request, $response) use ($cradle) {
    //protocol
    $protocol = 'http';
    if ($request->getServer('HTTP_CF_VISITOR')) {
        $pos = strpos($request->getServer('HTTP_CF_VISITOR'), 'https');
        if ($pos !== false) {
            $protocol = 'https';
        }
    }

    //host
    $host = $protocol . '://' . $request->getServer('HTTP_HOST');
    if (!$response->getMeta('host')) {
        $response->addMeta('host', $host);
    }

    //url and base
    $base = $url = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if (strpos($url, '?') !== false) {
        $base = substr($url, 0, strpos($url, '?') + 1);
    }

    $response->addMeta('url', $url)->addMeta('base', $base);

    //path
    $path = $request->getPath('string');
    if (strpos($path, '?') !== false) {
        $path = substr($path, 0, strpos($path, '?'));
    }

    $response->addMeta(
        'redirect',
        urlencode($request->getServer('REQUEST_URI'))
    );

    $response->addMeta('path', $path);

    //menu
    if(strpos($path, '/dashboard') !== false) {
        $response->setPage('active_menu', 'dashboard');
    } else if (strpos($path, '/lead') !== false) {
        $response->setPage('active_menu', 'lead');
    } else if (strpos($path, '/profile') !== false) {
        $response->setPage('active_menu', 'profile');
    }

    //deal with flash messages
    if ($request->hasSession('flash')) {
        $flash = $request->getSession('flash');
        $response->setPage('flash', $flash);
        $request->removeSession('flash');
    }

    $content = cradle('/app/sales')->template(
        '_page',
        [
            'page' => $response->getPage(),
            'results' => $response->getResults(),
            'content' => $response->getContent(),
            'i18n' => $request->getSession('i18n')
        ],
        [
            'head',
            'menu'
        ]
    );

    $response->setContent($content);
});
