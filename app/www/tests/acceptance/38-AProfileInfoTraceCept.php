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
// $I->wait(5);
// $I->click('#profile-completeness-modal .modal-footer a.btn.btn-default');

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

// $I->see('Test User');
// $I->see('Caloocan City, Metro Manila');

// // work info

// $I->click('.experience-wrapper a[data-modal="#experience-modal"]');
// $I->wait(5);
// $I->see('Add Work Experience');

// $I->fillField('#experience-modal input[name="experience_title"]','Developer');
// $I->fillField('#experience-modal input[name="experience_company"]','Jobayan');
// $I->selectOption('#experience-modal select[name="experience_industry"]','Technology');
// $I->selectOption('#experience-modal select[name="experience_related"]','Yes');
// $I->fillField('#experience-modal input[name="experience_from"]','June 15, 2015');
// $I->fillField('#experience-modal input[name="experience_from"]','September 18, 2017');

// $I->click('#experience-modal .modal-footer button.btn.btn-default');

// $I->wait(10);
// $I->see('Related to college degree: Yes');
// $I->see('Developer');
// $I->see('Jobayan');

// // school info
// $I->click('.education-wrapper a[data-modal="#education-modal"]');
// $I->wait(5);
// $I->see('Add Education');

// $I->fillField('#education-modal input[name="education_school"]','STI College');
// $I->fillField('#education-modal input[name="education_degree"]','BSIT');
// $I->fillField('#education-modal input[name="education_activity"]','ITSA');
// $I->fillField('#education-modal input[name="education_from"]','June 06, 2011');
// $I->fillField('#education-modal input[name="education_to"]','May 15, 2015');

// $I->click('#education-modal .modal-footer button.btn.btn-default');
// $I->wait(10);
// $I->see('STI College');
// $I->see('ITSA');
// $I->see('BSIT');

// // accomplishment
// $I->click('.accomplishment a[data-modal="#accomplishment-modal"]');
// $I->wait(5);
// $I->see('Add Accomplishment');

// $I->fillField('#accomplishment-modal input[name="accomplishment_name"]','Accomplishment 1');
// $I->fillField('#accomplishment-modal textarea[name="accomplishment_description"]','Accomplishment Detail');
// $I->executeJS('$(\'.datepicker\').removeAttr(\'readonly\');');
// $I->fillField('#accomplishment-modal input[name="accomplishment_from"]','June 06, 2011');
// $I->fillField('#accomplishment-modal input[name="accomplishment_to"]','May 15, 2015');
// $I->click('#accomplishment-modal .modal-footer button.btn.btn-default');

// $I->wait(10);
// $I->see('Accomplishment 1');
// $I->see('Accomplishment Detail');
// $I->see('80% Complete');

// // add skills

// $I->click('.skills a[data-modal="#skills-modal"]');

// $I->wait(5);

// $I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="information_skills[]" value="PHP" />\')');
// $I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="information_skills[]" value="HTML" />\')');
// $I->executeJS('$(\'.tag-field\').append(\'<input type="hidden" name="information_skills[]" value="cSS" />\')');

// $I->click('#skills-modal .modal-footer button.btn.btn-default');
// $I->wait(10);
// $I->see('HTML');
// $I->see('PHP');
// $I->see('CSS');
// $I->see('100% Complete');