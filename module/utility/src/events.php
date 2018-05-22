<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\Validator;
use Cradle\Module\Utility\File;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Cradle\Module\Transaction\Service as TransactionService;
use PHPMailer\PHPMailer\PHPMailer;

use Cradle\Curl\CurlHandler as Curl;

/**
 * CLI queue - bin/cradle queue auth-verify auth_slug=<email>
 *
 * @param Request $request
 * @param Response $response
 *
 * @return string
 */
$cradle->flow('queue', 'faucet-queue');

/**
 * CLI starts worker
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->flow('work', 'faucet-work');

/**
 * CLI Deploy
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->flow('deploy-production', 'faucet-deploy-production');

/**
 * CLI Deploy
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->flow('deploy-s3', 'faucet-deploy-s3');

/**
 * CLI production connect
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->flow('connect', 'faucet-connect-to');

/**
 * CLI server
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->flow('server', 'faucet-server');

/**
 * Crawl Page Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('crawl-page', function ($request, $response) {
    /*
        TODO
        Optimize crawl page
        Right now we will use this specific for payscale
        crawled by description
    */
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $position = str_replace(' ', '_', $data['post_position'] ? $data['post_position'] : '');

    $url = "https://www.payscale.com/research/PH/Job=" . $position . "/Salary";

    $seen = [];
    if (isset($seen[$url])) {
        return;
    }

    $seen[$url] = true;

    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);

    if (isset($dom->getElementById('abstractMoreDiv')->parentNode)) {
        $description = $dom->getElementById('abstractMoreDiv')
            ->parentNode
            ->nodeValue;

        $description = str_replace('Read More...', '', $description);

        return $response
            ->setError(false)
            ->setResults('description', $description);
    }

    return $response->setError(true, 'Not Found');
});

