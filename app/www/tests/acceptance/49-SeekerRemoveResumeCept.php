<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Remove Resume');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
// $I->amOnPage('/profile/resume/search');

// // check active post
// $I->amGoingTo('Remove Resume');
// $I->see('My Resumes');
// $I->click('#resume-2 a.btn.btn-remove');
// $I->wait(5);


// $I->see('Confirmation');
// $I->see('Are you sure you want to remove resume?');
// $I->click('#confirm-modal .modal-footer a.btn.btn-primary');
// $I->wait(2);

// $I->see('Resume was Removed');