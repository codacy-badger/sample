<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Job Post In Positions');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Job-Positions/Management-Consultancy-Jobs');
$I->see('Management and Consultancy');
// $I->see('All Posts');

$I->see('Change Position');
$I->click('.page-post-featured select[data-do="featured-select"]');
$I->see('IT and Software');
$I->click('IT and Software');
$I->seeInCurrentUrl('/Job-Positions/It-Software-Jobs');
$I->see('IT and Software');

// $I->click('Sort By');
// $I->see('By Latest');
// $I->see('By Popular');
// $I->see('By Seeker');
// $I->see('By Company');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Latest');
$I->seeInCurrentUrl('/Job-Positions/It-Software-Jobs?sort=latest');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Popular');
$I->seeInCurrentUrl('/Job-Positions/It-Software-Jobs?sort=popular');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Seeker');
$I->seeInCurrentUrl('/Job-Positions/It-Software-Jobs?sort=seeker');
$I->see('All Posts');

$I->click('.page-post-featured button.btn.btn-info.dropdown-toggle');
$I->click('By Company');
$I->seeInCurrentUrl('/Job-Positions/It-Software-Jobs?sort=company');
$I->see('All Posts');