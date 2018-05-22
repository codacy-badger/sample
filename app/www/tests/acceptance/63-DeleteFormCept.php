<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete ATS Form');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/application/poster/search');

// check active post
$I->see('Applicant Tracking System');
$I->see('Actions');
$I->click('a[data-do="form-delete"][data-id="7"]');

$I->wait(5);
$I->see('Delete Form');
$I->see('Updated ATS Form');
$I->see('Are you sure you want to delete this form?');

$I->click('Delete');

$I->wait(1);
$I->see('Form successfully deleted');