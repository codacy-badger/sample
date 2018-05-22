<?php //-->
use Cradle\Module\Utility\Rest;

/**
 * Render the Dashboard Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/marketing/dashboard', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();
    
    //set default type to company
    if (!$request->hasStage('type')) {
        $request->setStage('type', 'poster');
        $data['type'] = 'poster';
    } else {
        $data['type'] = $request->getStage('type');
        $request->setStage('type', $request->getStage('type'));
    }

    if (($request->hasStage('date', 'start') && empty($request->getStage('date', 'start')))) {
        $request->removeStage('date');
    }

    if (($request->hasStage('date', 'end') && empty($request->getStage('date', 'end')))) {
        $request->removeStage('date');
    }

    if ($request->hasStage('date')) {
        $request->setStage('chartFilter', 'date', 'start', $data['date']['start']);
        $request->setStage('chartFilter', 'date', 'end', $data['date']['end']);
    }

    //disable redis
    $request->setGet('nocache', 1);

    //this is an ajax request of the page
    if ($request->hasStage('chart')) {
        
        //signup query
        $dash = Rest::i($api.'/rest/marketing/dashboard/charts')
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->set('chart', $request->getStage('chart'))
            ->set('type', $data['type']);

        if ($request->getStage()) {
            foreach ($request->getStage() as $key => $value) {
                $dash->set($key, $value);
            }
        }
       
        $dash = $dash->get();
        
        $results = [];
        
        if (isset($dash['results'])) {
            $results = $dash['results'] ? $dash['results']: [];
        }
        
        $labels  = [];
        $data    = [];
        $total   = 0;
        $day = '';
        if (!empty($results)) {
            foreach ($results as $index => $result) {
                if (isset($result['day'])) {
                    $day = $result['day'] . ' ';
                }

                $labels[] = $day . substr($result['month'], 0, 3) . ' ' . $result['year'];
                $data[]   = $result['total'];
                $total    += $result['total'];
            }
        }

        unset($data['type']);

        echo json_encode(
            [
                'labels' => $labels,
                'data'   => $data,
                'total'  => number_format($total, '0', '.', ','),
            ]
        );

        $response->setHeader('Content-Type', 'application/json');
        die;
    }

    //Export CSV
    if ($request->hasStage('export')) {

        $export = Rest::i($api.'/rest/marketing/dashboard/export')
            ->set('client_id', $app['token'])
            ->set('client_secret', $app['secret'])
            ->set('type', $data['type']);

        if ($request->getStage()) {
            foreach ($request->getStage() as $key => $value) {
                $export->set($key, $value);
            }
        }

        $export = $export->get();

        $results = [];

        if (isset($export['results'])) {
            $results = $export['results'];
        }

        //Set Filename
        $filename = $request->getStage('type') . '-' . date("Y-m-d");

        $header = [
            'type'  => 'Data',
            'total' => 'Total'
        ];

        $rows = array();
        foreach ($results as $index => $value) {
            $rows[] = array(
                'type'  => $index,
                'total' => $value
            );
        }

        // Checks if there is a date range
        if ($request->hasStage('date')) {
            $date = $request->getStage('date');
            $type = $request->getStage('type');
            $filename = $type . '-' . $date['start'] . ' to ' . $date['end'];
        }
        
        $request->setStage('filename', 'Dashboard-'.$filename.".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $rows);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-marketing-dashboard';
    $body  = cradle('/app/marketing')->template('dashboard', $data);

    //Set Content
    $response
        ->setPage('title', 'Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');

/**
 * Render the Template One Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/marketing/template-one', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //must be admin
    // cradle('global')->requireLogin('admin');
    cradle('global')->requireRestLogin('marketing');
    $app = cradle('global')->config('services', 'jobayan_app');
    $api = cradle('global')->config('settings', 'api');

    //----------------------------//
    // 2. Prepare Data
    $data = [];

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-template-one';
    $body  = cradle('/app/marketing')->template('template1', $data);

    //hSet Content
    $response
        ->setPage('title', 'Template One - Marketing Dashboard')
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-marketing-page');
 