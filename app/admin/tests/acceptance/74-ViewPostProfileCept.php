<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('View Profile Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('View Profile Post');

$I->click(['xpath' => '//a[@href="/control/post/search/?profile=5"]']);
$I->seeInCurrentUrl('/control/post/search/?profile=5');