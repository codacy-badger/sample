<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Action\Service as ActionService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('action', ActionService::class);
