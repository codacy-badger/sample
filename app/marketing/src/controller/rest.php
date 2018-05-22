<?php //-->

use Cradle\Module\Utility\Rest;

/**
 * Render the Action Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/app/receive', function ($request, $response) {
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');
    $code = substr(
        $request->getStage('redirect_uri'),
        strpos($request->getStage('redirect_uri'), 'code=') + 5
    );

    $accept = Rest::i($api.'/rest/access')
        ->set('session_token', $code)
        ->set('code', $code)
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret'])
        ->post();

    $redirect = '/control/marketing/dashboard';
    if ($request->getStage('redirect_uri')) {
        $redirect = substr(
            $request->getStage('redirect_uri'),
            0,
            strpos($request->getStage('redirect_uri'), '?code=')
        );
    }


    if (!$accept['error'] && isset($accept['results'])) {
        $_SESSION['rest'] = $accept['results'];

        $_SESSION['app_session'] = Rest::i($api.'/rest/profile/detail/'.$_SESSION['rest']['profile_id'])
            ->get();
    } else {
        $redirect = '/';
        cradle('global')->flash($accept['message'], 'danger');
    }


    cradle('global')->redirect($redirect);
});
