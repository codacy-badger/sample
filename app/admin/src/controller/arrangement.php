<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
 use Cradle\Module\Utility\File;

/**
 * Render the Job Arrangement Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/arrangement/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');
    $path = cradle('global')->path('config') . '/arrangement.php';

    //----------------------------//
    // 2. Prepare Data
    $data['arrangement'] = $this->package('global')->config('arrangement');

    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }
 
    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-job_arrangement-search page-admin';
    $data['title'] = cradle('global')->translate('Job Arrangement');
    $body = cradle('/app/admin')->template('arrangement/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    // render admin page
}, 'render-admin-page');

/**
 * Render the Job Arrangement Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/arrangement/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if (!cradle('global')->role('admin:arrangement:create', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/arrangement/search');
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
    $class = 'page-developer-job_rrangement-create page-admin';
    $data['title'] = cradle('global')->translate('Create Job Arrangement');
    $body = cradle('/app/admin')->template('arrangement/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Job arrangement Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/arrangement/update/:arrangement', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permissions
    if (!cradle('global')->role('admin:arrangement:update', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/arrangement/search');
    }

    //----------------------------//
    // 2. Prepare Data

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-arrangement-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Job Arrangement');
    $body = cradle('/app/admin')->template('arrangement/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Job arrangement Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/arrangement/create', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // Get Input data from submit
    $data = $this->package('global')->config('arrangement');
    $arrangement = $request->getStage();

    
    //if job_arrangement has no value display error
    if (!$request->getStage('arrangement_val')) {
        $errors['arrangement'] = 'Job Arrangement Name is required';
        if (!empty($errors)) {
            $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
        }
    }

    // //if job_arrangement already exist display error
    if (in_array($arrangement['arrangement_val'], $data)) {
        $errors['arrangement'] = 'Job Arrangement Name already exist';
        if (!empty($errors)) {
            $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
        }
    }

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
        return cradle()->triggerRoute('get', '/control/arrangement/create', $request, $response);
    }

    // Path job_arrangement.php
    $path = cradle('global')->path('config') . '/arrangement.php';

    // New data
    $data[] = stripslashes($request->getStage('arrangement_val'));

    // Prepare data to be put on arrangement.php
    $data = implode("',\n    '", $data);
    $str = "<?php //-->   \n return \n[ \n    '";
    $data = $str . $data . "'\n];";

    // Wright new data to job_arrangement.php
    file_put_contents($path, $data);
 
    //it was good
    //add a flash
    cradle('global')->flash('Job Arrangement was Created', 'success');

    //redirect
    cradle('global')->redirect('/control/arrangement/search');
});

/**
 * Process the Job arrangement Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/control/arrangement/update/:arrangement', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = $this->package('global')->config('arrangement');
    $arrangement = $request->getStage();

    if (!$request->getStage('arrangement_val')) {
        $errors['arrangement'] = 'Job Arrangement Name is required';
        if (!empty($errors)) {
            $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
        }
    }

    // //if job_arrangement already exist display error
    if (in_array($arrangement['arrangement_val'], $data)) {
        $errors['arrangement'] = 'Job Arrangement Name already exist';
        if (!empty($errors)) {
            $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
        }
    }

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
        return cradle()->triggerRoute('get', '/control/arrangement/create', $request, $response);
    }
   
    // Update array content
    $data[$arrangement['arrangement']] = stripslashes($arrangement['arrangement_val']);
    // Path
    $path = cradle('global')->path('config') . '/arrangement.php';

    // Prepare data to be put on arrangement.php
    $data = implode("',\n    '", $data);
    $str = "<?php //-->   \n return \n[ \n    '";
    $data = $str . $data . "'\n];";
     
    // Wright new data to arrangement.php
    file_put_contents($path, $data);

    //it was good
    //add a flash
    cradle('global')->flash('job Arrangement was Updated', 'success');

    //redirect
    $redirect = '/control/arrangement/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Process the Job arrangement Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/control/arrangement/remove/:arrangement', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    // check permission
    if (!cradle('global')->role('admin:arrangement:remove', 'admin', $request)) {
        // set flash
        cradle('global')->flash('Request not Permitted', 'danger');

        // set content
        return cradle('global')->redirect('/control/arrangement/search');
    }

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    // Get index to be delete
    $arrangement = $request->getStage();

    // Get array content
    $data = $this->package('global')->config('arrangement');
    
    // Delete row on array
    unset($data[$arrangement['arrangement']]);

    // Path
    $path = cradle('global')->path('config') . '/arrangement.php';

    // Prepare data to be put on job_arrangement.php
    $data = implode("',\n    '", $data);

    if (empty($data)) {
        echo "string";
        $str = "<?php //-->   \n return \n[ \n    ";
        $data = $str . $data . "\n];";
    } else {
        $str = "<?php //-->   \n return \n[ \n    '";
        $data = $str . $data . "'\n];";
    }
     
    // Insert new file to job_arrangement.php
    file_put_contents($path, $data);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Job Arrangement was Removed');
        cradle('global')->flash($message, 'success');
    }

    //redirect
    $redirect = '/control/arrangement/search';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
