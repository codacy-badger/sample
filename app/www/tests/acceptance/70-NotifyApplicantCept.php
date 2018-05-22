<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create new label');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// //  redirect page
// $I->amOnPage('/profile/tracking/post/search');

// $I->click('button[data-form-id="6"][data-post-id="7"]');

// $I->wait(3);

// $I->see('Application was successfully submitted');