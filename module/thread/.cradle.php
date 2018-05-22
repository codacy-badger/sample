<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Thread\Service as ThreadService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('thread', ThreadService::class);
