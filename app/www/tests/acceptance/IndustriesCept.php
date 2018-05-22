<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Job Post In Industries');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Job-Industries/Jobs-In-Startup');
$I->see('Start Up');
// $I->see('All Posts');

// $I->see('Change Industry');
// $I->click('.page-post-featured select[data-do="featured-select"]');
// $I->see('Technology');
// $I->click('Technology');
// $I->seeInCurrentUrl('/Job-Industries/Jobs-In-Tech');
// $I->see('Technology');
// $I->click('Sort By');
// $I->see('By Latest');
// $I->see('By Popular');
// $I->see('By Seeker');
// $I->see('By Company');
// $I->see('All Posts');

// $I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
// $I->click('By Latest');
// $I->seeInCurrentUrl('/Job-Industries/Jobs-In-Tech?sort=latest');
// $I->see('All Posts');

// $I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
// $I->click('By Popular');
// $I->seeInCurrentUrl('/Job-Industries/Jobs-In-Tech?sort=popular');
// $I->see('All Posts');

// $I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
// $I->click('By Seeker');
// $I->seeInCurrentUrl('/Job-Industries/Jobs-In-Tech?sort=seeker');
// $I->see('All Posts');

// $I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
// $I->click('By Company');
// $I->seeInCurrentUrl('/Job-Industries/Jobs-In-Tech?sort=company');
// $I->see('All Posts');