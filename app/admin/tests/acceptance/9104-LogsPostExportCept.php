<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export file Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Export Profile Details');

// click export
$I->click('.page-admin-post-search .upload-export a.export-button');

$I->wait(2);

$I->amOnPage('/control/history/search');

$I->seeInCurrentUrl('/control/history/search');

$I->see('Profile id #1 imported a post file');