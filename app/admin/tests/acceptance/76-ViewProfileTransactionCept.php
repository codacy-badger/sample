<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('View Profile Transaction');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('View Profile Transaction');

$I->click(['xpath' => '//a[@href="/control/transaction/search?filter[profile_id]=5"]']);
$I->seeInCurrentUrl('/control/transaction/search?filter%5Bprofile_id%5D=5');