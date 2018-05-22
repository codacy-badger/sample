<?php //-->

use Spipu\Html2Pdf\Html2Pdf;
use Cradle\Module\Utility\File;

/**
 * Render the Tracking Post Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/post/search', function ($request, $response) {
   //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_position',
            'application_count'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_position'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    //trigger job
    cradle()->trigger('post-tracking-job-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    // set the data
    $data = array_merge(
        $request->getStage(),
        $response->getResults()
    );

    // check for rows
    if ($data['rows']) {
        foreach ($data['rows'] as $index => $post) {
            // check if appliction already submitted
            $request->setStage('post_id', $post['post_id']);
            cradle()->trigger('post-seeker-toinform', $request, $response);
            $informResults = $response->getResults();

            //match the people who like and people who has already have an applicant record
            if (!empty($informResults['profileHasApplicant'])) {
                foreach ($informResults['profileWhoLiked'] as $key => $profileId) {
                    if (!in_array($profileId, $informResults['profileHasApplicant'])) {
                        $data['rows'][$index]['inform'] = 1;
                    }
                }
            }
        }
    }

    // reset stage
    $request->removeStage('profile_id');
    $request->removeStage('order');

    // profile id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Set range to 0
    $request->setStage('range', 0);
    $request->removeStage('q');
    $request->setStage('filter', 'form_flag', 1);

    // get all forms
    cradle()->trigger('form-search', $request, $response);
    $data['forms'] = $response->getResults('rows');

    if ($data['forms']) {
         $data['form_flag'] = array_sum(
             array_column($data['forms'], 'form_flag')
         );
    }

    // check for template and class
    $template = '/tracking/post/search';
    $class = 'page-tracking-post-search branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/form_confirmation',
            'profile_menu',
            'profile_alert'
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Post Detail Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/post/detail/:post_id', function ($request, $response) {
   //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'profile_name',
            'applicant_created'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'applicant_status'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    $request->setStage(
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    //post_id
    $request->setStage(
        'filter',
        'post_id',
        $data['post_id']
    );

    $request->setGet('noindex', 1);
    $request->setGet('nocache', 1);

    //trigger job
    cradle()->trigger('profile-applicant-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/tracking/post/search');
    }

    $data = array_merge(
        $request->getStage(),
        $response->getResults()
    );

    // Gets the post detail
    cradle()->trigger('post-detail', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/tracking/post/search');
    }

    $data['detail'] = $response->getResults();

    cradle()->trigger('form-post', $request, $response);

    $data['form'] = $response->getResults();

    $request->removeStage();

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    $request->setGet('noindex', 1);
    $request->setGet('nocache', 1);

    cradle()->trigger('label-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    if (empty($response->getResults('rows'))) {
        $data['label'] = array();
        $data['label']['label_custom'] = array();
    } else {
        $data['label'] = $response->getResults('rows')[0];
    }

    // check for profile address
    if (isset($data['rows'])) {
        foreach ($data['rows'] as $a => $applicant) {
            if ($applicant['profile_address_street'] ||
                $applicant['profile_address_city'] ||
                $applicant['profile_address_state'] ||
                $applicant['profile_address_country'] ||
                $applicant['profile_address_postal']) {
                    $data['rows'][$a]['profile_address'] = true;
            }

            $data['rows'][$a]['label_custom'] = $data['label']['label_custom'];
        }
    }

    // get the information
    if ($data['rows'] && !empty($data['rows'])) {
        foreach ($data['rows'] as $r => $row) {
            $request->setStage('profile_id', $row['profile_id']);

            // trigger the job
            cradle()->trigger('profile-information', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_information'] = $response->getResults();
            }

            // trigger the job
            cradle()->trigger('profile-resume', $request, $response);

            // check for results
            if ($response->getResults()) {
                $data['rows'][$r]['profile_resume'] = $response->getResults();
            }
        }
    }

    // check for template and class
    $template = '/tracking/post/detail';
    $class = 'page-tracking-post-detail branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial_applicantactions',
            'partial/form_confirmation',
            'partial_elementclone',
            'profile_alert',
            'profile_menu',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Post Form Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/post/form/:post_id/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //trigger job
    cradle()->trigger('applicant-view-form', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $data = array_merge(
        $request->getStage(),
        $response->getResults('rows')
    );

    // decode applicant_status
    $data['applicant_status'] = json_decode($data['applicant_status'], true);

    if (!empty($data['questions'])) {
        // Loops through the questions
        foreach ($data['questions'] as $index => $question) {
            $data['questions'][$index]['question_custom'] = false;
            $data['questions'][$index]['question_file'] = false;

            // convert question type to array
            $question['question_type'] = explode(',', $question['question_type']);

            // Checks for question_type = custom
            if (in_array('custom', $question['question_type'])) {
                $data['questions'][$index]['question_custom'] = true;
            }

            // Checks for question_type = file
            if (in_array('file', $question['question_type'])) {
                $data['questions'][$index]['question_file'] = true;
            }

            // Checks if there is an answer
            if (isset($question['answer_name']) && !empty($question['answer_name'])) {
                if (in_array($question['answer_name'], $question['question_choices'])) {
                    $data['questions'][$index]['question_custom'] = false;
                }
            }
        }
    }

    // check for profile address
    if ($data['profile_address_street'] ||
        $data['profile_address_city'] ||
        $data['profile_address_state'] ||
        $data['profile_address_country'] ||
        $data['profile_address_postal']) {
            $data['profile_address'] = true;
    }

    // check for download form
    if ($request->hasGet('download')) {
        //Render body
        $download = cradle('/app/www')->template(
            'tracking/post/download',
            $data
        );

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'fr');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->pdf->SetTitle('Jobayan - Application Tracking System | '.$data['form_name']);
            $html2pdf->writeHTML($download);
            $html2pdf->output($data['form_name'].'.pdf');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();
            $formatter = new ExceptionFormatter($e);
            cradle('global')->flash($formatter->getHtmlMessage(), 'danger');
            return cradle('global')->redirect('/profile/tracking/post/form/'.$data['post_id']);
        }
    }

    // check for template and class
    $template = '/tracking/post/form';
    $class = 'page-tracking-post-form branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'profile_menu',
            'profile_alert'
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Application Poster Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/application/poster/update/:form_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    // Checks if there are isorder
    if (!$request->hasStage('order')) {
        // Set default order
        $request->setStage('order', 'question_priority', 'ASC');
    }

    //if no item
    if (empty($data['item'])) {
        // Gets the form detail
        // Based on form_id
        cradle()->trigger('form-detail', $request, $response);

        // Checks if there are errors
        if ($response->isError()) {
            // At this point, the form does not exist
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/profile/tracking/application/search');
        }

        // There are no errors at this point
        $data['item'] = $response->getResults();

        $request->setStage('filter', 'form_id', $request->getStage('form_id'));
        cradle()->trigger('question-search', $request, $response);

        // Gets the questions
        $questions = $response->getResults('rows');

        if ($response->isError()) {
            $response->setFlash($response->getMessage(), 'danger');
            $data['errors'] = $response->getValidation();
        }

        // Checks if there are questions
        if (!empty($questions)) {
            // Loops through the questions
            foreach ($questions as $index => $question) {
                $questions[$index]['question_custom'] = false;
                $questions[$index]['question_file'] = false;

                // Checks for question_type = custom
                if (in_array('custom', $question['question_type'])) {
                    $questions[$index]['question_custom'] = true;
                }

                // Checks for question_type = file
                if (in_array('file', $question['question_type'])) {
                    $questions[$index]['question_file'] = true;
                }
            }
        }

        $data['item']['question'] = $questions;
    }

    $data['title'] = 'Edit form';

    // check for template and class
    $template = '/tracking/application/poster/form';
    $class = 'page-tracking-application-poster-update branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/form_questionclone',
            'partial/form_question',
            'partial/form_confirmation',
            'profile_menu',
            'profile_alert',
            'post/modal_resume',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Application Form Update')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Application Poster Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/application/poster/search', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'form_name',
            'form_id'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'form_name',
            'form_id',
            'form_active'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    // Checks if there are isorder
    if (!$request->hasStage('order')) {
        // Set default order
        $request->setStage('order', 'form_id', 'DESC');
    }

    // profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    //trigger job
    cradle()->trigger('form-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    if ($response->getResults('total') > 0) {
        $data = array_merge($request->getStage(), $response->getResults());
    }

    // check for template and class
    $template = '/tracking/application/poster/search';
    $class = 'page-tracking-application-poster-search branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/form_confirmation',
            'profile_alert',
            'profile_menu',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Application Forms')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Application Poster Form Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/application/poster/form/:form_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if (empty($data['item'])) {
        // Gets the form detail
        // Based on form_id
        cradle()->trigger('form-detail', $request, $response);

        // Checks if there are errors
        if ($response->isError()) {
            // At this point, the form does not exist
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/profile/tracking/application/search');
        }

        // There are no errors at this point
        $data['item'] = $response->getResults();

        $request->setStage('filter', 'form_id', $request->getStage('form_id'));
        cradle()->trigger('question-search', $request, $response);

        // Gets the questions
        $questions = $response->getResults('rows');

        if ($response->isError()) {
            $response->setFlash($response->getMessage(), 'danger');
            $data['errors'] = $response->getValidation();
        }

        // Checks if there are questions
        if (!empty($questions)) {
            // Loops through the questions
            foreach ($questions as $index => $question) {
                $questions[$index]['question_custom'] = false;
                $questions[$index]['question_file'] = false;

                // Checks for question_type = custom
                if (in_array('custom', $question['question_type'])) {
                    $questions[$index]['question_custom'] = true;
                }

                // Checks for question_type = file
                if (in_array('file', $question['question_type'])) {
                    $questions[$index]['question_file'] = true;
                }
            }
        }

        $data['item']['question'] = $questions;
    }

    $data['title'] = 'View form';
    $data['view_form'] = true;

    // check for template and class
    $template = '/tracking/application/poster/form';
    $class = 'page-tracking-application-poster-update branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/form_questionclone',
            'partial/form_question',
            'partial/form_confirmation',
            'profile_menu',
            'profile_alert',
            'post/modal_resume',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Application Form')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Application Seeker Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/application/seeker/search', function ($request, $response) {
   //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // Checks for profile
    if (!empty($request->getSession('me')['profile_company'])) {
        cradle('global')->flash('Unauthorized', 'danger');
        return cradle('global')->redirect('/');
    }

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Prepare Data
    if (!$request->hasStage('range')) {
        $request->setStage('range', 10);
    }

    //filter possible sorting options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'post_id'
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'post_name',
            'post_position'
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    //trigger job
    cradle()->trigger('applicant-search', $request, $response);

    if ($response->isError()) {
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    $data = array_merge($data, $response->getResults());

    // check for template and class
    $template = '/tracking/application/seeker/search';
    $class = 'page-tracking-application-seeker-search branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'profile_alert',
            'profile_menu',
            '_modal-profile-completeness'
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Application Forms')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Tracking Application Seeker Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/tracking/application/seeker/update/:post_id/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //Need to be logged in
    cradle('global')->requireLogin();

    // Checks for profile
    if (!empty($request->getSession('me')['profile_company'])) {
        cradle('global')->flash('Unauthorized', 'danger');
        return cradle('global')->redirect('/');
    }
    //----------------------------//
    // 2. Prepare Data
    $data = ['post' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    // check for error
    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
    }

    // get applicant view form
    cradle()->trigger('applicant-view-form', $request, $response);

    // check for error
    if ($response->isError()) {
        // add flash
        cradle('global')->flash($response->getMessage(), 'danger');
        // redirect
        return cradle('global')->redirect('/profile/tracking/application/seeker/search');
    }

    // check if there is results
    if (!$response->getResults('rows')) {
        // add flash
        cradle('global')->flash('Not Found', 'danger');
        // redirect
        return cradle('global')->redirect('/profile/tracking/application/seeker/search');
    }

    // set the results to data item
    $data['item'] = $response->getResults('rows');

    // assume the there is answer
    $data['item']['answer'] = true;
    // check if there are questions with answer
    if (!$data['item']['questions']) {
        // if not set the answer to false
        $data['item']['answer'] = false;

        // get the questions without answerts
        cradle()->trigger('question-view', $request, $response);

        // check if error
        if ($response->isError()) {
            // add flash
            cradle('global')->flash($response->getMessage(), 'danger');
            // redirect
            return cradle('global')->redirect('/profile/tracking/application/seeker/search');
        }

        // set the questions to data
        $data['item']['questions'] = $response->getResults('rows');
    }

    // if there's already a question
    if ($data['item']['questions']) {
        // Loops through the questions
        foreach ($data['item']['questions'] as $index => $question) {
            $data['item']['questions'][$index]['question_custom'] = false;
            $data['item']['questions'][$index]['question_file'] = false;

            // convert question type to array
            $question['question_type'] = explode(',', $question['question_type']);

            // Checks for question_type = custom
            if (in_array('custom', $question['question_type'])) {
                $data['item']['questions'][$index]['question_custom'] = true;
            }

            // Checks for question_type = file
            if (in_array('file', $question['question_type'])) {
                $data['item']['questions'][$index]['question_file'] = true;
            }

            // add value for post
            if ($data['post']) {
                foreach ($data['post']['question'] as $p => $post) {
                    if ($question['question_id'] == $p) {
                        $data['item']['questions'][$index]['question_value'] = $post;
                    }
                }
            }
        }
    }

    // check for template and class
    $template = '/tracking/application/seeker/form';
    $class = 'page-tracking-application-seeker-update branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'profile_alert',
            'profile_menu',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Applicant Tracking System | Application Forms')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Process the Post Restore
 *
 * @param Request $request
 * @param Response $response
 */
 $cradle->get('/profile/tracking/application/restore/:form_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin();

    if ($request->getSession('me')) {
        //update profile session
        cradle()->trigger('profile-session', $request, $response);
    }
    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
        cradle()->trigger('form-restore', $request, $response);
        //add a flash
        $message = cradle('global')->translate('Form was Restored');
        cradle('global')->flash($message, 'success');
    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    }

    //redirect
    $redirect = '/profile/tracking/application/poster/search';
    if ($request->getStage('redirect_uri')) {
          $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
 });

