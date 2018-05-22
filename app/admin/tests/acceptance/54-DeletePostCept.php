<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Delete Post');

// redirect to create utm
$I->click('a[href="/control/post/remove/11?redirect_uri=%2Fcontrol%2Fpost%2Fsearch"]');

$I->wait(2);
$I->see('Post was Removed');
