<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter By Profile ID');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Filter By Profile ID');

// filter by profile id
$I->click(['xpath' => '//tr/td/a[@href="?filter[profile_id]=1"]']);

$I->seeInCurrentUrl('/control/service/search?filter%5Bprofile_id%5D=1');
