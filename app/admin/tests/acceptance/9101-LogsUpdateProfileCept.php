<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update new Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
// $I->amGoingTo('Click Update New Profile');

// // redirect to edit utm
// $I->click(['xpath'=> '//tr/td/a[@href="/control/profile/update/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch"]']);

// $I->seeInCurrentUrl('/control/profile/update/1');
// $I->see('Updating Profile');

// // edit details
// $I->fillField('.page-developer-profile-update input[name="profile_name"]', 'Test sample');
// $I->fillField('.page-developer-profile-update input[name="profile_address_city"]', 'Makati City');
// $I->fillField('.page-developer-profile-update input[name="profile_address_state"]', 'Metro Manila');

// // submit form
// $I->click('.page-developer-profile-update button.btn-primary');

// $I->seeInCurrentUrl('/control/profile/search');

// $I->wait(2);
// $I->see('Profile was Updated');

// $I->amOnPage('/control/history/search');

// $I->seeInCurrentUrl('/control/history/search');

// $I->see('Profile id #1 updated profile id 1');



