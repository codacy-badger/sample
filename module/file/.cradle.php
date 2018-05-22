<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\File\Service as FileService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('file', FileService::class);
