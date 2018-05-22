<?php //-->
include_once __DIR__ . '/src/Accomplishment/events.php';
include_once __DIR__ . '/src/Education/events.php';
include_once __DIR__ . '/src/Experience/events.php';
include_once __DIR__ . '/src/Information/events.php';

use Cradle\Module\Tracer\Accomplishment\Service as AccomplishmentService;
use Cradle\Module\Tracer\Education\Service as EducationService;
use Cradle\Module\Tracer\Experience\Service as ExperienceService;
use Cradle\Module\Tracer\Information\Service as InformationService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('accomplishment', AccomplishmentService::class);
ServiceFactory::register('education', EducationService::class);
ServiceFactory::register('experience', ExperienceService::class);
ServiceFactory::register('information', InformationService::class);
