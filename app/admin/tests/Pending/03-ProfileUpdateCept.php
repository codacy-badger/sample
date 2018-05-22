<?php //-->
use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);

$I->wantTo('Update a Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/profile/search');
$I->expectTo('see profile list');
$I->see('Profiles');

$I->expect('Update Profile');
$I->amOnPage('/admin/profile/update/1');

$I->amGoingTo('update a profile');
$I->fillField('profile_phone', '123456');
$I->click('form button');

$I->expect('Profile Updated');
$I->see('Profile was Updated');
