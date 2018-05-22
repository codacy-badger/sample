<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Transaction\Service as TransactionService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('transaction', TransactionService::class);
