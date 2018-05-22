<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Research\Service as ResearchService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('research', ResearchService::class);
