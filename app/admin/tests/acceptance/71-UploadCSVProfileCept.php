<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Upload CSV Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Upload CSV Profile');

$I->attachFile('.page-admin-profile-search input[type="file"]', 'profile_format.csv');
$I->click('.page-admin-profile-search button.btn-info');

// $I->seeInCurrentUrl('/control/profile/search');

// $I->wait(2);
// $I->see('[1] Profile Created 
// [0] Profile Updated 
// [0] Error(s) ');
