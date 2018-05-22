<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Update Profile Information');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');
// $I->wait(5);

// $I->click('#information-update-modal a[data-modal="information-modal"]');

// $I->wait(5);

// $I->see('Edit Personal Information');

// $I->fillField('#information-modal input[name="information_heading"]','Im a kick-ass Web Developer with 4 years of solid programming experience');
// $I->selectOption('#information-modal select[name="profile_gender"]','Male');
// $I->selectOption('#information-modal select[name="information_civil_status"]','Single');
// $I->fillField('#information-modal input[name="profile_phone"]','09999999999');
// $I->executeJS('$(\'input[name="profile_birth"]\').removeAttr(\'readonly\');');
// $I->fillField('#information-modal input[name="profile_birth"]','November 01, 2005');

// $I->fillField('#information-modal input[name="profile_address_street"]','1552 Shaw Blvd, Pleasant Hills, Mandaluyong');

// $I->selectOption('#information-modal select[name="profile_address_state"]','Metro Manila');

// $I->selectOption('#information-modal select[name="profile_address_city"]','Caloocan City');

// $I->fillField('#information-modal input[name="profile_address_postal"]','12345');

// $I->click('#information-modal .modal-footer button.btn.btn-default');

// $I->wait(5);

// $I->amOnPage('/profile/information');

// $I->see('Caloocan City, Metro Manila');