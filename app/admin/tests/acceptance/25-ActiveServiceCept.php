<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Service');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Click Active Service');

$I->click('.page-admin-service-search a.btn-primary');

$I->seeInCurrentUrl('/control/service/search?filter%5Bservice_active%5D=1');