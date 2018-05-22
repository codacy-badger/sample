<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Remove Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Remove Profile');
$I->amOnPage('/admin/profile/search');
$I->expectTo('see profile list');
$I->see('Profiles');

$I->amGoingTo('search profile');
$I->fillField('q[]', 'newprofile@gmail.com');
$I->click('form button');

//remove profile
$I->click('.text-danger.remove');
