<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update IS Contact Info');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/interview/settings');

$I->see('Interview Scheduler');

$I->click('a[data-do="contact-detail-change"]');

$I->wait(5);

$I->fillField('.page-interview-scheduler .contact-number input[name="contact_number"]', '09999999999');

$I->fillField('.page-interview-scheduler .contact-edit textarea[name="contact_address"]', '1552 Shaw Blvd, Pleasant Hills, Mandaluyong, 1552 Metro Manila');

$I->click('.page-interview-scheduler .contact-actions a[data-do="contact-detail-update"]');

$I->wait(5);

$I->seeInCurrentUrl('/profile/interview/settings');