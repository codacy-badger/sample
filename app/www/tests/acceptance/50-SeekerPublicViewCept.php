<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Go to Public View');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

// logout
$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/post/search');

$I->see('Public View');
$I->amGoingTo('Click Public View');
$I->click('.menu-content a[href="/Test-User-u5/profile-post"]');

$I->seeInCurrentUrl('/Job-Seekers/Test-User-u5');
// $I->see('Test User');
// $I->see('http://www.google.com');
// $I->see('Joined January 2018');