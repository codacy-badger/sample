<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Service');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Search Service');

// 
$I->fillField('.page-admin-service-search  input[name="q[]"]', 'Jane Doe');

// submit form
$I->click('.page-admin-service-search  button.btn');

// $I->see('2 Services matching Jane Doe');

$I->seeInCurrentUrl('/control/service/search?q%5B%5D=Jane+Doe');

