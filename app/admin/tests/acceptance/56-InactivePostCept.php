<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Inactive Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Go to Active Post');

$I->click('a[href="?filter%5Bpost_active%5D=0"]');

$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=0');