<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Degree\Service as DegreeService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('degree', DegreeService::class);
