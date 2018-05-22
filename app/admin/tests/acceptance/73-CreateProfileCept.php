<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Click Create New Profile');

// redirect to create utm
$I->click(['xpath'=> '//a[@href="/control/profile/create"]']);

$I->seeInCurrentUrl('/control/profile/create');
$I->see('Create Profile');

$I->fillField('.page-developer-profile-create input[name="profile_name"]', 'tech labs');
$I->fillField('.page-developer-profile-create input[name="profile_email"]', 'test@email.com');
$I->fillField('.page-developer-profile-create input[name="profile_phone"]', '554-4321');
$I->fillField('.page-developer-profile-create input[name="profile_company"]', 'tech labs');
$I->fillField('.page-developer-profile-create input[name="profile_address_street"]', '123 Sesame Street');
$I->fillField('.page-developer-profile-create input[name="profile_address_city"]', 'Makati City');
$I->fillField('.page-developer-profile-create input[name="profile_address_state"]', 'Metro Manila');

$I->click('.page-developer-profile-create button.btn-primary');

$I->seeInCurrentUrl('/control/profile/search');

$I->wait(2);
$I->see('Profile was Created');