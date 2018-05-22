<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Widget\Service as WidgetService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('widget', WidgetService::class);
