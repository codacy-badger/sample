<?php //-->
include(__DIR__.'/../bootstrap.php');

return cradle()
    ->register('/app/admin')
    ->register('/app/api')
    ->register('/app/crawler')
    ->register('/app/sitemap')
    ->register('/app/marketing')
    ->register('/app/sales')
    ->register('/app/www')
    ->render();
