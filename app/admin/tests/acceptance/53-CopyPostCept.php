<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Copy Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Copy Post');

// redirect to create utm
$I->click(['xpath'=> '//tr/td/a[@href="/control/post/copy/7?redirect_uri=%2Fcontrol%2Fpost%2Fsearch"]']);

$I->seeInCurrentUrl('/control/post/copy/7?redirect_uri=%2Fcontrol%2Fpost%2Fsearch');
$I->see('Create Post');
// $I->fillField('.page-developer-post-create input[name="post_position"]', 'Mobile Tester');
// $I->click('.page-developer-post-create button.btn-primary');

// $I->seeInCurrentUrl('/control/post/search');

// $I->wait(2);
// $I->see('Post was Created');