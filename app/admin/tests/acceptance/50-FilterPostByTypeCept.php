<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Post By Type');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Filter Post By Type');

// $I->selectOption('//form/select[@data-do="show-select"]', 'filter by post type');
// $I->selectOption('//form/select[@name="filter[post_type]"]', 'poster');
// $I->click('.page-admin-post-search input.btn-success');

// $I->seeInCurrentUrl('/control/post/search?filter%5Bpost_type%5D=poster');

$I->selectOption('//form/div/select[@class="form-control pull-left post_type"]', 'All');
$I->click('button.btn-success.post-type-search');
$I->wait(2);
$I->see('Posts');

$I->selectOption('//form/div/select[@class="form-control pull-left post_type"]', 'Poster');
$I->click('button.btn-success.post-type-search');
$I->wait(2);
$I->see('Posts');

$I->selectOption('//form/div/select[@class="form-control pull-left post_type"]', 'Seeker');
$I->click('button.btn-success.post-type-search');
$I->wait(2);
$I->see('Posts');