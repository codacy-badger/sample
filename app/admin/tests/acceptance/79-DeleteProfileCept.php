<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Click Delete Profile');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/profile/remove/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);

$I->seeInCurrentUrl('/control/profile/search');
$I->wait(2);
$I->see('Profile was Removed');
