<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Upload file Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Upload file post');

$I->attachFile('.page-admin-post-search input[type="file"]', 'post_format.csv');
$I->click('.page-admin-post-search button.btn-info');

 $I->seeInCurrentUrl('/control/post/search');

// $I->wait(2);
// $I->see('[0] post Created 
// [0] post Updated 
// [0] Error(s)');

// $I->amOnPage('/control/history/search');

// $I->seeInCurrentUrl('/control/history/search');

// $I->see('Profile id #1 imported a post file');