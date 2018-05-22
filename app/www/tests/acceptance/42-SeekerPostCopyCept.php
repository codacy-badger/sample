<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Copy a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->click(['xpath' => '//div/a[@href="/post/copy/10?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);
$I->see('Create a New Post');

$I->seeInCurrentUrl('/post/copy/10?redirect_uri=%2Fprofile%2Fpost%2Fsearch');
$I->fillField('.page-post-create div.form-group input[name="post_name"]','Tester');
$I->fillField('.page-post-create input[name="post_phone"]','10004567');

$I->click('.page-post-create button.submit');
$I->seeInCurrentUrl('/post/search');
$I->see('Post was Created');

$I->see('All job opportunities and job seekers');