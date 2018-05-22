<?php //-->

//include the other routes
include_once __DIR__ . '/src/controller/auth.php';
include_once __DIR__ . '/src/controller/profile.php';

//START: GENERATED CONTROLLERS
include_once __DIR__ . '/src/controller/history.php';
include_once __DIR__ . '/src/controller/static.php';
include_once __DIR__ . '/src/controller/arrangement.php';
include_once __DIR__ . '/src/controller/blog.php';
include_once __DIR__ . '/src/controller/currency.php';
include_once __DIR__ . '/src/controller/feature.php';
include_once __DIR__ . '/src/controller/industry.php';
include_once __DIR__ . '/src/controller/position.php';
include_once __DIR__ . '/src/controller/post.php';
include_once __DIR__ . '/src/controller/research.php';
include_once __DIR__ . '/src/controller/role.php';
include_once __DIR__ . '/src/controller/service.php';
include_once __DIR__ . '/src/controller/term.php';
include_once __DIR__ . '/src/controller/transaction.php';
include_once __DIR__ . '/src/controller/utm.php';
include_once __DIR__ . '/src/controller/ajax/export.php';
//END: GENERATED CONTROLLERS

//include global events, methods
include_once __DIR__ . '/src/events.php';
include_once __DIR__ . '/src/methods.php';
