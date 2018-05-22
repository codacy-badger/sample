<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Crawler\Log\Service as LogService;
use Cradle\Module\Crawler\Webpage\Service as WebpageService;
use Cradle\Module\Crawler\Website\Service as WebsiteService;
use Cradle\Module\Crawler\Worker\Service as WorkerService;
use Cradle\CommandLine\Index as CommandLine;
use Cradle\Framework\Queue\Service\RabbitMQService as RabbitMQService;

use Cradle\Curl\CurlHandler as Curl;

/**
 * Main crawler job
 *
 * Priorities:
 * 0-9 - Not important
 * 10-19 - Search Pages Requeued
 * 20-29 - Search Pages
 * 30-39 - Detail Pages Requeued
 * 40-49 - Detail Pages
 * 50-59 - Seed Pages
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl', function ($request, $response) {
    static $logging = false;

    if ($request->hasStage('v') && !$logging) {
        $logging = true;
        $this->addLogger(function ($message) {
            echo '[cradle] ' . $message . PHP_EOL;
        });
    }

    if (!defined('WORKER_ID')) {
        if ($request->hasStage('__worker_id')) {
            define('WORKER_ID', $request->getStage('__worker_id'));
        } else {
            define('WORKER_ID', md5(uniqid()));
        }
    }

    $this->log('Crawling - ' . $request->getStage('webpage_link'));

    $model = [
        'worker' => WorkerService::get('sql'),
        'website' => WebsiteService::get('sql'),
        'webpage' => WebpageService::get('sql')
    ];

    $this->log('1. Validating');
    $this->trigger('crawl-validate', $request, $response);

    if ($response->isError()) {
        $this->log('2. Cleaning Up');
        $this
            ->trigger('crawl-cleanup', $request, $response)
            ->trigger('crawl-error', $request, $response);

        sleep(1);
        return;
    }

    $this->log('2. Website Status to CRAWLING');
    if ($response->hasResults('website_id')) {
        $model['website']->update([
            'website_id' => $response->getResults('website_id'),
            'website_status' => 'CRAWLING'
        ]);
    }

    $results = $response->getResults();

    $this->log('3. Worker Status to RETRIEVING');

    //update crawler status
    $model['worker']->create([
        'worker_id' => WORKER_ID,
        'worker_root' => $results['website_root'],
        'worker_link' => $results['webpage_link'],
        'worker_status' => 'RETRIEVING'
    ]);

    $this->log('4. Calling Phantom');

    //CRAWL w PhantomJS
    $this->trigger('crawl-phantom', $request, $response);
    //if this is a search result
    if ($results['webpage_type'] === 'search') {
        $this->log('5. Search Page - Worker Status to QUEUING');

        //update crawler status
        $model['worker']->update([
            'worker_id' => WORKER_ID,
            'worker_status' => 'QUEUING'
        ]);

        $this->trigger('crawl-queue', $request, $response);
        //it's a detail, if it's not valid
    } else if (!trim($response->getResults('post_name'))
        || !trim($response->getResults('post_position'))
        || !trim($response->getResults('post_location'))
    ) {
        $this->log('5. Detail Page - Invalid');

        if (!isset($results['new']) || $results['new']) {
            $this->log(' - New Page - Setting Webpage Type to other');

            $model['webpage']->update([
                'webpage_id' => $results['webpage_id'],
                'webpage_type' => 'other'
            ]);
        } else {
            $this->log(' - Existing Page - Setting Webpage Type to invalid');

            $response->setError(true, 'Could not get the required data from Detail page.');
            $model['webpage']->update([
                'webpage_id' => $results['webpage_id'],
                'webpage_type' => 'invalid'
            ]);
        }
    } else {
        $this->log('5. Detail Page - Worker Status to SAVING');

        //update crawler status
        $model['worker']->update([
            'worker_id' => WORKER_ID,
            'worker_status' => 'SAVING'
        ]);

        $this->log(' - Calling REST');
        $this->trigger('crawl-rest', $request, $response);
    }

    //update the timestamp
    $model['webpage']->update(['webpage_id' => $results['webpage_id']]);

    $this->log('6. Cleaning Up');

    $this->trigger('crawl-cleanup', $request, $response);

    if ($response->isError()) {
        $this->trigger('crawl-error', $request, $response);
    }

    sleep(5);
});

/**
 * Sub Job - Validate webpage link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-validate', function ($request, $response) {
    $model = [
        'worker' => WorkerService::get('sql'),
        'website' => WebsiteService::get('sql'),
        'webpage' => WebpageService::get('sql')
    ];

    $link = $request->getStage('webpage_link');
    $type = $request->getStage('webpage_type');

    $this->log(' - Cleaning up Workers');

    //clear the worker
    $model['worker']->remove(WORKER_ID);

    //clean workers
    $model['worker']->cleanWorkers();

    $this->log(' - Search/Detail Check');

    //is it a search or detail ?
    if ($type !== 'detail' && $type !== 'search') {
        $response->setError(true, 'Link is not a search or detail page');
        return;
    }

    $this->log(' - Already Crawling Check');

    //is it being crawled ?
    $worker = $model['worker']->get($link);

    if ($worker) {
        $response->setError(true, 'A worker is working on this');
        return;
    }

    $this->log(' - Website Exist Check');

    //determine the website
    $website = $model['website']->getByLink($link);

    if (!$website || !isset($website['website_active']) || !$website['website_active']) {
        $response->setError(true, 'No website found ' . $link);
        return;
    }

    $this->log(' - Redesign Check');

    //check for invalid pages
    $count = $model['webpage']->getInvalidCount($website['website_root']);

    //if there are 3 or more invalid pages
    if ($count > 100) {
        //then it means a redeseign happened
        //dont allow anymore crawling
        $model['website']->update([
            'website_id' => $website['website_id'],
            'website_active' => 0,
        ]);

        $model['webpage']->clearInvalid($website['root']);

        $response->setError(true, 'A redesign happened on this website ' . $website['website_root']);
        return;
    }

    $this->log(' - Getting Webpage');

    //has this been crawled ?
    $webpage = $model['webpage']->get($link);
    //if webpage exists,
    if (!$webpage) {
        $this->log('    - Creating Webpage');

        $model['webpage']->create([
            'webpage_root' => $website['website_root'],
            'webpage_link' => $link,
            'webpage_type' => $type
        ]);

        $webpage = $model['webpage']->get($link);
        //we need to back date it for the recently crawled check
        $webpage['webpage_updated'] = date('Y-m-d H:i:s', strtotime('-60 days'));
        $webpage['new'] = true;
    } else {
        $webpage['new'] = false;
    }

    $this->log(' - Max Crawler Check');

    //are more than 2 workers crawling this root
    $count = $model['worker']->getRootCount($website['website_root']);

    if ($count > 2) {
        $this->log('    - Requeuing (No Delay)');

        $response->setError(true, 'Too many crawlers on one site');
        $priority = $webpage['webpage_type'] === 'detail' ? rand(30, 39): rand(10, 19);

        return $this
            ->package('global')
            ->queue()
            ->setQueue('thejobs_crawler')
            ->setData(['webpage_link' => $request->getStage('webpage_link')])
            ->setPriority($priority)
            ->send('crawl', false);
    }

    $this->log(' - Recently Crawled Check');

    //check to see if it's been passed 30 days
    $timePassed = time() - strtotime($webpage['webpage_updated']);
    $timeThreshold = 60 * 60 * 24 * 30;
    if ($timePassed < $timeThreshold) {
        $this->log('    - Requeuing (7 days)');

        $response->setError(true, 'Link has already been crawled recently');
        $priority = $webpage['webpage_type'] === 'detail' ? rand(30, 39): rand(10, 19);

        return $this
            ->package('global')
            ->queue()
            ->setQueue('thejobs_crawler')
            ->setData(['webpage_link' => $request->getStage('webpage_link')])
            ->setPriority($priority)
            ->setDelay(60 * 60 * 24 * 7)
            ->send('crawl', false);
    }

    $this->log(' -- ALL GOOD --');

    $response
        ->setError(false)
        ->setResults(array_merge(
            $website,
            $webpage,
            [
                'worker_root' => $webpage['webpage_root'],
                'worker_link' => $webpage['webpage_link']
            ]
        ));
});

/**
 * Sub Job - Validate webpage link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-phantom', function ($request, $response) {
    $link = $request->getStage('webpage_link');
    $results = $response->getResults();

    //assume it's detail
    $priority = rand(30, 39);
    $extractor = $results['website_settings']['data_extractor'];

    if($results['webpage_type'] !== 'detail') {
        $priority = rand(10, 19);
        $extractor = $results['website_settings']['link_extractor'];
    }

    $command = sprintf(
        '%s%s/phantomjs %s/extractor.js "%s" "%s"%s',
        //it's possible that phantom hangs
        //this will time it out
        PHP_OS === 'Linux'? 'timeout 300 ':'',
        __DIR__.'/../../../bin',
        __DIR__,
        $link,
        base64_encode($extractor),
        $request->getStage('action') === 'test' ? ' 2>&1': ''
    );

    //close mysql connection
    $this->package('global')->service(null);

    $this->log(' - ' . $command);
    $response->set('phantom', $command);
    exec($command, $output);
    $this->log(' - Response Timeout Check');
    // if no output, return, it must've timedout
    if (empty($output)) {
        $response->setError(true, 'Phantom request timeout');

        //if we are just testing
        if ($request->getStage('action') === 'test') {
            return;
        }

        $this->log('    - Retry (No Delay)');

        return $this
            ->package('global')
            ->queue()
            ->setQueue('thejobs_crawler')
            ->setData(['webpage_link' => $request->getStage('webpage_link')])
            ->setPriority($priority)
            ->send('crawl', false);
    }

    $output = implode("\n", $output);
    $boundaries = explode("--BOUNDARY", $output);

    $this->log(' - Boundary Check');
    // if item is not our expected item
    // its a bad data
    if (!isset($boundaries[1])) {
        if (strpos($output, 'Segmentation fault') !== false) {
            $response->setError(true, 'Segmentation fault');

            //if we are just testing
            if ($request->getStage('action') === 'test') {
                return;
            }

            $this->log('    - Retry (No Delay)');

            return $this
                ->package('global')
                ->queue()
                ->setQueue('thejobs_crawler')
                ->setData(['webpage_link' => $request->getStage('webpage_link')])
                ->setPriority($priority)
                ->send('crawl', false);
        }

        $response->setError(true, 'Unknown bad data');

        return;
    }

    $this->log(' - JSON Check');

    $json = trim($boundaries[1]);
    try {
        $json = json_decode($json, true);
    } catch (Exception $e) {
        $response->setError(true, 'Bad JSON data');

        //if we are just testing
        if ($request->getStage('action') === 'test') {
            return;
        }

        $this->log('    - Retry (No Delay)');

        return $this
            ->package('global')
            ->queue()
            ->setQueue('thejobs_crawler')
            ->setData(['webpage_link' => $request->getStage('webpage_link')])
            ->setPriority($priority)
            ->send('crawl', false);
    }

    //json should be an array
    if (!is_array($json)) {
        $response->setError(true, 'Bad JSON data');

        //if we are just testing
        if ($request->getStage('action') === 'test') {
            return;
        }

        $this->log('    - Retry (No Delay)');

        return $this
            ->package('global')
            ->queue()
            ->setQueue('thejobs_crawler')
            ->setData(['webpage_link' => $request->getStage('webpage_link')])
            ->setPriority($priority)
            ->send('crawl', false);
    }

    //json error
    if (isset($json['error'], $json['message']) && $json['error']) {
        $response->setError(true, $json['message']);

        if (isset($json['key'])) {
            $response->addValidation($json['key'], $json['message']);
        }

        $this->log('    - Bad Website Data');
        $this->log('    - ' . $json['message']);
        return;
    }

    $response->setResults(array_merge($results, $json));

    $this->trigger('crawl-crop', $request, $response);
});

/**
 * Sub Job - Queue links
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-queue', function ($request, $response) {
    //if we are just testing
    if ($request->getStage('action') === 'test') {
        return;
    }

    $results = $response->getResults();

    if (isset($results['detail_links']) && is_array($results['detail_links'])) {
        $results['detail_links'] = array_unique($results['detail_links']);
        //queue
        foreach ($results['detail_links'] as $url) {
            //should be an http link
            if (strpos($url, 'http') !== 0) {
                continue;
            }

            //only queue links that are from this website
            if (isset($results['website_root'])
                && strpos($url, $results['website_root']) !== 0
            ) {
                continue;
            }

            $priority = rand(40, 49);

            if ($request->hasStage('priority')) {
                $priority = $request->getStage('priority');
            }

            $this->log(' - Queuing(' . $priority . ') - ' . $url);

            $this
                ->package('global')
                ->queue()
                ->setQueue('thejobs_crawler')
                ->setData([
                    'webpage_link' => $url,
                    'webpage_type' => 'detail'
                ])
                ->setPriority($priority)
                ->send('crawl', false);
        }
    }

    if (isset($results['search_links']) && is_array($results['search_links'])) {
        $results['search_links'] = array_unique($results['search_links']);
        //queue
        foreach ($results['search_links'] as $url) {
            //should be an http link
            if (strpos($url, 'http') !== 0) {
                continue;
            }

            //only queue links that are from this website
            if (isset($results['website_root'])
                && strpos($url, $results['website_root']) !== 0
            ) {
                continue;
            }

            $priority = rand(20, 29);

            if ($request->hasStage('priority')) {
                $priority = $request->getStage('priority');
            }

            $this->log(' - Queuing(' . $priority . ') - ' . $url);

            $this
                ->package('global')
                ->queue()
                ->setQueue('thejobs_crawler')
                ->setData([
                    'webpage_link' => $url,
                    'webpage_type' => 'search'
                ])
                ->setPriority($priority)
                ->send('crawl', false);
        }
    }
});

/**
 * Sub Job - Seed a link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-seed', function ($request, $response) {
    //if we are just testing
    if ($request->getStage('action') === 'test') {
        return;
    }

    $results = $response->getResults();

    //get the webpage
    $webpage = WebpageService::get('sql')->get($results['webpage_link']);

    if ($webpage) {
        //backdate the timestamp
        WebpageService::get('sql')
            ->getResource()
            ->model()
            ->setWebpageId($webpage['webpage_id'])
            ->setWebpageUpdated(date('Y-m-d H:i:s', strtotime('-60 days')))
            ->update('webpage');
    }

    $priority = rand(50, 59);
    $this->log(' - Queuing(' . $priority . ') - ' . $results['webpage_link']);

    //this is special because this is the main app queuing the crawler rabbit
    $resource = cradle('global')->service('rabbitmq-crawler');

    $this
           ->package('global')
           ->queue()
           ->setQueue('thejobs_crawler')
           ->setData([
               'webpage_link' => $results['webpage_link'],
               'webpage_type' => $results['webpage_type']
           ])
           ->setPriority($priority)
           ->send('crawl', false);
});

/**
 * Sub Job - Call rest to save in system
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-rest', function ($request, $response) {
    //if we are just testing
    if ($request->getStage('action') === 'test') {
        return;
    }

    $config = $this->package('global')->config('crawler');
    $results = $response->getResults();
    $results['post_link'] = $request->getStage('webpage_link');

    //call the API
    $endpoint = sprintf(
        $config['post_search_endpoint'],
        urlencode($results['post_link']),
        $config['client_id']
    );

    $this->log(' - Calling - ' . $endpoint);
    $post = Curl::i()->setUrl($endpoint)->getResponse();
    $this->log(' - REST RESPONSE - ' . $post);
    $post = json_decode($post, true);

    if ($post['error']) {
        return $response->setError(true, $post['message']);
    }

    if (!isset($post['results']['rows'][0])) {
        $post['results']['rows'][0] = null;
    }

    $existing = $post['results']['rows'][0];

    if(!isset($results['post_experience']) || !$results['post_experience']) {
        $results['post_experience'] = 0;
    }

    $post = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'post_name' => $results['post_name'],
        'post_position' => $results['post_position'],
        'post_link' => $results['post_link'],
        'post_location' => $results['post_location'],
        'post_experience' => $results['post_experience'],
        'post_notify' => ['likes']
    ];

    if (isset($results['post_tags'])) {
        $post['post_tags'] = $results['post_tags'];
        if (!is_array($post['post_tags'])) {
            $post['post_tags'] = [$post['post_tags']];
        }
    }

    $optionalFields = [
        'post_email',
        'post_phone',
        'post_detail',
        'post_banner',
        'post_salary',
        'post_tags',
        'profile_id',
        'profile_name',
        'profile_email',
        'profile_phone',
        'profile_image',
        'profile_company',
        'profile_reference',
        'profile_type'
    ];

    foreach ($optionalFields as $field) {
        if (isset($results[$field])) {
            $post[$field] = $results[$field];
        }
    }

    foreach(['name', 'email', 'phone'] as $key) {
        if((!isset($post['post_' . $key]) || !$post['post_' . $key])
            && (isset($profile['profile_' . $key]) && $profile['profile_' . $key])
        ) {
            $post['post_' . $key] = $profile['profile_' . $key];
        }
    }

    $matches = [];
    if(isset($post['post_detail'])
        && (
            !isset($post['post_email'])
            || strpos($post['post_email'], 'jobayan.com') !== false
        )
        && preg_match_all(
            '#[\w\d\-\_\.]+@[\w\d\-\_\.]{6,100}#i',
            $post['post_detail'],
            $matches
        )
        && isset($matches[0][0])
    ) {
        $post['post_email'] = $matches[0][0];
    }

    //issue #173: let's just skip everything that we can't find an email for
    if(!trim($post['post_email']) || $post['post_email'] === 'info@jobayan.com') {
        $this->log(' - No email found. Aborting REST call.');
        return;
    }

    $matches = [];
    if(isset($post['post_detail'])
        && !isset($post['post_phone'])
        && preg_match_all(
            '#[\d\s\.\-\(\)]{9,30}#',
            $post['post_detail'],
            $matches
        )
        && isset($matches[0][0])
    ) {
        $post['post_phone'] = $matches[0][0];
    }

    $endpoint = $config['post_create_endpoint'];
    if (isset($existing['post_id'])) {
        $endpoint = sprintf(
            $config['post_update_endpoint'],
            $existing['post_id']
        );

        //extended life
        $post['post_expires'] = date('Y-m-d H:i:s', strtotime('+30 days'));
    }

    $this->log(' - Calling - ' . $endpoint);
    $this->log(json_encode($post));

    $post = Curl::i()
        ->setUrl($endpoint)
        ->setPost(1)
        ->setPostFields($post)
        ->getResponse();

    $this->log(' - REST RESPONSE - ' . $post);
    $post = json_decode($post, true);

    if ($post['error']) {
        return $response->setError(true, $post['message']);
    }
});

/**
 * Sub Job - Crop Image
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-crop', function ($request, $response) {
    if (!$response->getResults('website_crop')
        || !$response->getResults('post_image')
    ) {
        return;
    }

    $crop = $response->getResults('website_crop');
    $crop = explode(',', $crop);
    $crop = array_pad($crop, 4, 0);

    if (!array_sum($crop)) {
        return;
    }

    $this->log(' - Cropping Image ' . $response->getResults('website_crop'));

    //crop the original
    $source = Curl::i()
        ->setUrl($response->getResults('post_image'))
        ->setReturnTransfer(1)
        ->setBinaryTransfer(1)
        ->getResponse();

    $source = imagecreatefromstring($source);
    //crop will be like 0,0,0,0 where top,right,bottom,left
    $width = imagesx($source);
    $height = imagesy($source);

    $destination = imagecreatetruecolor(
    //$width - (left + right),
        $width - ($crop[3] + $crop[1]),
        //$height - (top + bottom)
        $height - ($crop[0] + $crop[2])
    );

    imagecopy(
        $destination,
        $source,
        0,
        0,
        //left
        $crop[3],
        //top
        $crop[0],
        //right
        $width - ($crop[3] + $crop[1]),
        //bottom
        $height - ($crop[0] + $crop[2])
    );

    ob_start();
    imagejpeg($destination);
    $data = ob_get_contents();
    ob_end_clean();

    $response->setResults('post_image', 'data:image/jpg;base64,'.base64_encode($data));
});

/**
 * Sub Job - Resets statuses
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-cleanup', function ($request, $response) {
    $model = [
        'worker' => WorkerService::get('sql'),
        'website' => WebsiteService::get('sql')
    ];

    //clear the worker
    $model['worker']->remove(WORKER_ID);

    $results = $response->getResults();

    $count = $model['worker']->getRootCount($results['website_root']);

    //if no more are working on this
    if (isset($results['website_id']) && !$count) {
        //set the website to idle
        $model['website']->update([
            'website_id' => $results['website_id'],
            'website_status' => 'IDLE'
        ]);
    }
});

/**
 * Sub Job - Log errors
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-error', function ($request, $response) {
    //A worker is working on this
    //Link is not a search or detail page
    //Too many crawlers on one site
    //Link has already been crawled recently
    //No website found
    //Bad JSON data
    //No links were set on search type
    //Phantom request timeout
    //Segmentation fault
    //Unknown bad data
    //Could not get the required data from Detail page.
    //A redesign happened on this website
    $message = $response->getMessage();
    if (!in_array(
        $message,
        [
            'A worker is working on this',
            'Link is not a search or detail page',
            'Too many crawlers on one site',
            'Link has already been crawled recently'
        ]
    )
    ) {
        LogService::get('sql')->create([
            'log_message' => $message,
            'log_link' => $request->getStage('webpage_link')
        ]);
        return;
    }
});
