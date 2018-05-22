<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Add Availability');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/interview/settings');

$I->seeInCurrentUrl('/profile/interview/settings');

$I->see('Interview Scheduler');

$I->click('.page-interview-scheduler .actions a[data-do="availability-add-modal"]');

$I->wait(5);

$I->see('Add Availability');

$I->executeJS('$(\'input[name="start_date"]\').removeAttr(\'readonly\');');

$I->executeJS('$(\'input[name="end_date"]\').removeAttr(\'readonly\');');

$I->fillField('.page-interview-scheduler #interview-availability-add-clone .availability-dates .clearfix.form-group input[name="start_date"]', 'April 26, 2018');

$I->fillField('.page-interview-scheduler #interview-availability-add-clone .availability-dates .clearfix.form-group input[name="end_date"]', 'April 27, 2018');

$I->click('.page-interview-scheduler #interview-availability-add-clone .modal-footer button[data-do="availability-add"]');

// $I->wait(3);

// $I->see('Interview Schedules successfully added');