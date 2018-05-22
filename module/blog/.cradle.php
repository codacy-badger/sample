<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Blog\Service as BlogService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('blog', BlogService::class);
