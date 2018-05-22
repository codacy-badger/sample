<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Inactive Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Check Inactive Profile');

$I->click('a[href="?filter%5Bprofile_active%5D=0"]');

$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=0');