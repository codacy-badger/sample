<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Inactive Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Go to Active Post');

$I->click(['xpath' => '//div/a[@href="?filter[post_active]=0"]']);
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=0');

$I->click(['xpath' => '//tr/td/a[@href="/control/post/restore/8?redirect_uri=%2Fcontrol%2Fpost%2Fsearch%3Ffilter%5Bpost_active%5D%3D0"]']);

$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_active%5D=0');
$I->see('Post was Restored');