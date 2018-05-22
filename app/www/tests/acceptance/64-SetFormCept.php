<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Set ATS Form');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/search');

// check active post
$I->see('Applicant Tracking System');
$I->see('List of Job Posts');

$I->click('#post-12 button.btn.btn-default.btn-plain.dropdown-toggle');
$I->wait(5);

// $I->click('a[data-do="attach-form"][data-form-id="6"]');

$I->click('a[data-do="enable-ats"][data-form-id="6"]');

$I->wait(5);

$I->see('Attach Application Form');

$I->see('You are about to attach');

$I->click('.modal.fade.confirmation-form.in .modal-footer button.btn.btn-default');

$I->wait(1);

$I->see('Application successfully attached');