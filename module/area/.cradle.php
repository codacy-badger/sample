<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Area\Service as AreaService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('area', AreaService::class);
