<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

//include the other routes
include_once __DIR__ . '/src/controller/rest/auth.php';
include_once __DIR__ . '/src/controller/rest/profile.php';
include_once __DIR__ . '/src/controller/rest/charts.php';

include_once __DIR__ . '/src/controller/dialog/auth.php';
include_once __DIR__ . '/src/controller/developer/auth.php';
include_once __DIR__ . '/src/controller/developer/app.php';

//START: GENERATED CONTROLLERS
include_once __DIR__ . '/src/controller/rest/thread.php';
include_once __DIR__ . '/src/controller/rest/service.php';
include_once __DIR__ . '/src/controller/rest/transaction.php';
include_once __DIR__ . '/src/controller/rest/file.php';
include_once __DIR__ . '/src/controller/rest/comment.php';
include_once __DIR__ . '/src/controller/rest/event.php';
include_once __DIR__ . '/src/controller/rest/history.php';
include_once __DIR__ . '/src/controller/rest/pipeline.php';
include_once __DIR__ . '/src/controller/rest/label.php';
include_once __DIR__ . '/src/controller/rest/deal.php';
include_once __DIR__ . '/src/controller/rest/utm.php';
include_once __DIR__ . '/src/controller/rest/campaign.php';
include_once __DIR__ . '/src/controller/rest/lead.php';
include_once __DIR__ . '/src/controller/rest/template.php';
include_once __DIR__ . '/src/controller/rest/action.php';
include_once __DIR__ . '/src/controller/rest/term.php';
include_once __DIR__ . '/src/controller/rest/post.php';
//END: GENERATED CONTROLLERS

//include globals, events, methods
include_once __DIR__ . '/src/package/methods.php';
include_once __DIR__ . '/src/package/events.php';
