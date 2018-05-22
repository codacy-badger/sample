<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Click Edit Profile');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/profile/update/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);

$I->seeInCurrentUrl('/control/profile/update/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch');
$I->see('Updating Profile');

$I->fillField('.page-developer-profile-update input[name="profile_name"]', 'for tester');
$I->fillField('.page-developer-profile-update input[name="profile_address_city"]', 'Makati City');
$I->fillField('.page-developer-profile-update input[name="profile_address_state"]', 'Metro Manila');

$I->click('.page-developer-profile-update button.btn-primary');

$I->wait(2);
$I->see('Profile was Updated');