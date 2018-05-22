<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/position/search');
$I->amGoingTo('Go to Active Position');

$I->click('//a[@href="?filter[position_active]=1"]');
$I->seeInCurrentUrl('/control/position/search');

$I->see('1 Positions');