/**
 * Process the Tracking Application Seeker Update Page
 *
 * @param Request $request
 * @param Response $response
 */
 $cradle->post('/profile/tracking/application/seeker/update/:post_id/:profile_id', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    cradle('global')->requireLogin();

    // get the data from stage
    $data = $request->getStage();

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    // check for error
    if ($response->isError()) {
        return cradle()->triggerRoute(
            'get',
            '/profile/tracking/application/seeker/update/'.$data['post_id'].'/'.$data['profile_id'].'',
            $request,
            $response
        );
    }

    // required all the fields
    $errors = false;
    foreach ($data['question'] as $id => $answer) {
        if (empty($answer)) {
            $errors = true;
        }
    }


    // check for error
    if ($errors) {
        $response
            ->setError(true, 'Fill up the Form')
            ->set('json', 'validation', $errors);

        return cradle()->triggerRoute(
            'get',
            '/profile/tracking/application/seeker/update/'.$data['post_id'].'/'.$data['profile_id'].'',
            $request,
            $response
        );
    }

    // set the s3 config
    $config = $this->package('global')->service('s3-main');
    // set the upload path
    $upload = $this->package('global')->path('upload');

    $answerIds = [];
    // loop questions id then insert answer
    foreach ($data['question'] as $id => $answer) {
        // check if answer is a file
        if (base64_decode($answer, true) === false) {
            // try to upload to s3
            $answer = File::base64ToS3($answer, $config);
            //try being old school
            $answer = File::base64ToUpload($answer, $upload);
        }

        // checks if answer already exists
        $request->setStage('question_id', $id);
        $request->setStage('answer_name', $answer);

        // trigger the job
        cradle()->trigger('answer-create', $request, $response);

        // check for error
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            // redirect
            cradle('global')->redirect(
                '/profile/tracking/application/seeker/update/'.$data['post_id'].'/'.$data['profile_id']
            );
        }

        // get answer ids
        $answerIds[] = $response->getResults('answer_id');
    }

    // loop answer id then link to applicant
    foreach ($answerIds as $a => $id) {
        $request->setStage([
            'applicant_id' => $data['applicant_id'],
            'answer_id' => $id,
        ]);

        // link the applicant to answer
        cradle()->trigger('applicant-link-answer', $request, $response);

        // check for error
        if ($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            // redirect
            cradle('global')->redirect(
                '/profile/tracking/application/seeker/update/'.$data['post_id'].'/'.$data['profile_id']
            );
        }
    }

    //it was good
    //add a flash
    cradle('global')->flash('Application was successfully submitted', 'success');

    //redirect
    cradle('global')->redirect(
        '/profile/tracking/application/seeker/update/'.$data['post_id'].'/'.$data['profile_id']
    );
 });
