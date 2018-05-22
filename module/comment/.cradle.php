<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Comment\Service as CommentService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('comment', CommentService::class);
