<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Job Seekers');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Job-Seekers-Search');
// $I->see('Job Seekers');
// $I->click('//ul/li/a[@href="/Job-Seekers-Search"]');

$I->seeInCurrentUrl('/Job-Seekers-Search');
$I->see('All job seekers');

// $I->see('Jack Doe');
// $I->click('.page-post-search article#post-4 a.interested');
// $I->wait(3);
// $I->see('User is being notified of your interest');