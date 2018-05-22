<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Blog');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Delete Blog Details');

$I->click(['xpath' => '//tr/td/a[@href="/control/blog/remove/1?redirect_uri=%2Fcontrol%2Fblog%2Fsearch"]']);

$I->seeInCurrentUrl('/control/blog/search');
$I->see('Articles');

$I->wait(1);
$I->see('Article was Removed');
