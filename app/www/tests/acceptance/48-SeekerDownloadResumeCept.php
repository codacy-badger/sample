<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Download Resume');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
// $I->amOnPage('/profile/resume/search');

// // check active post
// $I->amGoingTo('Download Resume');
// $I->see('My Resumes');
// $I->click('#resume-2 a.download');
// $I->seeInCurrentUrl('/profile/resume/search');