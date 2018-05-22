<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

//include routes
include_once __DIR__ . '/src/controller/ajax.php';
include_once __DIR__ . '/src/controller/deal.php';
include_once __DIR__ . '/src/controller/lead.php';
include_once __DIR__ . '/src/controller/profile.php';
include_once __DIR__ . '/src/controller/pipeline.php';
include_once __DIR__ . '/src/controller/agent.php';
include_once __DIR__ . '/src/controller/comment.php';
include_once __DIR__ . '/src/controller/gmail.php';
include_once __DIR__ . '/src/controller/sms.php';
include_once __DIR__ . '/src/controller/view.php';
include_once __DIR__ . '/src/controller/event.php';

//include globals, events, methods
include_once __DIR__ . '/src/package/globals.php';
include_once __DIR__ . '/src/package/methods.php' ;
include_once __DIR__ . '/src/package/events.php';
