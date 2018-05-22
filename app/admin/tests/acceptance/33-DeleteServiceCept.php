<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Service');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
// $I->amGoingTo('Delete Service');

// // submit form
// $I->click(['xpath' => '//tr/td/a[@href="/control/service/remove/2"]']);

// $I->seeInCurrentUrl('/control/service/remove/2');

// $I->wait(2);
// $I->see('Service was Removed');