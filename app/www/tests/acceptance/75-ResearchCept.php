<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Research');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/research');

$I->see('Know your Worth');
$I->see('Search Job Salaries');

$I->see('Enter a Job title or keyword to learn and compare your salaries.');

// fill fields

$I->fillField('.page-research-search .research-position input[name="position"]','IT And Software');
$I->fillField('.page-research-search .research-location input[name="location"]','Metro Manila');

$I->click('.research-form button.btn.btn-default');

$I->see('IT and Software');

$I->see('Philippines');

$I->see('AVERAGE SALARY PER MONTH');

$I->see('Salary Distribution');

$I->see('Top Reported');

$I->see('Top Paying Companies');

$I->click('a.breadcrumbs');

$I->seeInCurrentUrl('/research');