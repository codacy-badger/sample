<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Campaign\Service as CampaignService;
use Cradle\Module\Campaign\Validator as CampaignValidator;
use Cradle\Module\Ses\Service as SesService;
use Cradle\Module\Utility\Rest;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Campaign Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $users = [];
    $tags = [];
    $total = 0;

    //----------------------------//
    // 2. Validate Data
    $errors = CampaignValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['campaign_tags'])) {
        $tags = $data['campaign_tags'];
        $data['campaign_tags'] = json_encode($data['campaign_tags']);
    }

    if(!isset($data['campaign_message_id'])) {
        $data['campaign_message_id'] = md5(date('Y-m-d H:i:s', strtotime('now')));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save campaign to database
    $results = $campaignSql->create($data);

    //index campaign
    // $campaignElastic->create($results['campaign_id']);

    //invalidate cache
    $campaignRedis->removeSearch();

    //link template
    if(isset($data['template_id'])) {
        $campaignSql->linkTemplate($results['campaign_id'], $data['template_id']);
    }

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Campaign Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['campaign_id'])) {
        $id = $data['campaign_id'];
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
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $campaignRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $campaignElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $campaignSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $campaignRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Campaign Enable Send Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-enable-send', function ($request, $response) {
  //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage('results');
    }

    $users = [];
    $tags = [];
    $total = 0;


    //Prepare data
    if(isset($data['campaign_tags'])) {
        $tags = $data['campaign_tags'];
        $data['campaign_tags'] = json_encode($data['campaign_tags']);
    }

    // if(!isset($data['campaign_message_id'])) {
    //     $data['campaign_message_id'] = md5(date('Y-m-d H:i:s', strtotime('now')));
    // }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save campaign to database
    $results = $campaignSql->create($data);

    //index campaign
    // $campaignElastic->create($results['campaign_id']);

    //invalidate cache
    $campaignRedis->removeSearch();

    //link template
    if(isset($data['template_id'])) {
        $campaignSql->linkTemplate($results['campaign_id'], $data['template_id']);
    }

    if (isset($data['campaign_source'])) {
        // if we're sending it out to leads
        if ($data['campaign_source'] == 'lead' ||
            $data['campaign_source'] == 'profile') {
            $type = $data['campaign_source'];
            if ($data['campaign_source'] == 'lead') {
                $request->setStage(
                    'filter',
                    ['lead_type' => $data['campaign_audience']]
                );
            }

            if ($data['campaign_source'] == 'profile') {
                // is it a solo audience?
                if ($data['campaign_audience'] == 'solo' &&
                    $data['profile_id']) {
                    $request->setStage(
                        'filter',
                        ['profile_id' => $data['profile_id']]
                    );
                // or multiple audience of specific type?
                } else {
                    $request->setStage(
                        'filter',
                        ['type' => $data['campaign_audience']]
                    );
                }
            }

            // set tags if any
            if ($tags) {
                $request->setStage($type.'_tags', $tags);
            }

            // pull total lead of the type given
            $request->setStage('range', 1);
            $this->trigger($type.'-search', $request, $response);
            $total = $response->getResults('total');

            // now we have to update how much were queued for sending/receiving
            $request->setStage('campaign_id', $data['campaign_id']);
            $request->setStage('campaign_queue', $total);
            $this->trigger('campaign-update', $request, $response);
            // pull by batch of 200 from the database
            // until all the leads under the type given
            // has been queued to receive this campaign
            $request->setStage('range', 200);
            for ($start = 0; $start < $total; $start++) {
                $request->setStage('start', $start);
                $this->trigger($type.'-search', $request, $response);
                $users = $response->getResults('rows');
                // now we have to link each pulled user to this campaign
                // and also send thru whatever type of channel we have to send
                foreach ($users as $user) {
                    if ($type == 'lead') {
                        // link campaign to this lead
                        $campaignSql->linkLead(
                            $results['campaign_id'],
                            $user['lead_id']
                        );
                    }

                    if ($type == 'profile') {
                        // link campaign to this profile
                        $campaignSql->linkProfile(
                            $results['campaign_id'],
                            $user['profile_id']
                        );
                    }

                    $sendRequest = new Request();
                    $sendResponse = new Response();

                    // set peripherals for sending this campaign to this user
                    $sendRequest->setStage('campaign_id', $results['campaign_id']);
                    $sendRequest->setStage('template_id', $data['template_id']);
                    $sendRequest->setStage('receiver', $user[$type.'_id']);
                    $sendRequest->setStage('medium', $data['campaign_medium']);
                    $sendRequest->setStage('source', $data['campaign_source']);
                    $sendRequest->setStage('message_id', $data['campaign_message_id']);
                    $protocol = 'http';
                    if ($request->getServer('SERVER_PORT') === 443) {
                        $protocol = 'https';
                    }

                    $sendRequest
                        ->setStage(
                            'host',
                            $protocol . '://' . $request->getServer('HTTP_HOST')
                        );

                    $sendData = $sendRequest->getStage();
                    // queue the send if queueing is available
                    // if (!$this->package('global')->queue('campaign-send', $sendData)) {
                        // send campaign manually
                        $this->trigger('campaign-send', $sendRequest, $sendResponse);
                    // }
                }
                // update start for pulling,
                // add 200 since range is 200
                $start += 200;
            }
        }
    }

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Campaign Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the campaign detail
    $this->trigger('campaign-detail', $request, $response);

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
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save to database
    $results = $campaignSql->update([
        'campaign_id' => $data['campaign_id'],
        'campaign_active' => 0
    ]);

    //remove from index
    // $campaignElastic->remove($data['campaign_id']);

    //invalidate cache
    $campaignRedis->removeDetail($data['campaign_id']);
    $campaignRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Campaign Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the campaign detail
    $this->trigger('campaign-detail', $request, $response);

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
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save to database
    $results = $campaignSql->update([
        'campaign_id' => $data['campaign_id'],
        'campaign_active' => 1
    ]);

    //create index
    // $campaignElastic->create($data['campaign_id']);

    //invalidate cache
    $campaignRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Campaign Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-search', function ($request, $response) {
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
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $campaignRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $campaignElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $campaignSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $campaignRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Campaign Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the campaign detail
    $this->trigger('campaign-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $detail =  $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = CampaignValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['campaign_tags'])) {
        $data['campaign_tags'] = json_encode($data['campaign_tags']);
    }

    if (isset($data['subtract'])) {
        if (is_array($data['subtract'])) {
            foreach ($data['subtract'] as $field) {
                $data['campaign_'.$field] = $detail['campaign_'.$field] - 1;
            }
        } else {
            $data['campaign_'.$data['subtract']] = $detail['campaign_'.$data['subtract']] - 1;
        }
    }

    if (isset($data['field'])) {
        if (is_array($data['field'])) {
            foreach ($data['field'] as $field) {
                $data['campaign_'.$field] = $detail['campaign_'.$field] + 1;
            }
        } else {
            $data['campaign_'.$data['field']] = $detail['campaign_'.$data['field']] + 1;
        }
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save campaign to database
    $results = $campaignSql->update($data);

    // relink template
    if(isset($data['template_id'])) {
        // remove the campaign_id and template_id from the campaign_template table
        $campaign = $campaignSql->get($request->getStage('campaign_id'));

        $campaignSql->unlinkTemplate($campaign['campaign_id'], $campaign['template_id']);
        $campaignSql->linkTemplate($results['campaign_id'], $data['template_id']);
    }

    //index campaign
    // $campaignElastic->update($response->getResults('campaign_id'));

    //invalidate cache
    $campaignRedis->removeDetail($response->getResults('campaign_id'));
    $campaignRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});


