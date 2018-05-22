<?php
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Job Seekers');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/seeker/search');

// fill fields
$I->amGoingTo('Search Job Seekers');
$I->see('Job Seeker Search');
$I->fillField('.page-profile-seeker-search form.form-inline input.form-control','jack doe');
$I->click('.page-profile-seeker-search form.form-inline button.btn.btn-default');

$I->seeInCurrentUrl('/profile/seeker/search?q=jack+doe');