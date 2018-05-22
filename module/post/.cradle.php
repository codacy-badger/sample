<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Post\Service as PostService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('post', PostService::class);
