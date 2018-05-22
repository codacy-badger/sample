<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create a Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/profile/search');
$I->expectTo('see profile list');
$I->see('Profiles');

$I->amGoingTo('go to add profile page.');
$I->click('Create New Profile');

$I->expect('Create Profile');
$I->seeInCurrentUrl('/admin/profile/create');

$I->amGoingTo('add a new profile');
$I->fillField('profile_name', 'New Profile');
$I->fillField('profile_email', 'newprofile@gmail.com');
$I->fillField('profile_phone', '123456');
$I->fillField('profile_detail', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ut ornare nulla, et suscipit mauris.');
$I->fillField('profile_job', 'New Profile Job');
$I->selectOption('form input[name=profile_gender]', 'male');
$I->fillField('profile_birth', '2000-01-01');
$I->click('form button');

$I->expect('Profile Created');
$I->see('Profile was Created');
