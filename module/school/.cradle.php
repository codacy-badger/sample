<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\School\Service as SchoolService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('school', SchoolService::class);
