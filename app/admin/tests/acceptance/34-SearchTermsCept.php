<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Term Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Search Term Details');

// 
$I->fillField('.page-admin-term-search input[name="q[]"]', 'Programmer');

// submit form
$I->click('.page-admin-term-search button.btn');

$I->seeInCurrentUrl('/control/term/search?q%5B%5D=Programmer');

