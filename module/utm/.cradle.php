<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Utm\Service as UtmService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('utm', UtmService::class);
