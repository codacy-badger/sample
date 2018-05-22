<?php //-->

// These pages are for viewing/searching purposes only
use Cradle\Module\Utility\Rest;

/**
 * Render the Transaction Search Page
 *
 * @param Request $request
 * @param Response $response
 */
 $cradle->get('/control/business/dashboard', function ($request, $response) {
     //----------------------------//
     // 1. Route Permissions
     //only for admin
     cradle('global')->requireRestLogin('business');
     $app = cradle('global')->config('services', 'jobayan_app');
     $api = cradle('global')->config('settings', 'api');

     //----------------------------//
     // 2. Prepare Data
     $data = $request->getStage();

     // get recently added leads
     $leads = Rest::i($api.'/rest/lead/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 5)
         ->set('sales', true)
         ->set('order', ['lead_created' => 'DESC'])
         ->get();

     $leads = $leads['results'] ? $leads['results']['rows']: [];
     $data['leads'] = $leads;

     // get recently added profiles
     $profiles = Rest::i($api.'/rest/profile/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 5)
         ->set('sales', true)
         ->set('order', ['profile_created' => 'DESC'])
         ->get();

     $profiles = $profiles['results'] ? $profiles['results']['rows']: [];
     $data['profiles'] = $profiles;

     // get recent activities
     $history = Rest::i($api.'/rest/history/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 5)
         ->set('sales', true)
         ->set('filter_not', ['history_type' => 'create'])
         ->set('order', ['history_created' => 'DESC'])
         ->get();

     $history = $history['results'] ? $history['results']['rows']: [];
     $data['activities'] = $history;

     // get recent transactions
     $transactions = Rest::i($api.'/rest/transaction/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 5)
         ->set('sales', true)
         ->set('order', ['transaction_created' => 'DESC'])
         ->get();

     $transactions = $transactions['results'] ? $transactions['results']['rows']: [];
     $data['transactions'] = $transactions;

     // get deal summary
     $summary = Rest::i($api.'/rest/deal/summary')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 10)
         ->set('sales', true)
         ->get();

     $summary = $summary['results'] ? $summary['results']: [];
     $data['summary'] = $summary;

     // get upcoming events
     $events = Rest::i($api.'/rest/event/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret'])
         ->set('range', 5)
         ->set('sales', true)
         ->set('upcoming', true)
         ->get();

     $events = $events['results'] ? $events['results']['rows']: [];
     $data['events'] = $events;
     $data['next_week'] = date('Y-m-d', strtotime('+1 week'));

     //----------------------------//
     // 3. Render Template
     $class = 'page-sales-dashboard page-sales';
     $data['title'] = cradle('global')->translate('Business Dashboard');
     $body = cradle('/app/sales')->template('view/dashboard', $data);

     //set content
     $response
         ->setPage('title', $data['title'])
         ->setPage('class', $class)
         ->setContent($body);

     //render page
 }, 'render-sales-page');

/**
 * Render the Transaction Search Page
 *
 * @param Request $request
 * @param Response $response
 */
 $cradle->get('/control/business/activity/timeline', function ($request, $response) {
      //----------------------------//
      // 1. Route Permissions
      //only for admin
      cradle('global')->requireRestLogin('business');
      $app = cradle('global')->config('services', 'jobayan_app');
      $api = cradle('global')->config('settings', 'api');

      //----------------------------//
      // 2. Prepare Data
      if (!$request->hasStage('range')) {
          $request->setStage('range', 6);
      }

      $data = Rest::i($api.'/rest/history/search')
          ->set('client_id', $app['token'])
          ->set('client_secret', $app['secret'])
          ->set('order', ['history_created' => 'DESC']);

      if ($request->getStage()) {
          foreach ($request->getStage() as $key => $value) {
              if ($key == 'display') {
                  continue;
              }

              $data->set($key, $value);
          }
      }

      if ($request->getStage('display')) {
          if ($request->getStage('display') == 'own') {
              $me = $request->getSession('app_session', 'results', 'profile_id');
              $data->set('filter', ['profile_id' => $me]);
          } else {
              $data->set('filter', ['deal_type' => $request->getStage('display')]);
          }
      }

      $data = $data->get();

      if ($data['error'] && isset($data['message'])) {
          //add a flash
          cradle('global')->flash($data['message'], 'danger');
          return cradle('global')->redirect('/control/business/dashboard');
      }

      $data = $data['results'] ? $data['results'] : [];

      $data = array_merge($request->getStage(), $data);

      //----------------------------//
      // 3. Render Template
      $class = 'page-sales-activity-timeline page-sales';
      $data['title'] = cradle('global')->translate('Activities');
      $body = cradle('/app/sales')->template('view/timeline', $data);

      //set content
      $response
          ->setPage('title', $data['title'])
          ->setPage('class', $class)
          ->setContent($body);

      //render page
  }, 'render-sales-page');

/**
 * Render the Transaction Search Page
 *
 * @param Request $request
 * @param Response $response
 */
 $cradle->get('/control/business/transaction/search', function ($request, $response) {
     //----------------------------//
     // 1. Route Permissions
     //only for admin
     cradle('global')->requireRestLogin('business');
     $app = cradle('global')->config('services', 'jobayan_app');
     $api = cradle('global')->config('settings', 'api');

     //----------------------------//
     // 2. Prepare Data
     if (!$request->hasStage('range')) {
         $request->setStage('range', 50);
     }

     //filter possible filter options
     //we do this to prevent SQL injections

     if (is_array($request->getStage('filter'))) {
         $filterable = [
             'transaction_active',
             'transaction_status',
             'transaction_payment_method',
             'profile_id'
         ];

         foreach ($request->getStage('filter') as $key => $value) {
             if (!in_array($key, $filterable) || empty($value)) {
                 $request->removeStage('filter', $key);
             }
         }
     }

     // Checks for export action
     if ($request->hasStage('export')) {
         $request->setStage('export', '1');
         $request->setGet('noindex', true);
     }
     $data = $request->getStage();

     if (isset($data['date']['start']) && $data['date']['end']) {
         $date = [
             'start_date' => $data['date']['start'],
             'end_date' => $data['date']['end']
         ];
     }


     if (isset($date)) {
         $request->setStage('groupDate', ['transaction_created' => $date]);
     }

     //trigger job
     $transactions = Rest::i($api.'/rest/transaction/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret']);

     if ($request->getStage()) {
         foreach ($request->getStage() as $key => $value) {
             $transactions->set($key, $value);
         }
     }

     $transactions = $transactions->get();

     $data = array_merge($request->getStage(), $transactions['results']);

     // Export CSV
     if ($request->hasStage('export')) {
         //Set CSV header
         $header = [
             'transaction_status'            => 'Transaction Status',
             'transaction_payment_method'    => 'Transaction Method',
             'transaction_payment_reference' => 'Transaction Reference',
             'profile_name'                  => 'Profile',
             'transaction_total'             => 'Total',
             'transaction_credits'           => 'Credits',
             'transaction_created'           => 'Created',
             'transaction_updated'           => 'Updated'
         ];

         //Set Filename
         $request->setStage('filename', 'Transactions-'.date("Y-m-d").".csv");
         $request->setStage('header', $header);
         $request->setStage('csv', $data['rows']);
         cradle()->trigger('csv-export', $request, $response);
         exit;
     }

     //----------------------------//
     // 3. Render Template
     $class = 'page-sales-transaction-search page-sales';
     $data['title'] = cradle('global')->translate('Transactions');
     $body = cradle('/app/sales')->template('view/transaction', $data);

     //set content
     $response
         ->setPage('title', $data['title'])
         ->setPage('class', $class)
         ->setContent($body);

     //render page
 }, 'render-sales-page');

 /**
  * Render the Service Search Page
  *
  * @param Request $request
  * @param Response $response
  */
 $cradle->get('/control/business/service/search', function ($request, $response) {
     //----------------------------//
     // 1. Route Permissions
     //only for admin
     cradle('global')->requireRestLogin('business');
     $app = cradle('global')->config('services', 'jobayan_app');
     $api = cradle('global')->config('settings', 'api');

     //----------------------------//
     // 2. Prepare Data
     if (!$request->hasStage('range')) {
         $request->setStage('range', 50);
     }

     // Checks for export action
     if ($request->hasStage('export')) {
         $request->setStage('export', '1');
         $request->setGet('noindex', true);
     }

     //trigger job
     $services = Rest::i($api.'/rest/service/search')
         ->set('client_id', $app['token'])
         ->set('client_secret', $app['secret']);

     if ($request->getStage()) {
         foreach ($request->getStage() as $key => $value) {
             $services->set($key, $value);
         }
     }

     $services = $services->get();

     $data = array_merge($request->getStage(), $services['results']);

     //Export CSV
     if ($request->hasStage('export')) {
         //Set CSV header
         $header = [
             'profile_company' => 'Company Name',
             'profile_name'    => 'Profile Name',
             'service_name'    => 'Service Name',
             'service_credits' => 'Credits',
             'service_created' => 'Date Used'
         ];

         //Set Filename
         $request->setStage('filename', 'Services-'.date("Y-m-d").".csv");
         $request->setStage('header', $header);
         $request->setStage('csv', $data['rows']);
         cradle()->trigger('csv-export', $request, $response);
         exit;
     }

     //----------------------------//
     // 3. Render Template
     $class = 'page-admin-service-search page-admin';
     $data['title'] = cradle('global')->translate('Services');
     $body = cradle('/app/sales')->template('view/service', $data);

     //set content
     $response
         ->setPage('title', $data['title'])
         ->setPage('class', $class)
         ->setContent($body);

     //render page
 }, 'render-sales-page');
