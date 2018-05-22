<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Inactive UTM');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/utm/search');
$I->amGoingTo('Click Inactive UTM');

$I->click('.page-admin-utm-search a.btn-danger');

$I->seeInCurrentUrl('/control/utm/search?filter%5Butm_active%5D=0');
