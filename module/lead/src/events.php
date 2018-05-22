<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Lead\Service as LeadService;
use Cradle\Module\Lead\Validator as LeadValidator;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Lead Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = LeadValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['lead_birth'])) {
        $data['lead_birth'] = date('Y-m-d', strtotime($data['lead_birth']));
    }

    if(isset($data['lead_tags'])) {
        $data['lead_tags'] = json_encode($data['lead_tags']);
    }

    if(isset($data['lead_campaigns'])) {
        $data['lead_campaigns'] = json_encode($data['lead_campaigns']);
    }

    if (isset($data['lead_phone'])) {
        $data['lead_phone'] = str_replace('-', '', $data['lead_phone']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    //save lead to database
    $results = $leadSql->create($data);

    if (isset($results['lead_company']) && $results['lead_company']) {
        // create deal
        $request->setStage('deal_name', $data['lead_company']);
        $request->setStage('deal_close', date('Y-m-d', strtotime('+3 months')));
        $request->setStage('deal_type', 'lead');
        $request->setStage('deal_company', $results['lead_id']);
        if (!isset($data['pipeline_id'])) {
            $this->trigger('pipeline-search', $request, $response);
            $pipeline = $response->getResults('rows');
            $pipeline = $pipeline[0];
            $request->setStage('pipeline_id', $pipeline['pipeline_id']);
            $request->setStage('deal_status', $pipeline['pipeline_stages'][0]);
        }

        $this->trigger('deal-create', $request, $response);
    }

    //index lead
    // $leadElastic->create($results['lead_id']);

    //invalidate cache
    $leadRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Lead Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['lead_id'])) {
        $id = $data['lead_id'];
    } else if (isset($data['lead_email'])) {
        $id = $data['lead_email'];
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
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $leadRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $leadElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $leadSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $leadRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Lead Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the lead detail
    $this->trigger('lead-detail', $request, $response);

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
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    //save to database
    $results = $leadSql->update([
        'lead_id' => $data['lead_id'],
        'lead_active' => 0
    ]);

    //remove from index
    // $leadElastic->remove($data['lead_id']);

    //invalidate cache
    $leadRedis->removeDetail($data['lead_id']);
    $leadRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Lead Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the lead detail
    $this->trigger('lead-detail', $request, $response);

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
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    //save to database
    $results = $leadSql->update([
        'lead_id' => $data['lead_id'],
        'lead_active' => 1
    ]);

    //create index
    // $leadElastic->create($data['lead_id']);

    //invalidate cache
    $leadRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Lead Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-search', function ($request, $response) {
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
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $leadRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            // $results = $leadElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $leadSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $leadRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Lead Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the lead detail
    $this->trigger('lead-detail', $request, $response);
    $lead = $response->getResults();

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = $request->getStage();

    //remove tags (in update)
    if (isset($data['remove_lead_tags']) && $data['remove_lead_tags']) {
        $data['lead_tags'] = [];
    }

    // add tags
    if (isset($data['add_tags'])) {
        $data['lead_tags'] = array_unique(array_merge(
            $lead['lead_tags'],
            $data['add_tags']
        ));
    }

    // remove tag
    if (isset($data['remove_tags'])) {
        $data['lead_tags'] = array_diff(
            $lead['lead_tags'],
            $data['remove_tags']
        );
    }

    //remove campaigns (in update)
    if (isset($data['remove_lead_campaigns']) && $data['remove_lead_campaigns']) {
        $data['lead_campaigns'] = [];
    }

    // add campaign tags
    if (isset($data['add_campaigns'])) {
        $data['lead_campaigns'] = array_unique(array_merge(
            $lead['lead_campaigns'],
            $data['add_campaigns']
        ));
    }

    // remove campaign tag
    if (isset($data['remove_campaigns'])) {
        $data['lead_campaigns'] = array_diff(
            $lead['lead_campaigns'],
            $data['remove_campaigns']
        );
    }


    if (!isset($data['lead_name'])) {
        $data['lead_name'] = $lead['lead_name'];
    }

    //----------------------------//
    // 2. Validate Data
    $errors = LeadValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['lead_birth'])) {
        $data['lead_birth'] = date('Y-m-d', strtotime($data['lead_birth']));
    }

    if(isset($data['lead_tags'])) {
        $data['lead_tags'] = json_encode($data['lead_tags']);
    }

    if(isset($data['lead_campaigns'])) {
        $data['lead_campaigns'] = json_encode($data['lead_campaigns']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    //save lead to database
    $results = $leadSql->update($data);

    //index lead
    // $leadElastic->update($response->getResults('lead_id'));

    //invalidate cache
    $leadRedis->removeDetail($response->getResults('lead_id'));
    $leadRedis->removeSearch();

    // update attached deal detail
    if (isset($lead['deal_id']) && $lead['deal_id']) {
        $request->setStage('deal_id', $lead['deal_id']);
        $this->trigger('deal-update', $request, $response);
    }

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Move Profile Image to S3 (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-unsubscribe', function ($request, $response) {
    $data = $request->getStage();

    // update type _active to 0
    // add unsubsribe to tags
    $request->setStage('lead_active', 0);
    $request->setStage('add_tags', ['unsubscribed']);
    cradle()->trigger('lead-update', $request, $response);

    if ($response->isError()) {
        return $response->setError(true, 'Invalid Request');
    }

    // update campaign unsubsribe count
    $campaignRequest = new Request();
    $campaignResponse = new Response();

    $campaignRequest->setStage(
        'filter',
        ['campaign_message_id' => $data['message_id']]
    );

    cradle()->trigger('campaign-search', $campaignRequest, $campaignResponse);
    $campaign = $response->getResults('rows');

    if ($campaign) {
        $campaign = $campaign[0];

        $campaignRequest->setStage('campaign_id', $campaign['campaign_id']);
        $campaignRequest->setStage('field', 'unsubscribed');
        cradle()->trigger('campaign-update', $request, $response);
    }

    $response->setError(false);
});

/**
 * Update Lead Bounce
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-update-bounce', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the subscriber detail
    $this->trigger('lead-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    $lead = $response->getResults();

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    //this/these will be used a lot
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    // if soft bounce update flag
    if($data['bounce_type'] == 'Transient') {
        //save profile to database
        $request->setStage('lead_bounce', $lead['lead_bounce'] + 1);
        $this->trigger('lead-update', $request, $response);

        // if soft bounced 2 times already plus this transaction
        // it means it has bounced 3 times, so we'll unsubscribe it
        if($lead['lead_bounce'] >= 2) {
            $this->trigger('lead-unsubscribe', $request, $response);
        }
    }

    // if permanent remove profile
    if($data['bounce_type'] == 'Permanent') {
        $this->trigger('lead-unsubscribe', $request, $response);
    }

    $results = $response->getResults();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Template Bulk Action Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('lead-bulk-action', function ($request, $response) {
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
    $leadSql = LeadService::get('sql');
    $leadRedis = LeadService::get('redis');
    // $leadElastic = LeadService::get('elastic');

    //save to database
    $results = $leadSql->bulkAction(
        $data['bulk_ids'],
        $data['bulk_value'],
        $data['bulk_field']
    );

    foreach ($data['bulk_ids'] as $id) {
        //remove from index
        // $leadElastic->remove($id);

        //invalidate cache
        $leadRedis->removeDetail($id);
        $leadRedis->removeSearch();
    }

    $response->setError(false)->setResults($results);
});
