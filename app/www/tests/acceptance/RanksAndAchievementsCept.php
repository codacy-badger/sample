<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ranks and Achievements');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->amOnPage('/Ranks-And-Achievements');
// $I->see('Ranks and Achievements');
// $I->click('//ul/li/a[@href="/Ranks-And-Achievements"]');

$I->seeInCurrentUrl('/Ranks-And-Achievements');

$I->see('RANKS AND ACHIEVEMENTS');
$I->see('Welcome to your reward page! Jobayan offers you points that correspond to ranks that your account can achieve. Explore these achievements and unlock badges to entice job seekers with your engagement.');
$I->see('Earning Points');
$I->see('Ranks');
$I->see('Achievements');