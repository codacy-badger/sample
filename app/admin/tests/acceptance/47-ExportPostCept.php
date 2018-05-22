<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export Post Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Export Post Details');

// click export
$I->click('.page-admin-post-search .upload-export a.export-button');

$I->seeInCurrentUrl('/csv/post_format.csv');

