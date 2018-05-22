<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Schedule Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/interview/calendar?filter%5Bdates%5D%5Bstart_date%5D=2018-04-23');

// $I->click('.page-interview-scheduler .setting-profile-preview #profile-schedule-2');

// $I->wait(5);

// $I->click('.page-interview-scheduler #interview-schedule-2  button.btn.dropdown-toggle');

// $I->wait(5);

// $I->click('.page-interview-scheduler a[data-do="calendar-reschedule"][data-id="2"]');

// $I->wait(5);

// // $I->see('Reschedule Interview');

// // $I->see('Date and Time');

// $I->executeJS('$(\'input[name="interview_setting_id"]\').val(\'13\');');

// $I->click('#interview-reschedule-clone button[data-do="reschedule-interview"]');