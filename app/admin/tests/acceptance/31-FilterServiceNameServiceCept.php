<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Service Name');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Filter Service Name');

// $I->selectOption('//form/select[@name="filter[service_name]"]', 'Resume Download');
// $I->click('.page-admin-service-search input.btn-success');

// $I->seeInCurrentUrl('/control/service/search?filter%5Bservice_name%5D=Resume+Download');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'All');
$I->wait(2);
$I->see('Services');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Resume Download');
$I->wait(2);
$I->see('Services');
// $I->see('No Results Found');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Sms Interest');
$I->wait(2);
$I->see('Services');
// $I->see('No Results Found');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Post Promotion');
$I->wait(2);
$I->see('Services');
// $I->see('No Results Found');