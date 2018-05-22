<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Terms');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Check Active Terms');

$I->wantTo('Click Active');
$I->click('.page-admin-term-search a.btn-primary');
$I->seeInCurrentUrl('/control/term/search?filter%5Bterm_active%5D=1');