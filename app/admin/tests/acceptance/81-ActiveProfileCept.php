<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Check Active Profile');

$I->click('a[href="?filter%5Bprofile_active%5D=1"]');

$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=1');