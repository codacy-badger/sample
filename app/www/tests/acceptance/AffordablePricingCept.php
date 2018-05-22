<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Services and Pricing');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Affordable-Pricing');
// $I->see('Services and Pricing');
// $I->click('//ul/li/a[@href="/Affordable-Pricing"]');

$I->seeInCurrentUrl('/Affordable-Pricing');

$I->see('Affordable Pricing, Pay as you go,
No Subscriptions, No Surprise Cost.');

$I->see('Address your immediate job hiring needs with Jobayan’s low-cost job post packages and services catered as an inexpensive yet powerful resource for different types of businesses. Our on-the-go job promotion tools bridge seamless solutions to maximize job seeker engagement.');

$I->see('Promoted Post');
$I->see('Sponsored Post');
$I->see('Need more exposure for your job post? Promoting a post is an easy and cost-effective way to reach talents.');
$I->see('Make your job post more visible on Jobayan’s job listings.');
$I->see('Promoted posts are guaranteed increased visibility for a month.');

$I->see('Know your applicants! ');
$I->see('Get immediate updates on potential talent!');
$I->see('It’s nice to know there are new applicants on the way! ');