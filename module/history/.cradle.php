<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\History\Service as HistoryService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('history', HistoryService::class);
