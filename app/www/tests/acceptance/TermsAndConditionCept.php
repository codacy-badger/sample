<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Terms and Conditions');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Terms-And-Conditions');
// $I->see('Terms and Conditions');
// $I->click('//ul/li/a[@href="/Terms-And-Conditions"]');

$I->seeInCurrentUrl('/Terms-And-Conditions');

$I->see('Terms and Conditions');
$I->see('ACCEPTANCE OF TERMS THROUGH USE');
$I->see('YOU MUST BE 18 AND ABOVE TO AGREE TO THIS AGREEMENT AND USE THIS SITE');
$I->see('LICENSE TO USE THIS SITE');
$I->see('LICENSE RESTRICTIONS USE');
$I->see('SECURITY');
$I->see('EXPORT');
$I->see('GOVERNMENT USE');
$I->see('ERRORS AND CORRECTIONS');
$I->see('LINKS TO OTHER WEBSITES');
$I->see('USER CONDUCT');
$I->see('INTELLECTUAL PROPERTY RIGHTS');
$I->see('DISCLAIMER OF WARRANTIES');
$I->see('LIMITATION OF LIABILITY');
$I->see('INDEMNIFICATION');
$I->see('LEGAL COMPLIANCE');
$I->see('MISCELLANEOUS');
