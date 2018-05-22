<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\Currency\Service as CurrencyService;
use Cradle\Module\Utility\ServiceFactory;

ServiceFactory::register('currency', CurrencyService::class);