/**
 * Campaign Send Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-send', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $email = '';
    $phone = '';

    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    // 3. Prepare Data
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 4. Process Data

    // first pull the receiver details
    if (isset($data['source']) &&
        ($data['source'] == 'profile' ||
        $data['source'] == 'lead')) {
        $user = Rest::i($api.'/rest/'.$data['source'].'/detail/'.$data['receiver'])
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->get();

        $user = $user['results'] ? $user['results']: [];

        if (!$user) {
            return $response->setError(true, 'user not found');
        }

        $email = $user[$data['source'].'_email'];
        $phone = $user[$data['source'].'_phone'];

        // clean phone number
        if (strlen($phone) == 10 &&
            preg_match('/^9[0-9]{9}/', $phone)) {
            $phone = '0'.$phone;
        }

        $phone = preg_replace('/[() -]/', '', $phone, -1);

        if ($data['source'] == 'lead') {
            // now we have to clean the user
            foreach ($user as $key => $value) {
                $k = str_replace('lead_', 'profile_', $key);
                $user[$k] =  $value;
                unset($user[$key]);
            }
        }

        if ($data['source'] == 'profile') {
            // if it's an existing user we have to formulate
            // the profile location because what we have in the database
            // are chunked address
            $user['profile_location'] = $user['profile_address_city'] . ' ' .
                $user['profile_address_state'];
        }

        $user['profile_firstname'] = substr(
            $user['profile_name'],              // string
            0,                                  // start
            strpos($user['profile_name'], ' ')  // end is the first ' '
        );
    }

    if (isset($data['template_id'])) {
        $template = Rest::i($api.'/rest/template/detail/'.$data['template_id'])
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->get();

        $template = $template['results'] ? $template['results']: [];
    }

    // if medium is email
    if (isset($data['medium']) && $data['medium'] == 'email') {
        $emailRequest = new Request();
        $emailResponse = new Response();

        $emailRequest->setStage('campaign_id', $data['campaign_id']);
        $emailRequest->setStage('template', $template);
        $emailRequest->setStage('user', $user);
        $emailRequest->setStage('receiver', $email);
        $emailRequest->setStage('sender', 'marketing@jobayan.com');
        $emailRequest->setStage('message_id',  $data['message_id']);
        $emailRequest->setStage('user_type', $data['source']);
        $emailRequest->setStage('host', $data['host']);
        $emailRequest->setStage(
            'custom_headers',
            ['messageId' => $data['message_id']]
        );

        $this->trigger('campaign-send-email', $emailRequest, $emailResponse);
    }

    // if medium is sms
    if (isset($data['medium']) && $data['medium'] == 'sms') {
        // initialize the template compiler
        $handlebars = $this->package('global')->handlebars();

        // compile template with the data
        $txtTemplate = $handlebars->compile($template['template_text']);
        $message = $txtTemplate($user);

        $smsRequest = new Request();
        $smsResponse = new Response();

        // send sms
        $smsRequest->setStage('message', $message);
        $smsRequest->setStage('receiver', $phone);
        cradle()->trigger('sms-send', $smsRequest, $smsResponse);

        if ($smsResponse->isError()) {
            // now we have to update add 1 for bounced and remove 1 for queued
            // value of this campaign
            Rest::i($api.'/rest/campaign/update/'.$data['campaign_id'])
                ->set('client_id', $app['token'])
                ->set('client_secret', $app['secret'])
                ->set('field', ['bounced'])
                ->set('subtract', 'queue')
                ->post();

            return $response->setError(true, 'Invalid Number');
        }
    }

    // now we have to update add 1 for send and remove 1 for queued
    // value of this campaign
    Rest::i($api.'/rest/campaign/update/'.$data['campaign_id'])
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('field', ['sent', 'unopened'])
        ->set('subtract', 'queue')
        ->post();

    //return response format
    $response->setError(false)->setResults([]);
});

/**
 * Campaign Link
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-link-client', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the campaign detail
    $this->trigger('campaign-detail', $request, $response);

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
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    if ($data['link_type'] == 'lead' ) {
        // link campaign to this lead
        $campaignSql->linkLead(
            $data['campaign_id'],
            $data['client_id']
        );
    }

    if ($data['link_type'] == 'profile' ) {
        // link campaign to this lead
        $campaignSql->linkProfile(
            $data['campaign_id'],
            $data['client_id']
        );
    }

    //remove from index
    // $campaignElastic->remove($data['campaign_id']);

    //invalidate cache
    $campaignRedis->removeDetail($data['campaign_id']);
    $campaignRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Campaign Send Email Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-send-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $config = $this->package('global')->service('ses');

    //----------------------------//
    // 2. Prepare Data
    $data['from'] = $data['sender'];
    $data['to'] = [$data['receiver']];
    $template = $data['template'];
    $data['subject'] = $this->package('global')
        ->translate($template['template_title']);

    // initialize the template compiler
    $handlebars = $this->package('global')->handlebars();

    // data to be passed in the template
    $variables = $data['user'];
    // formulate unsubscribe url
    $host = $request->getStage('host');
    $type = $data['user_type'];
    $hash = md5($type.$data['user']['profile_id']);
    $variables['unsubscribe'] = $data['host'] . '/unsubscribe/' . $type . '/' .
        $data['user']['profile_id'] . '/' . $hash . '/' . $data['message_id'];

    if (trim($template['template_text']) != '') {
        $txtTemplate = $handlebars->compile($template['template_text']);
        $data['text'] = $txtTemplate($variables);
    } else {
        $txtTemplate = $handlebars->compile(strip_tags($template['template_html']));
        $data['text'] = $txtTemplate($variables);
    }

    $htmlTemplate = $handlebars->compile($template['template_html']);
    $data['html'] = $htmlTemplate($variables);

    // send
    $request->setStage($data);
    $this->trigger('prepare-email', $request, $response);
});

/**
 * Campaign Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('campaign-bulk-action', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();
    //----------------------------//
    // 2. Validate Data
    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $campaignSql = CampaignService::get('sql');
    $campaignRedis = CampaignService::get('redis');
    // $campaignElastic = CampaignService::get('elastic');

    //save to database
    $results = $campaignSql->bulkAction(
        $data['bulk_ids'],
        $data['bulk_value'],
        $data['bulk_field']
    );

    foreach ($data['bulk_ids'] as $id) {
        //remove from index
        // $campaignElastic->remove($id);

        //invalidate cache
        $campaignRedis->removeDetail($id);
        $campaignRedis->removeSearch();
    }

    $response->setError(false)->setResults($results);
});

$cradle->on('ses-detail-byMessage', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $sesMessage = null;
    if (isset($data['campaign_message_id'])) {
        $sesMessage = $data['campaign_message_id'];
    }

    //----------------------------//
    // 2. Validate Data
    //we need the message id
    if (!$sesMessage) {
        return $response->setError(true, 'Invalid Message ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $sesSql = SesService::get('sql');
    $sesRedis = SesService::get('redis');
    // $sesElastic = SesService::get('elastic');

    $results = null;

    //if no results
    if (!$results) {
        //if no results
        if (!$results) {
            //get it from database
            $results = $sesSql->getByMessage($sesMessage);
        }
    }

    $response->setError(false)->setResults($results);
});
