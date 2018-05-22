<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Profile By Type');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Filter Profile By Type');

// $I->selectOption('//form/select[@data-do="show-select"]', 'Type');
// $I->selectOption('//form/select[@name="filter[type]"]', 'poster');
// $I->click('.page-admin-profile-search input.btn-success');
// $I->seeInCurrentUrl('/control/profile/search?filter%5Btype%5D=poster');

// $I->selectOption('//form/select[@data-do="show-select"]', 'Type');
// $I->selectOption('//form/select[@name="filter[type]"]', 'seeker');
// $I->click('.page-admin-profile-search input.btn-success');
// $I->seeInCurrentUrl('/control/profile/search?filter%5Btype%5D=seeker');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'All');
$I->see('Profiles');
$I->seeInCurrentUrl('/control/profile/search');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Poster');
$I->see('Profiles');
$I->seeInCurrentUrl('/control/profile/search');

$I->selectOption('//form/div/select[@data-do="redirect-filter"]', 'Seeker');
$I->see('Profiles');
$I->seeInCurrentUrl('/control/profile/search');
