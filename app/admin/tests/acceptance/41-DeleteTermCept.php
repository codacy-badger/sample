<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Term Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Delete Term Details');

$I->wantTo('Click Edit');
$I->click(['xpath' => '//tr/td/a[@href="/control/term/remove/8"]']);


$I->seeInCurrentUrl('/control/term/search');

$I->wait(2);
$I->see('Term was Removed');