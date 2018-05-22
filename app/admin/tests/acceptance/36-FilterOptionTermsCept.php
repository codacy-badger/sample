<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Terms By Type');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Filter Terms By Type');

// $I->selectOption('//form/select[@data-do="show-select"]', 'Type');
$I->selectOption('//form/div/select[@class="form-control pull-left term_type"]', 'Position');
$I->click('.page-admin-term-search input.btn-success');

$I->seeInCurrentUrl('/control/term/search');
