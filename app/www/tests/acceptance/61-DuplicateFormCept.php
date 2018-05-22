<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Duplicate ATS Form');

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
$I->click('a[data-do="form-duplicate"][data-id="6"]');

$I->wait(5);
$I->see('Duplicate Form');
$I->see('Test ATS Form');
$I->see('Are you sure you want to duplicate this form?');

$I->click('Duplicate');
$I->wait(1);
$I->see('Form successfully duplicated');
$I->wait(2);
$I->seeInCurrentUrl('/profile/tracking/application/poster/update/8');

$I->click('Publish');
$I->wait(5);
$I->see('Publish Form');
$I->see('Are you sure you want to publish this form?');

$I->click('.page-tracking-application-poster-update .confirmation-form.in button.btn.btn-default');
$I->wait(1);
$I->see('Successfully published form');
$I->wait(5);
$I->seeInCurrentUrl('/profile/tracking/application/poster/search');
