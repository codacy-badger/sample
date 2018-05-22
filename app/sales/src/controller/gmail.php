<?php //-->

use Cradle\Module\Utility\Rest;

/**
 * Gmail Connect
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/gmail/connect', function ($request, $response) {
    $redirect = '/';
    if ($request->hasStage('destination')) {
        $redirect = $request->getStage('destination');
    }

    //get auth_id
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));

    //get state
    if($request->hasSession('oauth_state')) {
        $request->setStage('oauth_state', $request->getSession('oauth_state'));
        $request->removeSession('oauth_state');
    }

    cradle()->trigger('gmail-connect', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect($redirect);
    }

    if ($response->getResults('oauth_stage') === 'request') {
        // Get the state generated for you and store it to the session.
        $request->setSession(
            'oauth_state',
            $response->getResults('oauth_state')
        );

        // Redirect the user to the authorization URL.
        return cradle('global')->redirect(
            $response->getResults('authorization_url')
        );

    }

    if ($response->getResults('oauth_stage') === 'access') {
        $oauth = $response->getResults('auth_google_token');
        $request->setSession('me', 'auth_google_token', $oauth);
        return cradle('global')->redirect($redirect);
    }
});

/**
 * Gmail Send
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/gmail/send/mail/deal/:deal_id', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    // are there any files, included?
    if (!empty($_FILES['attachments']['name'][0])) {
        // fix files to be uploaded to s3
        $attachments = [];

        foreach ($_FILES['attachments']['tmp_name'] as $key => $file) {
            $attachments[] = [
                'file' => $file,
                'name' => $_FILES['attachments']['name'][$key],
                'type' => $_FILES['attachments']['type'][$key],
                'size' => $_FILES['attachments']['size'][$key]
            ];
        }

        $request->setStage('files', $attachments);
    }

    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    if ($request->getStage('html')) {
        $request->setStage('text', strip_tags($request->getStage('html')));
    }

    cradle()->trigger('gmail-send', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        cradle('global')->redirect('/control/business/deal/overview/'.$request->getStage('deal_id'));
    }

    $request->setStage('thread_subject', $request->getStage('subject'));
    $request->setStage('thread_snippet', substr($request->getStage('text'), 0, 30));
    $request->setStage('thread_gmail_id', $response->getResults('threadId'));

    $thread = Rest::i($api.'/rest/thread/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('action', 'emailed')
        ->set('user_history', $request->getSession('app_session', 'results', 'profile_id'));

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $thread->set($key, $value);
        }
    }

    $results = $thread->post();

    cradle('global')->flash('email sent successfully', 'success');
    cradle('global')->redirect('/control/business/deal/overview/'.$request->getStage('deal_id'));
});

/**
 * Gmail Reply
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/gmail/reply/mail/deal/:deal_id/thread/:threadId', function ($request, $response) {
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    // are there any files, included?
    if (!empty($_FILES['attachments']['name'][0])) {
        // fix files to be uploaded to s3
        $attachments = [];

        foreach ($_FILES['attachments']['tmp_name'] as $key => $file) {
            $attachments[] = [
                'file' => $file,
                'name' => $_FILES['attachments']['name'][$key],
                'type' => $_FILES['attachments']['type'][$key],
                'size' => $_FILES['attachments']['size'][$key]
            ];
        }

        $request->setStage('files', $attachments);
    }

    if ($request->getSession('me', 'auth_id')) {
        $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    } else {
        $request->setStage('auth_id', $request->getSession('app_session', 'results', 'auth_id'));
    }

    if ($request->getStage('html')) {
        $request->setStage('text', strip_tags($request->getStage('html')));
    }

    cradle()->trigger('gmail-send', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        cradle()->triggerRoute('get',
            '/control/business/deal/'.$request->getStage('deal_id').
            '/thread/'.$request->getStage('threadId'), $request, $response);
    }

    $request->setStage('thread_subject', $request->getStage('subject'));
    $request->setStage('thread_snippet', substr($request->getStage('text'), 0, 30));
    $request->setStage('thread_gmail_id', $request->getStage('threadId'));

    $thread = Rest::i($api.'/rest/thread/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->set('action', 'replied')
        ->set('user_history', $request->getSession('app_session', 'results', 'profile_id'));

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $thread->set($key, $value);
        }
    }

    $results = $thread->post();

    cradle('global')->flash('email sent successfully', 'success');
    cradle('global')->redirect(
        '/control/business/deal/'.$request->getStage('deal_id').
        '/thread/'.$request->getStage('threadId'));
});
