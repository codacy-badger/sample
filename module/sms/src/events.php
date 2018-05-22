<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\Validator;
use Cradle\Module\Utility\File;
use \GoIP\GoipClient;
use \GoIP\Sms;

/**
 * Send SMS Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('sms-send', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $config = cradle('global')->config('services', 'goip');

    // if no sms config found do not proceed
    if (!$config) {
        return $response->setError(true, 'no SMS config found');
    }

    // determine which line to use
    $request->setStage('phone', $data['receiver']);
    $this->trigger('sms-number-line-association', $request, $response);
    $line =  $response->getResults();
    // $line = 1;

    // initialize client connection
    $goip = new GoipClient(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['port']
    );

    // start sms
    $sms = new Sms($goip);

    // send
    $result = $sms->sendSms($line, $data['receiver'], $data['message']);

    $response
        ->setError($result['error'], $result['message'])
        ->setResults(['message' => $result['message']]);
});

/**
 * Send SMS Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('sms-number-line-association', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // if no sms config found do not proceed
    if (!isset($data['phone'])) {
        return $response->setError(true, 'no phone number to determine');
    }

    $data['phone'] = preg_replace('/[() -]/', '', $data['phone'], -1);
    $phone = str_replace('+63', '0', $data['phone']);

    $id = substr($phone, 0, 4);
    $config = cradle('global')->config('sms');

    // determine what network
    if (isset($config['networks'][$id])) {
        $network = $config['networks'][$id];
        $response->setError(false)->setResults($config['line'][$network]);
    } else {
        $response->setError(false)->setResults($config['line']['default']);
    }
});
