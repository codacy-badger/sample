<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Transaction Search Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:transaction:listing', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/dashboard');
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    if (!$request->hasStage('filter')) {
        $request->setStage('filter', 'transaction_active', '1');
    }

    //filter possible filter options
    //we do this to prevent SQL injections

    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'transaction_active',
            'transaction_status',
            'transaction_payment_method',
            'profile_id',
            'profile_name'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'transaction_total',
            'transaction_credits',
            'transaction_created',
            'transaction_updated',
            'transaction_paid_date'
        ];

        // Loops through the orders
        foreach ($request->getStage('order') as $key => $direction) {
            // Checks if the sorting value is not in the allowed sorting
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } elseif ($direction !== 'ASC' && $direction !== 'DESC') {
                // Checks if the sorting
                $request->removeStage('order', $key);
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
            'end_date'   => $data['date']['end']
        ];
    }

    if (isset($date)) {
        $request->setStage('groupDate', ['transaction_created' => $date]);
    }

    $request->setGet('noindex', true);

    //trigger job
    $request->setStage('range', 0);
    cradle()->trigger('transaction-search', $request, $response);
    $transactions = $response->getResults();

    if ($transactions['rows']) {
        //fix profile data
        foreach ($transactions['rows'] as $index => $transaction) {
            $transactions['rows'][$index]['profile_name'] = '';
            //assign profile name to field profile_name
            if (!empty($transaction['transaction_profile']['profile_name'])) {
                $transactions['rows'][$index]['profile_name'] = $transaction['transaction_profile']['profile_name'];
            }
        }
    }

    $data = array_merge($request->getStage(), $transactions);

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
            'transaction_updated'           => 'Updated',
            'transaction_paid_date'         => 'Paid Date'
        ];

        //Set Filename
        $request->setStage('filename', 'Transactions-' . date("Y-m-d") . ".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        // check permission
        if (!cradle('global')->role('admin:transaction:export', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/transaction/search');
        }

        cradle()->trigger('csv-export', $request, $response);

        $value = [
            'GET'    => $request->getStage(),
            'POST'   => $request->getPost(),
            'SERVER' => $_SERVER
        ];
        $request->setStage('transaction_id', $response->getResults('transaction_id'));
        $request->setStage('profile_id', $_SESSION['me']['profile_id']);
        $request->setStage(
            'history_note',
            'Profile id #' . $_SESSION['me']['profile_id'] . ' exported a file transactions'
        );
        $request->setStage('history_attribute', 'transaction-export');
        $request->setStage('history_value', $value);

        cradle()->trigger('history-create', $request, $response);
        exit;
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-admin-transaction-search page-admin';
    $data['title'] = cradle('global')->translate('Transactions');
    $body          = cradle('/app/admin')->template('transaction/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Transaction Create Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/create/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:transaction:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/transaction/search');
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-developer-transaction-create page-admin';
    $data['title'] = cradle('global')->translate('Create Transaction');
    $body          = cradle('/app/admin')->template('transaction/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Transaction Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/update/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    if (!$request->hasStage('view')) {
        // check permission
        if (!cradle('global')->role('admin:transaction:update', 'admin', $request)) {
            // set flash
            cradle('global')->flash('Request not Permitted', 'danger');

            // set content
            return cradle('global')->redirect('/control/transaction/search');
        }
    }

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        cradle()->trigger('transaction-detail', $request, $response);

        //can we update ?
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');

            return cradle('global')->redirect('/control/transaction/search');
        }

        $data['item'] = $response->getResults();
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class         = 'page-developer-transaction-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Transaction');

    if ($request->hasStage('view')) {
        $data['view']  = true;
        $data['title'] = cradle('global')->translate('Transaction Id #' .
            $data['item']['transaction_id']);
    }
    $body = cradle('/app/admin')->template('transaction/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');


