<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Answer Applicant Form');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

$I->amOnPage('/');
$I->seeInCurrentUrl('/');

$I->amOnPage('/profile/tracking/application/seeker/search');

$I->see('Applicant Form');

$I->see('Application Listing');

$I->see('ATS Test');

$I->see('IT and Software');

$I->click('a[href="/profile/tracking/application/seeker/update/12/6"]');

$I->seeInCurrentUrl('/profile/tracking/application/seeker/update/12/6');

$I->see('Updated ATS Form');

// $I->executeJS('$(\'#31-question-3\').click()');

// $I->click('.form-submit button.btn.btn-default');

// $I->wait(2);

// $I->see('Application was successfully submitted');