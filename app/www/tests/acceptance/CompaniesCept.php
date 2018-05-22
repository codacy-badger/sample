<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Job Companies');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Job-Search-Companies');
// $I->see('Companies');
// $I->click('//ul/li/a[@href="/Job-Search-Companies"]');

$I->seeInCurrentUrl('/Job-Search-Companies');
$I->see('All job opportunities');

// $I->see('Google Maps');
// $I->click('.page-post-search article#post-3 a.interested');
// $I->wait(5);
// $I->see('User is being notified of your interest');