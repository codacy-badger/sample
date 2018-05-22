<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search Job Filter Position');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Homepage');
$I->seeInCurrentUrl('/');
$I->amOnPage('/post/search');

$I->fillField('.page-post-search form.navbar-form.navbar-left div.input-group input[name="q"]','programmer');
$I->click('form.navbar-form.navbar-left span.input-group-btn button.btn.btn-default');


// search all match
$I->seeInCurrentUrl('/post/search?q=programmer&location=');
$I->see('All job post matching "programmer"');

// // programmer latest post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=programmer&location=&sort=latest"]');
// $I->seeInCurrentUrl('/post/search?q=programmer&location=&sort=latest');
// $I->see('All job post matching "programmer"');

// // programmer popular post
// $I->click('//button[@class="btn btn-info dropdown-toggle"]');
// $I->wait(5);
// $I->click('//a[@href="?q=programmer&location=&sort=popular"]');
// $I->seeInCurrentUrl('/post/search?q=programmer&location=&sort=popular');
// $I->see('All job post matching "programmer"');

// // all popular companies post from programmer
// $I->click('//a[@href="/Job-Search-Companies?q=programmer&location=&sort=popular"]');
// $I->seeInCurrentUrl('/Job-Search-Companies?q=programmer&location=&sort=popular');
// $I->see('All job opportunities matching "programmer"');

// // all popular seekers post from programmer
// $I->click('//a[@href="/Job-Seekers-Search?q=programmer&location=&sort=popular"]');
// $I->seeInCurrentUrl('/Job-Seekers-Search?q=programmer&location=&sort=popular');
// $I->see('All job seekers matching "programmer"');

// filter by type poster
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_poster', 'Companies');
$I->executeJS('$(\'#type_poster\').click()');
// filter by industry
$I->see('Industry');
$I->executeJS('$(\'#Jobs-In-Tech\').click()');
//$I->checkOption('.filter-industry .filter-industry-wrapper #Jobs-In-Tech');

$I->click('.search-filter .search-button button.btn.btn-default');
$I->seeInCurrentUrl('/Job-Search-Companies?q=programmer');
$I->see('All job opportunities matching "programmer"');
 
// filter by type seeker
$I->see('Filter by');
// $I->selectOption('.filter-type .filter-by-wrapper input#type_seeker', 'Seeker');
$I->executeJS('$(\'#type_seeker\').click()');
$I->click('.search-filter .search-button button.btn.btn-default');
$I->seeInCurrentUrl('/Job-Seekers-Search?q=programmer');
$I->see('All job seekers matching "programmer"');


// reset
$I->amGoingTo('Reset filters');
$I->click('.search-filter .search-button a.btn.btn-primary[href="/post/search"]');
$I->seeInCurrentUrl('/post/search');
