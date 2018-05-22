<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Term\Service as TermService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('term', TermService::class);
