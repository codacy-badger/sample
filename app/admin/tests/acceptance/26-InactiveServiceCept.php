<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Inactive Service');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Click Inactive Service');

$I->click('.page-admin-service-search a.btn-danger');

$I->seeInCurrentUrl('/control/service/search?filter%5Bservice_active%5D=0');