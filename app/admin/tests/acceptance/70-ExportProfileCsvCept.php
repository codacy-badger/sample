<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export Profile CSV');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Export Profile CSV');

// click export
$I->click('.page-admin-profile-search form.form-inline a.export-button');

$I->seeInCurrentUrl('/csv/profile_format.csv');

