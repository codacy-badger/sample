<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Inactive Terms');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Check Inactive Terms');

$I->wantTo('Click Inactive Terms');
$I->click('.page-admin-term-search a.btn-danger');
$I->seeInCurrentUrl('/control/term/search?filter%5Bterm_active%5D=0');

// click restore icon
$I->click(['xpath' => '//tr/td/a[@href="/control/term/restore/8"]']);

$I->seeInCurrentUrl('/control/term/search');

$I->wait(2);
$I->see('Term was Restored');