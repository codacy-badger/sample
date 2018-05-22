<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ascend,Descend Slug');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/auth/search');
$I->amGoingTo('Ascend,Descend Slug');


$I->wantTo('Click Ascending');
$I->click(['xpath' => '//tr/th/span/a[@href="?order%5Bauth_slug%5D=ASC"]']);
$I->seeInCurrentUrl('/control/auth/search?order%5Bauth_slug%5D=ASC');

$I->wantTo('Click Descending');
$I->click(['xpath' => '//tr/th/span/a[@href="?order%5Bauth_slug%5D=DESC"]']);
$I->seeInCurrentUrl('/control/auth/search?order%5Bauth_slug%5D=DESC');
