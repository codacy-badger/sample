<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Logout to the site');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

// logout
$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/post/search');

$I->see('Sign Out');
$I->click('.menu-credits.text-center .sign-out-wrapper a[href="/logout"]');

$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// $I->wait(2);
// $I->see('Log Out Successful');
