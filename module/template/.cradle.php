<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Template\Service as TemplateService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('template', TemplateService::class);
