<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Event\Service as EventService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('event', EventService::class);
