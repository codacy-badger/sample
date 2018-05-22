<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Education Tracer Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.education-wrapper button.btn.btn-default.dropdown-toggle');

// $I->wait(5);

// $I->see('Edit');

// $I->click('a[data-detail="/ajax/education/detail/5"]');

// $I->wait(10);

// $I->see('Edit Education');

// $I->fillField('#education-modal input[name="education_activity"]','Others');

// $I->click('#education-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');
// $I->see('STI College');
// $I->see('Others');
// $I->see('BSIT');