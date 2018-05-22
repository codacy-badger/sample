<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Campaign\Service as CampaignService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('campaign', CampaignService::class);
