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
$I->amGoingTo('Delete Profile');

// submit form
$I->click(['xpath' => '//tr/td/a[@href="/control/profile/remove/2?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);

$I->seeInCurrentUrl('/control/profile/search');

$I->wait(2);
$I->see('Profile was Removed');

$I->amOnPage('/control/history/search');

$I->seeInCurrentUrl('/control/history/search');

$I->see('Profile id #1 removed profile id #2');
