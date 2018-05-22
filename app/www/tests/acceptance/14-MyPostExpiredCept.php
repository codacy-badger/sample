<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Buy Credits');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/post/search');

// check active post
$I->amGoingTo('check expired post');
$I->click(['xpath' => '//a[@href="?post_expires=-1"]']);

$I->seeInCurrentUrl('/profile/post/search?post_expires=-1');
$I->see('Post');

