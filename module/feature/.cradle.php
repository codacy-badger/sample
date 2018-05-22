<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Feature\Service as FeatureService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('feature', FeatureService::class);
