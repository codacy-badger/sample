<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

$I->amOnPage('/profile/post/search');

// check active post
$I->amGoingTo('check active post');
$I->click(['xpath' => '//a[@href="?filter[post_active]=1"]']);

$I->seeInCurrentUrl('/profile/post/search?filter%5Bpost_active%5D=1');
$I->see('Post');
