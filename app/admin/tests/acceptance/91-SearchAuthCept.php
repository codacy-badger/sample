<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Auth Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/auth/search');
$I->amGoingTo('Search Auth Details');

// 
$I->fillField('.page-admin-auth-search input[name="q[]"]', 'john@doe.com');

// submit form
$I->click('.page-admin-auth-search button.btn');

$I->see('Auth');

$I->seeInCurrentUrl('/control/auth/search?q%5B%5D=john%40doe.com');

