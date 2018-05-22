<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Remove Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/position/search');
$I->amGoingTo('Remove Position');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/position/remove/2"]']);

$I->seeInCurrentUrl('/control/position/search');
$I->wait(2);
$I->see('Position was Removed');


