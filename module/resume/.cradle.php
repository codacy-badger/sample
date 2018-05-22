<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Resume\Service as ResumeService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('resume', ResumeService::class);
