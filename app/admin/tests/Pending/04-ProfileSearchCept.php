<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/profile/search');
$I->expectTo('see profile list');
$I->see('Profiles');


$I->amGoingTo('search profile');
$I->fillField('q[]', 'john');
$I->click('form button');

$I->expectTo('see the name');
$I->see('John Doe');
