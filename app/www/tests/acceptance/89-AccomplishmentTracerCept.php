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

// $I->click('.accomplishment a[data-modal="#accomplishment-modal"]');

// $I->wait(5);

// $I->see('Add Accomplishment');

// $I->fillField('#accomplishment-modal input[name="accomplishment_name"]','Accomplishment 1');
// $I->fillField('#accomplishment-modal textarea[name="accomplishment_description"]','Accomplishment Detail');
// $I->executeJS('$(\'.datepicker\').removeAttr(\'readonly\');');
// $I->fillField('#accomplishment-modal input[name="accomplishment_from"]','June 06, 2011');
// $I->fillField('#accomplishment-modal input[name="accomplishment_to"]','May 15, 2015');

// $I->click('#accomplishment-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');
// $I->see('Accomplishment 1');
// $I->see('Accomplishment Detail');