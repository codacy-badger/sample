<?php //-->
/**
 * This file is part of the Jobayan Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
use Cradle\Module\Utility\Rest;

/**
 * Process the Template Detail Request
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/ajax/control/marketing/template/detail/:template_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Process
    $template = Rest::i($api.'/rest/template/detail/'.$request->getStage('template_id'))
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $template->set($key, $value);
        }
    }

    $results = $template->get();

    if ($results['error']) {
        //it was good Set json Content
        return $response->setContent(json_encode([
            'error'      => true,
            'message'    => 'There are some errors in the form',
            'validation' => $results['validation'],
        ]));
    }

    $response->setError($results['error'])->setResults($results['results']);
});
