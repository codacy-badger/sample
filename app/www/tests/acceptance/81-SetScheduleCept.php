<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Set Interview Schedule');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/interview/calendar?filter%5Bdates%5D%5Bstart_date%5D=2018-04-23');

// $I->see('Interview Scheduler');

// $I->click('.page-interview-scheduler .detail-action a[data-id="2018-04-26"]');

// $I->wait(5);

// $I->click('a[href="/profile/interview/schedule?interview=12"]');

// $I->seeInCurrentUrl('/profile/interview/schedule?interview=12');

// $I->selectOption('.page-interview-scheduler select[name="post_id"]','It And Software Is Ats');

// $I->wait(5);

// $I->selectOption('.page-interview-scheduler select[name="profile_id"]','Test User');

// $I->wait(5);

// $I->click('.page-interview-scheduler .actions a[data-do="schedule-interview"]');

// $I->wait(10);

// $I->amOnPage('/profile/interview/calendar?filter%5Bdates%5D%5Bstart_date%5D=2018-04-23');

// $I->see('Test User');