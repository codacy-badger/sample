<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Update Account Settings');

/**
* Assert Blank name and Email Fields
**/

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/password"]']);
$I->seeInCurrentUrl('/profile/account/password');

// update the form
$I->amGoingTo('Change Password');
$I->see('Account Settings');
$I->fillField('auth_password', '');
$I->fillField('confirm', '');
$I->click('//div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(1);
$I->see('Invalid Parameters');
$I->see('Cannot be empty');
$I->see('Must be at least 7 characters long');


/**
*
* Common Password
**/

$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/password"]']);
$I->seeInCurrentUrl('/profile/account/password');

// update the form
$I->amGoingTo('Change Password');
$I->see('Account Settings');
$I->fillField('auth_password', 'test123');
$I->fillField('confirm', 'test123');
$I->click('//div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(1);
$I->see('Invalid Parameters');
$I->see('Password too common!');

/**
*
* below 7 characters
**/

$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/password"]']);
$I->seeInCurrentUrl('/profile/account/password');

// update the form
$I->amGoingTo('Change Password');
$I->see('Account Settings');
$I->fillField('auth_password', 'test');
$I->fillField('confirm', 'test');
$I->click('//div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(1);
$I->see('Invalid Parameters');
$I->see('Must be at least 7 characters long');

/**
*
* password does not match
**/

$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/password"]']);
$I->seeInCurrentUrl('/profile/account/password');

// update the form
$I->amGoingTo('Change Password');
$I->see('Account Settings');
$I->fillField('auth_password', 'testtest123');
$I->fillField('confirm', 'testpassword123');
$I->click('//div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(1);
$I->see('Invalid Parameters');
$I->see('Passwords do not match');