<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Job');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Homepage');
$I->seeInCurrentUrl('/');
$I->amOnPage('/post/search');

$I->fillField('.page-post-search form.navbar-form.navbar-left div.input-group input[name="q"]','jobayan');
$I->click('form.navbar-form.navbar-left span.input-group-btn button.btn.btn-default');

// search all match
$I->seeInCurrentUrl('/post/search?q=jobayan&location=');
$I->see('All job post matching "jobayan"');

// jobayan latest post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=jobayan&location=&sort=latest"]');
// $I->seeInCurrentUrl('/post/search?q=jobayan&location=&sort=latest');
// $I->see('All job post matching "jobayan"');

// // jobayan popular post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=jobayan&location=&sort=popular"]');
// $I->seeInCurrentUrl('/post/search?q=jobayan&location=&sort=popular');
// $I->see('All job post matching "jobayan"');

// // all popular companies post from jobayan
// $I->click(['xpath' => '//a[@href="/Job-Search-Companies?q=jobayan&location=&sort=popular"]']);
// $I->seeInCurrentUrl('/Job-Search-Companies?q=jobayan&location=&sort=popular');
// $I->see('All job opportunities matching "jobayan"');

// // all popular seekers post from jobayan
// $I->click(['xpath' => '//a[@href="/Job-Seekers-Search?q=jobayan&location=&sort=popular"]']);
// $I->seeInCurrentUrl('/Job-Seekers-Search?q=jobayan&location=&sort=popular');
// $I->see('All job seekers matching "jobayan"');

// filter by type
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_poster', 'Companies');
$I->executeJS('$(\'#type_poster\').click()');
$I->click('.search-filter .search-button button.btn.btn-default');

$I->see('All job opportunities matching "jobayan"');
$I->seeInCurrentUrl('/Job-Search-Companies?q=jobayan');
// reset
// $I->amGoingTo('Reset filters');
// $I->click('.search-filter .search-button a.btn.btn-primary[href="/post/search"]');
// $I->seeInCurrentUrl('/post/search');

// filter by type
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_seeker', 'Seeker');
$I->executeJS('$(\'#type_seeker\').click()');
$I->click('.search-filter .search-button button.btn.btn-default');

$I->see('All job seekers matching "jobayan"');
$I->seeInCurrentUrl('/Job-Seekers-Search?q=jobayan');
// reset
$I->amGoingTo('Reset filters');
$I->click('.search-filter .search-button a.btn.btn-primary[href="/post/search"]');
$I->seeInCurrentUrl('/post/search');