<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Check Active Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Check Active Profile');

$I->click(['xpath' => '//a[@href="?filter[profile_active]=0"]']);

$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=0');

$I->click(['xpath' => '//tr/td/a[@href="/control/profile/restore/5?redirect_uri=%2Fcontrol%2Fprofile%2Fsearch%3Ffilter%5Bprofile_active%5D%3D0"]']);

$I->seeInCurrentUrl('/control/profile/search?filter%5Bprofile_active%5D=0');
