<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Job Filter Location');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Homepage');
$I->seeInCurrentUrl('/');
$I->amOnPage('/post/search');

$I->fillField('.page-post-search form.navbar-form.navbar-left div.input-group input[name="location"]','metro manila');
$I->click('form.navbar-form.navbar-left span.input-group-btn button.btn.btn-default');

// search all match
$I->seeInCurrentUrl('/post/search?q=&location=metro+manila');
$I->see('All job posts in Metro manila');

// // Metro manila latest post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=&location=metro+manila&sort=latest"]');
// $I->seeInCurrentUrl('/post/search?q=&location=metro+manila&sort=latest');
// $I->see('All job posts in Metro manila');

// // Metro manila popular post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=&location=metro+manila&sort=popular"]');
// $I->seeInCurrentUrl('/post/search?q=&location=metro+manila&sort=popular');
// $I->see('All job posts in Metro manila');

// // all popular companies post from Metro manila
// $I->click('//a[@href="/Job-Search-Companies?q=&location=metro+manila&sort=popular"]');
// $I->seeInCurrentUrl('/Job-Search-Companies?q=&location=metro+manila&sort=popular');
// $I->see('All job opportunities in Metro manila');

// // all popular seekers post from Metro manila
// $I->click('//a[@href="/Job-Seekers-Search?q=&location=metro+manila&sort=popular"]');
// $I->seeInCurrentUrl('/Job-Seekers-Search?q=&location=metro+manila&sort=popular');
// $I->see('All job seekers in Metro manila');

// poster type filter
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_poster', 'poster');
$I->executeJS('$(\'#type_poster\').click()');
// $I->seeInCurrentUrl('/post/search?type=poster');
// filter by locations
$I->see('Locations');
$I->executeJS('$(\'#Jobs-In-Manila\').click()');
// $I->checkOption('.filter-location .filter-location-wrapper #Jobs-In-Manila');
$I->click('.search-filter .search-button button.btn.btn-default');

$I->see('All job opportunities in Metro Manila');
$I->seeInCurrentUrl('/Job-Search-Companies?location%5B%5D=Metro+Manila');

// seeker type filter
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_poster', 'seeker');
$I->executeJS('$(\'#type_seeker\').click()');
$I->click('.search-filter .search-button button.btn.btn-default');
$I->seeInCurrentUrl('/Job-Seekers-Search?location%5B%5D=Metro+Manila');
$I->see('All job seekers in Metro Manila');

// reset
$I->amGoingTo('Reset filters');
$I->click('.search-filter .search-button a.btn.btn-primary[href="/post/search"]');
$I->seeInCurrentUrl('/post/search');