/**
 * Csv Export Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('csv-export', function ($request, $response) {
    $data['csv'] = $request->getStage('csv');
    $data['header'] = $request->getStage('header');
    $data['filename'] = $request->getStage('filename');
    $header = [];
    $fields = [];
    $newData = [];

    //Set CSV header
    if (isset($data['csv'][0])) {
        foreach (array_keys($data['csv'][0]) as $key => $value) {
            if (array_key_exists($value, $data['header'])) {
                $header[] = $data['header'][$value];
                $fields[] = $value;
            }
        }

        $fields = array_intersect(array_keys($data['header']), $fields);
        $header = array_values($request->getStage('header'));
    }

    if (!$data['csv']) {
        $header = $data['header'];
    }

    //Set new rows by required field
    foreach ($data['csv'] as $row) {
        $newRow = [];
        $arranged = [];

        foreach ($row as $key => $value) {
            if (in_array($key, $fields)) {
                $newRow[array_search($key, $fields)] = $row[$key];
            }
        }

        ksort($newRow);
        $newData[] = array_combine($fields, $newRow);
    }

    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename=' . $data['filename']);
    header("Content-Transfer-Encoding: UTF-8");

    // create and open file
    ob_clean();
    $f = fopen('php://output', 'w');

    // put csv headers
    fputcsv($f, $header);
    // set csv data
    foreach ($newData as $row) {
        fputcsv($f, $row);
    }

    // close file
    fclose($f);

    return ' ';
});

/**
 * Csv to S3 Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('csv-s3-export', function ($request, $response) {
    $data['csv'] = $request->getStage('csv');
    $data['header'] = $request->getStage('header');
    $data['filename'] = $request->getStage('filename');
    $header = [];
    $fields = [];
    $newData = [];

    //Set CSV header
    if (isset($data['csv'][0])) {
        foreach (array_keys($data['csv'][0]) as $key => $value) {
            if (array_key_exists($value, $data['header'])) {
                $header[] = $data['header'][$value];
                $fields[] = $value;
            }
        }

        $fields = array_intersect(array_keys($data['header']), $fields);
        $header = array_values($request->getStage('header'));
    }

    if (!$data['csv']) {
        $header = $data['header'];
    }

    //Set new rows by required field
    foreach ($data['csv'] as $row) {
        $newRow = [];
        $arranged = [];

        foreach ($row as $key => $value) {
            if (in_array($key, $fields)) {
                $newRow[array_search($key, $fields)] = $row[$key];
            }
        }

        ksort($newRow);
        $newData[] = array_combine($fields, $newRow);
    }

    // get s3 config
    $config = $this->package('global')->service('s3-main');

    // create and open file
    $filename = tempnam('/tmp', 'csv');
    $f = fopen($filename, 'w');

    // put csv headers
    fputcsv($f, $header);
    // set csv data
    foreach ($newData as $row) {
        fputcsv($f, $row);
    }

    // close file
    fclose($f);

    // prepare csv file
    $base64 = base64_encode(file_get_contents($filename));
    $file = 'data:application/csv;base64,' . $base64;

    // upload to s3
    $results['csv_link'] = File::base64ToS3($file, $config, 'export/', $data['filename']);
    // set response
    $response->setError(false)->setResults($results);
});

/**
 * Csv Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('csv-import', function ($request, $response) {

    //get columns
    $columns = $request->getStage('keys');

    if (!$columns) {
        return $response->setError(true, 'Column is not set');
    }

    $data  =[];
    $mimeTypes = [
        'text/comma-separated-values',
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel'
    ];

    //validate file
    if (empty($_FILES['csv']['tmp_name'])) {
        return $response->setError(true, 'No CSV');
    }

    $extension = substr(strrchr($_FILES['csv']['tmp_name'], "."), 1);
    if (!in_array($_FILES['csv']['type'], $mimeTypes) || $extension == 'csv') {
        return $response->setError(true, 'Invalid CSV');
    }

    $handle = fopen($_FILES['csv']['tmp_name'], 'r');

    $csv =[];
    if ($handle !== false) {
        $ctr = 0;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            //if columns not match
            if (count($columns) != count($row)) {
                return $response->setError(true, 'Columns not Match');
            }
            //set header
            if ($ctr == 0) {
                $data['header'] = $row;
            } else {
                $csv[] = $row;
            }

            $ctr++;
        }
    }

    if (empty($csv)) {
        return $response->setError(true, 'Empty CSV');
    }

    //set columns to key
    foreach ($csv as $item) {
        foreach ($item as $key => $value) {
            $tmp[$columns[$key]] = $value;
        }
        $data['csv'][] = $tmp;
    }

    $response->setError(false)->setResults($data);
});

/**
 * File Upload Job
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('file-upload', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage('upload')) {
        $data = $request->getStage('upload', 'file');
    }

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::getFileUploadErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $base64 = base64_encode(file_get_contents($data['tmp_name']));
    $file   = 'data:' . $data['type'] . ';base64,' . $base64;

    //upload files
    //try cdn if enabled
    $config     = $this->package('global')->service('s3-main');

    if (!$config) {
        //try being old school
        $upload        = $this->package('global')->path('upload');
        $data['resume_link'] = File::base64ToUpload($file, $upload);

        return $response->setError(false)->setResults($data);
    }

    $data['resume_link'] = File::base64ToS3($file, $config);

    //return response format
    $response->setError(false)->setResults($data);
});

/**
 * Link Upload Job
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('link-upload', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage('column_name')) {
        $column =  $request->getStage('column_name');
        $data = $request->getStage($column);
    }

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::isUrl($data);

    // Checks for errors
    if (!$errors) {
        return $response
            ->setError(true, 'Invalid URL')
            ->set('json', 'validation', $errors);
    }

    // upload files
    $config = $this->package('global')->service('s3-main');

    // Checks if we dont have s3 configured
    if (!$config) {
        return $response
            ->setError(true, 'Cannot Upload to Storage')
            ->set('json', 'validation', $errors);
    }

    // Uploads the file to our storage
    $result = File::linkToS3($data, $config);
    $data = [];
    $data[$column] = $result;

    //return response format
    $response->setError(false)->setResults($data);
});

/**
 * Send Email via SES
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('prepare-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();

    $request->setStage('email', $data['to'][0]);
    $data['email'] =  $data['to'][0];

    // settings
    $settings = cradle('global')->config('settings');

    // if not production send email
    // if (!isset($settings['environment'])
    //    || $settings['environment'] != 'production') {
        // if (!$this->package('global')->queue('send-email', $data)) {
            $this->trigger('send-email', $request, $response);
        // }
    // else validate email first
    // } else {
    //     if (!$this->package('global')->queue('validate-email', $data)) {
    //         $this->trigger('validate-email', $request, $response);
    //     }
    // }
});

/**
 * Send Email via SES
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('send-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();

    // $request->setStage('profile_email', $data['email']);
    // cradle()->trigger('profile-email-detail', $request, $response);

    // Checks for errors
    if ($response->isError()) {
        return $response
            ->setError(true, 'Invalid Email');
    }

    $profile = $response->getResults();
    $event = 'send-ses-email';

    // if email is valid send using SES
    // if (isset($profile['profile_email_flag'])
    //     && $profile['profile_email_flag'] == 1) {
    //     $event = 'send-ses-email';
    // }
   

    if (!$this->package('global')->queue($event, $data)) {
        // default smtp
        $this->trigger('send-ses-email', $request, $response);
    }
});

/**
 * Send Email via SES
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('validate-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();

    if (!isset($data['email'])
        && isset($data['profile_email'])) {
        $data['email'] = $data['profile_email'];
    }

    $email = $data['email'];

    // validate email
    $valid = \EmailChecker\Validator::validateEmail($email);
    if (!$valid) {
        return $response
            ->setError(true, 'Invalid Email');
    }

    // get host from email
    $host = \EmailChecker\Validator::getHostFromEmail($email);

    // dig it

    $lookup = new \EmailChecker\Lookup($host);

    // get mx records
    $mx = $lookup->getMxRecords();

    $mx = preg_replace('/\.$/', '', $mx[0]);
    // telnet
    $telnet = new \EmailChecker\Telnet($mx);

    // say helo
    $helo = $telnet->sayHelo();

    // set mail from
    $telnet->mailFrom('test@mailinator.com');

    // set receive to
    $telnet->rcptTo($email);
    if ($host == 'yahoo.com'
        || $host == 'ymail.com'
        || $host == 'yahoomail.com') {
        $telnet->data();
        $telnet->addHeader('Subject', 'SMTP TEST');
        $telnet->setBody('This is a SMTP test');
    }

    try {
        if (!$telnet->check()) {
            return $response
                ->setError(true, 'Invalid Email');
        }
    } catch (Exception $errors) {
        return $response
            ->setError(true, 'Invalid Email');
    }

    // send email
    if ($request->hasStage('to')
        && $request->hasStage('html')) {
        $this->trigger('send-email', $request, $response);
    }

    return $response
        ->setError(false, 'Valid Email');
});

/**
 * Send Email via SES
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('send-ses-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::getSendSesEmailErrors($data);

    //if there are errors
    if (!empty($errors)) {
        $request->setStage('event', 'send-smtp-email');
        $this->trigger('send-email', $request, $response);
        return;
    }

    //----------------------------//
    $config = cradle('global')->service('ses');

    // load ses client
    $ses = SesClient::factory([
        'version' => 'latest',
        'region'  => $config['region'],
        'credentials' => array(
            'key'    => $config['token'],
            'secret' => $config['secret'],
        )
    ]);

    // Create a new PHPMailer object.
    $mail = new PHPMailer;

    if (!isset($data['senderName'])) {
        $data['senderName'] = $data['from'];
    }

    // set default text
    if (!isset($data['text'])) {
        $data['text'] = $data['html'];
    }

    $mail->setFrom($data['from'], $data['senderName']);
    if (is_array($data['to'])) {
        foreach ($data['to'] as $to) {
            $mail->addAddress($to);
        }
    } else {
        $mail->addAddress($data['to']);
    }

    $mail->Subject = $data['subject'];
    $mail->Body = $data['html'];
    $mail->AltBody = $data['text'];
    $mail->addCustomHeader('X-SES-CONFIGURATION-SET', 'events');

    if (isset($data['attachment'])) {
        if (is_array($data['attachment'])) {
            foreach ($data['attachment'] as $path) {
                $mail->addAttachment($path);
            }
        } else {
            $mail->addAttachment($data['attachment']);
        }
    }

    if (isset($data['custom_headers']) && is_array($data['custom_headers'])) {
        foreach ($data['custom_headers'] as $header => $value) {
             $mail->addCustomHeader($header, $value);
        }
    }

    // Attempt to assemble the above components into a MIME message.
    if (!$mail->preSend()) {
        // echo $mail->ErrorInfo;
        return $response
            ->setError(true, $mail->ErrorInfo);
    } else {
        // Create a new variable that contains the MIME message.
        $message = $mail->getSentMIMEMessage();
    }

     // Try to send the message.
    try {
        $result = $ses->sendRawEmail([
            'RawMessage' => [
                'Data' => $message
            ]
        ]);

        return $response
        ->setError(false)
        ->setResults([
            'message' => 'Message sent!',
            'messageId' => $result->get('MessageId')
        ]);
    } catch (Exception $errors) {
        //if there are errors
        if (!empty($errors)) {
            $request->setStage('event', 'send-smtp-email');
            $this->trigger('send-email', $request, $response);
        }
    }
});

/**
 * Send Email via Smtp
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('send-smtp-email', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = $request->getStage();

    //----------------------------//
    // 2. Validate Data
    $errors = Validator::getSendSmtpEmailErrors($data);
    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    $config = $this->package('global')->service('mail-main');

    if (!$config) {
        return;
    }

    //if it's not configured
    if ($config['user'] === '<EMAIL ADDRESS>'
        || $config['pass'] === '<EMAIL PASSWORD>'
    ) {
        return;
    }

    $data['from'] = [$config['user'] => $config['name']];

    //send mail
    $message = new Swift_Message($data['subject']);

    // if not array
    if (!is_array($data['from'])) {
        $data['from'] = [$data['from'] => null];
    }

    $message->setFrom($data['from']);
    $message->setTo($data['to']);
    $message->setBody($data['html'], 'text/html');

    // Checks if there is a text to add
    if (isset($data['text'])) {
        $message->addPart($data['text'], 'text/plain');
    }

    $transport = Swift_SmtpTransport::newInstance();
    $transport->setHost($config['host']);
    $transport->setPort($config['port']);
    $transport->setEncryption($config['type']);
    $transport->setUsername($config['user']);
    $transport->setPassword($config['pass']);

    $swift = Swift_Mailer::newInstance($transport);
    $swift->send($message, $failures);
});

/**
 * Send Email via Smtp
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('contact-email', function ($request, $response) {
    $errors = Validator::getContactEmailErrors($request->getStage());

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'All Fields Required')
            ->set('json', 'validation', $errors);
    }

    //prepare data
    $data = [];
    $config = cradle('global')->service('ses');
    $data['from'] = $config['sender'];
    $config = cradle('global')->service('contact_us');

    $subject = $request->getStage('contact_subject');
    switch ($subject) {
        case 'bug_report':
            $subject = 'Bug Report';
            $data['to'] = $config['bug_report'];
            $action = 'and reported a bug';
            break;
        case 'marketing':
            $subject = 'Marketing Inquiry';
            $data['to'] = $config['marketing'];
            $action = 'and is asking a marketing question';
            break;
        case 'quote':
            $subject = 'Request for Quotation';
            $data['to'] = $config['sales'];
            $action = 'and requested for a quotation';
            break;
        default:
            $subject = 'Jobayan Inquiry';
            $data['to'] = $config['default'];
            $action = 'and is inquiring';
            break;
    }

    $data['subject'] = $this->package('global')->translate($subject);
    $handlebars = $this->package('global')->handlebars();

    $settings = $this->package('global')->config('settings');
    $host = $settings['host'];

    $contents = file_get_contents(__DIR__ . '/template/email/contact.txt');
    $template = $handlebars->compile($contents);

    $contents = file_get_contents(__DIR__ . '/template/email/contact.html');
    $template = $handlebars->compile($contents);
    $data['html'] = $template([
        'host'    => $host,
        'subject' => $subject,
        'action'  => $action,
        'item'    => $request->getStage()
    ]);

    $request->setStage($data);
    $this->trigger('send-ses-email', $request, $response);
    // if it wasn't sent thru ses
    if ($response->isError()) {
        $this->trigger('send-smtp-email', $request, $response);
    }
});

$cradle->on('calculate-existing-users-experience', function ($request, $response) {
    // get the users
    cradle()->trigger('profile-search', $request, $response);
    // get total users
    $totalUsers = $response->getResults('total');
    $range = 100;
    for ($i=0; $i < $totalUsers; $i++) {
        $request->setStage('range', $range);
        $request->setStage('start', $i);

        cradle()->trigger('profile-search', $request, $response);
        $users = $response->getResults('rows');
        $points = cradle('global')->config('experience');
        $badges = cradle('global')->config('achievements');

        foreach ($users as $key => $user) {
            $experience = 0;
            $achievements = [];
            // give the sign up experience points
            $experience += $points['signup'];

            // give the sign up badge
            $achievements[] = 'signup';

            // if this user is not a company
            // just award the experience and achievement for signup then
            // ignore the next lines
            if (empty(trim($user['profile_company']))) {
                $request->setStage('profile_id', $user['profile_id']);
                $request->setStage('profile_experience', $experience);
                $request->setStage('profile_achievements', json_encode($achievements));
                cradle()->trigger('profile-update', $request, $response);
                continue;
            }

            // check if profile is updated
            // compare created and updated
            if ($user['profile_created'] != $user['profile_updated']) {
                $experience += $points['profile_update'];
            }

            // if verified company
            if ($user['profile_verified'] == 1) {
                $achievements[] = 'verified_company';
            }

            // if verified recruiter
            if ($user['profile_verified'] == 2) {
                $achievements[] = 'verified_recruiter';
            }

            // get credits in transaction
            $totalCredits = TransactionService::get('sql')
                ->getTotalCredits($user['profile_id']);

            // multiply by the multiplier on our config to get experience
            $experience += $totalCredits * $points['credit_purchase'];

            // set profile id, we will be need this for multiple usage
            $request->setStage('filter', ['profile_id' => $user['profile_id']]);

            // get post total
            $this->trigger('post-search', $request, $response);
            $postCount = $response->getResults('total');

            // if there's atleast 1 post, award first post badge
            if ($postCount) {
                $achievements[] = 'post_1';
            }

            // if there's atleast 10, post award 10th post badge
            if ($postCount >= 10) {
                $achievements[] = 'post_10';
            }

            // if there's atleast 50, post award 50th post badge
            if ($postCount >= 50) {
                $achievements[] = 'post_50';
            }

            // if there's atleast 100, post award 100th post badge
            if ($postCount >= 100) {
                $achievements[] = 'post_100';
            }

            // award posting experience, make postCount as a multiplier
            $experience += $experience['create_post'] * $postCount;

            $request->setStage('profile_id', $user['profile_id']);

            // get total post interested
            $request->setStage('activity', 'interested');
            $this->trigger('post-get-count', $request, $response);
            $total = $response->getResults();

            // if there's atleast 1 interested, award first interested badge
            if ($total) {
                $achievements[] = 'interested_1';
            }

            // if there's atleast 10 interested, award 10th interested badge
            if ($total >= 10) {
                $achievements[] = 'interested_10';
            }

            // if there's atleast 50 interested, award 50th interested badge
            if ($total >= 50) {
                $achievements[] = 'interested_50';
            }

            // if there's atleast 100 interested, award 100th interested badge
            if ($total >= 100) {
                $achievements[] = 'interested_100';
            }

            // award interested experience, make total as a multiplier
            $experience += $experience['interested'] * $total;

            // get total promoted
            $request->setStage('activity', 'promoted');
            $this->trigger('post-get-count', $request, $response);
            $total = $response->getResults();

            // if there's atleast 10 promoted, award 10th promoted badge
            if ($total >= 10) {
                $achievements[] = 'promoted_10';
            }

            // if there's atleast 50 promoted, award 50th promoted badge
            if ($total >= 50) {
                $achievements[] = 'promoted_50';
            }

            // if there's atleast 100 promoted, award 100th promoted badge
            if ($total >= 100) {
                $achievements[] = 'promoted_100';
            }

            // award promoted experience, make total as a multiplier
            $experience += $experience['promote_post'] * $total;

            // get total resume downloaded
            $request->setStage('activity', 'downloaded');
            $this->trigger('post-get-count', $request, $response);
            $total = $response->getResults();

            // if there's atleast 10 downloaded, award 10th downloaded badge
            if ($total >= 10) {
                $achievements[] = 'downloaded_10';
            }

            // if there's atleast 50 downloaded, award 50th downloaded badge
            if ($total >= 50) {
                $achievements[] = 'downloaded_50';
            }

            // if there's atleast 100 downloaded, award 100th downloaded badge
            if ($total >= 100) {
                $achievements[] = 'downloaded_100';
            }

            // award resume download experience, make total as a multiplier
            $experience += $experience['resume_download'] * $total;

            $request->setStage('profile_experience', $experience);
            $request->setStage('profile_achievements', $achievements);
            cradle()->trigger('profile-update', $request, $response);

            // remove everything we added in the request stage
            $request->removeStage('filter');
            $request->removeStage('profile_id');
            $request->removeStage('activity');
            $request->removeStage('profile_experience');
            $request->removeStage('profile_achievements');
        }

        $i += $range - 1;
    }
});

$cradle->on('google-geomap', function ($request, $response) {
    // Variable declaration
    $location = null;
    $data = [];

    // Checks for staging data
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    // Checks for post_location
    if (isset($data['post_location'])) {
        $location = $data['post_location'];
    }

    // Checks to see if we should skip database checking
    if (!isset($data['skip_check'])) {
        cradle()->trigger('post-geo-location', $request, $response);

        // Checks to see if we have the geo location saved already
        if (!empty($response->getResults())) {
            $location = $response->getResults();
            // Return the locations
            return $response->setResults(json_decode($location['post_geo_location'], true));
        }
    }

    // Gets the services config
    $config = $this->package('global')->config('services');

    // Checks for google config
    if (!isset($config['google'])) {
        return $response->setError(true)->setResults([]);
    }

    // Gets the google config
    $config = $config['google'];

    // Gets the settings
    $settings = $this->package('global')->config('services');

    // Default country_code
    $ccode = 'PH';

    // Checks if there is a country_code set
    if (isset($settings['country_code'])) {
        $ccode = $settings['country_code'];
    }

    // Sets the param
    $param = array(
        'region'  => $ccode,
        'address' => $location,
        'key'     => $config['secret']
    );

    // Constructs the url
    foreach ($param as $index => $value) {
        $url[] = $index . '=' . rawurlencode($value);
    }

    $url = $config['endpoint'] . '/json?' . implode('&', $url);

    // Gets the results
    $result = Curl::i()
        ->setUrl($url)
        ->getJsonResponse();

    // Checks for results
    if (!empty($result['results'])) {
        // Checks if the location is set
        if ($result['results'][0]['geometry']['location']) {
            $location = $result['results'][0]['geometry']['location'];
            $location = array(
                'lat' => $location['lat'],
                'lon' => $location['lng']
            );

            // Returns the nearest location found based on name of city
            return $response->setError(false)->setResults($location);
        }
    }

    $response->setError(false)->setResults([]);
});

$cradle->on('create-deals-for-existing-company', function ($request, $response) {
    $request->setStage('range', 1);

    cradle()->trigger('pipeline-search', $request, $response);
    $pipeline = $response->getResults('rows');
    $pipeline = $pipeline[0];

    $request->setStage('filter', ['type' => 'poster']);
    cradle()->trigger('profile-search', $request, $response);
    $total = $response->getResults('total');

    $request->setStage('range', 100);
    $start = 0;

    while ($start <= $total-1) {
        $request->setStage('start', $start);
        cradle()->trigger('profile-search', $request, $response);
        $users = $response->getResults();

        if (!isset($users['rows'])) {
            continue;
        }

        $users = $users['rows'];

        foreach ($users as $user) {
            // remove admin and agent accounts here
            if ($user['profile_type'] == 'admin' ||
                $user['profile_type'] == 'agent') {
                continue;
            }

            $dealRequest = Cradle\Http\Request::i();
            $dealResponse = Cradle\Http\Response::i();

            $dealRequest->setStage(
                'filter',
                [
                    'deal_company.profile_id' => $user['profile_id'],
                    'deal_type' => 'profile'
                ]
            );

            cradle()->trigger('deal-search', $dealRequest, $dealResponse);
            $deals = $dealResponse->getResults();

            if ($deals['total']) {
                continue;
            }

            $dealRequest->setStage('deal_company', $user['profile_id']);
            $dealRequest->setStage('deal_name', $user['profile_company']);
            $dealRequest->setStage('deal_type', 'profile');
            $closeDate = date('Y-m-d', strtotime("+3 months", strtotime($user['profile_created'])));
            $dealRequest->setStage('deal_close', $closeDate);
            $dealRequest->setStage('deal_amount', 100);
            $dealRequest->setStage('deal_status', $pipeline['pipeline_stages'][0]);
            $dealRequest->setStage('pipeline_id', $pipeline['pipeline_id']);

            cradle()->trigger('deal-create', $dealRequest, $dealResponse);

            if (!$dealResponse->isError()) {
                $dealRequest->setStage('deal_id', $dealResponse->getResults('deal_id'));

                $dealRequest->setStage('deal_created', $user['profile_created']);
                $this->trigger('deal-update', $dealRequest, $dealResponse);

                //only create a history when there's a successfully created deal
                $history = '<strong>'. $user['profile_company']
                    . '</strong> was created as a profile.';
                $dealRequest->setStage('history_action', $history);
                $dealRequest->setStage('history_type', 'create');


                // create history
                $this->trigger('history-create', $dealRequest, $dealResponse);
                $dealRequest->setStage('history_id', $dealResponse->getResults('history_id'));

                // update history create
                $dealRequest->setStage('history_created', $user['profile_created']);
                $this->trigger('history-update', $dealRequest, $dealResponse);
            }
        }

        $start += $request->getStage('range');
    };
});

$cradle->on('create-deals-for-lead-company', function ($request, $response) {
    $request->setStage('range', 1);

    cradle()->trigger('pipeline-search', $request, $response);
    $pipeline = $response->getResults('rows');
    $pipeline = $pipeline[0];

    $request->setStage('filter', ['lead_type' => 'poster']);
    cradle()->trigger('lead-search', $request, $response);
    $total = $response->getResults('total');

    $request->setStage('range', 100);
    $start = 0;

    while ($start <= $total-1) {
        $request->setStage('start', $start);
        cradle()->trigger('lead-search', $request, $response);
        $users = $response->getResults();

        if (!isset($users['rows'])) {
            continue;
        }

        $users = $users['rows'];

        foreach ($users as $user) {
            $dealRequest = Cradle\Http\Request::i();
            $dealResponse = Cradle\Http\Response::i();

            $dealRequest->setStage(
                'filter',
                [
                    'deal_company.profile_id' => $user['lead_id'],
                    'deal_type' => 'lead'
                ]
            );

            cradle()->trigger('deal-search', $dealRequest, $dealResponse);
            $deals = $dealResponse->getResults();

            if ($deals['total']) {
                continue;
            }

            $dealRequest->setStage('deal_company', $user['lead_id']);
            $dealRequest->setStage('deal_name', $user['lead_company']);
            $dealRequest->setStage('deal_type', 'lead');
            $closeDate = date('Y-m-d', strtotime("+3 months", strtotime($user['lead_created'])));
            $dealRequest->setStage('deal_close', $closeDate);

            $dealRequest->setStage('deal_amount', 100);
            $dealRequest->setStage('deal_status', $pipeline['pipeline_stages'][0]);
            $dealRequest->setStage('pipeline_id', $pipeline['pipeline_id']);

            cradle()->trigger('deal-create', $dealRequest, $dealResponse);

            if (!$dealResponse->isError()) {
                $dealRequest->setStage('deal_id', $dealResponse->getResults('deal_id'));

                // $dealRequest->setStage('deal_created', $user['lead_created']);
                // $this->trigger('deal-update', $dealRequest, $dealResponse);

                //only create a history when there's a successfully created deal
                $history = '<strong>'. $user['lead_company']
                    . '</strong> was created as a lead.';
                $dealRequest->setStage('history_action', $history);
                $dealRequest->setStage('history_type', 'create');

                // create history
                $this->trigger('history-create', $dealRequest, $dealResponse);
                $dealRequest->setStage('history_id', $dealResponse->getResults('history_id'));

                // update history create
                $dealRequest->setStage('history_created', $user['lead_created']);
                $this->trigger('history-update', $dealRequest, $dealResponse);
            }
        }

        $start += $request->getStage('range');
    }

    $response->setError(false);
});

/**
 * Daily Events
 *
 * @param Request  $request
 * @param Response $response
 */
$cradle->on('daily-events', function ($request, $response) {

    $daily = strtotime('+1 day', strtotime('now'));


    // Run Daily Events
    if (!$this->package('global')
        ->queue()
        ->send('check-post-expiration')) {
        $this->trigger('check-post-expiration', $request, $response);
    }

    // After running queues, requeue daily events
    $this->package('global')
        ->queue()
        ->setDelay($daily)
        ->send('daily-events');
});

$cradle->on('log-action', function ($request, $response) {
    // Gets the log path
    $settings = cradle('global')->config('settings');
    $logPath = $settings[$request->getStage('log_path')];

    // Log the data into a file
    $data = array(
        'action'    => $request->getStage('log_action'),
        'timestamp' => date('m-d-Y H:i:s', time()));
    file_put_contents($logPath, print_r($data, true), FILE_APPEND | LOCK_EX);

    $request->removeStage('log_action');
    $request->removeStage('log_path');

    $data = $request->getStage();
    file_put_contents($logPath, print_r($data, true), FILE_APPEND | LOCK_EX);

    $response->setError(false);
});
