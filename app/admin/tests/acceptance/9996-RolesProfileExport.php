<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export Profile Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginRole();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Export Profile Details');

// click export
$I->click('.page-admin-profile-search .pull-right a.export-button');

$I->seeInCurrentUrl('/control/profile/search');

