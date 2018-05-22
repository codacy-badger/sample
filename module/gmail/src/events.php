<?php //-->
/**
 * This file is part of The Socialite Project
 * (c) 2017-2019 Christan Blanquera.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 use League\OAuth2\Client\Provider\GenericProvider as OAuth2;
 use League\OAuth2\Client\Provider\Exception\IdentityProviderException as OAuth2Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Cradle\Module\Utility\Rest;

/**
 * Adds GMail tokens to the oauth table
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('gmail-connect', function ($request, $response) {
    //get facebook config. See: README for more info
    $service = cradle('global')->service('gmail');

    //if there's a problem with config
    if (
        !isset(
            $service['oauth']['client_id'],
            $service['oauth']['client_secret'],
            $service['oauth']['redirect_uri']
        )
        || $service['oauth']['client_id'] === '<CLIENT ID>'
        || $service['oauth']['client_secret'] === '<CLIENT SECRET>'
        || $service['oauth']['redirect_uri'] === '<REDIRECT URI>'
    )
    {
        return $response->setError(true, 'Google is not properly configured.');
    }

    //determine the scope
    if (isset($service['oauth']['scope']) && is_array($service['oauth']['scope'])) {
        $service['scope'] = array_merge($service['scope'], $service['oauth']['scope']);
    }

    //see: https://packagist.org/packages/league/oauth2-client
    $provider = new OAuth2([
        //map
        'clientId' => $service['oauth']['client_id'],
        'clientSecret' => $service['oauth']['client_secret'],
        'redirectUri' => $service['oauth']['redirect_uri'],
        'urlAuthorize' => $service['oauth_request_endpoint'],
        'urlAccessToken' => $service['oauth_access_endpoint'],
        'urlResourceOwnerDetails' => $service['oauth_user_endpoint']
    ]);

    //if we are on the first step
    if(!$request->getStage('code')) {
        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => $service['scope'],
            'access_type' => 'offline',
            'approval_prompt' => 'force'
        ]);

        return $response->setResults([
            //stage can be request or access. See Below
            'oauth_stage' => 'request',
            //oauth_state should be saved into session
            'oauth_state' => $provider->getState(),
            //this is where to redirect
            'authorization_url' => $authorizationUrl
        ]);
    }

    //there is a code
    //check the state, its for your own good :(
    if ($request->hasStage('oauth_state') &&
        $request->getStage('state') !== $request->getStage('oauth_state')
    )
    {
        return $response->isError(true, 'Invalid State');
    }

    try {
        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $request->getStage('code')
        ]);
    } catch (OAuth2Exception $e) {
        // Failed to get the access token or user details.
        return $response->isError(true, $e->getMessage());
    }

    // We have an access token, which we may use in authenticated
    // requests against the service provider's API.
    //Access Token
    $token = $accessToken->getToken();

    //Refresh Token
    $refreshToken = $accessToken->getRefreshToken();

    $request->setStage('auth_google_token', $token);
    $request->setStage('auth_google_refresh_token', $refreshToken);
    $this->trigger('auth-update', $request, $response);

    $response->setResults('oauth_stage', 'access');
});

/**
 * Sends a message
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('gmail-send', function ($request, $response) {
    $data = $request->getStage();

    // make sure we have a message
    if (!$request->hasStage('html') && !$request->hasStage('text')) {
        return $response->setError(true, 'No message payload.');
    }

    // make sure we have a receiever
    if (!$request->hasStage('to')) {
        return $response->setError(true, 'No receiver payload.');
    }

    // make sure we have a subject
    if (!$request->hasStage('subject')) {
        return $response->setError(true, 'No subject payload.');
    }

    $receivers = $request->getStage('to');
    if (is_array($receivers)) {
        $receiver = implode(',', $receivers);
    } else {
        $receiver = $receivers;
    }

    //get the oauth details
    $this->trigger('auth-detail', $request, $response);

    //if no facebook account
    if(!$response->hasResults('auth_google_token')) {
        return $response->setError(true, 'User did not setup a gmail account.');
    }

    $user = $response->getResults();

    $service = cradle('global')->service('gmail');
    $client = new Google_Client();
    $client->setClientId($service['oauth']['client_id']);
    $client->setClientSecret($service['oauth']['client_secret']);
    $client->setAccessToken($user['auth_google_token']);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->refreshToken($user['auth_google_refresh_token']);

    $service = new Google_Service_Gmail($client);

    $boundary = "__ctrlq_dot_org__";

    $rawMsg = "From: ".$user['profile_name']."<".$user['profile_email'].">\r\n";
    $rawMsg .= "To: ".$receiver."\r\n";
    $rawMsg .= 'Subject: =?utf-8?B?'.base64_encode($request->getStage('subject'))."?=\r\n";
    $rawMsg .= "MIME-Version: 1.0\r\n";
    if ($request->hasStage('replyId')) {
        $rawMsg .= 'Message-ID: '.$request->getStage('replyId')."\r\n";
        $rawMsg .= 'In-Reply-To: '.$request->getStage('replyId')."\r\n";
    }

    if ($request->hasStage('references')) {
        $rawMsg .= 'References: '.$request->getStage('references')."\r\n";
    }

    if ($request->getStage('files')) {
        $rawMsg .= "Content-Type: multipart/mixed; boundary=" . $boundary."\r\n";
    } else {
        $rawMsg .= "Content-Type: multipart/alternative; boundary=" . $boundary."\r\n";
    }

    $charset = 'utf-8';
    if (!$request->getStage('files') || !$request->getStage('html')) {
        $rawMsg .= "\r\n--{$boundary}\r\n";
        $rawMsg .= 'Content-Type: text/plain; charset=' . $charset . "\r\n";
        $rawMsg .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $rawMsg .= $request->getStage('text') . "\r\n";
    }

    $rawMsg .= "\r\n--{$boundary}\r\n";
    $rawMsg .= 'Content-Type: text/html; charset=' . $charset . "\r\n";
    $rawMsg .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
    $rawMsg .= $request->getStage('html') . "\r\n";

    if ($request->getStage('files')) {
        $rawMsg .= "\r\n--" . $boundary."\r\n";
        $rawMsg .= "\r\n--{$boundary}\r\n";
        foreach ($request->getStage('files') as $file) {
            $rawMsg .= "\r\n--{$boundary}\r\n";
            $rawMsg .= 'Content-Type: '. $file['mime'] .'; name="'. $file['name'] .'";' . "\r\n";
            $rawMsg .= 'Content-ID: <' . $user['profile_name'] . '>' . "\r\n";
            $rawMsg .= 'Content-Description: ' . $file['name'] . ';' . "\r\n";
            $rawMsg .= 'Content-Disposition: attachment; filename="' . $file['name'] . '"; size=' . $file['size']. ';' . "\r\n";
            $rawMsg .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
            $rawMsg .= chunk_split(base64_encode(file_get_contents($file['file'])), 76, "\n") . "\r\n";
            $rawMsg .= '--' . $boundary . "\r\n";
        }
    }

    $rawMsg .= "--".$boundary."--";

    // The message needs to be encoded in Base64URL
    $mime = rtrim(strtr(base64_encode($rawMsg), '+/', '-_'), '=');
    $msg = new Google_Service_Gmail_Message();
    $msg->setRaw($mime);

    if ($request->getStage('threadId')) {
        $msg->setThreadId($request->getStage('threadId'));
    }

    //The special value **me** can be used to indicate the authenticated user.
    $googleResponse = $service->users_messages->send("me", $msg);
    $googleResponse = json_decode(json_encode($googleResponse), true);

    if (in_array('SENT', $googleResponse['labelIds'])) {
        $response->setError(false);
    } else {
        $response->setError(true, 'Unable to send');
    }

    $response->setResults($googleResponse);
});

/**
 * Pulls gmail thread
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('gmail-pull-thread', function ($request, $response) {
    //get the oauth details
    $this->trigger('auth-detail', $request, $response);

    //if no facebook account
    if(!$response->hasResults('auth_google_token')) {
        return $response->setError(true, 'User did not setup a gmail account.');
    }

    $user = $response->getResults();

    $service = cradle('global')->service('gmail');
    $client = new Google_Client();
    $client->setClientId($service['oauth']['client_id']);
    $client->setClientSecret($service['oauth']['client_secret']);
    $client->setAccessToken($user['auth_google_token']);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->refreshToken($user['auth_google_refresh_token']);

    $service = new Google_Service_Gmail($client);

    try {
        $thread = $service->users_threads->get('me', $request->getStage('thread_id'));
    } catch (Exception $e) {
        $error = json_decode($e->getMessage(), true);
        return $response->setError(true, $error['error']['message']);
    }

    $msgs = json_decode(json_encode($thread->getMessages()), true);
    $messages = [];
    $references = [];
    foreach ($msgs as $msg) {
        // set to philippine timezone
        $given = new DateTime(date('Y-m-d H:i:s', $msg['internalDate']/1000));
        $given->setTimezone(new DateTimeZone("GMT+8"));
        $date = $given->format("Y-m-d H:i:s");

        $headers = [];
        $attachments = [];
        foreach ($msg['payload']['headers'] as $head) {
            $headers[$head['name']] = $head['value'];
        }

        if (!isset($msg['payload']['parts'][0]['parts'])) {
           $message = $msg['payload']['parts'][0]['body']['data'];
           if (isset($msg['payload']['parts'][1])) {
               $message = $msg['payload']['parts'][1]['body']['data'];
           }

           $message =  str_replace('-', '+', $message);
           $message =  str_replace('_', '/', $message);

           $snippet = $msg['payload']['parts'][0]['body']['data'];
           $snippet =  str_replace('-', '+', $snippet);
           $snippet =  str_replace('_', '/', $snippet);
        }

        // if theres parts in parts then it has attachment/s
        if (isset($msg['payload']['parts'][0]['parts'])) {
            $message = $msg['payload']['parts'][0]['parts'][0]['body']['data'];
            if (isset($msg['payload']['parts'][0]['parts'][1])) {
                $message = $msg['payload']['parts'][0]['parts'][1]['body']['data'];
            }

            $message =  str_replace('-', '+', $message);
            $message =  str_replace('_', '/', $message);

            $snippet = $msg['payload']['parts'][0]['parts'][0]['body']['data'];
            $snippet =  str_replace('-', '+', $snippet);
            $snippet =  str_replace('_', '/', $snippet);

            $parts = count($msg['payload']['parts']);
            for ($i = 1; $i < $parts; $i++) {
                $name = str_replace('attachment; filename="', '', $attchmntHeaders['Content-Disposition']);
                $name = substr($name, 0, -2);
                $mime = str_replace(' name="'.$name.'"', '', $attchmntHeaders['Content-Type']);

                $attachment = $service->users_messages_attachments->get(
                    'me',
                    $request->getStage('thread_id'),
                    $msg['payload']['parts'][$i]['body']['attachmentId']
                );

                $attachment = json_decode(json_encode($attachment), true);
                $attachment['data'] =  str_replace('-', '+', $attachment['data']);
                $attachment['data'] =  str_replace('_', '/', $attachment['data']);

                $attachments[] = [
                    'name' => $msg['payload']['parts'][$i]['filename'],
                    'mime' => $msg['payload']['parts'][$i]['mimeType'],
                    'file' => $attachment['data']
                ];
            }
        }

        $messages[] =  [
            'snippet' => base64_decode($snippet),
            'full_message' => base64_decode($message),
            'sender' => $headers['From'],
            'receievers' => $headers['To'],
            'attachments' => $attachments
        ];

        $replyId = isset($headers['Message-Id']) ? $headers['Message-Id'] : $headers['In-Reply-To'];
        $subject = $headers['Subject'];

        $references[] = $replyId;
    }

    // reverse messages so newest will go on top;
    // $messages = array_reverse($messages);

    $results = [
        'replyId' => $replyId,
        'references' => implode(' ', $references),
        'subject' => $subject,
        'messages' => $messages
    ];

    $response->setResults($results);
});


/**
 * Pulls gmail thread
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('gmail-pull-messages', function ($request, $response) {
    //get the oauth details
    $this->trigger('auth-detail', $request, $response);

    //if no facebook account
    if(!$response->hasResults('auth_google_token')) {
        return $response->setError(true, 'User did not setup a gmail account.');
    }

    $user = $response->getResults();

    $service = cradle('global')->service('gmail');
    $client = new Google_Client();
    $client->setClientId($service['oauth']['client_id']);
    $client->setClientSecret($service['oauth']['client_secret']);
    $client->setAccessToken($user['auth_google_token']);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->refreshToken($user['auth_google_refresh_token']);

    $service = new Google_Service_Gmail($client);

    $pageToken = NULL;
    $start = 0;
    $msgs = [];
    $messages = [];
    $options = [
        'maxResults' => 15
    ];

    if ($request->getStage('email')) {
        $options['q'] = $request->getStage('email');
    }

    if ($request->getStage('pageToken')) {
        $options['pageToken'] = $request->getStage('pageToken');
    }

    try {
        if ($pageToken) {
            $options['pageToken'] = $pageToken;
        }

        $messagesResponse = $service->users_messages->listUsersMessages('me', $options);
        if ($messagesResponse->getMessages()) {
            $messages = array_merge($messages, $messagesResponse->getMessages());
            $pageToken = $messagesResponse->getNextPageToken();
            // $options['pageToken'] = $pageToken;
        }

    } catch (Exception $e) {
        return $response->setError(true, $e->getMessage());
    }
// } while ($pageToken);
     foreach ($messages as $msg => $message) {
         $snippet = $service->users_messages
            ->get('me', $message->getId(), ['format' => 'metadata']);

        // set to philippine timezone
        $given = new DateTime(date('Y-m-d H:i:s', $snippet->getinternalDate()/1000));
        $given->setTimezone(new DateTimeZone("GMT+8"));
        $date = $given->format("M d, h:i a");

        $headers = $snippet->getPayload()->getHeaders();

        foreach ($headers as $key => $value) {
            $headers[$value->getName()] = $value->getValue();
            unset($headers[$key]);
        }

        $messages[$msg] = [
            'snippet' => $snippet->getSnippet(),
            'thread_id' => $snippet->getThreadId(),
            'msg_date' => $date,
            'subject' => $headers['Subject']
        ];
     }

    $results = [
        'nextPageToken' => $pageToken,
        'messages' => $messages
    ];

    $response->setResults($results);
});
