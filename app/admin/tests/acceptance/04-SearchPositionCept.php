<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/position/search');
$I->amGoingTo('Search Position');

$I->fillField('.page-admin-position-search input[name="q[]"]', 'John Doe');
$I->click('button.btn');
$I->seeInCurrentUrl('/control/position/search?q%5B%5D=John+Doe');

$I->see('1 Positions matching John Doe');