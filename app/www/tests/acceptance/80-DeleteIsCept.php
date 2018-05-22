<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Availability');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/interview/settings');

$I->click('.page-interview-scheduler #interview-detail-2 button.btn.dropdown-toggle');

$I->wait(5);

$I->click('.page-interview-scheduler #interview-detail-2 a[data-do="interview-content"]');

$I->wait(5);

$I->see('Deleting Availability');

$I->click('.page-interview-scheduler #confirmation-interview-current .modal-footer button.btn.btn-default');

$I->wait(3);

$I->see('Interview Schedules successfully removed');