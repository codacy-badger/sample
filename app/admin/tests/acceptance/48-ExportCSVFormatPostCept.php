<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Export CSV Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Export CSV Details');

// click export
$I->click('.page-admin-post-search form.form-inline a.export-button');

$I->seeInCurrentUrl('/csv/post_format.csv');

