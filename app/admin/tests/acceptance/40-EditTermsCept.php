<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Term Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Edit Term Details');

$I->wantTo('Click Edit');
$I->click(['xpath' => '//tr/td/a[@href="/control/term/update/8"]']);
$I->seeInCurrentUrl('/control/term/update/8');

// no fields to fill yet

// click submit
$I->click('.page-developer-term-update button.btn-primary');

$I->seeInCurrentUrl('/control/term/search');

$I->wait(2);
$I->see('Term was Updated');