<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Send Claim Email Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Click Send Claim Email Profile');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/profile/claim/3?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);

// $I->wait(5);
// $I->see('Claim email has been sent successfully');
