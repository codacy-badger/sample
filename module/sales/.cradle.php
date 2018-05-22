<?php //-->
include_once __DIR__ . '/src/Deal/events.php';
include_once __DIR__ . '/src/Pipeline/events.php';

use Cradle\Module\Sales\Deal\Service as DealService;
use Cradle\Module\Sales\Pipeline\Service as PipelineService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('deal', DealService::class);
ServiceFactory::register('pipeline', PipelineService::class);
