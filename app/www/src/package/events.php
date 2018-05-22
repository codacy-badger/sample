<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Http\Request as Request;
use Cradle\Http\Response as Response;

/**
 * Render Blank Web Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-www-blank', function ($request, $response) {
    //seo protocol
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

    //seo canonical
    if (!$response->getMeta('canonical')) {
        $response->addMeta('canonical', $path);
    }

    $response->addHeader('Link', '<' . $base . '>; rel="canonical"');

    // get the date modified of the file
    $wwwCss = filemtime(getcwd().'/styles/www.css');
    $wwwJs = filemtime(getcwd().'/scripts/www.js');

    if ($request->hasSession('logged_in_badge') &&
        $request->getSession('me')) {
        $request->setSession('badge', $request->hasSession('logged_in_badge'));
        $request->removeSession('logged_in_badge');
    }

    //deal with badges messages
    if ($request->hasSession('badge')) {
        $badge = $request->getSession('badge');
        $response->setPage('badge', $badge);
        $request->removeSession('badge');
    }

    //deal with flash messages
    if ($request->hasSession('flash')) {
        $flash = $request->getSession('flash');
        $response->setPage('flash', $flash);
        $request->removeSession('flash');
    }

    //deal with experience messages
    if ($request->hasSession('exp') &&
        $request->getSession('me')) {
        $exp = $request->getSession('exp');
        $response->setPage('exp', $exp);
        $request->removeSession('exp');
    }

    //deal with flash messages
    if ($request->hasSession('flash')) {
        $flash = $request->getSession('flash');
        $response->setPage('flash', $flash);
        $request->removeSession('flash');
    }
    
    //deal with rank messages
    if ($request->hasSession('rank')) {
        $rank = $request->getSession('rank');
        $response->setPage('rank', $rank);
        $request->removeSession('rank');
    }

    // set the version
    $response->setPage('version', [
        'css' => $wwwCss,
        'js' => $wwwJs
    ]);

    $content = cradle('/app/www')->template(
        '_blank',
        [
            'page' => $response->getPage(),
            'results' => $response->getResults(),
            'content' => $response->getContent()
        ],
        [
            'achievement',
            'free-credits'
        ]
    );

    //get config
    $settings = $this->package('global')->config('settings');

    //set gzip encoding only on production
    if (isset($settings['environment']) && $settings['environment'] == 'production') {
        //set content
        $response->addHeader('content-encoding', 'gzip');
        // deflate content
        $content = gzencode(trim($content), 9);
    }

    $response->setContent($content);
});

/**
 * Render Web Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('render-www-page', function ($request, $response) use ($cradle) {
    //seo protocol
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

    //seo canonical
    if (!$response->getMeta('canonical')) {
        $response->addMeta('canonical', $path);
    }

    $response->addHeader('Link', '<' . $base . '>; rel="canonical"');

    // utm meta
    if ($request->getStage('utm_source') &&
        $request->getStage('utm_medium') &&
        $request->getStage('utm_campaign')) {
        // get utm detail
        cradle()->trigger('utm-detail', $request, $response);
        $utm = $response->getResults();

        if ($utm && isset($utm['utm_source'])) {
            $meta = $response->getMeta();
            // update meta
            $meta['image'] = $utm['utm_image'];
            $meta['description'] = $utm['utm_detail'];

            $response->setPage('meta', $meta);
            $response->setPage('title', $utm['utm_title']);
        }
    }

    //Facebook Graph
    $facebook = $cradle->package('global')->service('facebook-graph');

    if ($facebook) {
        //FB App
        if (!$response->getMeta('fb_app_id')) {
            $response->addMeta('fb_app_id', $facebook['app_id']);
        }

        //FB Admin
        if (!$response->getMeta('fb_admin_id')) {
            $response->addMeta('fb_admin_id', $facebook['admin_id']);
        }
    }

    // get the date modified of the file
    $wwwCss = filemtime(getcwd().'/styles/www.css');
    $wwwJs = filemtime(getcwd().'/scripts/www.js');

    if ($request->hasSession('logged_in_badge') &&
        $request->getSession('me')) {
        $request->setSession('badge', $request->getSession('logged_in_badge'));
        $request->removeSession('logged_in_badge');
    }

    //deal with flash messages
    if ($request->hasSession('flash')) {
        $flash = $request->getSession('flash');
        $response->setPage('flash', $flash);
        $request->removeSession('flash');
    }

    //deal with badges messages
    if ($request->hasSession('badge')) {
        $badge = $request->getSession('badge');
        $response->setPage('badge', $badge);
        $request->removeSession('badge');
    }

    //deal with experience messages
    if ($request->hasSession('exp') &&
        $request->getSession('me')) {
        $exp = $request->getSession('exp');
        $response->setPage('exp', $exp);
        $request->removeSession('exp');
    }

    //deal with rank messages
    if ($request->hasSession('rank')) {
        $rank = $request->getSession('rank');
        $response->setPage('rank', $rank);
        $request->removeSession('rank');
    }

    // set the version
    $response->setPage('version', [
        'css' => $wwwCss,
        'js' => $wwwJs
    ]);

    //set feature_keywords
    $featured_keywords = [];
    if ($request->hasStage('featured_keywords')) {
        $featured_keywords = $request->getStage('featured_keywords');
    }

    // reset the credit_flag to false so that modal will only display once
    if ($request->hasSession('me') && ($request->getSession('me', 'credit_flag') === 1)) {
        $response->setPage('credits', $request->getSession('me', 'credit_flag'));
        $request->removeSession('me', 'credit_flag');
    }

    $response->setPage('featured_keywords', $featured_keywords);
    $request->removeStage('featured_keywords');

    if (is_array($request->getGet('location'))) {
        $request->setGet('location', implode(', ', $request->getGet('location')));
    }

    // remove modal flag session
    if ($request->hasSession('me', 'modal_flag')) {
        if (!$request->getSession('me', 'modal_flag_remove')) {
            $request->setSession('me', 'modal_flag_remove', '1');
        } else {
            $request->setSession('me', 'modal_flag_remove', '2');
        }
    }

    $content = cradle('/app/www')->template(
        '_page',
        [
            'get' => $request->getGet(),
            'page' => $response->getPage(),
            'results' => $response->getResults(),
            'content' => $response->getContent(),
            'i18n' => $request->getSession('i18n'),
        ],
        [
            'head',
            'foot',
            'achievement',
            'ats-restrict',
            'coming-soon',
            'match-likes',
            'partial_postersearch',
            'partial_seekersearch',
            'partial/modal_credits',
            'free-credits'
        ]
    );

    //get config
    $settings = $this->package('global')->config('settings');

    //set gzip encoding only on production
    if (isset($settings['environment']) && $settings['environment'] == 'production') {
        //set content
        $response->addHeader('content-encoding', 'gzip');
        // deflate content
        $content = gzencode(trim($content), 9);
    }

    $response->setContent($content);
});
