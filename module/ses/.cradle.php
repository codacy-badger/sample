<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Ses\Service as SesService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('ses', SesService::class);
