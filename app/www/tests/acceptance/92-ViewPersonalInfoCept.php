<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Personal Information Tracer');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.page-profile-information-account  a[data-target="#information-view-modal"]');

// $I->wait(5);

// $I->see('Test User');

// $I->click('#information-view-modal .modal-footer button');