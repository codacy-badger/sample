<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Availability');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/interview/settings');

// $I->click('.page-interview-scheduler #interview-detail-12 button.btn.dropdown-toggle');

// $I->wait(5);

// $I->click('.page-interview-scheduler #interview-detail-12 a[data-do="availability-edit-modal"]');

// $I->wait(5);

// $I->see('Edit Availability');

// $I->fillField('.page-interview-scheduler #interview-availability-edit-clone input[name="slots"]','5');

// $I->click('.page-interview-scheduler #interview-availability-edit-clone button[data-do="availability-edit"]');

// $I->wait(3);

// $I->see('Interview Schedules successfully edited');