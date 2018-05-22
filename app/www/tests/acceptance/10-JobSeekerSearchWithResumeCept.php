<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Job Seekers With Resume');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->seeInCurrentUrl('/');
$I->amOnPage('/profile/seeker/search');

// with resume seekers
$I->click('.btn-group > a.btn-default');

$I->seeInCurrentUrl('/profile/seeker/search?has_resume=1');
$I->see('Job Seeker Search');