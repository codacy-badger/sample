<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export Terms Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Export Terms Details');

// click export
$I->click('.page-admin-term-search a.export-button');

$I->seeInCurrentUrl('/control/term/search');

