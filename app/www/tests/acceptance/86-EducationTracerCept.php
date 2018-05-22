<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Education Tracer Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.education-wrapper a[data-modal="#education-modal"]');

// $I->wait(5);
// $I->see('Add Education');

// $I->fillField('#education-modal input[name="education_school"]','STI College');
// $I->fillField('#education-modal input[name="education_degree"]','BSIT');
// $I->fillField('#education-modal input[name="education_activity"]','ITSA');
// $I->fillField('#education-modal input[name="education_from"]','June 06, 2011');
// $I->fillField('#education-modal input[name="education_to"]','May 15, 2015');

// $I->click('#education-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');
// $I->see('STI College');
// $I->see('ITSA');
// $I->see('BSIT');