<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Lead\Service as LeadService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('lead', LeadService::class);
