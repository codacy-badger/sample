<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
use Cradle\Module\Research\Service as ResearchService;

/**
 * Render the Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];
    //Render body
    $class = 'page-research page-research-search';

    $body = cradle('/app/www')->template(
        'research/search',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    $title = cradle('global')->translate('Research');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/research', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    $researchSql = ResearchService::get('sql');

    // search by position only
    if (isset($data['position']) && !empty($data['position'])
        && isset($data['location']) && empty($data['location'])) {
        // set to stage the actual position for searching
        $request->setSession('position', $data['position']);
        //redirect research position
        cradle('global')->redirect('/research/'. $researchSql->slugify($data['position']). '-Position');
    }

    // search by location only
    if (isset($data['location'])
        && !empty($data['location'])
        && isset($data['position'])
        && !empty($data['position'])
    ) {
        // redirect research position-location
        cradle('global')->redirect('/research/'. $researchSql->slugify($data['position']). '-Position/'.  $researchSql->slugify($data['location']) .'-Philippines');
    }

    // redirect
    cradle('global')->redirect('/research');
});

/**
 * Render the Location Form Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research/location', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];
    //Render body
    $class = 'page-research page-research-location-form';

    $body = cradle('/app/www')->template(
        'research/location/index',
        $data,
        [
            '_modal-profile-completeness'
        ]
    );

    $title = cradle('global')->translate('Research');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Location Form Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/research/location', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = $request->getStage();

    $researchSql = ResearchService::get('sql');

    if (isset($data['location']) && !empty($data['location'])) {
        //redirect research location
        cradle('global')->redirect('/research/'. $researchSql->slugify($data['location']). '-Philippines');
    }

    // No location
    cradle('global')->flash('Location Not Found!', 'danger');
    //redirect
    cradle('global')->redirect('/research/location');
});

/**
 * Render the Academe
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research/academe', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // current year
    $currentYear = date('Y');

    $request->setStage('start_year', $currentYear - 3);

    // set school
    if ($request->hasStage('school') && !empty($request->getStage('school'))) {
        $request->setStage('education_school', $request->getStage('school'));
    }

    // set year
    if ($request->hasStage('year')) {
        $request->setStage('start_year');
    }

    // get employment rate
    cradle()->trigger('information-search-employment-rate', $request, $response);
    $data = array_merge($data, $response->getResults());

    // get civil status rate
    cradle()->trigger('information-search-civil-status', $request, $response);
    $data = array_merge($data, $response->getResults());

    // get job related rate
    cradle()->trigger('information-search-job-related', $request, $response);
    $data = array_merge($response->getResults(), $data);

     // get current year
    $currentYear = date('Y');
    if (isset($data['year'])) {
        $currentYear = $data['year'];
    }

    // set years
    $years = [];
    for ($i = 0; $i < 4; $i++) {
        $data['years'][] = ($currentYear - 1) - $i;
    }

    $data['year'] = $currentYear;

    //Export CSV
    if ($request->hasSession('me') && $request->hasStage('export')) {
        //Set CSV header
        $header = [
            'Type' => 'Type',
            'Year' => 'Year',
            'Total' => 'Total',
        ];

        $data['rows'] = [];

        // get employment rate data
        $employmentRate[] = [
            'Type' => 'Employment Rate - Employed',
            'Year' => $data['employment_rate_current']['year'],
            'Total' => $data['employment_rate_current']['total_employed'],
        ];

        $employmentRate[] = [
            'Type' => 'Employment Rate - Unemployed',
            'Year' => $data['employment_rate_current']['year'],
            'Total' => $data['employment_rate_current']['total_not_employed'],
        ];

        foreach ($data['employment_rate'] as $year => $value) {
            $employmentRate[] = [
                'Type' => 'Employment Rate - Employed',
                'Year' => $year,
                'Total' => $value['total_employed'],
            ];

            $employmentRate[] = [
                'Type' => 'Employment Rate - Unemployed',
                'Year' => $year,
                'Total' => $value['total_not_employed'],
            ];
        }

        $data['rows'] = $employmentRate;

        // get civil status data
        $civilStatus[] = [
            'Type' => 'Civil Status - Single',
            'Year' => $data['civil_status_current']['year'],
            'Total' => $data['civil_status_current']['total_single'],
        ];

        $civilStatus[] = [
            'Type' => 'Civil Status - Married',
            'Year' => $data['civil_status_current']['year'],
            'Total' => $data['civil_status_current']['total_married'],
        ];

        foreach ($data['civil_status'] as $year => $value) {
            $civilStatus[] = [
                'Type' => 'Civil Status - Single',
                'Year' => $year,
                'Total' => $value['total_single'],
            ];

            $civilStatus[] = [
                'Type' => 'Civil Status - Married',
                'Year' => $year,
                'Total' => $value['total_married'],
            ];
        }

        $data['rows'] = array_merge($data['rows'], $civilStatus);

        // get job related data
        $jobRelated[] = [
            'Type' => 'Job Related - Yes',
            'Year' => $data['job_related_current']['year'],
            'Total' => $data['job_related_current']['total_yes'],
        ];

        $jobRelated[] = [
            'Type' => 'Job Related - No',
            'Year' => $data['job_related_current']['year'],
            'Total' => $data['job_related_current']['total_no'],
        ];

        foreach ($data['job_related'] as $year => $value) {
            $jobRelated[] = [
                'Type' => 'Job Related - Yes',
                'Year' => $year,
                'Total' => $value['total_yes'],
            ];

            $jobRelated[] = [
                'Type' => 'Job Related - No',
                'Year' => $year,
                'Total' => $value['total_no'],
            ];
        }

        $data['rows'] = array_merge($data['rows'], $jobRelated);

        //Set Filename
        $request->setStage('filename', 'tracer-study-'.date("Y-m-d").".csv");
        $request->setStage('header', $header);
        $request->setStage('csv', $data['rows']);
        cradle()->trigger('csv-export', $request, $response);
        exit;
    }

    // get top companies
    $request->setStage('order', 'post_salary_max', 'DESC');
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('range', 5);
    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);
    $data['top_companies'] = $response->getResults('rows');
    $data['salary_min'] = true;

    //Render body
    $class = 'page-research page-research-academe';
    $body = cradle('/app/www')->template('research/academe', $data, [
        'research_companies',
        'research_ads',
        '_modal-profile-completeness'
    ]);

    $title = cradle('global')->translate('Academe');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Search Page for Positions
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research/*-Position', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    // get by session if there's special char
    $position = $request->hasSession('position') ? $request->getSession('position') : $request->getVariables(0);
    $position = str_replace('-', ' ', trim($position));
    $request->removeSession('position');

    // get by position
    $data['position'] = $position;
    $request->setStage('position_like', $position);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('not_filter', 'post_salary_max', null);
    $request->setStage('not_filter', 'post_salary_min', null);
    $request->setStage('order', 'post_updated', 'DESC');

    cradle()->trigger('post-search', $request, $response);

    // post results
    $results = $response->getResults();

    // research results
    $request->removeStage();
    $request->setStage('position', $data['position']);
    cradle()->trigger('research-search', $request, $response);

    $research = [];

    // if post and research not exists
    if (!$results['total'] && !$response->getResults('total')) {
        // translate message
        $message = cradle('global')->translate('Not Found!');

        // add flash
        cradle('global')->flash($message, 'danger');

        // redirect
        return cradle('global')->redirect('/research');
    }

    if ($response->getResults('total')) {
        $research = $response->getResults('rows')[0];
        $data['average_employer'] = isset($research['research_position']['average_employer'])
            ? $research['research_position']['average_employer'] : 0;

        $data['last_updated'] = $research['research_updated'];

        // salary min | max
        $data['salary_min'] = isset($research['research_position']['salary_range']['min'])
            ? $research['research_position']['salary_range']['min'] : 0;

        $data['salary_max'] = isset($research['research_position']['salary_range']['max'])
            ? $research['research_position']['salary_range']['max'] : 0;

        // get average salary
        $data['average_salary'] = isset($research['research_position']['average_salary'])
            ? $research['research_position']['average_salary'] : 0;
    } else {
        $companies = [];
        // get average employer bcompany
        foreach ($results['rows'] as $company) {
            $companies[$company['profile_id']] = $company['profile_id'];
        }

        // get average employer
        $data['average_employer'] = count($companies);

        // last updated post
        $data['last_updated'] = date('Y-m-d H:i:s');

        $salaryMin = array_filter(array_column($results['rows'], 'post_salary_min'));
        $salaryMax = array_filter(array_column($results['rows'], 'post_salary_max'));

        // limit salary min
        foreach ($salaryMin as $key => $value) {
            if ($value <= 100) {
                unset($salaryMin[$key]);
            }
        }

        // limit salary max
        foreach ($salaryMax as $key => $value) {
            if ($value > 900000) {
                unset($salaryMax[$key]);
            }
        }

        // salary min | max
        $data['salary_min'] = min($salaryMin);
        $data['salary_max'] = max($salaryMax);

        // get average salary
        $data['average_salary'] = floor((($data['salary_min'] + $data['salary_max']) / 2));
    }

    // get position description
    $description = '';
    $qualification = '';

    if (!empty($research)) {
        // if set to common description
        if (isset($research['research_position']['description'])
            && !empty($research['research_position']['description'])
        ) {
            $description = $research['research_position']['description'];
        }

        // if set to common qualification
        if (isset($research['research_position']['qualification'])
            && !empty($research['research_position']['qualification'])
        ) {
            $qualification = $research['research_position']['qualification'];
        }

        // if set to payscale
        if (empty($description)
            && isset($research['research_position']['payscale_description'])
            && !empty($research['research_position']['payscale_description'])
        ) {
            $description = $research['research_position']['payscale_description'];
        }

        // if set to payscale
        if (empty($qualification)
            && isset($research['research_position']['payscale_qualification'])
            && !empty($research['research_position']['payscale_qualification'])
        ) {
            $qualification = $research['research_position']['payscale_qualification'];
        }
    }

    // find description on PayScale
    if (empty($description)) {
        $request->removeStage();
        $request->setStage('post_position', $position);

        cradle()->trigger('crawl-page', $request, $response);

        if (!$response->isError()) {
            $description = $response->getResults('description');
        }

        if (!empty($description) && $research) {
            $request->removeStage();
            $research['research_position']['payscale_description'] = $description;
            $request->setStage($research);

            cradle()->trigger('research-update', $request, $response);
        }
    }

    // get description
    $data['description'] = $description;
    // get qualification
    $data['qualification'] = $qualification;

    // get top companies
    $request->removeStage('order');
    $request->setStage('order', 'post_salary_max', 'DESC');
    $request->setStage('position_like', $position);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('range', 10);
    cradle()->trigger('post-search', $request, $response);
    $companies = $response->getResults('rows');

    $data['top_companies'] = [];
    foreach ($companies as $company) {
        // if exist continue to loop
        if (array_key_exists($company['post_name'], $data['top_companies'])) {
            continue;
        }

        // store row to variable
        $data['top_companies'][$company['post_name']] = $company;

        // if count is 5 break the loop
        if (count($data['top_companies']) >= 5) {
            break;
        }
    }

    // get top companies
    $data['top_companies'] = array_values($data['top_companies']);

    // get location description
    $data['location_description'] = isset($research['research_position']['location_description'])
        ? $research['research_position']['location_description'] : '';

    // get paid companies
    $request->removeStage();
    $request->setStage('filter', 'post_position', $position);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('filter', 'post_flag', 1);
    cradle()->trigger('post-search', $request, $response);

    $data['paid_companies'] = $response->getResults('rows');

    // latest job post
    $request->removeStage();
    $request->setStage('order', 'post_created', 'DESC');
    $request->setStage('position_like', $position);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('not_filter', 'post_salary_max', null);
    $request->setStage('not_filter', 'post_salary_min', null);
    $request->setStage('range', 5);
    cradle()->trigger('post-search', $request, $response);
    $latest = $response->getResults('rows');

    $data['latest_jobs'] = $latest;

    // Render body
    $class = 'page-research page-research-position';
    $body = cradle('/app/www')->template('research/position', $data, [
            'research_companies',
            'research_jobs',
            'research_ads'
        ]);

    $title = cradle('global')->translate('Research');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Render the Search Page for Locations
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research/*-Philippines', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions

    //----------------------------//
    // 2. Prepare Data
    $location = $request->getVariables(0);

    $request->setStage('location_like', $location);
    $request->setStage('not_filter', 'post_salary_max', null);
    $request->setStage('not_filter', 'post_salary_min', null);
    $request->setStage('order', 'post_salary_max', 'DESC');
    $request->setStage('post_type', 'poster');

    cradle()->trigger('post-search', $request, $response);

    $results = [];
    if ($response->getResults('total')) {
        $results = $response->getResults();
    }

    $request->removeStage();
    $request->setStage('location', $location);
    $request->setStage('filter', 'research_type', 'location');
    cradle()->trigger('research-search', $request, $response);

    $research = [];

    if ($response->getResults('total')) {
        $research = $response->getResults('rows')[0];
    }

    if ($response->isError()) {
        // translate message
        $message = cradle('global')->translate($response->getMessage());

        // add flash
        cradle('global')->flash($message, 'danger');

        // redirect
        return cradle('global')->redirect('/research');
    }

    if (empty($results) && empty($research)) {
        cradle('global')->flash('Not Found!', 'danger');
        return cradle('global')->redirect('/research');
    }

    $hiringRates = [];
    if (!empty($results)) {
        foreach ($results['rows'] as $post) {
            // override data
            if (array_key_exists($post['post_position'], $hiringRates)) {
                $current = $hiringRates[$post['post_position']];
                if ($post['post_salary_min'] > $current['min']) {
                    $post['post_salary_min'] = $current['min'];
                }

                if ($post['post_salary_max'] < $current['max']) {
                    $post['post_salary_max'] = $current['max'];
                }
            }

            $hiringRates[$post['post_position']] = [
                'min' => $post['post_salary_min'],
                'max' => $post['post_salary_max'],
                'name' => $post['post_position']
            ];

            if (count($hiringRates) == 10) {
                break;
            }
        }

        $hiringRates = array_values($hiringRates);
    }

    if (empty($research)) {
        $data['research_updated'] = $results['rows'][0]['post_updated'];
        $data['research_location'] = [
            'name' => $location,
            'description' => cradle('global')->translate('Brief information about '. $location),
            'hiring_rate' => $hiringRates,
            'hiring_rate_details' => cradle('global')->translate('Graph below shows an up to date estimate salary for each position in ' . $location . '.'),
            'salary_range_details' => cradle('global')->translate('Information about the minimum and maximum salary per position.'),
            'unemployment_rate_details' => cradle('global')->translate('Percentage of unemployment per year.')
        ];

        $data['research_type'] = 'location';

        // get unemployement rate
        $unemploymentRequest    = Cradle\Http\Request::i();
        $unemploymentResponse   = Cradle\Http\Response::i();

        // TODO: static for now
        $years = ['2018', '2017'];

        $rates = [];

        foreach ($years as $year) {
            $unemploymentRequest->setStage('date', [
                'start_date' => $year. '-01-01 00:00:00',
                'end_date' => $year.'-12-31 11:59:59',
            ]);

            $unemploymentRequest->setStage('location', $location);
            $unemploymentRequest->setStage('range', 0);

            cradle()->trigger('profile-employment-search', $unemploymentRequest, $unemploymentResponse);

            $results = $unemploymentResponse->getResults();

            if ($results['total'] == 0 && $results['employed'] == 0) {
                continue;
            }

            $rates[] = [
                'rate' => ceil(
                    (
                        ($results['total'] - $results['employed']) / $results['total']
                    ) * 100
                ),
                'year' => $year
            ];
        }

        $data['research_location']['unemployment_rate'] = $rates;
    } else {
        // get research data
        $data = $research;
        // set current hiring rates on location
        $data['research_location']['hiring_rate'] = $hiringRates;
    }

    if (isset($data['research_location']['hiring_rate'])) {
        $data['max_graph_salary'] = $data['research_location']['hiring_rate'][0]['max'] * 1.1;
    }

    // get top companies
    cradle()->trigger('research-top-companies', $request, $response);

    if (!$response->isError()
        && $response->getResults('total') > 0) {
        $data['top_companies'] = $response->getResults('rows');
    }

    // get paid companies
    if (isset($data['research_location']['paid_companies'])
        && !empty($data['research_location']['paid_companies'])) {
        $request->setStage('companies', $data['research_location']['paid_companies']);
        cradle()->trigger('research-companies', $request, $response);

        if (!$response->isError()
            && $response->getResults('total') > 0) {
            $data['paid_companies'] = $response->getResults('rows');
        }
    }

    if (!$request->hasStage('companies')) {
        $companies= [];
        foreach ($data['top_companies'] as $company) {
            $companies[] = $company['profile_company'];
        }

        $request->setStage('companies', $companies);
    }

    if (!isset($data['research_location']['geo'])
        && !isset($data['research_location']['geo']['latitude'])
        && !isset($data['research_location']['geo']['longhitude'])
    ) {
        $geoRequest  = Cradle\Http\Request::i();
        $geoResponse  = Cradle\Http\Response::i();

        $geoRequest->setStage('post_location', $location);

        cradle()->trigger('google-geomap', $geoRequest, $geoResponse);

        $geo = $geoResponse->getResults();

        if ($geo) {
            $data['research_location']['geo'] = [
                'latitude' => $geo['lat'],
                'longhitude' => $geo['lon']
            ];
        }
    }

    $request->setStage('location', $location);

    // get top positions
    cradle()->trigger('research-top-positions', $request, $response);

    // Check for errors
    if (!$response->isError()
        && $response->getResults('total') > 0) {
            $data['top_positions'] = $response->getResults('rows');
    }

    $data['location'] = $location;

    // calculate dashoffset for svg element
    if (isset($data['research_location']['unemployment_rate'])
        && !empty($data['research_location']['unemployment_rate'])
    ) {
        $dasharray = 339.292;

        foreach ($data['research_location']['unemployment_rate'] as $key => $unemployment) {
            $dashoffset = (100 - $unemployment['rate']) * .01;
            $dashoffset = $dasharray * $dashoffset;
            $data['research_location']['unemployment_rate'][$key]['dasharray'] = $dasharray;
            $data['research_location']['unemployment_rate'][$key]['dashoffset'] = $dashoffset;
        }
    }

    // Render body
    $class = 'page-research page-research-location';
    $body = cradle('/app/www')->template('research/location', $data, [
            'research_companies',
            'research_positions',
            'research_ads'
        ]);

    $title = cradle('global')->translate('Research');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);
}, 'render-www-page');

/**
 * Render the Search Page for Positions with Locations
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/research/*-Position/*-Philippines', function ($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //----------------------------//
    // 2. Prepare Data
    $data = [];

    $position = $request->hasSession('position') ? $request->getSession('position') : $request->getVariables(0);
    $position = str_replace('-', ' ', trim($position));
    $location = str_replace('-', ' ', trim($request->getVariables(1)));


    $data['position'] = $position;
    $data['location'] = $location;

    // get by position
    $request->setStage('position_like', $data['position']);
    $request->setStage('location_like', $data['location']);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('not_filter', 'post_salary_max', null);
    $request->setStage('not_filter', 'post_salary_min', null);
    $request->setStage('order', 'post_updated', 'DESC');

    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);
    $results = $response->getResults();

    if (!$results['total']) {
        cradle('global')->flash('Not Found!', 'danger');
        return cradle('global')->redirect('/research');
    }

    $companies = [];
    // get average employer by company
    foreach ($results['rows'] as $company) {
        $companies[$company['profile_id']] = $company['profile_id'];
    }

    // get average employer
    $data['average_employer'] = count($companies);

    // last updated post
    $data['last_updated'] = $results['rows'][0]['post_updated'];

    $salaryMin = array_filter(array_column($results['rows'], 'post_salary_min'));
    $salaryMax = array_filter(array_column($results['rows'], 'post_salary_max'));

    // limit salary min
    foreach ($salaryMin as $key => $value) {
        if ($value <= 100) {
            unset($salaryMin[$key]);
        }
    }

    // limit salary max
    foreach ($salaryMax as $key => $value) {
        if ($value > 900000) {
            unset($salaryMax[$key]);
        }
    }

    // salary min | max
    $data['salary_min'] = min($salaryMin);
    $data['salary_max'] = max($salaryMax);

    // get average salary
    $data['average_salary'] = floor((($data['salary_min'] + $data['salary_max']) / 2));

    // get top companies
    $request->removeStage('order');
    $request->setStage('range', 5);
    $request->setStage('order', 'post_salary_max', 'DESC');
    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);

    $topCompanies = $response->getResults('rows');

    foreach ($topCompanies as $company) {
        if (isset($data['top_companies'][$company['post_name']])) {
            continue;
        }

        $data['top_companies'][$company['post_name']] = $company;
    }

    // get average salary
    $data['average_salary'] = $topCompanies[0]['post_salary_max'];

    // find description on PayScale
    //TODO save to database
    $request->removeStage();
    $request->setStage('post_position', $position);
    cradle()->trigger('crawl-page', $request, $response);

    if (!$response->isError()) {
        $data['description'] = $response->getResults('description');
    }

    // get paid companies
    $request->removeStage();
    $request->setStage('range', 5);
    $request->setStage('filter', 'post_position', $position);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('filter', 'post_flag', 1);
    $request->setGet('noindex', true);
    cradle()->trigger('post-search', $request, $response);
    $data['paid_companies'] = $response->getResults('rows');

    // latest job post
    $request->removeStage();
    $request->setStage('order', 'post_created', 'DESC');
    $request->setStage('position_like', $position);
    $request->setStage('filter', 'post_location', $location);
    $request->setStage('filter', 'post_type', 'poster');
    $request->setStage('not_filter', 'post_salary_max', null);
    $request->setStage('not_filter', 'post_salary_min', null);
    $request->setStage('range', 5);
    cradle()->trigger('post-search', $request, $response);
    $latest = $response->getResults('rows');

    $data['latest_jobs'] = $latest;

    // Render body
    $class = 'page-research page-research-position-location';
    $body = cradle('/app/www')->template('research/position', $data, [
            'research_companies',
            'research_jobs',
            'research_ads'
        ]);

    $title = cradle('global')->translate('Research');

    //set content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-www-page');

/**
 * Get Top Location
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/ajax/research/location', function ($request, $response) {
    $data = $request->getStage();

    // set filter by location
    $request->setStage('filter', 'feature_type', 'location');

    cradle()->trigger('feature-search', $request, $response);

    // get results
    $results = $response->getResults('rows');

    // remove filter on stage
    $request->removeStage('filter');

    // set position
    $request->setStage('position', $data['position']);

    // get list of locations
    $configLocations = cradle('global')->config('location');

    $data['topLocation'] = $data['location'] = [];

    // loop featured results
    foreach ($results as $feature) {
        $location = $feature['feature_name'];

        $locations = [];

        // set locations if exists on config
        if (isset($configLocations[$location])
            && is_array($configLocations[$location])
            && !empty($configLocations[$location])
        ) {
            $locations = $configLocations[$location];
        }

        // set locations
        $request->setStage('locations', $locations);

        cradle()->trigger('post-opening-location', $request, $response);

        // collect total position's opening per location
        $data['location'][$location] = $feature;
        $data['location'][$location]['total'] = $response->getResults('total');

        // use for sorting
        $data['total'][$location] = $response->getResults('total');
    }

    // sort value high to low
    arsort($data['total']);

    // collect only 4 locations
    foreach ($data['total'] as $key => $value) {
        if ($data['location'][$key]['total'] == 0) {
            $data['hide'] = true;
            break;
        }

        if (count($data['topLocation']) >= 4) {
            break;
        }

        // get location
        $data['topLocation'][$key] = $data['location'][$key];
    }

    // unset keys
    unset($data['total']);
    unset($data['location']);

    $data['topLocationCounter'] = count($data['topLocation']);

    $body = cradle('/app/www')->template(
        'research/location/_top',
        $data
    );

    $response
        ->setError(false)
        ->setResults(null)
        ->setContent($body);
});
