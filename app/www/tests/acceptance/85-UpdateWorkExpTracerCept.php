<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Work Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.experience-wrapper button.btn.btn-default.dropdown-toggle');

// $I->wait(5);

// $I->see('Edit');

// $I->click('a[data-detail="/ajax/experience/detail/5"]');

// $I->wait(10);

// $I->see('Edit Work Experience');

// $I->fillField('#experience-modal input[name="experience_title"]','Web Developer');

// $I->click('#experience-modal .modal-footer button.btn.btn-default');

// $I->wait(5);
// // $I->wait(3);
// // $I->see('Information successfully updated');
// $I->amOnPage('/profile/information');

// $I->see('Related to college degree: Yes');
// // $I->see('June 2015 - September 2017');
// $I->see('Web Developer');
// $I->see('Jobayan');