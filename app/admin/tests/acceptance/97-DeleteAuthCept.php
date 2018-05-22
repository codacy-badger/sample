<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Auth');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/auth/search');
$I->amGoingTo('Delete Auth');

$I->click('//tr/td/a[@href="/control/auth/remove/4"]');
$I->seeInCurrentUrl('/control/auth/search');

$I->wait(2);
$I->see('Auth was Removed');