/**
 * Process the Transaction Detail Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/detail/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:transaction:view', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/transaction/search');
    }

    $request->setStage('view', true);

    return cradle()->triggerRoute(
        'get',
        sprintf(
            '/control/transaction/update/%s',
            $request->getStage('transaction_id')
        ),
        $request,
        $response
    );
});


/**
 * Process the Transaction Create Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/transaction/create/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if transaction_status has no value use the default value
    if ($request->hasStage('transaction_status') && !$request->getStage('transaction_status')) {
        $request->setStage('transaction_status', 'pending');
    }

    if ($request->hasStage('transaction_status') && ($request->getStage('transaction_status') == 'verified') ||
        ($request->getStage('transaction_status') == 'complete')) {
        $request->setStage('transaction_paid_date', date('Y-m-d H:i:s'));
    }

    //if transaction_payment_method has no value make it null
    if ($request->hasStage('transaction_payment_method') && !$request->getStage('transaction_payment_method')) {
        $request->setStage('transaction_payment_method', null);
    }

    //if transaction_payment_reference has no value make it null
    if ($request->hasStage('transaction_payment_reference') && !$request->getStage('transaction_payment_reference')) {
        $request->setStage('transaction_payment_reference', null);
    }

    //if transaction_statement has no value make it null
    if ($request->hasStage('transaction_statement') && !$request->getStage('transaction_statement')) {
        $request->setStage('transaction_statement', '');
    }

    //transaction_profile is disallowed
    $request->removeStage('transaction_profile');

    //transaction_type is disallowed
    $request->removeStage('transaction_type');

    //transaction_flag is disallowed
    $request->removeStage('transaction_flag');

    if (!$request->hasStage('profile_id')) {
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    }

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $profile = $request->getStage('profile_id');

        return cradle()->triggerRoute('get', '/control/transaction/create/' . $profile, $request, $response);
    }

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('transaction_id', $response->getResults('transaction_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage('history_note', 'Profile id #' . $_SESSION['me']['profile_id'] . ' created transaction');
    $request->setStage('history_attribute', 'transaction-create');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Transaction was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/transaction/search');
});

/**
 * Process the Transaction Update Page
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->post('/control/transaction/update/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //if transaction_status has no value use the default value
    if ($request->hasStage('transaction_status') && !$request->getStage('transaction_status')) {
        $request->setStage('transaction_status', 'pending');
    }

    if ($request->hasStage('transaction_status') && ($request->getStage('transaction_status') == 'verified') ||
        ($request->getStage('transaction_status') == 'complete')) {
        $request->setStage('transaction_paid_date', date('Y-m-d H:i:s'));
    }

    //if transaction_payment_method has no value make it null
    if ($request->hasStage('transaction_payment_method') && !$request->getStage('transaction_payment_method')) {
        $request->setStage('transaction_payment_method', null);
    }

    //if transaction_payment_reference has no value make it null
    if ($request->hasStage('transaction_payment_reference') && !$request->getStage('transaction_payment_reference')) {
        $request->setStage('transaction_payment_reference', null);
    }

    //transaction_profile is disallowed
    $request->removeStage('transaction_profile');

    //transaction_currency is disallowed
    $request->removeStage('transaction_currency');

    //transaction_total is disallowed
    $request->removeStage('transaction_total');

    //transaction_credits is disallowed
    $request->removeStage('transaction_credits');

    //transaction_type is disallowed
    $request->removeStage('transaction_type');

    //transaction_flag is disallowed
    $request->removeStage('transaction_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $route = '/control/transaction/update/' . $request->getStage('transaction_id');

        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('transaction_id', $response->getResults('transaction_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id']
        . ' updated transaction id #' . $response->getResults('transaction_id')
    );
    $request->setStage('history_attribute', 'transaction-update');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('Transaction was Updated', 'success');

    //redirect
    cradle('global')->redirect('/control/transaction/search');
});

/**
 * Process the Transaction Remove
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/remove/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:transaction:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/transaction/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-remove', $request, $response);

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('transaction_id', $response->getResults('transaction_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id']
        . ' removed transaction id #' . $response->getResults('transaction_id')
    );
    $request->setStage('history_attribute', 'transaction-remove');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Transaction was Removed');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/transaction/search');
});

/**
 * Process the Transaction Restore
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->get('/control/transaction/restore/:transaction_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:transaction:restore', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/transaction/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('transaction-restore', $request, $response);

    $value = [
        'GET'    => $request->getStage(),
        'POST'   => $request->getPost(),
        'SERVER' => $_SERVER
    ];
    $request->setStage('transaction_id', $response->getResults('transaction_id'));
    $request->setStage('profile_id', $_SESSION['me']['profile_id']);
    $request->setStage(
        'history_note',
        'Profile id #' . $_SESSION['me']['profile_id']
        . ' restored transaction id #' . $response->getResults('transaction_id')
    );
    $request->setStage('history_attribute', 'transaction-restore');
    $request->setStage('history_value', $value);

    cradle()->trigger('history-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Transaction was Restored');
        cradle('global')->flash($message, 'success');
    }

    cradle('global')->redirect('/control/transaction/search');
});
