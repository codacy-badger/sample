<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Edit Post');

// redirect to create utm
$I->click(['xpath'=> '//tr/td/a[@href="/control/post/update/7?redirect_uri=%2Fcontrol%2Fpost%2Fsearch"]']);

$I->seeInCurrentUrl('/control/post/update/7?redirect_uri=%2Fcontrol%2Fpost%2Fsearch');
$I->see('Updating Post');

$I->fillField('.page-developer-post-update input[name="post_name"]', 'tech labs');

$I->click('.page-developer-post-update button.btn-primary');

$I->seeInCurrentUrl('/control/post/search');

$I->wait(2);
$I->see('Post was Updated');