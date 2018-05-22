<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export Auth');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/auth/search');
$I->amGoingTo('Export Auth');

// 
$I->fillField('.page-admin-auth-search input[name="q[]"]', 'john@doe.com');

// click export
$I->click('.page-admin-auth-search a.export-button');

$I->see('Auth');

$I->seeInCurrentUrl('/control/auth/search');

