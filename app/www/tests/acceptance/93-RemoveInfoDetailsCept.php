<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Remove Information Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// work exp

// $I->amOnPage('/profile/information');

// $I->click('.experience-wrapper button.btn.btn-default.dropdown-toggle');

// $I->see('Delete');

// $I->click('Delete');

// $I->wait(10);

// $I->see('Remove Experience?');

// $I->click('#information-confirm-modal .modal-footer a.btn.btn-default');

// $I->wait(4);

// $I->see('Information successfully updated');

// // education

// $I->click('.education-wrapper button.btn.btn-default.dropdown-toggle');

// $I->see('Delete');

// $I->click('Delete');

// $I->wait(10);

// $I->see('Remove Education?');

// $I->click('#information-confirm-modal .modal-footer a.btn.btn-default');

// $I->wait(4);

// $I->see('Information successfully updated');

// // accomplishment

// $I->click('.accomplishment button.btn.btn-default.dropdown-toggle');

// $I->see('Delete');

// $I->click('Delete');

// $I->wait(10);

// $I->see('Remove Accomplishment?');

// $I->click('#information-confirm-modal .modal-footer a.btn.btn-default');

// $I->wait(4);

// $I->see('Information successfully updated');

// // skills
// $I->click('.skills-list a[data-action="/ajax/skills/remove/PHP/5"]');

// $I->wait(5);

// $I->see('Remove PHP?');

// $I->click('#information-confirm-modal a[data-action="/ajax/skills/remove/PHP/5"]');

// $I->wait(4);

// $I->see('Information successfully updated');