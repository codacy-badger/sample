<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Fail Research');

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

$I->fillField('.page-research-search .research-position input[name="position"]','jobayantest');
$I->fillField('.page-research-search .research-location input[name="location"]','jobayantest');

$I->click('.research-form button.btn.btn-default');
$I->seeInCurrentUrl('/research');
$I->wait(1);


$I->see('Not Found!');