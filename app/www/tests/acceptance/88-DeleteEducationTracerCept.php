<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Remove Work Exp');

//Login
$loginPage = new LoginPage($I);
$loginPage->loginSeeker();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/information');

// $I->click('.education-wrapper button.btn.btn-default.dropdown-toggle');

// $I->see('Delete');

// $I->click('Delete');

// $I->wait(10);

// $I->see('Remove Education?');

// $I->click('#information-confirm-modal .modal-footer a.btn.btn-default');

// $I->wait(4);

// $I->see('Information successfully updated');
