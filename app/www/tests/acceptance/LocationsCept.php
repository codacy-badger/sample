<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Job Post In Locations');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Job-Locations/Jobs-In-Bicol');
$I->see('Bicol');
// $I->see('All Posts');

$I->see('Change Location');
$I->click('.page-post-featured select[data-do="featured-select"]');
$I->see('Metro Manila');
$I->click('Metro Manila');
$I->seeInCurrentUrl('/Job-Locations/Jobs-In-Manila');
$I->see('Manila');

// $I->click('Sort By');
// $I->see('By Latest');
// $I->see('By Popular');
// $I->see('By Seeker');
// $I->see('By Company');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Latest');
$I->seeInCurrentUrl('/Job-Locations/Jobs-In-Manila?sort=latest');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Popular');
$I->seeInCurrentUrl('/Job-Locations/Jobs-In-Manila?sort=popular');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Seeker');
$I->seeInCurrentUrl('/Job-Locations/Jobs-In-Manila?sort=seeker');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Company');
$I->seeInCurrentUrl('/Job-Locations/Jobs-In-Manila?sort=company');
$I->see('All Posts');