<?php //-->
include_once __DIR__ . '/src/Interview_schedule/events.php';
include_once __DIR__ . '/src/Interview_setting/events.php';

use Cradle\Module\Interview\Interview_schedule\Service as Interview_scheduleService;
use Cradle\Module\Interview\Interview_setting\Service as Interview_settingService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('interview_schedule', Interview_scheduleService::class);
ServiceFactory::register('interview_setting', Interview_settingService::class);
