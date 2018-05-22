<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search UTM Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/utm/search');
$I->amGoingTo('Search UTM Details');

// 
$I->fillField('.page-admin-utm-search input[name="q[]"]', 'Foo Bar Test');

// submit form
$I->click('.page-admin-utm-search button.btn');

$I->see('1 UTMs matching Foo bar test');

$I->seeInCurrentUrl('/control/utm/search?q%5B%5D=Foo+Bar+Test');

