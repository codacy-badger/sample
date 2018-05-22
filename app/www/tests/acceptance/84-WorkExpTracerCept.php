<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Work Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.experience-wrapper a[data-modal="#experience-modal"]');
// $I->wait(5);
// $I->see('Add Work Experience');

// $I->fillField('#experience-modal input[name="experience_title"]','Developer');
// $I->fillField('#experience-modal input[name="experience_company"]','Jobayan');
// $I->selectOption('#experience-modal select[name="experience_industry"]','Technology');
// $I->selectOption('#experience-modal select[name="experience_related"]','Yes');
// $I->fillField('#experience-modal input[name="experience_from"]','June 15, 2015');
// $I->fillField('#experience-modal input[name="experience_from"]','September 18, 2017');

// $I->click('#experience-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');

// $I->see('Related to college degree: Yes');
// // $I->see('June 2015 - September 2017');
// $I->see('Developer');
// $I->see('Jobayan');