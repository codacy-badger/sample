<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Public View');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

// logout
$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/post/search');

$I->see('Public View');
$I->amGoingTo('Click Public View');
$I->click('.menu-content a[href="/Test-User-u1/profile-post"]');

$I->seeInCurrentUrl('/Companies/Test-User-u1');
// $I->see('test');
// $I->see('http://www.google.com');

//	Jobayan tips arrangement
// $I->click('//div/a[@data-do="arrangement-full"]');
// $I->wait(2);
// $I->see('Post was Updated');
// $I->wait(3);


// //  promote post
// $I->click('//a[@data-do="promote-post"]');
// $I->wait(1);
// // $I->see('You earned 500 experience points');
// $I->see('Promote Post Success');

// // sms notification
// $I->click('//a[@data-do="sms-notification"]');
// $I->wait(2);
// $I->see('SMS Interest Notification Success');


// // sms interest
// $I->click('a[data-do="sms-interest"]');
// $I->wait(2);
// $I->see('SMS Interest Notification Success');

// // edit post
// $I->click('//a[@href="/post/update/poster/9?redirect_uri=/Companies/Test-User-u1"]');
// $I->wait(2);
// $I->seeInCurrentUrl('/post/update/poster/9?redirect_uri=/Companies/Test-User-u1');
// $I->fillField('.page-post-update div.form-group input[name="post_name"]','Openovate Tester2');
// $I->click('//button[@data-do="submit-post"]');

// // remove post
// // $I->click('//a[@href="/post/remove/7?redirect_uri=/post/search"]');
// // $I->wait(2);
// // $I->see('Post was Removed');

// $I->seeInCurrentUrl('/post/search');
// $I->wait(2);
// $I->see('Post was Updated');
// $I->see('All job opportunities and job seekers');
