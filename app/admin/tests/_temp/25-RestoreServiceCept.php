<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Restore Inactive Service');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Click Inactive UTM');

$I->click('.page-admin-service-search a.btn-danger');

$I->seeInCurrentUrl('/control/service/search?filter%5Bservice_active%5D=0');

$I->click(['xpath' => '//tr/td/a[@href="/control/service/restore/2"]']);

$I->seeInCurrentUrl('/control/service/search');

$I->see('Service was restored');

