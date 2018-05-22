<?php //-->

/**
 * Render the Schedule Interview Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/interview/schedule', function ($request, $response) {
    /**
     * Notes for this page
     * Only show posts for this user - based on the session profile_id
     * Only show posts that are not expired and active
     * Only show available interview dates
     * - dates greater than or equal to today
     * - dates with available slots
     */

    // Check for login
    cradle('global')->requireLogin();

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Sets the columns to be fetched
    $request->setStage('columns', 'profile_id, post_id, post_position');
    $request->setStage('order', 'post_id', 'DESC');
    $request->setGet('noindex', 1);
    $request->setGet('nocache', 1);

    // Triggers the event
    cradle()->trigger('post-search', $request, $response);

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/');
    }

    // Removes the staging data
    $request->removeStage('filter');
    $request->removeStage('columns');
    $request->removeStage('order');

    // Gets the posts
    $data['posts'] = $response->getResults()['rows'];

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Trigger the event
    // Gets the schedule settings / availabilities
    // Based on profile id / profile_id
    // Get total schedules as well
    $request->setStage('schedule_total', true);
    $request->setStage('exclude_maxed_out', true);
    $request->setStage('range', 50);
    $request->setStage('filter', 'today', true);
    $request->setStage('order', 'interview_setting_date', 'ASC');
    cradle()->trigger('interview-setting-search', $request, $response);

    // Removes the staging data
    $request->removeStage();

    // Get the results
    $results = $response->getResults();

    // Checks if there are errors
    if ($response->isError()) {
        // At this point, there are errors
        cradle('global')->flash('Error loading page', 'danger');
        return cradle('global')->redirect('/');
    }

    $rows = array();

    // Get the settings
    $data['settings'] = array();

    foreach ($results['rows'] as $row) {
        $data['settings'][$row['interview_setting_id']] = $row;
    }

    // Checks for selected interview
    if (isset($data['interview']) && is_numeric($data['interview'])) {
        if (isset($data['settings'][$data['interview']])) {
            $data['selected'] = $data['settings'][$data['interview']];
        }
    }

    // check for template and class
    $template = '/interview/scheduler';
    $class = 'page-interview-scheduler branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/interview_availabilitynone',
            'partial/interview_confirmation',
            'partial_elementclone',
            'profile_alert',
            'profile_menu',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Interview Scheduler | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Schedule Interview Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/interview/calendar', function ($request, $response) {
    /**
     * Notes for this page
     * Only show posts for this user - based on the session profile_id
     * Only show posts that are not expired and active
     * Only show available interview dates
     * - dates greater than or equal to today
     * - dates with available slots
     */

    // Check for login
    cradle('global')->requireLogin();

    // filter possible sorting options
    // we do this to prevent SQL injections
    if (is_array($request->getStage('order'))) {
        $sortable = [
            'interview_setting_date',
        ];

        foreach ($request->getStage('order') as $key => $direction) {
            if (!in_array($key, $sortable)) {
                $request->removeStage('order', $key);
            } else if ($direction !== 'ASC' && $direction !== 'DESC') {
                $request->removeStage('order', $key);
            }
        }
    }

    // filter possible filter options
    // we do this to prevent SQL injections
    if (is_array($request->getStage('filter'))) {
        $filterable = [
            'dates',
            'interview_setting_date',
        ];

        foreach ($request->getStage('filter') as $key => $value) {
            if (!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    // Checks if a frequency was not set
    if (!$request->hasStage('frequency')) {
        // Sets the frequency
        $request->setStage('frequency', 'weekly');
    }

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if (!isset($data['filter']['dates']['start_date'])) {
        $data['filter']['dates']['start_date'] = 'now';
    }

    // Checks for frequency
    if (isset($data['frequency'])) {
        // Checks the frequency data - daily
        if ($data['frequency'] == 'daily') {
            $data['filter']['dates']['start_date'] = date(
                'Y-m-d',
                strtotime($data['filter']['dates']['start_date'])
            );

            // Sets the pagination
            $data['pagination']['prev'] = date('Y-m-d', strtotime(
                '-1 day',
                strtotime($data['filter']['dates']['start_date'])
            ));
            $data['pagination']['next'] = date('Y-m-d', strtotime(
                '+1 day',
                strtotime($data['filter']['dates']['start_date'])
            ));
        } // Checks the frequency data - weekly
        else if ($data['frequency'] == 'weekly') {
            $dayToday = date('l', strtotime($data['filter']['dates']['start_date']));
            $plusDays = '+6 days';

            // Checks if today is Monday
            if ($dayToday != 'Monday') {
                $data['filter']['dates']['start_date'] = date(
                    'Y-m-d',
                    strtotime('previous Monday', strtotime($data['filter']['dates']['start_date']))
                );
            } else {
                $data['filter']['dates']['start_date'] = date(
                    'Y-m-d',
                    strtotime($data['filter']['dates']['start_date'])
                );
            }

            $data['filter']['dates']['end_date'] = date('Y-m-d', strtotime(
                $plusDays,
                strtotime($data['filter']['dates']['start_date'])
            ));

            // Sets the pagination
            $data['pagination']['prev'] = date('Y-m-d', strtotime(
                '-7 days',
                strtotime($data['filter']['dates']['start_date'])
            ));

            $data['pagination']['next'] = date('Y-m-d', strtotime(
                '+7 days',
                strtotime($data['filter']['dates']['start_date'])
            ));
        } // Checks the frequency data - monthlyinspec
        else if ($data['frequency'] == 'monthly') {
            $data['filter']['dates']['start_date'] = date(
                'Y-m',
                strtotime($data['filter']['dates']['start_date'])
            ) . '-01';

            $data['filter']['dates']['end_date'] = date('Y-m-d', strtotime(
                '+1 month',
                strtotime($data['filter']['dates']['start_date'])
            ));

            // Sets the pagination
            $data['pagination']['prev'] = date('Y-m-d', strtotime(
                '-1 month',
                strtotime($data['filter']['dates']['start_date'])
            ));

            $data['pagination']['next'] = date('Y-m-d', strtotime(
                '+1 month',
                strtotime($data['filter']['dates']['start_date'])
            ));
        }

        // Checks if the date is not empty
        if (!empty($data['filter']['dates'])) {
            // Pass the date to the filters
            $request->setStage('filter', 'dates', $data['filter']['dates']);
        }
    }

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Triggers the interview search
    // Also get the schedule total
    // Also get the schedules
    // Get all
    $request->setStage('schedule_total', true);
    $request->setStage('schedule_list', true);
    $request->setStage('range', 0);
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();
    $interviews = array();
    if (!empty($results['rows'])) {
        $data['csv'] = $results['rows'][0]['interview_schedule'];
    }

    // Checks if there are interviews settings
    if (!empty($results['rows'])) {
        // Loops through the rows
        foreach ($results['rows'] as $row) {
            $interviews[$row['interview_setting_date']] = $row;
        }
    }

    // We need to construct the dates to be shown
    $dates = array();
    $today = strtotime('now');
    $i = $j = strtotime($data['filter']['dates']['start_date']);

    // Checks if we have an end date
    if (isset($data['filter']['dates']['end_date'])) {
        $j = strtotime($data['filter']['dates']['end_date']);
    }

    // Loops through the dates
    for ($i = $i; $i <= $j; $i += 86400) {
        $date = date('Y-m-d', $i);

        // Checks if there is data for the date
        if (isset($interviews[$date])) {
            $dates[$date] = $interviews[$date];

            if ($today > $i) {
                $dates[$date]['extra_option'] = true;
            }
        } else if ($data['frequency'] != 'monthly') {
            $dates[$date] = array();
        }
    }

    $data['dates'] = $dates;

    // export
    if ($request->hasStage('export')) {
        $request->setStage('export', '1');
        $request->setGet('noindex', true);
    }

    if (empty($interviews) && $request->hasStage('date')) {
        cradle('global')->flash('No available interview schedule!', 'danger');
        return cradle('global')->redirect('/profile/interview/calendar');
    }

    //Export CSV
    if ($request->hasStage('export') && !empty($interviews)) {
        //Set CSV header
        $header = [
            'post_position' => 'Job Title',
            'profile_name' => 'Applicant Name',
            'interview_schedule_date' => 'Interview Date',
            'interview_schedule_start_time' => 'Interview Start',
            'interview_schedule_end_time' => 'Interview End',
            'interview_schedule_status' => 'Status',
        ];

        $export = array();
        foreach ($interviews as $interview) {
            $export = array_merge($export, $interview['interview_schedule']);
        }

        //Set Filename
        $request->setStage('filename', 'interivew-schedule.csv');
        $request->setStage('header', $header);
        $request->setStage('csv', $export);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    // check for template and class
    $template = '/interview/calendar';
    $class = 'page-interview-scheduler branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/interview_availabilitynone',
            'partial/interview_confirmation',
            'partial/interview_reschedule',
            'partial_elementclone',
            'profile_alert',
            'profile_menu',
        ]
    );
    
    //Set Content
    $response
        ->setPage('title', 'Jobayan - Interview Scheduler | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);

    //Render blank page
}, 'render-www-page');

/**
 * Render the Schedule Interview Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/profile/interview/settings', function ($request, $response) {
    /**
     * Notes for this page
     * Shows interview availabilities set by the user
     * - Sorted ASC based on interview_setting_date
     */

    // Check for login
    cradle('global')->requireLogin();

    $data = [];

    // Gets the profile details
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    cradle()->trigger('profile-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        cradle('global')->flash('Unauthorized', 'danger');
        return cradle('global')->redirect('/');
    }

    $data['profile'] = $response->getResults();

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Gets the schedule settings / availabilities
    // Based on profile id / profile_id
    $request->setStage('order', 'interview_setting_date', 'DESC');
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();
    $rows = $results['rows'];

    // Checks if there are rows
    if (!empty($rows)) {
        // Loops through the rows
        foreach ($rows as $i => $row) {
            $rows[$i]['interview_setting_date_format'] =
                date('F d, Y', strtotime($row['interview_setting_date']));
        }
    }

    $data['settings'] = $rows;
    $data['total'] = $results['total'];

    // check for template and class
    $template = '/interview/setting';
    $class = 'page-interview-scheduler branding';

    //Render body
    $body = cradle('/app/www')->template(
        $template,
        $data,
        [
            'partial/interview_availabilityadd',
            'partial/interview_availabilityedit',
            'partial/interview_confirmation',
            'partial_elementclone',
            'profile_alert',
            'profile_menu',
        ]
    );

    //Set Content
    $response
        ->setPage('title', 'Jobayan - Interview Scheduler | Job Posts')
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');


/**
 * Process Profile Interview calendar Csv Import
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/profile/interview/calendar/import', function ($request, $response) {
    //----------------------------//
    $profile = $request->getSession('me');

    $cost = cradle('global')->config('credits', 'import-schedule');
    $available = $request->getSession('me', 'profile_credits');

    // if insufficient credit balance
    if ($available < $cost) {
        cradle('global')->flash('Insufficient Credits', 'danger');
        return cradle('global')->redirect('/profile/interview/calendar');
    }

    $columns = array(
        'keys' => array(
            'post_position',
            'profile_name',
            'interview_schedule_date',
            'interview_schedule_start_time',
            'interview_schedule_end_time',
            'interview_schedule_status'
        )
    );

    $request->setStage($columns);
    cradle()->trigger('csv-import', $request, $response);

    if ($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
        return cradle('global')->redirect('/profile/interview/calendar');
    }

    $data = $response->getResults();

    // Set filters
    // profile id / profile_id
    $request->setStage(
        'filter',
        'profile_id',
        $request->getSession('me', 'profile_id')
    );

    // Triggers the interview search
    // Also get the schedule total
    // Also get the schedules
    // Get all
    $request->setStage('schedule_total', true);
    $request->setStage('schedule_list', true);
    $request->setStage('exclude_maxed_out', true);
    $request->setStage('filter', 'today', true);
    $request->setStage('range', 0);
    cradle()->trigger('interview-setting-search', $request, $response);
    $results = $response->getResults();

    // Checks if there were no results returned
    if (empty($results['rows'])) {
        // The interview could either be expired or inactive at this point
        // Return an error
        cradle('global')->flash('Error scheduling interview', 'danger');
        return cradle('global')->redirect('/profile/interview/calendar');
    }

    // Checks if there are interviews settings
    if (!empty($results['rows'])) {
        // Loops through the rows
        foreach ($results['rows'] as $row) {
            $interviews[$row['interview_setting_date']] = $row;
        }
    }

    // set up available dates for interview
    if (!empty($results['rows'])) {
        // Loops through the rows
        foreach ($results['rows'] as $resultsKey => $resultsValue) {
            // Prepage interivew dates
            $data['interview_dates'][$resultsValue['interview_setting_id']] = $resultsValue['interview_setting_date'];
            // Preprage interview_schedule
            foreach ($resultsValue['interview_schedule'] as $key => $value) {
                if ($value['interview_schedule_type'] == 'anonymous') {
                    $data['interview_schedule'][] = $value;
                }
            }
        }
    }

    // Check if csv date is available in interview dates
    foreach ($data['csv'] as $csvKey => $csvValue) {
        if (!empty($data['interview_dates'])) {
            // prepare interivew dates availability
            $csvInterviewDats = trim($csvValue['interview_schedule_date']);
            if (in_array($csvInterviewDats, $data['interview_dates'])) {
                // Prepare available interview dates
                $data['interview_dates_available'][] = $csvInterviewDats;
            } else {
                // Prepare unavailable interview dates
                $data['interview_dates_unavailable'][$csvKey] = $csvInterviewDats;
                // remove unavailable interview dates on csv rows
                unset($data['csv'][$csvKey]);
            }

            // Chech interview dates available
            if (!empty($data['interview_dates_available'])) {
                // Check if csv data has the same value in


                // prepare csv
                foreach ($data['interview_dates'] as $interviewKey => $interviewValue) {
                    // Add interview setting id on csv
                    if ($interviewValue == trim($csvValue['interview_schedule_date'])) {
                        $data['csv'][$csvKey]['interview_setting_id'] = $interviewKey;
                    }
                }
            }
        } else {
            // return error no available dates
            cradle('global')->flash('No available dates!', 'danger');
            return cradle('global')->redirect('/profile/interview/calendar');
        }
    }

    $request->removeStage();
    $successCreateCtr = $successUpdateCtr = $errorCtr = 0;

    foreach ($data['csv'] as $key => $value) {
        $interviwMeta = [
            'post_position' => $value['post_position'],
            'profile_name' => $value['profile_name']
        ];

        $request->setStage($value);
        $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
        $request->setStage('interview_schedule_flag', 1);
        $request->setStage('interview_schedule_meta', $interviwMeta);
        $request->setStage('interview_schedule_type', 'anonymous');
        $request->setStage('interview_setting_id', $value['interview_setting_id']);
        cradle()->trigger('interview-schedule-create', $request, $response);
        $successCreateCtr++;
    }

    // Setup errors
    if (!empty($data['interview_dates_unavailable'])) {
        foreach ($data['interview_dates_unavailable'] as $key => $value) {
            $errors[] = '<br>#'. $value . ' unavailable';
            $errorCtr++;
        }
    }

    // prepare stage to deduct credits
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    $request->setStage('service_name', 'Import Schedule')
        ->setStage('service_meta', [
            'profile_id' => $profile['profile_id']
        ])
        ->setStage('service_credits', $cost);

    cradle()->trigger('service-create', $request, $response);

    if (!$response->isError()) {
        $request->setSession('me', 'profile_credits', $available - $cost);
    }

    //set message
    $message = ' ['. $successCreateCtr. '] Interview schedule imported. <br> <br>';

    if ($errorCtr > 0) {
        $message .= ' Errors: ' . (implode(' ', $errors));
    }

    // Flush information about import
    cradle('global')->flash($message, 'info', 20000);

    //redirect
    $redirect = '/profile/interview/calendar';
    if ($request->getStage('redirect_uri')) {
        $redirect = $request->getStage('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});
