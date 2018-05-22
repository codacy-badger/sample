<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update ATS Form');

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
$I->click('a[href="/profile/tracking/application/poster/update/6"]');

$I->seeInCurrentUrl('/profile/tracking/application/poster/update/6');

$I->click('a[data-do="form-name-change"]');
$I->wait(2);

$I->fillField('.page-tracking-application-poster-update .detail-form .form-title .form-name-input input[name="form_name"]','Updated ATS Form');

$I->click('.page-tracking-application-poster-update .form-title a.btn.btn-default');

$I->wait(1);
$I->see('Form title successfully updated');

$I->click('.page-tracking-application-poster-update #question-31 .form-action .btn-group button.btn.btn-default.dropdown-toggle');
$I->wait(2);

$I->click('a[data-do="question-edit"][data-id="31"]');

$I->wait(5);
$I->see('Custom Question');

$I->executeJS('$(\'#form-custom-6\').click();');

$I->click('.page-tracking-application-poster-update .form-custom-question.in button.btn.btn-default');

$I->wait(1);
$I->see('Question was successfully edited');

$I->seeInCurrentUrl('/profile/tracking/application/poster/update/6');

$I->click('.content .top a[href="/profile/tracking/application/poster/search"]');

$I->seeInCurrentUrl('/profile/tracking/application/poster/search');