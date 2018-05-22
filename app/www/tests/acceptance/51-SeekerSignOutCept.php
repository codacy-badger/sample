<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Sign out Seeker');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

// logout
$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/post/search');

$I->see('Sign Out');
$I->click('.menu-my-account .menu-content a[href="/logout"]');

$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// $I->wait(2);
// $I->see('Log Out Successful');
