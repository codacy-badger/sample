<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Accomplishment Tracer Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.accomplishment button.btn.btn-default.dropdown-toggle');

// $I->wait(5);

// $I->see('Edit');

// $I->click('a[data-detail="/ajax/accomplishment/detail/5"]');

// $I->wait(10);

// $I->see('Edit Accomplishment');

// $I->fillField('#accomplishment-modal input[name="accomplishment_name"]','Accomplishment for today');

// $I->click('#accomplishment-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');
// $I->see('Accomplishment for today');
// $I->see('Accomplishment Detail');