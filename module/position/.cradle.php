<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Position\Service as PositionService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('position', PositionService::class);
