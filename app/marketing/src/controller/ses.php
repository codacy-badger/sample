<?php //-->

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Bounce Handler Postback
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ses/stats', function ($request, $response) {
    $request->setStage('to', ['asacil@openovate.com', 'aprilvsacil@gmail.com']);
    $request->setStage('from', 'marketing@jobayan.com');
    $request->setStage('subject', 'Hey');
    $request->setStage('html', '<a href="http://www.jobayan.com"><strong>Hello</strong></a>');
    $request->setStage('text', 'Hello');
    $request->setStage('custom_headers', ['messageId' => '123456-sample-message-id']);

    cradle()->trigger('send-ses-email', $request, $response);

    // Need to be logged in
    // cradle('global')->requireLogin();
    //
    // $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    //
    // //get the oauth details
    // $this->trigger('auth-oauth-detail', $request, $response);
    //
    // //if no facebook account
    // if(!$response->hasResults('ses')) {
    //     //add a flash
    //     cradle('global')->flash('User did not setup a ses account.', 'danger');
    //     return cradle('global')->redirect('/social/accounts');
    // }
    //
    // $request->setStage('id', md5($request->getSession('me', 'auth_id')));
    // cradle()->trigger('get-ses-quota', $request, $response);
    // $data['quota'] = $response->getResults();
    //
    // cradle()->trigger('get-ses-statistics', $request, $response);
    // $data['statistics'] = $response->getResults();
    //
    // $data['chart'] = [
    //     'labels' => array_keys($data['statistics']['data_points']),
    //     'bounces' => [],
    //     'complaints' => []
    // ];
    //
    // // prepare for health status
    // $data['statistics']['bounces'] = round(($data['statistics']['bounces']/$data['statistics']['deliveries']) * 100, 2);
    // $data['statistics']['complaints'] = round(($data['statistics']['complaints']/$data['statistics']['deliveries']) * 100, 2);
    //
    // // prepare for charts
    // foreach ($data['statistics']['data_points'] as $points) {
    //     $data['chart']['bounces'][] = round(($points['bounces']/$points['deliveries']) * 100);
    //     $data['chart']['complaints'][] = round(($points['complaints']/$points['deliveries']) * 100);
    //     $data['chart']['rejects'][] = round(($points['rejects']/$points['deliveries']) * 100);
    //     $data['chart']['deliveries'][] = $points['deliveries'];
    // }
    //
    // //----------------------------//
    // // 3. Render Template
    // $class = 'page-ses-stats branding';
    // $data['title'] = cradle('global')->translate('SES Statistics');
    // $body = cradle('/app/www')->template('ses/stats', $data);
    //
    // //set content
    // $response
    //     ->setPage('title', $data['title'])
    //     ->setPage('class', $class)
    //     ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Notification Handler Postback for AWS
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/ses/notification', function ($request, $response) {
    $data  = file_get_contents('php://input');
    $data = json_decode($data);
    $time = time();

    // Confirm SNS subscription
    if ($data->Type == 'SubscriptionConfirmation') {
        $request->setStage('subscription_url', $data->SubscribeURL);
        $request->getStage('host', $_SERVER['HTTP_HOST']);
        return cradle()->trigger('complete-ses-subscription', $request, $response);
    }

    // pull necessary details
    $obj = json_decode($data->Message, true);

    if ($data->Type == 'Notification' && isset($obj['eventType'])) {
        $headers = $obj['mail']['headers'];

        foreach ($headers as $key => $value) {
            $headers[$value['name']] = $value['value'];
            unset($headers[$key]);
        }

        if (isset($headers['messageId']) &&
            ($obj['eventType'] == 'Open' || $obj['eventType'] == 'Click')) {
            // pull campaign with the message id
            $request->setStage(
                'filter',
                ['campaign_message_id' => $headers['messageId']]
            );

            cradle()->trigger('campaign-search', $request, $response);
            $campaign = $response->getResults('rows');

            // if there's a campaign update
            if ($campaign) {
                $filters = [
                    'ses_type' => strtolower($obj['eventType']),
                    'ses_message' => $headers['messageId'],
                ];

                if (isset($obj['click']) && isset($obj['click']['link'])) {
                    $filters['ses_link'] = $obj['click']['link'];
                }

                // pull email event of this type
                $request->setStage('filter', $filters);

                cradle()->trigger('ses-search', $request, $response);
                $event = $response->getResults('rows');

                $total = 0;

                $sesRequest = new Request();
                $sesResponse = new Response();

                // if no event yet, create
                if (!$event) {
                    $total += 1;
                    $sesRequest->setStage('ses_message', $headers['messageId']);
                    $sesRequest->setStage('ses_total', 1);
                    $sesRequest->setStage('ses_type', strtolower($obj['eventType']));
                    $sesRequest->setStage(
                        'ses_emails',
                        [$obj['mail']['destination'][0] => date('Y-m-d H:i:s')]
                    );

                    if (isset($obj['click']) && isset($obj['click']['link'])) {
                        $sesRequest->setStage('ses_link', $obj['click']['link']);
                    }

                    // add ses event record
                    cradle()->trigger('ses-create', $sesRequest, $sesResponse);

                    // we assume this is unique
                    // since no record was found
                    if (strtolower($obj['eventType']) == 'open') {
                        $request->setStage('field', 'opened');
                        $request->setStage('subtract', 'unopened');
                    }

                    if (strtolower($obj['eventType']) == 'click') {
                        $request->setStage('field', 'clicked');
                    }

                    $request->setStage('campaign_id', $campaign[0]['campaign_id']);
                    cradle()->trigger('campaign-update', $request, $response);

                // if there's already an event
                } else {
                    $uniqueOpen = false;
                    $total = $event[0]['ses_total'] + 1;

                    // add 1 total
                    $sesRequest->setStage('ses_id', $event[0]['ses_id']);
                    $sesRequest->setStage('ses_total', $total);

                    $emails = $event[0]['ses_emails'];

                    // check if email haven't done this event
                    // add the email and set unique open to true
                    if (!isset($emails[$obj['mail']['destination'][0]])) {
                        $emails[$obj['mail']['destination'][0]] = date('Y-m-d H:i:s', strtotime('now'));
                        $sesRequest->setStage('ses_emails', $emails);
                        $uniqueOpen = true;
                    }

                    // update ses event record
                    cradle()->trigger('ses-update', $sesRequest, $sesResponse);

                    // if event is open and
                    // and is opened for the first time
                    // add 1
                    if (strtolower($obj['eventType']) == 'open' && $uniqueOpen) {
                        $request->setStage('field', 'opened');
                        $request->setStage('subtract', 'unopened');
                    }

                    // if event is click, add 1
                    // for now it doesn't matter if this is first time
                    // click from the user or not
                    // the latter matters only in the click event
                    // which is the record in the ses table
                    if (strtolower($obj['eventType']) == 'click') {
                        $request->setStage('field', 'clicked');
                    }

                    $request->setStage('campaign_id', $campaign[0]['campaign_id']);
                    cradle()->trigger('campaign-update', $request, $response);
                }
            }
        }

        if ($obj['eventType'] == 'Bounce' || $obj['eventType'] == 'Complaint') {
            // bounce details
            if ($obj['eventType'] == 'Bounce') {
                $bounceType = $obj['bounce']['bounceType'];
                $problemEmail = $obj['bounce']['bouncedRecipients'];
            }

            // complaint details
            if ($obj['eventType'] == 'Complaint') {
                $problemEmail = $obj['complaint']['complainedRecipients'];
            }

            foreach (preg_split('/\s/', $problemEmail[0]['emailAddress']) as $token) {
                $email = filter_var(
                    filter_var($token, FILTER_SANITIZE_EMAIL),
                    FILTER_VALIDATE_EMAIL
                );
                if ($email !== false) {
                    $emails[] = $email;
                }
            }

            foreach ($emails as $problemEmail) {
                // check if email is valid, if not, exit
                if (!filter_var($problemEmail, FILTER_VALIDATE_EMAIL)) {
                    // invalid email
                    continue;
                }

                // is it a bounce notification
                if ($obj['eventType'] == 'Bounce') {
                    // update bounce in the lead/profile listing
                    $request->setStage('bounce_type', $bounceType);
                    $request->setStage('email', $problemEmail);
                    cradle()->trigger(
                        'update-subscriber-bounce',
                        $request,
                        $response
                    );
                }

                // is it a complaint notification
                if ($obj['eventType'] == 'Complaint') {
                    // update complaint in the lead/profile listing
                    $request->setStage('email', $problemEmail);
                    cradle()->trigger(
                        'process-subscriber-complaint',
                        $request,
                        $response
                    );
                }

                // if there's a messageId
                if (isset($headers['messageId'])) {
                    // check against campaign table
                    $request->setStage(
                        'filter',
                        ['campaign_message_id' => $headers['messageId']]
                    );

                    cradle()->trigger('campaign-search', $request, $response);
                    $campaign = $response->getResults('rows');

                    // if there's a campaign
                    if ($campaign) {
                        // if event is bounce, update bounce counter
                        if (strtolower($obj['eventType']) == 'bounce') {
                            $request->setStage('field', 'bounced');
                            $request->setStage('subtract', ['sent', 'unopened']);
                        }

                        // if it's a complaint, update complaint counter
                        if (strtolower($obj['eventType']) == 'complaint') {
                            $request->setStage('field', 'spam');
                        }

                        $request->setStage('campaign_id', $campaign[0]['campaign_id']);
                        cradle()->trigger('campaign-update', $request, $response);
                    }
                }
            }
        }
    }

    $response->setContent('Thank You!');
});
