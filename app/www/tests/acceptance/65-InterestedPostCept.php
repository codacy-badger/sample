<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Interested Post for ATS');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();

$I->amOnPage('/');
$I->seeInCurrentUrl('/');
$I->amOnPage('/post/search');

$I->see('All job opportunities and job seekers');

$I->see('ATS Test');

$I->see('Hello! We are ATS Test from Metro Manila');

$I->click('a[data-id="12"]');

$I->wait(5);

$I->see('APPLY FOR THIS POSITION');

$I->executeJS('$(\'label[for="31-question-0"]\').click();');

$I->click('button[data-id="12"]');

// $I->attachFile('//div[@class="resume-content choose-file"]', 'CV-Templates-Curriculum-Vitae.pdf

// $I->executeJS('$(\'.upload-group input.hide\').removeClass(\'hide\')');

// $I->attachFile('.upload-group input[data-do="file-uploading"]', 'CV-Templates-Curriculum-Vitae.pdf');

// $I->see('1 file selected');

// $I->see('What is your preferred working hours?');

// $I->executeJS('$(\'#31-question-3\').click()');

// $I->click('button.btn.btn-default.btn-file-send');
// $I->click('a#no-thanks');

$I->wait(1);

$I->see('Application was successfully submitted');
// $I->see('Resume was uploaded');
// $I->see('Application was successfully submitted');