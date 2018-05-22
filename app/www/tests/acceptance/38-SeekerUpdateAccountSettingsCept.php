<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Account Settings');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

$I->amOnPage('/');
$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/account?redirect_uri=%2F');

// redirect to edit
$I->click(['xpath' => '//div/a[@href="/profile/account/information"]']);
$I->seeInCurrentUrl('/profile/account/information');

// update the form
$I->amGoingTo('update my account settings');
// $I->see('Account Settings');
$I->fillField('profile_website', 'http://www.google.com');
$I->click('//div/div/button[@class="btn btn-default text-uppercase"]');

$I->seeInCurrentUrl('/profile/account');

// notif
// $I->see('Update Successful');
// $I->see('You earned 15 experience points');