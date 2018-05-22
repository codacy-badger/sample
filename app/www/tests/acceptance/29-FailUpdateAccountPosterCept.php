<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Update Account Settings');

/**
* Assert Blank name and Email Fields
**/

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/information"]']);
$I->seeInCurrentUrl('/profile/account/information');

// update the form
$I->amGoingTo('update my account settings');
$I->see('Account Settings');
$I->fillField('profile_name', '');
$I->fillField('profile_email', '');
$I->click('//div/div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');
$I->see('Must be a valid email');

/**
* Assert Blank name and Correct Email
**/

$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/information"]']);
$I->seeInCurrentUrl('/profile/account/information');

// update the form
$I->amGoingTo('update my account settings');
$I->see('Account Settings');
$I->fillField('profile_name', '');
$I->fillField('profile_email', 'test@gmail.com');
$I->click('//div/div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Name is required');

/**
* Assert Invalid Email
**/

$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/information"]']);
$I->seeInCurrentUrl('/profile/account/information');

// update the form
$I->amGoingTo('update my account settings');
$I->see('Account Settings');
$I->fillField('profile_name', 'Test');
$I->fillField('profile_email', 'test@test');
$I->click('//div/div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
$I->wait(2);
$I->see('Invalid Parameters');
$I->see('Must be a valid email');

