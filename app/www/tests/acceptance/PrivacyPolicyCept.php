<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Privacy Policy');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Privacy-Policy');
// $I->see('Privacy Policy');
// $I->click('//ul/li/a[@href="/Privacy-Policy"]');

$I->seeInCurrentUrl('/Privacy-Policy');

$I->see('Privacy Policy');
$I->see('Our Commitment to Privacy');
$I->see('What Information Do We Collect?');
$I->see('1. Personally Identifiable Information');
$I->see('A. Registration');
$I->see('B. Post Actions and Reactions');
$I->see('C. Credit Card Storage');
$I->see('D. Surveys and Promotions');
$I->see('2. Aggregate Information');
$I->see('Active Information You Choose to Provide');
$I->see('Passive Information Collected');
$I->see('What is a Cookie?');
$I->see('How Do We Use the Information Collected?');
$I->see('Your Information In Relation to Others We Link To');
$I->see('Sharing Information with Advertisers or Other Third Parties');
$I->see('Sharing Information with the Government or As Otherwise Required by Law');
$I->see('How Do We Secure Active Information and Passive Information?');
$I->see('Accessing and Correcting Your Information');
$I->see('Protecting Your Information');
$I->see('Links to Other Websites');
