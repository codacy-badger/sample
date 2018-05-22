<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Add Resume');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
// $I->amOnPage('/profile/resume/search');

// check active post
// $I->amGoingTo('Add Resume');
// $I->see('My Resumes');
// $I->click(['xpath' => '//a[@class="btn btn-default text-uppercase"]']);
// $I->wait(5);
// $I->executeJs('$(\'.file-upload input[type="file"]\').removeClass(\'hide\');');
// $I->attachFile('.file-upload input[type="file"]', 'CV-Templates-Curriculum-Vitae.pdf');
// $I->fillField('.page-profile-resume-search #sendResume input[name="resume_position"]', 'Resume Test');
// $I->click('//button[@data-do="file-upload"]','#sendResume');
// // $I->click('//div/button[@data-do="file-upload"]');
// $I->seeInCurrentUrl('/profile/resume/search');
// $I->wait(1);
// // $I->see('Please upload a file');
// $I->see('Resume was uploaded');
// $I->see('Resume Test');