<?php //-->

/**
 * Send Sms
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/sms/send/deal/:deal_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('sales');

    //----------------------------//
    // 2. Prepare Data
    $request->setStage('message', strip_tags($request->getStage('message')));

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('sms-send', $request, $response);
    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/control/business/deal/detail/'.$request->getStage('deal_id'));
    }

    $request->setStage('profile_id', $request->getSession('app_session', 'results', 'profile_id'));
    $request->setStage('comment_type', 'sms');
    $request->setStage('comment_detail', $request->getStage('message'));

    cradle()->trigger('comment-create', $request, $response);
    $request->setStage('comment_id', $response->getResults('comment_id'));

    cradle('global')->flash('sms sent successfully', 'success');
    cradle('global')->redirect('/control/business/deal/detail/'.$request->getStage('deal_id'));
});
