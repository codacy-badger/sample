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
// $I->amGoingTo('Filter Post By Type');

// $I->attachFile('.page-admin-post-search input[type="file"]', 'post_format.csv');
// $I->click('.page-admin-post-search button.btn-info');
// $I->wait(2);
// $I->seeInCurrentUrl('/control/post/search');

// $I->wait(2);
// $I->see('[2] Post Created 
// [0] Post Updated 
// [0] Error(s)');