<?php //-->

use Cradle\Module\Utility\File;
use Cradle\Module\Utility\Rest;
use Cradle\Module\Utility\Validator;

/**
 * Process the Deal Create Request
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/business/deal/:deal_id/note/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('business');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $request->setStage('profile_id', $request->getSession('app_session', 'results', 'profile_id'));
    $request->setStage('comment_type', 'note');
    //----------------------------//
    // 3. Process Request
    $data = Rest::i($api.'/rest/comment/create')
        ->set('client_id', $app['token'])
        ->set('client_secret', $app['secret']);

    if ($request->getStage()) {
        foreach ($request->getStage() as $key => $value) {
            $data->set($key, $value);
        }
    }

    $results = $data->post();
    //----------------------------//
    // 4. Interpret Results
    if ($results['error']) {
        $route = '/control/business/deal/overview/'.$request->getStage('deal_id');

        foreach ($results['validation'] as $validation) {
            cradle('global')->flash($validation, 'danger');
        }

        //redirect
        cradle('global')->redirect($route);
    }

    //----------------------------//
    // 4. Interpret Results
    // are there any files, included?
    if (!empty($_FILES['attachments']['name'][0])) {
        // fix files to be uploaded to s3
        $attachments = [];

        foreach ($_FILES['attachments']['tmp_name'] as $key => $file) {
            $attachments[] = [
                'file' => 'data:'.$_FILES['attachments']['type'][$key].
                ';base64,'.base64_encode(file_get_contents($file)),
                'name' => $_FILES['attachments']['name'][$key],
                'type' => $_FILES['attachments']['type'][$key]
            ];
        }

        $config = $this->package('global')->service('s3-main');
        foreach ($attachments as $file) {
            $link = File::base64ToS3($file['file'], $config);

            // if link was converted to a link/ valid url that means it
            // was successfully uploaded to S3
            if (Validator::isUrl($link)) {
                Rest::i($api.'/rest/file/create')
                    ->set('client_id', $app['token'])
                    ->set('client_secret', $app['secret'])
                    ->set('comment_id', $results['results']['comment_id'])
                    ->set('file_link', $link)
                    ->set('file_type', $file['type'])
                    ->set('file_name', $file['name'])
                    ->post();
            }
        }
    }

    //it was good
    //add a flash
    cradle('global')->flash('Note was Added', 'success');

    //redirect
    cradle('global')->redirect('/control/business/deal/overview/'.$request->getStage('deal_id'));
});
