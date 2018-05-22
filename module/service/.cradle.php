<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Service\Service as ServiceService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('service', ServiceService::class);
